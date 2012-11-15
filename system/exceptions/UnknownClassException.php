<?php
namespace Exception;

class UnknownClassException extends FrameworkException
{
	public function __construct($object = null, $message = "", $code = 0, Exception $previous = null)
	{
		if($message == null)
			$message = 'Unknown class '.$object;
		
		if($code == 0)
			$code = 0x02;
		
		parent::__construct($object, $message, $code, $previous);
	}
}
