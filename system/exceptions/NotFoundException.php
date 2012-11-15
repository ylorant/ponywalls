<?php
namespace Exception;
use \Debug;

class NotFoundException extends FrameworkException
{
	protected $errfile;
	protected $errline;
	
	public function __construct($page = null, $message = null, $code = null, Exception $previous = null)
	{
		if($code == null)
			$code = 0x07;
		
		if($message == null)
			$message = 'Page '.(!empty($page) ? $page.' ' : '').'not found';
		
		parent::__construct(null, $message, $code, $previous);
	}
}
