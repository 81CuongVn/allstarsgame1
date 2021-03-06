<?php
function __model_autoloader($klass) {
	global $___memory;

	$class_file = realpath(dirname(__FILE__) . "/../models/{$klass}.php");
	$___memory["before_model_{$klass}"]  = memory_get_usage();

	include_once $class_file;

	if (!class_exists($klass)) {
		throw new Exception("File '{$class_file}' expected to define {$klass}", 1);
	}

	$table_name = '';

	for ($_i = 0; $_i < strlen($klass); $_i++) {
		if ($_i > 0 && ctype_upper($klass[$_i])) {
			$table_name .= '_' . strtolower($klass[$_i]);
		} else {
			$table_name .= strtolower($klass[$_i]);         
		}
	}

	$klass::initialize(Inflector::pluralize($table_name));

	$___memory["after_model_{$klass}"]  = memory_get_usage();
}

spl_autoload_register('__model_autoloader');