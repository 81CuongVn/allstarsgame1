<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

define('DB_LOGGING',                FALSE);
define('BACKTRACE_SELECTS',         TRUE);
define('BACKTRACE_UPDATES',         TRUE);
define('BACKTRACE_DELETES',         TRUE);

define('ROOT',						dirname(dirname(__FILE__)));
define('RECORDSET_CACHE_OFF_FORCE', TRUE);

$env = 'dev';
if (!in_array($_SERVER['SERVER_ADDR'], [ '127.0.0.1' ])) {
	$env = 'prod';
}

define('FW_ENV',                    $env);
require ROOT . '/config.' . $env . '.php';

date_default_timezone_set(DEFAULT_TIMEZONE);

require ROOT . '/vendor/autoload.php';
require ROOT . '/helpers/global_helpers.php';

# base framework files
require ROOT . '/includes/autoloader.php';
require ROOT . '/includes/inflector.php';
require ROOT . '/includes/shared_store.php';
require ROOT . '/includes/relation.php';
require ROOT . '/includes/recordset.php';
require ROOT . '/includes/url_helper.php';

# database
require ROOT . '/includes/db.php';

if (is_dir(ROOT . '/lib')) {
    foreach(glob(ROOT . '/lib/*.php') as $libFile) {
        require $libFile;
    }
}