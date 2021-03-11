syncdb	= require('mysql-libmysqlclient');

exports.connect = function(config) {
	user		= config.user || '';
	host		= config.host || '127.0.0.1';
	password	= config.password || '';
	db			= config.db || '';

	syncdb	= syncdb.createConnectionSync(host, user, password, db);
	syncdb.realQuerySync("SET NAMES 'utf8'");
}

exports.query_only	= function (sql) {
	syncdb.realQuerySync(sql)
	var result	= syncdb.storeResultSync();	
}

exports.row_of	= function(sql) {
	syncdb.realQuerySync(sql)

	var result	= syncdb.storeResultSync();
	
	if(!result) {
		console.log("SQL ERROR: \n\n" + sql);
		process.exit();
	}
	
	return (result.fetchAllSync())[0];
}

exports.result_of = function (sql) {
	syncdb.realQuerySync(sql)

	var result	= syncdb.storeResultSync();
	return (result.fetchAllSync());	
}
