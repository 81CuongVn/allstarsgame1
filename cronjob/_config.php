<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

define('DB_LOGGING',                FALSE);
define('BACKTRACE_SELECTS',         TRUE);
define('BACKTRACE_UPDATES',         TRUE);
define('BACKTRACE_DELETES',         TRUE);

define('ROOT',						dirname(dirname(__FILE__)));
define('RECORDSET_CACHE_OFF_FORCE', TRUE);

// $env = 'prod';
// if (array_key_exists('SERVER_ADDR', $_SERVER) && in_array($_SERVER['SERVER_ADDR'], [ '127.0.0.1' ])) {
	$env = 'dev';
// }

define('FW_ENV',                    $env);
require ROOT . '/public/config.' . $env . '.php';

date_default_timezone_set(DEFAULT_TIMEZONE);

require ROOT . '/public/vendor/autoload.php';
require ROOT . '/public/helpers/global_helpers.php';

# base framework files
require ROOT . '/public/includes/autoloader.php';
require ROOT . '/public/includes/inflector.php';
require ROOT . '/public/includes/shared_store.php';
require ROOT . '/public/includes/relation.php';
require ROOT . '/public/includes/recordset.php';
require ROOT . '/public/includes/url_helper.php';

# database
require ROOT . '/public/includes/db.php';

if (is_dir(ROOT . '/public/lib')) {
    foreach(glob(ROOT . '/public/lib/*.php') as $libFile) {
        require $libFile;
    }
}