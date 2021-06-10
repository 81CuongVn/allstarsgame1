// Libraries globally required by the worker
var crypto = require("crypto");
var express = require("express");
var app = express();
var phpjs = require("./phpjs");
var mysql = require("mysql");
var uuid = require("node-uuid");
var	MCrypt = require("mcrypt").MCrypt;
var server = app.listen(process.argv[2], function () {
	console.log("Worker is listening on " + server.address().address + " at port " + server.address().port);
});

function md5(string) {
	return crypto.createHash('md5').update(string).digest('hex');
}

function decrypt(string, key) {
	var decryptor		= new MCrypt('des', 'ecb');
	
	decryptor.open(key);
	return decryptor.decrypt(new Buffer(string, 'base64')).toString('ascii').replace(/\0/g, '');
}

function pool_queries(db, queries, names, cb) {
	var	expect	= queries.length;
	var	returns	= {};
	var	ran		= 0;
	var	current	= 0;

	queries.forEach(function (query) {
		var	return_name	= names[current];
		var	args		= query.concat([function (e, results) {
			returns[return_name]	= results;

			ran++;

			if (ran == expect) {
				cb.apply(null, [returns]);
			}
		}]);

		current++;

		db.query.apply(db, args);
	});
}

var	io				= require('socket.io')(server, {origins: '*:* animeallstarsgame.com.br:* http://animeallstarsgame.com.br:*'});
var	config			= require('./config');

var	IORedis			= require('socket.io-redis');
var redis			= require('redis');
var	pubsub			= IORedis({ host: 'localhost', port: 6379, auth_pass: config.redis_auth });
var db				= null;

var	redis_pub		= redis.createClient();
var	redis_sub		= redis.createClient();
var	redis_client	= redis.createClient();
var	redis_players	= redis.createClient();
var	redis_messages	= redis.createClient();
var	redis_pms		= redis.createClient();

// Authenticate to redis DB
redis_pub.auth(config.redis_auth);
redis_sub.auth(config.redis_auth);
redis_client.auth(config.redis_auth);
redis_players.auth(config.redis_auth);
redis_messages.auth(config.redis_auth);
redis_pms.auth(config.redis_auth);

// connect redis adapter
io.adapter(pubsub);

// origins
io.origins("*:*");

// database
function handle_db() {
	db	= mysql.createConnection({
		host:			'127.0.0.1',
		user:			'chat_cluster',
		password:		'42q0n3Ui1r2uzyv',
		database:		'chat_cluster',
		insecureAuth:	true
	});

	db.connect();

	db.on('error', function(err) {
		console.log('Got db error', err);
		handle_db();
	});
}

io.sockets.on('connection', function (socket) {
	socket.on('join', function (key) {
		redis_players.get('player_' + key, function (e, player) {
			var	player	= JSON.parse(player);

			if(player) {
				player.allowed_channels.forEach(function (channel) {
					socket.join(channel);
					//console.log('Player sucessfuly joined channel -> ' + channel);

					redis_messages.llen('messages_' + channel, function (e, length) {
						redis_messages.lrange('messages_' + channel, 0, length - 1, function (e, messages) {
							messages.forEach(function (broadcast) {
								socket.emit('broadcast', JSON.parse(broadcast));
							});
						});
					});
				});

				var	pm_channel		= player.game_key + '_' + player.data.player;
				var	pm_store_key	= 'pm_store_' + player.game_key + '_' + player.data.player;

				socket.join(pm_channel);
				//console.log('Player joined PM channel ->' + pm_channel);

				redis_pms.get(pm_store_key, function (err, reply) {
					if (reply) {
						var	store	= JSON.parse(reply);

						for(var key in store) {
							if (store[key].state == 'open') {
								redis_pms.get(key, function (err, reply) {
									socket.emit('pminitial', {
										from:		store[key].target,
										who:		store[key].who,
										messages:	JSON.parse(reply)
									});
								});
							}
						}
					}
				});

				redis_players.incr("worker_" + process.argv[2]);
			} else {
				console.log('Invalid player data received at join event')
			}
		});
	});

	socket.on('message', function (message_data) {
		if(!message_data.key) {
			return;
		}

		redis_players.get('player_' + message_data.key, function (e, player) {
			var	player		= JSON.parse(player);
			var	is_allowed	= false;

			player.allowed_channels.forEach(function (channel) {
				if (channel == message_data.channel) {
					is_allowed	= true;
				}
			});

			if (is_allowed) {
				var	channel	= message_data.channel;
				var	decodes	= [];

				if(!player.data.is_master) {
					var safe_message	= phpjs.htmlspecialchars(message_data.message.substr(0, 100));
					safe_message		= safe_message.replace(/[^\u0000-\u00FF]/img, '');
				} else {
					var	safe_message	= message_data.message;
				}

				if(message_data.embeds && message_data.embeds.length) {
					for(var i in message_data.embeds) {
						var	embed	= message_data.embeds[i];

						try {
							var	decoded	= JSON.parse(decrypt(embed[1], player.game_crypto));
						} catch (e) {}

						if (decoded) {
							decodes.push([embed[0], decoded]);
						}
					};
				}

				var broadcast	=	{
					message:	safe_message,
					who:		player.data.name,
					id:			player.data.player,
					when:		new Date(),
					staff:		message_data.staff && player.data.is_master,
					decodes:	decodes,
					filter:		player.filter_channels[message_data.channel],
					color:		player.color
				}

				//console.log('Broadcasting message at channel -> ' + channel);

				io.sockets.in(channel).emit('broadcast', broadcast);
				redis_messages.rpush('messages_' + channel, JSON.stringify(broadcast));
				
				redis_messages.llen('messages_' + channel, function (e, length) {
					if(length > 100) {
						redis_messages.lpop('messages_' + channel);
					}
				});
			} else {
				console.log('Player trying to broadcast into an invalud channel');
			}
		});
	});

	socket.on('pmsend', function (pm_data) {
		if(!pm_data.key) {
			return;
		}

		redis_players.get('player_' + pm_data.key, function (e, player) {
			var	player		= JSON.parse(player);
			var	source_key	= 'pm_store_' + player.game_key + '_' + player.data.player;
			var	target_key	= 'pm_store_' + player.game_key + '_' + pm_data.target;

			var	message		= {
				when:		new Date(),
				content:	phpjs.htmlspecialchars(pm_data.content),
				from:		parseInt(player.data.player)
			};
			
			redis_pms.get(source_key, function (err, reply) {
				var	create_store	= true;
				var store_key		= 'pm_messages_';

				if (reply) {
					var	store		= JSON.parse(reply);

					for (var key in store) {
						if (parseInt(store[key].target) == parseInt(pm_data.target)) {
							store_key		= key;
							create_store	= false;
							break;
						}
					}
				}

				if (create_store) {
					store_key				+= uuid.v4();
					var	source_store		= new Object();
					
					source_store[store_key]	= {
						target:	parseInt(pm_data.target),
						state:	'open',
						who:	pm_data.who
					};

					redis_pms.set(source_key, JSON.stringify(source_store));
					redis_pms.set(store_key, JSON.stringify([message]));
				} else {
					redis_pms.get(store_key, function (err, reply) {
						reply	= JSON.parse(reply);

						if (reply.length >= 50) {
							reply.shift();
						}

						reply.push(message);
						redis_pms.set(store_key, JSON.stringify(reply));
					});
				}

				redis_pms.get(target_key, function (err, reply) {
					if (!reply) {
						var	target_store		= new Object();
						
						target_store[store_key]	= {
							target:	parseInt(player.data.player),
							state:	'open',
							who:	player.data.name
						};

						redis_pms.set(target_key, JSON.stringify(target_store));
					}

					//console.log('Broadcasting PM channel ->' + player.game_key + '_' + pm_data.target)

					io.sockets.in(player.game_key + '_' + pm_data.target).emit('pmreceived', {
						from:		player.data.player,
						who:		player.data.name,
						message:	message
					});
				});
			});
		});
	});

	socket.on('pmclose', function (close_data) {
		if(!close_data.key) {
			return;
		}

		redis_players.get('player_' + close_data.key, function (e, player) {
			var	player		= JSON.parse(player);
			var	source_key	= 'pm_store_' + player.game_key + '_' + player.data.player;

			redis_pms.get(source_key, function (err, reply) {
				if (reply) {
					var	store		= JSON.parse(reply);
					var	changed		= false;

					for (var key in store) {
						if (parseInt(store[key].target) == parseInt(close_data.target)) {
							if (store[key].state != 'closed') {
								store[key].state	= 'closed';
								changed				= true;
							}

							break;
						}
					}

					if (changed) {
						redis_pms.set(source_key, JSON.stringify(store));
					}
				}
			});
		});
	});

	socket.on("disconnect", function () {
		redis_players.decr("worker_" + process.argv[2]);
	});
});