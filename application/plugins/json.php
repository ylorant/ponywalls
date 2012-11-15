<?php
namespace Plugin;

class JSON
{
	private $data;
	
	public function __construct($data = null)
	{	
		if(is_array($data))
			$this->data = $data;
		elseif(!empty($data))
			$this->data = json_decode($data, true);
		
		if(is_null($this->data))
			$this->data = array();
	}
	
	public function __get($var)
	{
		if(isset($this->data[$var]))
			return $this->$var;
		else
			return null;
	}
	
	public function __set($var, $value)
	{
		$this->data[$var] = $value;
	}
	
	public function __toString()
	{
		return $this->encode();
	}
	
	public static function decode($json)
	{
		return new JSON($json);
	}
	
	public function encode()
	{
		return json_encode($this->data);
	}
}
