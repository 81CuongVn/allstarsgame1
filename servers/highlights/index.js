// Needed extensions
var jsyaml		= require('js-yaml');
var util		= require('util');
var fs			= require('fs');
var express		= require('express');
var sio			= require('socket.io');
var cors		= require('cors');
var config		= require('./config');

// Redis
var IORedis		= require('socket.io-redis');
var redis		= require('redis');

var app	= express();
app.use(cors());
app.use(express.json())
app.use(express.urlencoded());

if (config.ssl.active) {
	var https		= require('https');
	var server		= https.createServer({
		key:	config.ssl.key,
		cert:	config.ssl.cert,
	}, app);
} else {
	var http	= require('http');
	var server	= http.createServer(app);
}

var io = sio(server, {
    cors: {
        origin: '*',
        methods: ['GET', 'POST'],
    }
});

// Start dungeon system
io.adapter(IORedis({
    host:		'localhost',
    port:		6379,
    auth_pass:	config.redis
}));

var redisServer = redis.createClient();
if (config.redis) {
	redisServer.auth(config.redis);
}

setInterval(function () {
    console.log("- Checking for dungeon invites to send");

    var broadcastInvite = function (queue_id) {
        var multi = redisServer.multi();

        multi.get("od_id_" + queue_id);
        multi.get("od_name_" + queue_id);

        multi.lrange("od_targets_" + queue_id, 0, -1);
        multi.lrange("od_accepts_" + queue_id, 0, -1);
        multi.lrange("od_refuses_" + queue_id, 0, -1);

        multi.get("od_needed_" + queue_id);
        multi.get("od_guild_" + queue_id);

        multi.exec(function (err, replies) {
            console.log('Need: ' + parseInt(replies[5]) + " | Accepteds: " + replies[3].length);

            if (parseInt(replies[5]) == replies[3].length) {
                console.log("Broadcast redirect");

                io.sockets.in("guild_" + replies[6]).emit('dungeon-redirect', replies[3])

                multi.lrem('aasg_od_invites', queue_id, 0);

                multi.del("od_id_" + queue_id);
                multi.del("od_name_" + queue_id);
                multi.del("od_targets_" + queue_id);
                multi.del("od_accepts_" + queue_id);
                multi.del("od_refuses_" + queue_id);
                multi.del("od_needed_" + queue_id);
                multi.del("od_guild_" + queue_id);
                multi.del("od_event_" + queue_id);

                multi.exec()
            } else {
                console.log("- Broadcast dungeon invite to " + replies[6] + " / " + queue_id);

                io.sockets.in("guild_" + replies[6]).emit('dungeon-invite', {
                    event: replies[0],
                    name: replies[1],
                    targets: replies[2],
                    accepts: replies[3],
                    refuses: replies[4],
                    needed: replies[5],
                    queue: queue_id,
                });
            }
        });
    }

    redisServer.lrange("aasg_od_invites", 0, -1, function (err, queues) {
        console.log("- Got " + queues.length + " queues");

        queues.forEach(function (queue) {
            if (queue) {
                broadcastInvite(queue);
            }
        });
    });
}, 2000);
// End dungeon system

var token			= config.key;
var languages		= config.langs;
var translations	= {};

var sprintf			= (text, params) => util.format.apply(null, [text].concat(params));

var bootstrap		= () => {
	server.listen(config.port);

	console.log(`+ Highlights Thread Started on ${server.address().address} at port ${server.address().port}`);
}

var counters		= {
	connecitons:	0,
	broadcasts:		0,
	broadcastsSent:	0,
	currentClients:	0,
};

languages.forEach(function (lang) {
    console.log("- Translation '" + lang + "' is now buffered on thread");

    var buffer = fs.readFileSync(__dirname + '/../../public/locales/' + lang + '.yml', 'utf8').toString().replace(/\t/img, '  ');
    buffer = buffer.replace(/:\|/, ': |');

    return translations[lang] = jsyaml.load(buffer);
});

app.post('/console/write/', function (req, res) {
    var result = 'unknown error';
    if (req.body.token !== token) {
        result = 'invalid token';
    } else {
        if (req.body.message) {
            console.log('Will broadcast standard message');
            result = 'ok';
			counters.broadcasts++;

            languages.forEach(function (lang) {
                return io.sockets["in"]('lang_' + lang).emit('message', {
                    message: req.body.message
                });
            });
        } else if (req.body.yaml) {
            languages.forEach(function (lang) {
                var message = '';
                default_message = '-- translation missing: ' + req.body.yaml + '--';

                try {
                    message = sprintf(eval("translations[lang][lang]." + req.body.yaml), req.body.assigns);
                    console.log('* Will broadcast translatable message');
					counters.broadcasts++;
                } catch (_error) {
                    message = default_message;
                    console.log('* Will broadcast normal message');
					counters.broadcasts++;
                }

                if (message === 'undefined') {
                    message = default_message;
                }

				return io.sockets["in"]('lang_' + lang).emit('message', {
                    message: message
                });
            });
        } else {
            result = 'missing message';
        }
    }

	res.set('Content-Type', 'text/plain');
	return res.send(result);
});

app.get('/status/:token', (req, res) => {
	res.set('Content-Type', 'text/plain');

	return res.send(`Clients connected: ${counters.currentClients}\nOverall connections: ${counters.connecitons}\nBroadcasts: ${counters.broadcasts}\nBroadcasts(Client count): ${counters.broadcastsSent}\n`);
});

io.sockets.on('connection', function (socket) {
	counters.connecitons++;
	counters.currentClients++;

	console.log("+ Got client connection");

	socket.on('disconnect', () => {
		counters.currentClients--;
	});

    socket.on('set-language', function (data) {
        if (data.lang) {
            return socket.join('lang_' + data.lang);
        }
    });

    socket.on('enter-orgnaization', function (data) {
        if (data.guild) {
            return socket.join('guild_' + data.guild);
        }
    });
});

// Startup =)
bootstrap()
