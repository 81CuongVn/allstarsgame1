<?php
function make_url($to = '', $params = [], $ignore_path = false) {
	$to	= explode('#', $to);

	if (sizeof($params)) {
		$final_params	= [];
		foreach ($params as $_ => $value) {
			$final_params[]	= urlencode($_) . '=' . urlencode($value);
		}

		$final_params	= '?' . join('&', $final_params);
	} else {
		$final_params	= '';
	}

	if (!$to[0] && !REWRITE_ENABLED) {
		return ($ignore_path ? '' : SITE_URL . (REWRITE_ENABLED ? '/' : '/index.php'));
	} else {
		$url	= implode('/', $to);
		return ($ignore_path ? '' : SITE_URL . (REWRITE_ENABLED ? '/' : '/index.php/')) .$url . $final_params;
	}
}

function resource_url($path) {
	return SITE_URL . '/' . $path;
}

function asset_url($path) {
	return SITE_URL . '/assets/' . $path  . '?v=' . GAME_VERSION;
}

function image_url($path) {
	return SITE_URL . '/assets/images/' . $path  . '?v=' . GAME_VERSION;
}

function redirect_to($to = '', $params = []) {
	$url	= make_url($to, $params);
	if (!headers_sent()) {
		header('Location: ' . $url);
		die();
	}
}
