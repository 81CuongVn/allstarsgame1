<?php
header('Content-Type: application/json');

define('ROOT',						dirname(__FILE__));

require ROOT . '/config.php';

date_default_timezone_set(DEFAULT_TIMEZONE);

require ROOT . '/vendor/autoload.php';
require ROOT . '/helpers/global_helpers.php';

define('DB_LOGGING',				false);
define('BACKTRACE_SELECTS',			true);
define('BACKTRACE_UPDATES',			true);
define('BACKTRACE_DELETES',			true);
define('RECORDSET_CACHE_OFF_FORCE', true);

// Base classes
require ROOT . '/includes/recordset.php';
require ROOT . '/includes/relation.php';
require ROOT . '/includes/shared_store.php';

// Traits
require ROOT . '/lib/attribute_manager.php';
require ROOT . '/lib/effect_manager.php';
require ROOT . '/lib/battle_technique_locks.php';

// DB
require ROOT . '/includes/db.php';

// Models
require ROOT . '/models/Player.php';
require ROOT . '/models/PlayerStat.php';
require ROOT . '/models/PlayerAttribute.php';

$json		= new stdClass();
$json->ping	= false;

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
			`battle_pvps` AS `a`
			JOIN `players` AS `b` ON `a`.`id` = `b`.`battle_pvp_id`
		WHERE
			`b`.`id` = ' . $_GET['uuid']);

	if ($result->num_rows) {
		$result	= $result->row();

		$field		= ($result->player_id == $_GET['uuid'] ? 'player' : 'enemy') . '_should_refresh';
		$value		= $result->$field;
		$json->ping	= $value ? true : false;

		if ($json->ping || $result->finished_at) {
			Recordset::update('battle_pvps', [
				$field	=> 0
			], [
				'id'	=> $result->id
			]);
		}
	}
}

echo json_encode($json);
