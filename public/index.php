<?php
class WarningWithStacktrace extends ErrorException {}
set_error_handler(function($severity, $message, $file, $line) {
	if ((error_reporting() & $severity)) {
		if ($severity & (E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE)) {
			throw new ErrorException($message, 0, $severity, $file, $line);
		}
	}
});

session_start();

define('ROOT',						dirname(__FILE__));
require_once ROOT . '/config.php';

date_default_timezone_set(DEFAULT_TIMEZONE);

// Inicialização da sessão --->
	if (!isset($_SESSION['language_id']))		$_SESSION['language_id']	= 1;
	if (!isset($_SESSION['user_id']))			$_SESSION['user_id']		= null;
	if (!isset($_SESSION['player_id']))			$_SESSION['player_id']		= null;
	if (!isset($_SESSION['loggedin']))			$_SESSION['loggedin']		= false;
	if (!isset($_SESSION['universal']))			$_SESSION['universal'] 		= false;
	if (!isset($_SESSION['orig_user_id']))		$_SESSION['orig_user_id']	= 0;
	if (!isset($_SESSION['orig_player_id']))	$_SESSION['orig_player_id']	= 0;
// <---

if (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL']) {
	$_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_URL'];
}

define('DB_LOGGING',				true);
define('BACKTRACE_SELECTS',			true);
define('BACKTRACE_UPDATES',			true);
define('BACKTRACE_DELETES',			true);
// define('RECORDSET_CACHE_OFF_FORCE',	FW_ENV == 'dev');

$___clear_cache_key				= 'vaMALORuhvCTTiCGvnDehblfdIJnPNbUak7OxcE1knbPGuwwTuPrpTGCGzdbYVwXBusrqhXcvqqIjhBIetDDPvzOvPaqzLHVE7eb';
$___start						= microtime(true);
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
	Recordset::clearCache();
}

# Fix to 'PATH_INFO' ??? Really? Oh My God!
if (!array_key_exists('PATH_INFO', $_SERVER)) {
	$filePath				= str_replace(DIRECTORY_SEPARATOR, '/', getcwd());
	$folderPath				= str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
	$requestUriParts		= explode('?', $_SERVER['REQUEST_URI'], 2);
	$_SERVER['PATH_INFO']	= str_replace($folderPath, '', $requestUriParts[0]);
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

$is_admin	= false;
if (!$_SERVER['PATH_INFO']) {
	$home	= explode('#', SITE_HOME);

	$controller	= $home[0];
	$action		= $home[1];
} else {
	$parts		= explode('/' , $_SERVER['PATH_INFO']);
	$parts		= array_splice($parts, 1);

	if ($parts[0] != 'admin') {
		$controller	= $parts[0];
		$action		= sizeof($parts) > 1 ? $parts[1] : null;

		if (sizeof($parts) > 2) {
			$params		= array_splice($parts, 2);
		}
	} else {
		$is_admin	= true;
		if (sizeof($parts) > 1) {
			$controller	= $parts[1];
			$action		= sizeof($parts) > 2 ? $parts[2] : null;
			$params		= array_splice($parts, 3);
		} else {
			$home		= explode('#', SITE_HOME);
			$controller	= $home[0];
			$action		= $home[1];
		}
	}

	if (!$action) {
		$action	= 'index';
	}
}

$___memory['before_libs']		= memory_get_usage();

if (is_dir(ROOT . '/lib')) {
	foreach (glob(ROOT . '/lib/*.php') as $lib) {
		require_once $lib;
	}
}

$___memory['after_libs']		= memory_get_usage();
$___memory['before_langs']		= memory_get_usage();

require_once ROOT . '/includes/lang.php';

$___memory['after_langs']		= memory_get_usage();
$___memory['before_helpers']	= memory_get_usage();

require_once ROOT . '/helpers/global_helpers.php';
require_once ROOT . '/helpers/url_helper.php';
require_once ROOT . '/helpers/bars_helper.php';
require_once ROOT . '/helpers/item_helper.php';
require_once ROOT . '/helpers/menu_helper.php';
require_once ROOT . '/helpers/player_helper.php';
require_once ROOT . '/helpers/user_helper.php';

$___memory['after_helpers']     = memory_get_usage();
$___memory['before_mailers']	= memory_get_usage();

if (is_dir(ROOT . '/mailers')) {
	foreach (glob(ROOT . '/mailers/*.php') as $mailer) {
		require $mailer;
	}
}

$___memory['after_mailers']     = memory_get_usage();

$controller_file	= 'controllers/' . ($is_admin ? 'admin' : 'game') . '/' . $controller . '_controller.php';
$controller_class	= '';
$_ignore			= false;

for ($_i = 0; $_i < strlen($controller); $_i++) {
	if ($controller[$_i] == '_') {
		$controller_class	.= strtoupper($controller[$_i + 1]);
		$_ignore			= true;
	} else {
		if (!$_ignore) {
			if ($_i == 0) {
				$controller_class	.= strtoupper($controller[$_i]);
			} else {
				$controller_class	.= $controller[$_i];
			}
		} else {
			$_ignore	= false;
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
	$instance	= null;
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

if (isset($instance->render)) {
	if (is_a($instance, 'InternalController')) {
		$view_file	= 'views/' . ($is_admin ? 'admin' : 'game') . '/' . $instance->render . '.php';
	} else {
		if ($instance->render !== false) {
			$view_file	= 'views/' . ($is_admin ? 'admin' : 'game') . '/' . $controller . '/' . $instance->render . '.php';
		} else {
			$view_file	= false;
		}
	}
} else {
	$view_file	= 'views/' . ($is_admin ? 'admin' : 'game') . '/' . $controller . '/' . $action . '.php';
}

$can_render_layout	= true;
$layout_file		= 'views/' . ($is_admin ? 'admin' : 'game') . '/application.php';

if (isset($instance->layout)) {
	if ($instance->layout === false) {
		$can_render_layout	= false;
	} else {
		$layout_file	= 'views/' . ($is_admin ? 'admin' : 'game') . '/' . $instance->layout . '.php';
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
	if ($_SESSION['universal'] && !$is_admin) {
		$view	.= render_file('views/debug.php', []);
	}

	echo str_replace('@yield', $view, $layout);
} else {
	if ($instance->as_json) {
		header('Content-Type: application/json');

		if ($_SESSION['universal']) {
			echo json_encode($instance->json, JSON_PRETTY_PRINT);
		} else {
			echo json_encode($instance->json);
		}
	} else {
		echo $view;
	}
}
