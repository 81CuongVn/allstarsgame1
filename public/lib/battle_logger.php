<?php
trait BattleLogger {
	function get_battle_log($battle_id, $type) {
		$file = ROOT . "/logs/battles/{$type}/{$battle_id}.log";
		if (file_exists($file)) {
			$log = file_get_contents($file);
			if ($log) {
				return json_decode($log);
			}
		}
		return [];
	}
	function add_battle_log($battle_id, $type, $log) {
		$file = ROOT . "/logs/battles/{$type}/{$battle_id}.log";

		$open_file = fopen($file, "w+");
		fwrite($open_file, json_encode($log, JSON_PRETTY_PRINT));
		fclose($open_file);
	}
}