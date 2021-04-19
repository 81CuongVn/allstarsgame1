<?php
class Inflector {
	private static $plurals	= array(
		'([aeiouy]o)$'		=> '$1s',
		'([^aeiouy]o)$'		=> '$1es',
		'([aeiou]y)$'		=> '$1s',
		'y$'				=> 'ies',
		'(ch|s|sh|x|z)$'	=> '$1es',
		'(f|fe)$'			=> 'ves',
		'(.)$'				=> '$1s'
	);

	private static $irregulars	= array(
		'child'	=> 'children'
	);

	static function singularize() {

	}

	static function pluralize($word) {
		$words	= explode(' ', $word);
		$final	= array();

		foreach($words as $word) {
			$found	= false;

			foreach(Inflector::$irregulars as $_ => $plural) {
				if(preg_match('/' . $_ . '/i', $word)) {
					$final[]	= preg_replace('/' . $_ . '/i', $plural, $word);
					$found		= true;

					break;
				}
			}

			if(!$found) {
				foreach(Inflector::$plurals as $_ => $plural) {
					if(preg_match('/' . $_ . '/i', $word)) {
						$final[]	= preg_replace('/' . $_ . '/i', $plural, $word);
						$found		= true;

						break;
					}
				}
			}

			if(!$found) {
				$final[]	= $word;
			}
		}

		return join(' ', $final);
	}
}
