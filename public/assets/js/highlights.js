var socket = io.connect(_node_server + ':2600');
socket.on('connect', function () {
	console.log('Highlights service connected');
	socket.emit('set-language', {
		lang: _language
	});

	socket.on('message', function (data) {
		console.log('Got message from Highlights service');

		var d = $(document.createElement('DIV')).addClass('highlight-window');
		var m = $(document.createElement('DIV')).addClass('highlight-text');
		var len = $('.highlight-window').length;

		m.html(data.message);
		d.append(m);

		$(document.body).append(d);

		if(len) {
			d.css({marginTop: len * (d.height() + 10)});
		}

		var __iv  = setInterval(function () {
			d.animate({opacity: 0}, function () {
				d.remove();
			});

			clearInterval(__iv);
		}, 7000);
	});
	socket.on('error', function () {
		console.log('Highlights service error');
		console.log(arguments);
	});
});