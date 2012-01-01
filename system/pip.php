<?php

function pip()
{
	global $config;
    
    // Set our defaults
    $controller = $config['default_controller'];
    $action = 'index';
    $url = '';
    
	// Get request url and script url
	$request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
	$script_url  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';
    
	// Get our url path and trim the / of the left and the right
	if($request_url != $script_url) $url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
    
    if($url == "google156fa52c99afca5d.html")
		die(file_get_contents('google156fa52c99afca5d.html'));
    
	// Split the url into segments
	$segments = explode('/', $url, 3);
	
	// Do our default checks
	if(isset($segments[0]) && $segments[0] != '') $controller = $segments[0];
	if(isset($segments[1]) && $segments[1] != '') $action = $segments[1];
	// Get our controller file
    $path = APP_DIR . 'controllers/' . $controller . '.php';
    
    $params = array();
	if(file_exists($path)){
		if(isset($segments[2]))
			$params = explode('/', $segments[2]);
		else
			$params = array();
        require_once($path);
	} else {
		$path = APP_DIR . 'controllers/' . $config['default_controller'] . '.php';
		require_once($path);
		
		if(method_exists($config['default_controller'], $controller)){
			$params = explode('/', array_pop($segments));
			if(count($segments) >= 2)
				array_unshift($params, array_pop($segments));
			$action = $controller;
			$controller = $config['default_controller'];
		} else {
			$params = explode('/', $url);
			$controller = $config['error_controller'];
			require_once(APP_DIR . 'controllers/' . $controller . '.php');
		}
	}
    
    // Check the action exists
    if(!method_exists($controller, $action)){
        $controller = $config['error_controller'];
        require_once(APP_DIR . 'controllers/' . $controller . '.php');
        $action = 'index';
    }
	
	// Create object and call method
	$obj = new $controller;
    call_user_method_array($action, $obj, $params);
    die();
}

?>
