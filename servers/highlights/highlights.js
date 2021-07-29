// Needed extensions
var express = require("express"),
    app = express(),
    fs = require('fs'),
    bodyParser = require('body-parser'),
    jsyaml = require('js-yaml'),
    util = require('util');

var token = '430rBdLShn8yK930907L2a8yeTszrDip',
    languages = ['pt-BR'],
    translations = {};

languages.forEach(function (lang) {
    console.log("- Translation '" + lang + "' is now buffered on thread");

    var buffer = fs.readFileSync(__dirname + '/../../public/locales/' + lang + '.yml', 'utf8').toString().replace(/\t/img, '  ');
    buffer = buffer.replace(/:\|/, ': |');

    return translations[lang] = jsyaml.load(buffer);
});

var server = app.listen(2530, function () {
    console.log("+ Highlights Thread Started on " + server.address().address + " at port " + server.address().port);
});
var io = require('socket.io').listen(server, { origins: '*:*' });

var redis_auth = 'uD7uSr8Bgxb3fMzB9TKSURmeYGw6u1pHsf7HOo9r62mErXp9YDGrJERvkcPHDVGt3Ybw4v21SBhYcFOibvNkXux8DSU5HckhvAyS';
var IORedis = require('socket.io-redis');
var redis = require('redis');

io.adapter(IORedis({
    host: 'localhost',
    port: 6379,
    auth_pass: redis_auth
}));

var redisServer = redis.createClient();
redisServer.auth(redis_auth);

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

var sprintf = function (text, params) {
    return util.format.apply(null, [text].concat(params));
};

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

app.post('/console/write/', function (req, res) {
    var result = 'unknown error';
    if (req.body.token !== token) {
        result = 'invalid token';
    } else {
        if (req.body.message) {
            console.log('Will broadcast standard message');
            result = 'ok';

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
                } catch (_error) {
                    message = default_message;
                    console.log('* Will broadcast normal message');
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

io.sockets.on('connection', function (socket) {
    console.log("+ Got client connection");

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
