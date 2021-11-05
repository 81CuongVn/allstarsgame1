<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

define('DB_LOGGING',                false);
define('BACKTRACE_SELECTS',         true);
define('BACKTRACE_UPDATES',         true);
define('BACKTRACE_DELETES',         true);

define('ROOT',						dirname(dirname(__FILE__)));
define('RECORDSET_CACHE_OFF_FORCE', true);

require_once ROOT . '/public/config.php';

date_default_timezone_set(DEFAULT_TIMEZONE);

// Composer modules
require_once ROOT . '/public/vendor/autoload.php';

// Base framework files
require_once ROOT . '/public/includes/autoloader.php';
require_once ROOT . '/public/includes/inflector.php';
require_once ROOT . '/public/includes/shared_store.php';
require_once ROOT . '/public/includes/relation.php';
require_once ROOT . '/public/includes/recordset.php';

// DB
require_once ROOT . '/public/includes/db.php';

if (is_dir(ROOT . '/public/lib')) {
    foreach (glob(ROOT . '/public/lib/*.php') AS $libFile) {
        require_once $libFile;
    }
}

// require_once ROOT . '/public/includes/lang.php';

require_once ROOT . '/public/helpers/global_helpers.php';
require_once ROOT . '/public/helpers/url_helper.php';
