<?php
namespace Controller;
use \View\View;

class Controller
{
	public function redirect($loc)
	{
		global $config;
		
		header('Location: '. $config['base_url'] . $loc);
		die();
	}
    
    public function HTTPReturnCode($code)
    {
		require(ROOT_DIR.'system/utils/http_codes.php');
		if(isset($httpErrorCodes[$code]))
			header('HTTP/1.1 '.$code.' '.$httpErrorCodes[$code]);
		else
			header('HTTP/1.1 500 Internal Server Error');
	}
}

?>
