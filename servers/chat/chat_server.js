var spawn = require("child_process").spawn;
var config = require("./config");
var colors = require('colors');

function child_hanlder(proc_cb, manager) {
	var proc = proc_cb.apply(null, []);
	var header = (manager ? "Manager ".red : "Worker  ".green) + ("[" + proc.pid + "]").yellow + ": ";

	function format_stream(data) {
		var output = [];

		data.toString().split("\n").forEach(function (entry) {
			if(entry) {
				output.push(header + entry);
			}
		})

		console.log(output.join("\n"));
	}

	proc.stdout.on('data', function (data) { format_stream(data) });
	proc.stderr.on('data', function (data) { format_stream(data) });

	proc.on('disconnect', function () {
		console.log(header + "** DISCONNECTED **".red);
	});

	proc.on('close', function () {
		console.log(header + "** CLOSED **".red);
	});
}

child_hanlder(function () {
		return spawn('node', [__dirname + '/manager.js', config.manager_port, config.workers]);
}, true);

for (var i = 0; i <= config.workers - 1; i++) {
	child_hanlder(function () {
		return spawn('node', [__dirname + '/worker.js', config.base_port + i]);
	});
}