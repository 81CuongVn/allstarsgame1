<?php
	function make_url($to = '', $params = array(), $ignore_path = false) {
		global $rewrite_enabled, $site_url;

		$to				= explode('#', $to);

		if(sizeof($params)) {
			$final_params	= array();

			foreach($params as $_ => $value) {
				$final_params[]	= urlencode($_) . '=' . urlencode($value);
			}

			$final_params	= '?' . join('&', $final_params);
		} else {
			$final_params	= '';
		}

		if(!$to[0] && !$rewrite_enabled) {
			return ($ignore_path ? '' : $site_url . ($rewrite_enabled ? '/' : '/index.php'));
		} else {
			return ($ignore_path ? '' : $site_url . ($rewrite_enabled ? '/' : '/index.php/')) . $to[0] . (isset($to[1]) ? '/' . $to[1] : '') . $final_params;
		}
	}

	function resource_url($path) {
		global $site_url;
		return $site_url . '/' . $path;
	}

	function asset_url($path) {
		global $site_url;
		return $site_url . '/assets/' . $path  . '?v=1.2';
	}

	function image_url($path) {
		global $site_url;
		return $site_url . '/assets/images/' . $path  . '?v=1.2';
	}

	function redirect_to($to = '', $params = array()) {
		$url	= make_url($to, $params);

		if(!headers_sent()) {
			header('Location: ' . $url);
			die();
		}
	}