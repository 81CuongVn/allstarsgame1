<?php
class Lang {
	private	static $strings	= [];
	private static $parsed	= [];


	// static function toJSON($lid = NULL) {
	// 	if (is_null($lid)) {
	// 		$lid	= $_SESSION['language_id'];
	// 	}
	// 	$loc		= Language::find_first($lid)->header;

    //     $json		= new stdClass();
	// 	$json->$loc = new stdClass();

	// 	$parsed		= Lang::$parsed[$lid];
	// 	foreach ($parsed AS $key => $translation) {
	// 		$json->$loc->$key	= $translation;
	// 	}

	// 	return json_encode($json);
    // }

	static function toJSON() {
        $json	= new stdClass();
        foreach (self::$parsed AS $tkey => $translations) {
            $loc			= Language::find($tkey)->header;
            $json->{$loc}	= new stdClass();

            foreach ($translations AS $key => $translation) {
                $json->{$loc}->{$key}	= $translation;
            }
        }
	    return json_encode($json);
    }
	static function initialize() {
		$path		= dirname(__FILE__);
		$cache_root	= ROOT . '/cache/';
		$cache_path	= ROOT . '/cache/yaml/';

		if (!is_dir($cache_path)) {
			if (is_writable(realpath($cache_root))) {
				mkdir($cache_path);
			} else {
				throw new Exception("Cache path isn't writable, change the permissions", 1);
			}
		}

		if (file_exists($path . '/../locales')) {
			$files				= glob($path . '/../locales/*.yml');
			foreach ($files as $file) {
				$is_cached	= TRUE;
				$cache_file	= $cache_path . md5(basename($file)) . '.data';
				$cache_date	= $cache_path . md5(basename($file)) . '.ts';

				if (file_exists($cache_file)) {
					if (file_get_contents($cache_date) != filemtime($file)) {
						$is_cached	= FALSE;
					}
				} else {
					$is_cached	= FALSE;
				}

				// $is_cached	= FALSE;
				if (!$is_cached) {
					$data	= spyc_load_file($file);
					if ($data === FALSE) {
						echo "Error found when parsing translation file:\n\n";
						spyc_load_file($file);
						exit;
					}

					$header	= key($data);
					$lid	= Language::find_first('header="' . $header . '"', [
					    'cache' => TRUE
                    ])->id;
					
					if (!isset(Lang::$strings[$lid])) {
						Lang::$strings[$lid]	= [];
					}

					if (!isset(Lang::$parsed[$lid])) {
						Lang::$parsed[$lid]		= [];
					}

					Lang::$strings[$lid]	= array_merge(
						Lang::$strings[$lid],
						$data[$header]
					);

					if (!function_exists('_locale_cb')) {
						function _locale_cb($items, &$parsed, $level = '') {
							foreach ($items AS $_ => $item) {
								if (is_array($item))
								    _locale_cb($item, $parsed, $level . $_ . '.');
								else
								    $parsed[$level . $_]	= $item;
							}
						};
					}

                    $parsed				= [];
					_locale_cb(Lang::$strings[$lid], $parsed);
					Lang::$parsed[$lid]	= array_merge(Lang::$parsed[$lid], $parsed);

					if (!is_writable(dirname($cache_file))) {
						die("YAML cache dir '" . dirname($cache_path) . "' is not writable!");
					}

					file_put_contents($cache_date, filemtime($file));
					file_put_contents($cache_file, serialize(Lang::$parsed));
				} else {
					$cache_read_data	= unserialize(file_get_contents($cache_file));
					foreach ($cache_read_data as $lid_key => $parsed_data) {
						if (!isset(Lang::$parsed[$lid_key])) {
							Lang::$parsed[$lid_key]	= [];
						}

						Lang::$parsed[$lid_key]	    = array_merge(Lang::$parsed[$lid_key], $parsed_data);
					}
				}
			}
		}
	}

	static function translate($path, $assigns = [], $lid = NULL) {
		if (is_null($lid))
			$lid	= $_SESSION['language_id'];

		$parsed	= Lang::$parsed[$lid];
		if (isset($parsed[$path])) {
			foreach ($assigns AS $key => $value)
				$parsed[$path]	= str_replace('#{' . $key . '}', $value, $parsed[$path]);

			return $parsed[$path];
		} else
			return FALSE;
	}
}
Lang::initialize();

function t($path, $assigns = [], $lid = NULL) {
	$translation	= Lang::translate($path, $assigns, $lid);
	return $translation === false ? '-- TRANSLATION MISSING: ' . $path . ' --' : $translation;
}
function tb($path, $assigns = [], $lid = NULL) {
	$translation	= Lang::translate($path, $assigns, $lid);
	return $translation === false ? false : $translation;		
}
