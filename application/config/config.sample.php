<?php 

$config['base_url'] = '/'; // Base URL including trailing slash (e.g. http://localhost/)

$config['default_controller'] = 'main'; // Default controller to load
$config['error_controller'] = 'error'; // Controller used for errors (e.g. 404, 500 etc)

define('DB_ENGINE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', '');
define('DB_PW', '');
define('DB_DBNAME', '');

require(APP_DIR .'config/ponywalls.php');
?>
