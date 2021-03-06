<?php
error_reporting(E_ALL);

# General settings
$home				= 'home#index';
$site_url			= 'http://animeallstarsgame.local';
$rewrite_enabled	= TRUE;

# Game settings
define('GAME_NAME', 			'All-Stars Game');
define('GAME_VERSION', 			'2.0.12');
define('GLOBAL_PASSWORD', 		'dev2@21');

# Database settings
define('RECORDSET_APC',			1);
define('RECORDSET_SHM',			2);
$database			= [
	'host'			=> '127.0.0.1',
	'username'		=> 'root',
	'password'		=> '',
	'database'		=> 'aasg',
	'connection'	=> 'primary',
	'cache_mode'	=> RECORDSET_SHM,
	'cache_id'		=> 'AASG'
];

# SMTP settings
$mailConfig		= [
	'host'		=> 'mail.animeallstarsgame.com',
	'port'		=> 587,
	'username'	=> 'contato@animeallstarsgame.com',
	'password'	=> '[z29e|?IEi',
	'from'		=> 'noreply@animeallstarsgame.com',
	'from_name'	=> GAME_NAME
];

# Attributes rate
$attrRate = [
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
if (!isset($_SESSION['language_id']))	$_SESSION['language_id']	= 1;
if (!isset($_SESSION['user_id']))		$_SESSION['user_id']		= NULL;
if (!isset($_SESSION['player_id']))		$_SESSION['player_id']		= NULL;
if (!isset($_SESSION['loggedin']))		$_SESSION['loggedin']		= FALSE;
if (!isset($_SESSION['universal']))		$_SESSION['universal'] 		= FALSE;

# Timezone settings
define('DEFAULT_TIMEZONE',		'America/Sao_Paulo');

# Chat settinsg
define('CHAT_ID',				1);
define('CHAT_KEY',				'a7b5b8f8-7256-4e22-b982-ecaaf98b7b79');
define('CHAT_SECRET',			'YAn8yK930907L2KUTnnSqLDuI6jl0G9N');

# Highligts settings
define('HIGHLIGHTS_KEY',		'430rBdLShn8yK930907L2a8yeTszrDip');

define('NODE_SERVER',			'http://localhost');

# Round settings
define('ROUND_END',				'2021-03-31 23:59:59');

# Initial settings
define('INITIAL_MONEY',			500);

# Techniques limit
define('MAX_EQUIPPED_ATTACKS',	10);

# Rate settings
define('EXP_RATE',				100);
define('MONEY_RATE',			1);

# PvP settings
define('PVP_RANGE',				5);
define('PVP_TURN_TIME',			90);

# Energy costs
define('NPC_COST',				2);
define('PVP_COST',				2);

# PagSeguro settings
define('PS_ENV',                'sandbox');  # production, sandbox
define('PS_EMAIL',              'felipe.fmedeiros95@gmail.com');
define('PS_TOKEN_SANDBOX',      'C43E8E781D194CAE9E6523999B98DCDE');
define('PS_TOKEN_PRODUCTION',   '26247afc-e082-4cf9-8448-eaae9a7349b63013c9b84cfea0c11f7d5169cf2b9beedd74-9c75-4896-9e82-5f0e513a3421');
define('PS_LOG',				TRUE);
define('PS_LOG_FILE',			ROOT . '/logs/pagseguro.log');

# PayPal settings
define('PAYPAL_EMAIL',			'medeiros.dev@gmail.com');
define('PAYPAL_LOG_FOLDER',		ROOT . '/logs/paypal');

# Facebook settings
define('FB_APP_ID',				'268588491549960');
define('FB_APP_SECRET',			'f9fe309ecdfc8c8f66c0d850fba7449b');