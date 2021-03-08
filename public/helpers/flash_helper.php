<?php
// Should move a a master class later
register_shutdown_function(function () {
	$_SESSION['__flashes']	= [];
});

function set_flash($key, $value) {
	$_SESSION['__flashes'][$key]	= $value;
}

function get_flash($key) {
	if (isset($_SESSION['__flashes'][$key])) {
		return $_SESSION['__flashes'][$key];
	} else {
		return false;
	}
}

if (!isset($_SESSION['__flashes'])) {
	$_SESSION['__flashes']	= [];
}