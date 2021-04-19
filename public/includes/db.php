<?php
Recordset::connect(
	$database['connection'],
	true,
	$database['host'],
	$database['username'],
	$database['password'],
	$database['database']
);
Recordset::$cache_mode	= $database['cache_mode'];
Recordset::$key_prefix	= $database['cache_id'];

Recordset::query('SET NAMES utf8');
