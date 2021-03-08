<?php
session_start();

$env = 'dev';
if (!in_array($_SERVER['SERVER_ADDR'], [ '127.0.0.1' ]))
	$env = 'prod';

define('FW_ENV',                    $env);

define('ROOT',						dirname(__FILE__));

require_once ROOT . '/config.' . $env . '.php';

date_default_timezone_set(DEFAULT_TIMEZONE);

$maintenance    = ROUND_END <= date('Y-m-d H:i:s');
define('IS_BETA',					FALSE);
define('IS_MAINTENANCE',			$maintenance);
define('MAINTENANCE_CONTROLLER',	'internal');
define('MAINTENANCE_ACTION',		'maintenance');


if (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL']) {
    $_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_URL'];
}

define('DB_LOGGING',		        FALSE);
define('BACKTRACE_SELECTS',	        TRUE);
define('BACKTRACE_UPDATES',	        TRUE);
define('BACKTRACE_DELETES',	        TRUE);

define('RECORDSET_CACHE_OFF_FORCE',	$env == 'dev');

$___clear_cache_key				= 'vaMALORuhvCTTiCGvnDehblfdIJnPNbUak7OxcE1knbPGuwwTuPrpTGCGzdbYVwXBusrqhXcvqqIjhBIetDDPvzOvPaqzLHVE7eb';
$___start						= microtime(TRUE);
$___memory						= [];
$___memory['before_autoload']	= memory_get_usage();

# composer modules
require_once ROOT . '/vendor/autoload.php';

$___memory['after_autoload']	= memory_get_usage();
$___memory['before_includes']	= memory_get_usage();

# base framework files
require_once ROOT . '/includes/autoloader.php';
require_once ROOT . '/includes/controller.php';
require_once ROOT . '/includes/inflector.php';
require_once ROOT . '/includes/mailer.php';
require_once ROOT . '/includes/shared_store.php';
require_once ROOT . '/includes/relation.php';
require_once ROOT . '/includes/recordset.php';
require_once ROOT . '/includes/renderer.php';
require_once ROOT . '/includes/pagseguro.php';
require_once ROOT . '/includes/paypal.php';
require_once ROOT . '/includes/db.php';

$___memory['after_includes']	= memory_get_usage();

if (isset($_GET['__clear_the_damn_cache']) && $_GET['__clear_the_damn_cache'] == $___clear_cache_key) {
    foreach(glob(ROOT . '/cache/recset/RECSET_' . Recordset::$key_prefix . '*') as $cache_file) {
        @unlink($cache_file);
    }
}

# Fix to 'PATH_INFO' ??? Really? Oh My God!
if (!array_key_exists('PATH_INFO', $_SERVER)) {
    $filePath               = str_replace(DIRECTORY_SEPARATOR, '/', getcwd());
    $folderPath             = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
    $requestUriParts        = explode('?', $_SERVER['REQUEST_URI'], 2);
    $_SERVER['PATH_INFO']   = str_replace($folderPath, '', $requestUriParts[0]);
}

if (isset($_SERVER['ORIG_PATH_INFO'])) {
    $_SERVER['PATH_INFO']	= $_SERVER['ORIG_PATH_INFO'];
}

if (!isset($_SERVER['PATH_INFO'])) {
    $_SERVER['PATH_INFO']	= '';
} else {
    if ($_SERVER['PATH_INFO'] == '/') {
        $_SERVER['PATH_INFO']	= '';
    }
}

$params		= [];

if (!$_SERVER['PATH_INFO']) {
    $home	= explode('#', $home);

    $controller	= $home[0];
    $action		= $home[1];
} else {
    $parts		= explode('/' , $_SERVER['PATH_INFO']);
    $parts		= array_splice($parts, 1);

    $controller	= $parts[0];
    $action		= sizeof($parts) > 1 ? $parts[1] : NULL;

    if (sizeof($parts) > 2) {
        $params		= array_splice($parts, 2);
    }

    if (!$action) {
        $action	= 'index';
    }
}

if (IS_MAINTENANCE) {
    if (isset($_GET['is_admin'])) {
        $_SESSION['skip_maintenance']	= TRUE;
    }

    if (!isset($_SESSION['skip_maintenance'])) {
        if (!(($controller == 'users' && (preg_match('/beta|join_complete|beta_activ/', $action))) || $controller == 'captcha')) {
            $controller	= MAINTENANCE_CONTROLLER;
            $action		= MAINTENANCE_ACTION;
        }
    }
}

$___memory['before_libs']	= memory_get_usage();

if (is_dir(ROOT . '/lib')) {
    foreach (glob(ROOT . '/lib/*.php') as $lib) {
        require_once $lib;
    }
}

$___memory['after_libs']		= memory_get_usage();
$___memory['before_langs']	= memory_get_usage();

require_once ROOT . '/includes/lang.php';

$___memory['after_langs']		= memory_get_usage();
$___memory['before_helpers']	= memory_get_usage();

require_once ROOT . '/helpers/global_helpers.php';
require_once ROOT . '/helpers/url_helper.php';
require_once ROOT . '/helpers/bars_helper.php';
require_once ROOT . '/helpers/flash_helper.php';
require_once ROOT . '/helpers/item_helper.php';
require_once ROOT . '/helpers/menu_helper.php';
require_once ROOT . '/helpers/player_helper.php';
require_once ROOT . '/helpers/user_helper.php';
require_once ROOT . '/helpers/pagseguro_helper.php';

$___memory['after_helpers']	= memory_get_usage();
$___memory['before_mailers']	= memory_get_usage();

if (is_dir(ROOT . '/mailers')) {
	foreach (glob(ROOT . '/mailers/*.php') as $mailer) {
		require $mailer;
	}
}

$___memory['after_mailers']	= memory_get_usage();

$controller_file	= 'controllers/' . $controller . '_controller.php';
$controller_class	= '';
$_ignore			= FALSE;

for ($_i = 0; $_i < strlen($controller); $_i++) {
    if ($controller[$_i] == '_') {
        $controller_class	.= strtoupper($controller[$_i + 1]);
        $_ignore			= TRUE;
    } else {
        if (!$_ignore) {
            if ($_i == 0) {
                $controller_class	.= strtoupper($controller[$_i]);
            } else {
                $controller_class	.= $controller[$_i];
            }
        } else {
            $_ignore	= FALSE;
        }
    }
}

$controller_class	.= 'Controller';
$denied				= function (&$instance) {
    require_once ROOT . '/controllers/internal_controller.php';

    $instance	= new InternalController();
    $instance->denied();
};

if (isset($framework_force_denied) && $framework_force_denied) {
    $instance	= NULL;
    $denied($instance);
} else {
    if (!file_exists($controller_file)) {
        require_once ROOT . '/controllers/internal_controller.php';

        $instance	= new InternalController();
        $instance->not_found();
    } else {
        require $controller_file;

        $instance	= new $controller_class();

        if (!method_exists($instance, $action)) {
            require_once ROOT . '/controllers/internal_controller.php';

            $instance	= new InternalController();
            $instance->not_found();
        } else {
            if (isset($instance->denied) && $instance->denied) {
                $denied($instance);
            } else {
                call_user_func_array([&$instance, $action], $params);

                if (isset($instance->denied) && $instance->denied) {
                    $denied($instance);
                }
            }
        }
    }
}

// SID Checker --->
if ($_SESSION['loggedin'] && !$_SESSION['universal']) {
    $rSID = Recordset::query("SELECT session_key FROM users WHERE id=" . $_SESSION['user_id'])->result_array();

    if ($rSID[0]['session_key'] != session_id()) {
        session_destroy();
        redirect_to();
    } else {
        //header("SIDCHECK: OK");
    }
}
// <---

if (isset($instance->render)) {
    if (is_a($instance, 'InternalController')) {
        $view_file	= 'views/' . $instance->render . '.php';
    } else {
        if ($instance->render !== FALSE) {
            $view_file	= 'views/' . $controller . '/' . $instance->render . '.php';
        } else {
            $view_file	= FALSE;
        }
    }
} else {
    $view_file	= 'views/' . $controller . '/' . $action . '.php';
}

$can_render_layout	= TRUE;
$layout_file		= 'views/application.php';

if (isset($instance->layout)) {
    if ($instance->layout === FALSE) {
        $can_render_layout	= FALSE;
    } else {
        $layout_file	= 'views/' . $instance->layout . '.php';
    }
}

if ($can_render_layout) {
    if ($instance->as_json) {
        $layout	= '';
    } else {
        $layout	= render_file($layout_file, $instance->get_assignments());
    }
} else {
    $layout	= '';
}

if ($view_file && !$instance->as_json) {
    $view	= render_file($view_file, $instance->get_assignments());
} else {
    $view	= '';
}

if ($layout) {
    if ($_SESSION['universal']) {
		$view	.= render_file('views/debug.php', array());
	}

    echo str_replace('@yield', $view, $layout);
} else {
    if ($instance->as_json) {
        header('Content-Type: application/json');

        echo json_encode($instance->json);
    } else {
        echo $view;
    }
}