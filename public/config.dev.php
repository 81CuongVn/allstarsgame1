<?php
error_reporting(E_ALL);

# General settings
$home				= 'home#index';
$site_url			= 'http://animeallstarsgame.local';
$rewrite_enabled	= TRUE;

# Game settings
define('GAME_NAME', 			'All-Stars Game');
define('GAME_VERSION', 			'2.0.16');
define('GLOBAL_PASSWORD', 		'dev2@21');

# Round settings
define('ROUND_END',				'2021-03-31 23:59:59');

# Beta / Maintenance
$maintenance		= ROUND_END <= date('Y-m-d H:i:s');
define('IS_BETA',					FALSE);
define('IS_MAINTENANCE',			FALSE);

# Database settings
define('RECORDSET_APC',			1);
define('RECORDSET_SHM',			2);
$database			= [
	'host'			=> '127.0.0.1',
	'username'		=> 'root',
	'password'		=> '',
	'database'		=> 'aasg_dev',
	'connection'	=> 'primary',
	'cache_mode'	=> RECORDSET_SHM,
	'cache_id'		=> 'AASG'
];

# SMTP settings
$mailConfig			= [
	'host'			=> 'smtp.gmail.com',
	'port'			=> 465,
	'username'		=> 'animeallstarsgamebr@gmail.com',
	'password'		=> 'ffm160295',
	'from'			=> 'animeallstarsgamebr@gmail.com',
	'from_name'		=> GAME_NAME
];

# Attributes rate
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

# Default sessions
if (!isset($_SESSION['language_id']))		$_SESSION['language_id']	= 1;
if (!isset($_SESSION['user_id']))			$_SESSION['user_id']		= NULL;
if (!isset($_SESSION['player_id']))			$_SESSION['player_id']		= NULL;
if (!isset($_SESSION['loggedin']))			$_SESSION['loggedin']		= FALSE;
if (!isset($_SESSION['universal']))			$_SESSION['universal'] 		= FALSE;
if (!isset($_SESSION['orig_user_id']))		$_SESSION['orig_user_id']	= 0;
if (!isset($_SESSION['orig_player_id']))	$_SESSION['orig_player_id']	= 0;

# Timezone settings
define('DEFAULT_TIMEZONE',		'America/Sao_Paulo');

# Regex settings
define('REGEX_PLAYER',			'/^[ÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÃÕÑÇáéíóúàèìòùâêîôûãõñç\w\d\s]+$/');
define('REGEX_GUILD',			'/^[ÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÃÕÑÇáéíóúàèìòùâêîôûãõñç\w\d\s]+$/');

# Chat settinsg
define('CHAT_KEY',				'YAn8yK930907L2KUTnnSqLDuI6jl0G9N');
define('CHAT_SERVER',			'http://localhost:2934');

# Highligts settings
define('HIGHLIGHTS_KEY',		'430rBdLShn8yK930907L2a8yeTszrDip');
define('HIGHLIGHTS_SERVER',		'http://localhost:2600');

# Redis Server settings
define('REDIS_SERVER', 			'127.0.0.1');
define('REDIS_PORT',			6379);
define('REDIS_PASS',			'uD7uSr8Bgxb3fMzB9TKSURmeYGw6u1pHsf7HOo9r62mErXp9YDGrJERvkcPHDVGt3Ybw4v21SBhYcFOibvNkXux8DSU5HckhvAyS');

# PvP Server settings
define('PVP_SERVER',			'127.0.0.1');
define('PVP_PORT',				5672);
define('PVP_CHANNEL',			'allstars_pvp_queue');

# Initial settings
define('INITIAL_MONEY',			500);

# Techniques limit
define('MAX_EQUIPPED_ATTACKS',	10);

# Rate settings
define('EXP_RATE',				1);
define('MONEY_RATE',			1);

# PvP settings
define('PVP_TURN_TIME',			90);

# Energy costs
define('NPC_COST',				2);
define('PVP_COST',				2);

# Event settings
define('EVENT_ACTIVE', 			TRUE);
define('EVENT_ITEM', 			2059);

# PagSeguro settings
define('PS_ENV',                'sandbox');  # production, sandbox
define('PS_EMAIL',              'felipe.fmedeiros95@gmail.com');
define('PS_TOKEN_SANDBOX',      'C43E8E781D194CAE9E6523999B98DCDE');
define('PS_TOKEN_PRODUCTION',   '26247afc-e082-4cf9-8448-eaae9a7349b63013c9b84cfea0c11f7d5169cf2b9beedd74-9c75-4896-9e82-5f0e513a3421');
define('PS_LOG',				TRUE);
define('PS_LOG_FILE',			ROOT . '/logs/pagseguro.log');

# PayPal settings
define('PAYPAL_EMAIL',			'medeiros.dev@gmail.com');
define('PAYPAL_SANDBOX',		TRUE);
define('PAYPAL_LOG_FOLDER',		ROOT . '/logs/paypal');

# Facebook settings
define('FB_APP_ID',				'871809436995595');
define('FB_APP_SECRET',			'7381e66f91500385865ea313ebe4f8c3');
define('FB_CALLBACK_URL',		'callback/facebook');