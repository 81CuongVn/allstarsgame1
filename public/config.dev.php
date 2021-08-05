<?php
error_reporting(E_ALL);

// General settings
$home				= 'home#index';
$site_url			= 'http://allstarsgame.test';
$rewrite_enabled	= true;

// Game settings
define('GAME_NAME', 			'Anime All-Stars Game');
define('GAME_PREFIX', 			'AASG');
define('GAME_VERSION', 			'2.22');
define('GLOBAL_PASSWORD', 		'allStars2@21');

// Maintenance
define('IS_MAINTENANCE',		false);

// Database settings
define('RECORDSET_APC',			1);
define('RECORDSET_SHM',			2);
$database			= [
	'host'			=> '127.0.0.1',
	'username'		=> 'root',
	'password'		=> '',
	'database'		=> 'allstars_db',
	'connection'	=> 'primary',
	'cache_mode'	=> RECORDSET_SHM,
	'cache_id'		=> GAME_PREFIX
];

// SMTP settings
$mailConfig			= [
	'host'			=> 'smtp.gmail.com',
	'port'			=> 465,
	'username'		=> 'animeallstarsgamebr@gmail.com',
	'password'		=> 'allStars2@21',
	'from'			=> 'animeallstarsgamebr@gmail.com',
	'from_name'		=> GAME_NAME
];

// Attributes rate
$attrRate			= [
	'for_atk'		=> 4,
	'for_def'		=> 4,
	'for_crit'		=> 3,
	'for_crit_inc'	=> 2,
	'for_abs'		=> 3,
	'for_abs_inc'	=> 2,
	'for_prec'		=> 2,
	'for_init'		=> 2,
];

// reCAPTCHA keys
$recaptcha_keys		= [
	'standard'	=> [
		'site'		=> '6LfHukgbAAAAAMA1dG8__VZv0zCyHYneKN-o_60R',
		'secret'	=> '6LfHukgbAAAAAH77ueUrFJuXVQadq-caO1agnPwF'
	],
	'invisible'	=> [
		'site'		=> '6LeGwEgbAAAAAP6tVKTV_1NYxn8oHj_wmDqzlzFJ',
		'secret'	=> '6LeGwEgbAAAAAF_B4I2OQ-9rxElOq-UfhfJ42Vt5'
	]
];

// Ranked Schedules
$ranked_schedules	= [
	[ '10', '12' ],		// 10h at 12h
	[ '16', '18' ],		// 16h at 18h
	[ '22', '24' ]		// 22h at 00h
];

// Default sessions
if (!isset($_SESSION['language_id']))		$_SESSION['language_id']	= 1;
if (!isset($_SESSION['user_id']))			$_SESSION['user_id']		= null;
if (!isset($_SESSION['player_id']))			$_SESSION['player_id']		= null;
if (!isset($_SESSION['loggedin']))			$_SESSION['loggedin']		= false;
if (!isset($_SESSION['universal']))			$_SESSION['universal'] 		= false;
if (!isset($_SESSION['orig_user_id']))		$_SESSION['orig_user_id']	= 0;
if (!isset($_SESSION['orig_player_id']))	$_SESSION['orig_player_id']	= 0;

// Timezone settings
define('DEFAULT_TIMEZONE',		'America/Sao_Paulo');

// Regex settings
define('REGEX_PLAYER',			'/^[ÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÃÕÑÇáéíóúàèìòùâêîôûãõñç\w\d\s]+$/');
define('REGEX_GUILD',			'/^[ÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÃÕÑÇáéíóúàèìòùâêîôûãõñç\w\d\s]+$/');

// Chat settinsg
define('CHAT_KEY',				'YAn8yK930907L2KUTnnSqLDuI6jl0G9N');
define('CHAT_SERVER',			'http://allstarsgame.test:2934');

// Highligts settings
define('HIGHLIGHTS_KEY',		'430rBdLShn8yK930907L2a8yeTszrDip');
define('HIGHLIGHTS_SERVER',		'http://allstarsgame.test:2600');

// Redis Server settings
define('REDIS_SERVER', 			'127.0.0.1');
define('REDIS_PORT',			6379);
define('REDIS_PASS',			'uD7uSr8Bgxb3fMzB9TKSURmeYGw6u1pHsf7HOo9r62mErXp9YDGrJERvkcPHDVGt3Ybw4v21SBhYcFOibvNkXux8DSU5HckhvAyS');

// PvP Server settings
define('PVP_SERVER',			'127.0.0.1');
define('PVP_PORT',				5672);
define('PVP_CHANNEL',			'allstars_queue');

// Max level settings
define('MAX_LEVEL_USER',		50);
define('MAX_LEVEL_PLAYER',		50);
define('MAX_LEVEL_GUILD',		15);

// Initial settings
define('INITIAL_MONEY',			0);

// Techniques limit
define('MAX_EQUIPPED_ATTACKS',	10);

// Verifica se é final de semana
$isWeekend			= in_array(date('w'), [ 0, 6 ]);
if (!$isWeekend) {
	$isWeekend	= date('w') == 5 && date('H') >= 18;
}

// Rate settings
define('EXP_RATE',				!$isWeekend ? 1 : 1.5);
define('MONEY_RATE',			!$isWeekend ? 1 : 1.5);
define('DROP_RATE',				!$isWeekend ? 1 : 1.5);

// PvP settings
define('PVP_TURN_TIME',			90);
define('PVP_COST',				2);

// NPC settings
define('NPC_DAILY_LIMIT',		!$isWeekend ? 10 : 20);
define('NPC_EASY_COST',			2);
define('NPC_NORMAL_COST',		4);
define('NPC_HARD_COST',			6);
define('NPC_EXTREME_COST',		8);

// Event settings
define('EVENT_ACTIVE', 			false);
define('EVENT_ITEM', 			2059);

// PagSeguro settings
define('PS_ENV',                'sandbox');  // production, sandbox
define('PS_EMAIL',              'felipe.fmedeiros95@gmail.com');
define('PS_TOKEN_SANDBOX',      'C43E8E781D194CAE9E6523999B98DCDE');
define('PS_TOKEN_PRODUCTION',   '26247afc-e082-4cf9-8448-eaae9a7349b63013c9b84cfea0c11f7d5169cf2b9beedd74-9c75-4896-9e82-5f0e513a3421');
define('PS_LOG',				true);
define('PS_LOG_FILE',			ROOT . '/logs/pagseguro.log');

// Mercado Pago settings
define('MP_SAMDBOX',			true);
define('MP_SAMDBOX_TOKEN',		'TEST-8109339744564538-051219-b52093c24cfb57581a381e277f56f841-214407314');
define('MP_PROD_TOKEN',			'APP_USR-8109339744564538-051219-327a4137a53c0bafe411babf4b0d088a-214407314');

// PayPal settings
define('PAYPAL_EMAIL',			'medeiros.dev@gmail.com');
define('PAYPAL_SANDBOX',		true);
define('PAYPAL_LOG_FOLDER',		ROOT . '/logs/paypal');

// Facebook settings
define('FB_PAGE_USER',			'AllStarsGame');
define('FB_APP_ID',				'871809436995595');
define('FB_APP_SECRET',			'7381e66f91500385865ea313ebe4f8c3');
define('FB_CALLBACK_URL',		'callback/facebook');

// ProxyCheck Token
define('PROXY_CHECK_KEY',		'j4c1vy-037102-9q9644-32dj0h');
