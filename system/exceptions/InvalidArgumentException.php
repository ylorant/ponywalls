<?php
namespace Exception;

class InvalidArgumentException extends FrameworkException
{
	protected $query;
	protected $sqlerror;
	protected $sqlerrno;
	
	public function __construct($name, $value = null, $object = null, $message = null, $code = null, Exception $previous = null)
	{
		if($message == null)
		{
			$message = 'Invalid argument $'.$name.' ('.gettype($value).'), value: '.$value;
		}	
		
		if($code == null)
			$code = 0x04;
		
		parent::__construct($object, $message, $code, $previous);
	}
}
