<?php
use Exception\PHPErrorException;
use \View\View;

//Exception handler
function exception_handler(Exception $e)
{
    global $config;
    
    include_once(APP_DIR.'controllers/'.$config['error_controller_file']);                                                                                                                                 
    $error = new $config['error_controller']();
    $return = $error->$config['exception_handler']($e);
    //If the debug mode is enabled, we show the debug trace, else we return a HTTP 500
    if($config['debug'] === true && !$return)
    {
		echo '<h1>Oops ! Something wrong occured !</h1>';
		$view = new View('debug');
		$view->render();
	}
	elseif(!$return)
		$error->HTTPReturnCode(500);
	
	Debug::terminate();
}

//Shortcuts errors by changing them into exceptions
function error_handler($errno, $errstr, $errfile, $errline, array $errcontext)
{
	if($errno == E_ERROR)
		throw new PHPErrorException($errfile, $errline, $errstr, $errno);
	else
		Debug::exception(new PHPErrorException($errfile, $errline, $errstr, $errno));
}


//When needing autoloading, using autoload table
function __autoload($class)
{
	global $config;
	
	if(isset($config['autoload'][$class]))
		include_once($config['autoload'][$class]);
	else
	{
		//If the namespace is specified, try to load the class file from the folder.
		if(strpos($class, '\\') !== FALSE)
		{
			$classNS = explode('\\', $class, 2);
			$paths = array();
			switch($classNS[0])
			{
				case 'Controller':
					$paths[] = ROOT_DIR.'application/controllers/'.strtolower($classNS[1]).'.php';
					break;
				case 'Model':
					$paths[] = ROOT_DIR.'application/models/'.strtolower($classNS[1]).'.php';
					break;
				case 'Plugin':
					$paths[] = ROOT_DIR.'application/plugins/'.strtolower($classNS[1]).'.php';
					break;
				case 'Exception':
					$paths[] = ROOT_DIR.'system/exceptions/'.$classNS[1].'.php';
					$paths[] = ROOT_DIR.'application/exceptions/'.$classNS[1].'.php';
					break;
			}
			
			//Check if file exists in specified paths
			foreach($paths as $path)
			{
				if(is_file($path))
				{
					include_once($path);
					return;
				}
			}
			
			throw new \Exception\UnknownClassException($class); //Not found -> error
		}
		else
			throw new \Exception\UnknownClassException($class); //Not found -> error
	}
}

function shutdown()
{
	global $config;
	
	//If we already got an exception, the handler handled it and already shown the trace, so we quit right now.
	if(Debug::terminated())
		die();
	
	$error = error_get_last();
    if($error !== NULL) //If a fatal error occured, we log it into the debug section
		exception_handler(new PHPErrorException($error['file'], $error['line'], $error['message'], $error['type'])) + die();
    
    if($config['debug'] === true)
	{
		$view = new View('debug');
		$view->render();
	}
}
