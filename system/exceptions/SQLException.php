<?php
namespace Exception;

class SQLException extends FrameworkException
{
	protected $query;
	protected $sqlerror;
	protected $sqlerrno;
	protected $sqlstate;
	
	public function __construct($query, $sqlerror = null, $sqlstate = '000O0', $sqlerrno = '0', $object = null, $message = null, $code = null, Exception $previous = null)
	{
		if($message == null && $sqlerror != null)
			$message = 'SQL Error: '.$sqlerror;
		
		if($code == null)
			$code = 0x05;
			
		$this->query = $query;
		$this->sqlerror = $sqlerror;
		$this->sqlstate = $sqlstate;
		$this->sqlerrno = $sqlerrno;
		
		parent::__construct($object, $message, $code, $previous);
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
				'Message: '.(!empty($this->message) ? $this->message : 'None')."\n".
				'SQL Query: '.$this->query."\n".
				'SQLState: '.$this->sqlstate."\n".
				'Driver Error code: '.$this->sqlerrno."\n\n".
				"Stack trace:\n----------\n".$this->getTraceAsString();
		
		return $str;
	}
}
