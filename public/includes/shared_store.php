<?php
class SharedStore {
	private static function get_store_path() {
		return ROOT . '/../tmp/shared/';
	}

	static function S($key, $v = null) {
		$file	= SharedStore::get_store_path() . md5($key) . '.store';

		file_put_contents($file, serialize($v));
		@chmod($file, 0777);
	}

	static function G($key, $default = null) {
		$mem = @file_get_contents(SharedStore::get_store_path() .  md5($key) . '.store');
		return $mem != null ? unserialize($mem) : $default;
	}

	static function D($key, $t = 10) {
		@unlink(SharedStore::get_store_path() . md5($key) . '.store');
	}
}
