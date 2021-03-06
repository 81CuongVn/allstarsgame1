<?php
set_time_limit(0);
ini_set('memory_limit', '-1');
date_default_timezone_set("America/Sao_Paulo");

define('ROOT',				dirname(dirname(__FILE__)));
define('DB_LOGGING',		FALSE);
define('BACKTRACE_SELECTS',	TRUE);
define('BACKTRACE_UPDATES',	TRUE);
define('BACKTRACE_DELETES',	TRUE);

// No cache for crons! They'll be sad I know, but it's necessary :(
define('RECORDSET_CACHE_OFF_FORCE', TRUE);

# base framework files
require ROOT . '/includes/autoloader.php';
require ROOT . '/includes/inflector.php';
require ROOT . '/includes/shared_store.php';
require ROOT . '/includes/relation.php';
require ROOT . '/includes/recordset.php';
require ROOT . '/includes/url_helper.php';

# database
require ROOT . '/config.php';
require ROOT . '/includes/db.php';

if (is_dir(ROOT . '/lib')) {
    foreach(glob(ROOT . '/lib/*.php') as $libFile) {
        require $libFile;
    }
}