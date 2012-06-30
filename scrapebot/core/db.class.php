<?php

class Database {

	protected $_PDO;
	protected $_query;
	protected $_curParamID = 1;
	
	public function connect($engine, $host, $port, $user, $password, $db)
	{
		$this->_PDO = new PDO($engine.':host='.$host.';port='.$port.';dbname='.$db, $user, $password);
	}

	public function prepare($query)
	{
		$this->_query = $this->_PDO->prepare($query);
	}
	
	public function bind($name, $value = NULL)
	{
		if($value === NULL)
			list($value, $name) = array($name, ++$this->_curParamID);
		
		$type = PDO::PARAM_STR;
		if(is_int($value))
			$type = PDO::PARAM_INT;
		
		$this->_query->bindValue($name, $value, $type);
	}
	
	public function execute($values = array())
	{
		/*
		foreach($values as $name => $value)
		{
			if($name[0] == ':')
				$this->bind($name, $value);
			else
				$this->bind($value);
		}
		*/
		
		return $this->_query->execute($values);
	}
	
	public function fetch()
	{
		return $this->_query->fetch(PDO::FETCH_ASSOC);
	}
	
	public function fetchAll()
	{
		return $this->_query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function reset()
	{
		$this->_query->resetCursor();
	}
	
	public function drop()
	{
		unset($this->_query);
	}
	
	public function lastInsertID()
	{
		return $this->_PDO->lastInsertId();
	}
}

class DB
{
	private static $instance;
	
	public static function getInstance()
	{
		if(!DB::$instance)
			DB::$instance = new Database();
		
		return DB::$instance;
	}
	
	public static function __callStatic($func, $args)
	{
		$self = DB::getInstance();
		call_user_func_array(array($self, $func), $args);
	}
}
?>
