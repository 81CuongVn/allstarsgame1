var mysql		= require('mysql');
var connection	= null;

exports.connect		= function(config) {
	connection	= mysql.createConnection('mysql://' + config.user + ':' + config.pass + '@' + config.host + '/' + config.name + '?charset=utf8');
	connection.connect(function(error) {
		if (error) {
		  console.error('Error Connecting: ' + error.stack);
		  return;
		}
	});
}
exports.query		= function (sql, callback) {
	connection.query(sql, function(error, results, fields) {
		if (error) {
			console.error(sql);
			console.error(error);
		}

		if (callback) {
			callback(error, results, fields);
		}
	});
};
exports.disconnect	= function () {
	connection.end();
};