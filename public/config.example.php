<?php
error_reporting(E_ALL);

// Environment	(dev / prod)
define('FW_ENV',				'dev');

// General settings
define('SITE_HOME',				'home#index');
define('SITE_URL', 				'http://localhost');
define('REWRITE_ENABLED',		true);

// Game settings
define('GAME_NAME', 			'Anime All-Stars Game');
define('GAME_PREFIX', 			'AASG');
define('GAME_VERSION', 			2);
define('GLOBAL_PASSWORD', 		'master');

// Maintenance
define('IS_MAINTENANCE',		false);

// Database settings
define('DATABASE', 				[
	'host'			=> '127.0.0.1',
	'username'		=> 'root',
	'password'		=> '',
	'database'		=> 'aasg_db',
	'connection'	=> 'primary',
	'cache_id'		=> GAME_PREFIX,
	'cache_mode'	=> 'file'		// file or redis
]);

// SMTP settings
define('MAIL_CONFIG',			[
	'active'		=> false,
	'host'			=> 'smtp.example.com',
	'port'			=> 465,
	'username'		=> 'example@email.com',
	'password'		=> 'YourPassword',
	'from'			=> 'example@email.com',
	'from_name'		=> GAME_NAME
]);

// Attributes rate
define('ATTR_RATE',				[
	'for_atk'		=> 4,
	'for_def'		=> 4,
	'for_crit'		=> 3,
	'for_crit_inc'	=> 2,
	'for_abs'		=> 3,
	'for_abs_inc'	=> 2,
	'for_prec'		=> 2,
	'for_init'		=> 2,
]);

// reCAPTCHA keys
define('RECAPTCHA_KEYS',		[
	'standard'	=> [
		'site'		=> 'reCAPTCHASiteKey',
		'secret'	=> 'reCAPTCHASecretKey'
	],
	'invisible'	=> [
		'site'		=> 'reCAPTCHASiteKey',
		'secret'	=> 'reCAPTCHASecretKey'
	]
]);

// Ranked Schedules
define('RANKED_SCHEDULES',		[
	[ '10', '12' ],		// 10h at 12h
	[ '16', '18' ],		// 16h at 18h
	[ '22', '24' ]		// 22h at 00h
]);

// Timezone settings
define('DEFAULT_TIMEZONE',		'America/Sao_Paulo');

// Regex settings
define('REGEX_PLAYER',			'/^[ÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÃÕÑÇáéíóúàèìòùâêîôûãõñç\w\d\s]+$/');
define('REGEX_GUILD',			'/^[ÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÃÕÑÇáéíóúàèìòùâêîôûãõñç\w\d\s]+$/');

// Chat settinsg
define('CHAT_KEY',				'YAn8907KUTny0G9K930nSqLDL2uI6jlN');
define('CHAT_SERVER',			'http://localhost:2930');

// Highligts settings
define('HIGHLIGHTS_KEY',		'430BdLS9ye07L2aTszrr8hn8yK930Dip');
define('HIGHLIGHTS_SERVER',		'http://localhost:2530');

// Redis Server settings
define('REDIS_SERVER', 			'127.0.0.1');
define('REDIS_PORT',			6379);
define('REDIS_PASS',			'RedisPassword');

// PvP Server settings
define('PVP_SERVER',			'127.0.0.1');
define('PVP_PORT',				5672);
define('PVP_CHANNEL',			'allstars_queue');

// Max level settings
define('MAX_LEVEL_USER',		50);
define('MAX_LEVEL_PLAYER',		50);
define('MAX_LEVEL_GUILD',		15);

// Initial settings
define('INITIAL_MONEY',			1000);

// Techniques limit
define('MAX_EQUIPPED_ATTACKS',	10);

// Rate settings
define('EXP_RATE',				1);
define('MONEY_RATE',			1);
define('DROP_RATE',				1);

// PvP settings
define('PVP_TURN_TIME',			90);
define('PVP_COST',				2);

// NPC settings
define('NPC_DAILY_LIMIT',		10);
define('NPC_EASY_COST',			2);
define('NPC_NORMAL_COST',		4);
define('NPC_HARD_COST',			6);
define('NPC_EXTREME_COST',		8);

// Event settings
define('EVENT_ACTIVE', 			false);
define('EVENT_ITEM', 			2059);

// PagSeguro settings
define('PS_ENV',                'sandbox');  // production, sandbox
define('PS_EMAIL',              'YourPagSeguroEmail@example.com');
define('PS_TOKEN_SANDBOX',      'PagSeguroTokenSandbox');
define('PS_TOKEN_PRODUCTION',   'PagSeguroTokenProduction');
define('PS_LOG',				true);
define('PS_LOG_FILE',			ROOT . '/logs/pagseguro.log');

// Mercado Pago settings
define('MP_SAMDBOX',			true);
define('MP_SAMDBOX_TOKEN',		'MercadoPagoTokenSandbox');
define('MP_PROD_TOKEN',			'MercadoPagoTokenProduction');

// PayPal settings
define('PAYPAL_EMAIL',			'YourPagSeguroEmail@example.com');
define('PAYPAL_SANDBOX',		true);
define('PAYPAL_LOG_FOLDER',		ROOT . '/logs/paypal');

// Facebook settings
define('FB_PAGE_USER',			'AllStarsGame');
define('FB_APP_ID',				'FacebookAppId');
define('FB_APP_SECRET',			'FacebookAppSecret');
define('FB_CALLBACK_URL',		'callback/facebook');

// ProxyCheck Token
define('PROXY_CHECK_KEY',		'ProxyCheckKey');
