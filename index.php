<?php
/*
 * PIP v0.5.3
 */

//Start the Session
session_start(); 

// Defines
define('ROOT_DIR', realpath(dirname(__FILE__)) .'/');
define('APP_DIR', ROOT_DIR .'application/');
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
if(!$_SERVER['QUERY_STRING'])
	define('CURRENT_PAGE', '/index');
else
	define('CURRENT_PAGE', $_SERVER['QUERY_STRING']);

// Apply core configuration
global $config;

//Populating autoload table with default classes (Controller, Model and View)
$config['autoload'] = array();

$config['autoload']['Controller\Controller'] = ROOT_DIR .'system/mvc/controller.php';
$config['autoload']['Model\Model'] = ROOT_DIR .'system/mvc/model.php';
$config['autoload']['View\View'] = ROOT_DIR .'system/mvc/view.php';
$config['autoload']['Debug'] = ROOT_DIR .'system/utils/debug.php';
$config['autoload']['Route'] = ROOT_DIR.'system/utils/route.php';

// Includes
require(ROOT_DIR .'system/utils/handlers.php');
require(ROOT_DIR .'system/pip.php');
require(APP_DIR .'config/config.php');


set_exception_handler('exception_handler');
set_error_handler('error_handler');
register_shutdown_function('shutdown');
define('BASE_URL', $config['base_url']);
Controller\pip();

?>
