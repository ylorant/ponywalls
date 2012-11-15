<?php
namespace Exception;

class UnknownPropertyException extends FrameworkException
{
	protected $property;
	
	public function __construct($property, $object = null, $message = "", $code = 0, Exception $previous = null)
	{
		if(empty($message))
			$message = "Unknown property: ".$property;
		
		if($code == 0)
			$code = 0x03;
		
		parent::__construct($object, $message, $code, $previous);
	}
	
	public function __toString()
	{
		return parent::__toString();
	}
}
