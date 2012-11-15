<?php
namespace Controller;
use \Exception, \Debug;
use \View\View;

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
    
    if(strpos($request_url, '?') !== false)
    {
		$parts = explode('?', $request_url, 2);
		$request_url = $parts[0];
		parse_str($parts[1], $_GET);
	}
    
    Debug::show($request_url, "System/Request URL");
    
	// Get our url path and trim the / of the left and the right
	if($request_url != $script_url) $url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
	
	define('REQUESTED_PAGE', $url);
	Debug::show($_SESSION, 'System/Session');
	
    //                //
	// Static routing //
	//                //
	
    if(isset($config['routes']) && !empty($config['routes']))
    {
		foreach($config['routes'] as $route)
		{
			if($route->match($url))
			{
				$call = explode('::', $route->mapping());
				
				if($call[0][0] != '\\')
					$call[0] = '\\Controller\\'.$call[0];
				
				$class = $call[0];
				$func = $call[1];
				$obj = new $class();
				
				if($route->callback !== null)
				{
					$cb = $route->callback;
					$cb($obj);
				}
				
				call_user_func_array(array($obj, $func), $route->matches);
				die();
			}
		}
	}
	
	//                            //
	// Dynamic routing (outdated) //
	//                            //
	/*
	// Split the url into segments
	$segments = explode('/', $url, 3);
	
	// Do our default checks
	if(isset($segments[0]) && $segments[0] != '') $controller = $segments[0];
	if(isset($segments[1]) && $segments[1] != '') $action = $segments[1];
	
	//Check if the controller file exists, and if not, rollback on the default controller.
    $params = array();
	if(file_exists(APP_DIR.'controllers/'.$controller.'.php')){
		if(isset($segments[2]))
			$params = explode('/', $segments[2]);
		else
			$params = array();
        
        $controller = 'Controller\\'.$controller;
	} else {
		if($controller == $config['default_controller'])
			$controller = $action;
		if(method_exists($config['default_controller'], $controller)){
			$params = explode('/', array_pop($segments));
			if(count($segments) >= 2)
				array_unshift($params, array_pop($segments));
			$action = $controller;
			$controller = $config['default_controller'];
		} else {
			$params = explode('/', $url);
			$controller = $config['error_controller'];
		}
	}
    
    // Error handling (404)
    if(!method_exists($controller, $action)){*/
        $controller = $config['error_controller'];
        $action = 'index';
    //}
	
	// Create object and call method
	$obj = new $controller;
    call_user_func_array(array( $obj, $action), $route->matches);
    
    die();
}

?>
