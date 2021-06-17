<?php
function render_file($__view_file, $vars) {
	global $site_url;
	global $rewrite_enabled;
	global $controller;
	global $action;
	global $params;

	if(!file_exists($__view_file))
		throw new Exception('View was not found at "' . $__view_file . '"');

	extract($vars);

	ob_start();
	require $__view_file;
	return ob_get_clean();
}

function render_mailer($mailer, $method, $vars) {
	global $site_url;
	global $rewrite_enabled;

	$__view_path	= ROOT . '/views/' . $mailer . '/' . $method . '.php';

	extract($vars);
	ob_start();
	require $__view_path;
	return ob_get_clean();
}

function partial($partial, $vars = []) {
	global $site_url;
	global $rewrite_enabled;
	global $controller;
	global $action;
	global $is_admin;

	if (strpos($partial, '/') === false) {
		$__partial_path	= ROOT . '/views/' . ($is_admin ? 'admin' : 'game') . '/' . $controller . '/_' . $partial . '.php';
	} else {
		$__partial_path	= explode('/', $partial);
		$__partial_file	= $__partial_path[sizeof($__partial_path) - 1];
		$__partial_path	= ROOT . '/views/' . ($is_admin ? 'admin' : 'game') . '/' . join('/', array_splice($__partial_path, 0, sizeof($__partial_path) - 1)) . '/_' . $__partial_file . '.php';
	}

	if (!file_exists($__partial_path))
		throw new Exception("Partial '" . $partial . "' not found", 1);

	extract($vars);

	ob_start();
	require $__partial_path;
	return ob_get_clean();
}
