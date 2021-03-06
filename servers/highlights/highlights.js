// Needed extensions
var express			= require("express"),
	app				= express(),
	fs				= require('fs'),
	bodyParser		= require('body-parser'),
    jsyaml          = require('js-yaml'),
    util            = require('util');;

var token	        = '430rBdLShn8yK930907L2a8yeTszrDip',
    languages	    = [ 'pt-BR' ],
    translations    = {};

languages.forEach(function(lang) {
    console.log("- Translation '" + lang + "' is now buffered on thread");

    var buffer = fs.readFileSync(__dirname + '/../../public/locales/' + lang + '.yml', 'utf8').toString().replace(/\t/img, '  ');
    buffer = buffer.replace(/:\|/, ': |');

    return translations[lang] = jsyaml.load(buffer);
});

var server	        = app.listen(2600, function () {
    console.log("+ Highlights Thread Started on " + server.address().address + " at port " + server.address().port);
});
var io              = require('socket.io').listen(server, { origins: '*:*' });

var sprintf = function(text, params) {
    return util.format.apply(null, [text].concat(params));
};

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

app.post('/console/write/', function(req, res) {
	var result = 'unknown error';
	if (req.body.token !== token) {
		result = 'invalid token';
	} else {
		if (req.body.message) {
            console.log('Will broadcast standard message');
            result = 'ok';

            languages.forEach(function(lang) {
                return io.sockets["in"]('lang_' + lang).emit('message', {
                    message: req.body.message
                });
            });
        } else if (req.body.yaml) {
            languages.forEach(function(lang) {
                var message         = '';
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

io.sockets.on('connection', function(socket) {
	console.log("+ Got client connection");
    socket.on('set-language', function(data) {
        if (data.lang) {
            return socket.join('lang_' + data.lang);
        }
    });
});