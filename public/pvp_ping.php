<?php
header('Content-Type: application/json');

define('DB_LOGGING',		FALSE);
define('BACKTRACE_SELECTS',	TRUE);
define('BACKTRACE_UPDATES',	TRUE);
define('BACKTRACE_DELETES',	TRUE);

define('RECORDSET_CACHE_OFF_FORCE', TRUE);

$env = 'dev';
if (!in_array($_SERVER['SERVER_ADDR'], [ '127.0.0.1' ]))
	$env = 'prod';

define('FW_ENV',                    $env);

define('ROOT',						dirname(__FILE__));

require ROOT . '/config.' . $env . '.php';

date_default_timezone_set(DEFAULT_TIMEZONE);

require ROOT . '/vendor/autoload.php';
require ROOT . '/helpers/global_helpers.php';

# Base classes
require ROOT . '/includes/recordset.php';
require ROOT . '/includes/relation.php';
require ROOT . '/includes/shared_store.php';

# Traits
require ROOT . '/lib/attribute_manager.php';
require ROOT . '/lib/effect_manager.php';
require ROOT . '/lib/battle_technique_locks.php';

# Db
require ROOT . '/includes/db.php';

# Models
require ROOT . '/models/Player.php';
require ROOT . '/models/PlayerStat.php';
require ROOT . '/models/PlayerAttribute.php';

$json		= new stdClass();
$json->ping	= FALSE;

if (isset($_SERVER['argv'][1])) {
	$_GET['uuid']	= $_SERVER['argv'][1];
}

if (isset($_GET['uuid']) && is_numeric($_GET['uuid'])) {
	$result	= Recordset::query('
			SELECT
				`a`.`id`,
				`a`.`player_id`,
				`a`.`enemy_id`,
				`a`.`player_should_refresh`,
				`a`.`enemy_should_refresh`,
				`a`.`finished_at`
			FROM
				`battle_pvps` AS `a` JOIN `players` AS `b` ON `a`.`id` = `b`.`battle_pvp_id`
			WHERE
				`b`.`id` = ' . $_GET['uuid']);

	if ($result->num_rows) {
		$result	= $result->row();

		$field		= ($result->player_id == $_GET['uuid'] ? 'player' : 'enemy') . '_should_refresh';
		$value		= $result->$field;
		$json->ping	= $value ? TRUE : FALSE;

		if($json->ping || $result->finished_at) {
			Recordset::update('battle_pvps', [
				$field	=> 0
			], [
				'id'	=> $result->id
			]);
		}
	}
}

echo json_encode($json);