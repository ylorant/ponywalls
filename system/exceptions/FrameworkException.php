<?php
namespace Exception;
use \Exception;

class FrameworkException extends Exception
{
	protected $object;
	
	public function __construct($object = null, $message = null, $code = null, Exception $previous = null)
	{
		$this->object = $object;
		
		if($code == null)
			$code = 0x01;
		
		parent::__construct($message, $code, $previous);
	}
	
	public function __toString()
	{
		$className = explode('\\', get_class($this));
		$className = end($className);
		$str = "Framework Exception (".$className."): ";
		$str .=	"\n".'Class: '.(is_object($this->object) ? get_class($this->object) : $this->object)."\n".
				'File: '.(!empty($this->file) ? $this->file : 'Unknown')."\n".
				'Line: '.(!empty($this->line) ? $this->line : 'Unknown')."\n".
				'Code: 0x'.(!empty($this->code) ? dechex($this->code) : '0')."\n".
				'Message: '.(!empty($this->message) ? $this->message : 'None')."\n\n".
				"Stack trace:\n----------\n".$this->getTraceAsString();
		
		return $str;
	}
}
