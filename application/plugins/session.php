<?php
namespace Plugin;

class Session
{
	public function __set($key, $val)
	{
		$_SESSION[$key] = $val;
	}
	
	public function __get($key)
	{
		if(isset($_SESSION[$key]))
			return $_SESSION[$key];
		
		return NULL;
	}
	
	public function __construct()
	{
        global $config;
        if(!empty($config['session_check']))
        {
                $check = explode('=', $config['session_check']);
	        if(isset($_SESSION[$check[0]]) && $_SESSION[$check[0]] == $check[1])
				return;
	        else
	        {
		        $error = new $config['error_controller']();
		        $error->check_failed($check[0], $check[1]);
			}
		}
	}
        
	public function destroy()
	{
		session_destroy();
	}

}

?>
