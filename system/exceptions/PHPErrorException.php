<?php
namespace Exception;
use \Debug;

class PHPErrorException extends FrameworkException
{
	protected $errfile;
	protected $errline;
	
	public function __construct($errfile = null, $errline = null, $message = null, $code = null, Exception $previous = null)
	{
		$this->errfile = $errfile;
		$this->errline = $errline;
		
		if($code == null)
			$code = 0x06;
		
		parent::__construct(null, $message, $code, $previous);
	}
	
	public function __toString()
	{
		switch($this->code)
		{
			case E_ERROR:
				$codestr = 'Fatal error (E_ERROR)';
				break;
			case E_WARNING:
				$codestr = 'Warning (E_WARNING)';
				break;
			case E_NOTICE:
				$codestr = 'Notice (E_NOTICE)';
				break;
			default:
				$codestr = 'Unknown(0x'.(!empty($this->code) ? dechex($this->code) : '0').')';
		}
		
		$str = "PHP Error: ";
		$str .=	"\n".
				'File: '.(!empty($this->errfile) ? $this->errfile : 'Unknown')."\n".
				'Line: '.(!empty($this->errline) ? $this->errline : 'Unknown')."\n".
				'Gravity: '.$codestr."\n".
				'Message: '.(!empty($this->message) ? $this->message : 'None');
				
		if($this->code != E_ERROR) //If it's not a fatal error, we print the error.
			$str .= "\n\nStack trace:\n----------\n".$this->getTraceAsString();
		
		return $str;
	}
}
