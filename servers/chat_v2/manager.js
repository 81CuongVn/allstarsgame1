var path = require("path");
var config = require("./config");
var	express = require("express");
var mysql = require("mysql");
var expressSession = require("express-session");
var ConnectRedis = require("connect-redis")(expressSession);
var	app = express();
var stylus = require("stylus");
var	nib = require("nib");
var cookieParser = require("cookie-parser");
var bodyParser = require("body-parser");
var favicon = require("serve-favicon");
var crypto = require("crypto");
var	MCrypt = require("mcrypt").MCrypt;
var redis = require('redis');

var db = mysql.createConnection({
	host: config.db.host,
	user: config.db.user,
	password: config.db.password,
	database: config.db.name,
	insecureAuth:	true
});

var	redis_players = redis.createClient();
redis_players.auth(config.redis_auth);

for (var i = 0; i < process.argv[3]; i++) {
	var port = config.base_port + i;

	redis_players.set("worker_" + port, 0);
	console.log("Redis counters reset for worker at port " + port);
}

var server = app.listen(process.argv[2], function () {
	console.log("Manager is listening on " + server.address().address + " at port " + server.address().port);
});

function md5(string) {
	return crypto.createHash('md5').update(string).digest('hex');
}

function decrypt(string, key) {
	var decryptor		= new MCrypt('des', 'ecb');
	
	decryptor.open(key);
	return decryptor.decrypt(new Buffer(string, 'base64')).toString('ascii').replace(/\0/g, '');
}

app.use(expressSession({
	store: new ConnectRedis({
		host: 'localhost',
		port: 6379,
		db: 2,
		pass: config.redis_auth
	}),
	secret: '25NuwYF6z5pB47q',
	resave: true,
	saveUninitialized: true
}));

app.use(cookieParser());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(stylus.middleware({
	src: __dirname + '/public', compile: function (str) {
		return stylus(str)
		.set('filename', path)
		.use(nib());
	}
}));

app.use(express.static(path.join(__dirname, 'public')));
app.use(favicon(__dirname + '/public/assets/favicon.ico'));

app.set('views', __dirname + '/views/');
app.set('view engine', 'jade');

app.get('/register')

app.post('/register', function (req, res) {
	res.header('Content-Type', 'application/json');
	res.header("Access-Control-Allow-Origin", "*");
	res.header("Access-Control-Allow-Headers", "x-requested-with");
	res.status(200);

	var	register	= false;

	//console.log('Register: start');

	db.query('SELECT id, game_key, game_crypto FROM games WHERE id=?', [req.body.game_id], function (e, results) {
		var	got_auth	= false;

		results.forEach(function (result) {
			try {
				register	= JSON.parse(decrypt(req.body.registration, result.game_crypto));

				if (!register.channels) {
					return;
				}

				if (register.key != result.game_key) {
					console.log('Game Key and Game ID mismatch');
					return;
				}

				//console.log('Register: first step passed');
				//console.log(register);

				var	game_id				= result.game_key;
				var	game_key			= result.game_key;
				var game_crypto			= result.game_crypto;
				var	player_key			= md5(register.player + '_' + game_id);
				var	allowed_channels	= [];
				var filter_channels		= {};
				var multi				= redis_players.multi();
				var worker				= null;

				for (var i = 0; i < process.argv[3]; i++) {
					multi.get("worker_" + (config.base_port + i));
				}

				multi.exec(function (err, replies) {
					var counters = [];

					for (var i = 0; i < process.argv[3]; i++) {
						counters.push({
							port: config.base_port + i,
							count: Math.abs(replies[i])
						})
					}

					counters.sort(function (a, b) {
						// signs are inverse
						if (parseInt(a.count) > parseInt(b.count)) return -1;
						if (parseInt(a.count) < parseInt(b.count)) return 1;
						return 0;
					});

					console.log("Balancer data: " + JSON.stringify(counters));

					worker = counters[counters.length - 1];

					db.query('SELECT * FROM game_channels WHERE game_id=?', [result.id], function (e, results) {
						var channels	= [];

						results.forEach(function (result) {
							var	channel	= {
								name:		result.name,
								key:		result.channel_key,
								filter:		result.channel_key,
								subchannel:	result.allow_subchannel,
								color:		result.color
							};

							if (result.allow_subchannel) {
								if (!parseInt(register.channels[result.channel_key])) {
									return;
								} else {
									channel.key	+= '_' + md5(register.channels[result.channel_key]);
								}
							}

							channel.key	+= '_' + md5(game_id);
							channels.push(channel);
							allowed_channels.push(channel.key);
							filter_channels[channel.key]	= channel.filter;
						});

						redis_players.set('player_' + player_key, JSON.stringify({
							game_id:			game_id,
							game_key:			game_key,
							game_crypto:		game_crypto,
							data:				register,
							allowed_channels:	allowed_channels,
							filter_channels:	filter_channels
						}));

						res.send(JSON.stringify({status: 200, key: player_key, channels: channels, server: worker.port}));
					});
				});

				got_auth	= true;
			} catch (ee) {
				console.log(ee);
				console.log('Invalid registration data, key mismatch');
			}
		});

		if (!got_auth) {
			console.log('Register: invalid registration data');

			res.send(JSON.stringify({status: 500, messages: ['Invalid registration data']}));
		}
	});
});

// Admin services -->
	app.get('/admin', function (req, res) {
		if(!req.session.loggedin) {
			res.render('login');
		} else {
			res.render('admin');
		}
	});

	app.post('/login', function (req, res) {
		json		= {success: false};

		db.query('SELECT id FROM users WHERE username=? AND password=PASSWORD(?)', [req.body.user, req.body.password], function (e, results) {
			if (results.length) {
				var	user	= results[0];

				req.session.loggedin	= true;
				req.session.user_id		= user.id;

				json.success			= true;
			}

			res.json(json);
		});
	});

	app.get('/games', function (req, res) {
		if(!req.session.loggedin) {
			res.render('login');
		} else {
			db.query('SELECT * FROM games', [], function (e, results) {
				res.render('games', {games: results});
			});
		}
	});

	app.get('/game/:id?', function (req, res) {
		if(!req.session.loggedin) {
			res.render('login');
		} else {
			if (req.params.id) {
				pool_queries(db,
					[
						['SELECT * FROM games WHERE id=?', [req.params.id]],
						['SELECT * FROM game_channels WHERE game_id=?', [req.params.id]]
					], ['game', 'channels'], function (results) {
					res.render('game', {game: results.game[0], channels: results.channels});
				});
			} else {
				res.render('game', {game: null, channels: []});
			}
		}
	});

	app.post('/games/update/:id?', function (req, res) {
		if(!req.session.loggedin) {
			res.render('login');
		} else {
			var	messages	= [];

			if (!req.body.name || !req.body.game_key || !req.body.game_crypto) {
				messages.push('Name, Game Key and Game Crypto are required');
			} else {
				if (!req.body.game_crypto.match(/^[a-f0-9]{8}$/i)) {
					messages.push('Game key must be an hexadecimal value with 8 characters');
				}

				if (!req.body.channel || (req.body.channel && !req.body.channel.length)) {
					messages.push('At least a channel must be specified');
				}
			}

			if (!messages.length) {
				function update_channels(game_id) {
					var	index	= 0;

					req.body.channel.forEach(function (channel) {
						if (!channel) {
							return;
						}

						var subchannel	= req.body.channel_allow_subchannel[index];
						var key			= req.body.channel_key[index];
						var color		= req.body.channel_color[index];
						var	id			= req.body.channel_id[index];

						if (id) {
							db.query('UPDATE game_channels SET name = ?, allow_subchannel = ?, channel_key = ?, color = ? WHERE id = ?', [channel, subchannel, key, color, id]);
						} else {
							db.query('INSERT INTO game_channels(name, allow_subchannel, game_id, channek_key, color) VALUES(?, ?, ?, ?, ?)', [channel, subchannel, game_id, key, color]);
						}

						index++;
					});
				}

				if (req.params.id) {
					if (isNaN(req.params.id)) {
						res.json({success: false, messages: ['Invalid game id']});
						return;
					}

					db.query('UPDATE games SET name=?, game_key=?, game_crypto=? WHERE id=?', [
						req.body.name,
						req.body.game_key,
						req.body.game_crypto,
						req.params.id
					]);

					update_channels(req.params.id);
				} else {
					db.query('INSERT INTO games(name, game_key, game_crypto) VALUES(?, ?, ?)', [
						req.body.name,
						req.body.game_key,
						req.body.game_crypto
					], function (e, result) {
						update_channels(result.insertId);
					});
				}

				res.json({success: true});
			} else {
				res.json({success: false, messages: messages});
			}
		}
	});

	app.post('/games/destroy', function (req, res) {
		if(!req.session.loggedin) {
			res.render('login');
		} else {

		}
	});
// <--

// User services -->
	app.post('/block', function (req, res) {

	});
// <--