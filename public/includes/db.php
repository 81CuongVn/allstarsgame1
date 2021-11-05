<?php
$database	= DATABASE;

Recordset::connect(
	$database['connection'],
	true,
	$database['host'],
	$database['username'],
	$database['password'],
	$database['database']
);
Recordset::$key_prefix	= $database['cache_id'];
Recordset::$cache_mode	= $database['cache_mode'];
