<?php
class SharedStore {
	private static function get_store_path() {
		return ROOT . '/cache/store/';
	}
	
	static function S($key, $v = NULL) {
		$file	= SharedStore::get_store_path() . md5($key) . '.store';

		file_put_contents($file, serialize($v));
		@chmod($file, 0777);
	}
	
	static function G($key, $default = NULL) {
		$mem = @file_get_contents(SharedStore::get_store_path() .  md5($key) . '.store');

		/*if ($_SESSION['universal']) {
			echo "[+] Loading " . SharedStore::get_store_path() . md5($key) . ".store\n";
			flush();

			debug_print_backtrace();
		}*/
		
		return $mem != NULL ? unserialize($mem) : $default;
	}
	
	static function D($key, $t = 10) {
		@unlink(SharedStore::get_store_path() . md5($key) . '.store');
	}
}
