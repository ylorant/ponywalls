<?php
namespace Model;
use \PDO;
use \Exception\SQLException;
use \Debug;

class Model {

	protected $_PDO;
	protected $_query;
	protected $_curParamID = 0;
	
	public function __construct()
	{
		$this->_PDO = DatabaseHandler::get();
		call_user_func_array(array($this, '__init'), func_get_args());
	}
	
	public function __init()
	{
		
	}
	
	public static function serialize($object, $ignore = array())
	{
		$data = array();
		foreach($object as $name => $value)
		{
			if($name[0] != '_' && !in_array($name, $ignore))
			{
				if(is_object($value))
					$data[$name] = 'object/'.get_class($value).':'.Model::serialize($value);
				else
					$data[$name] = $value;
			}
		}
		
		return serialize($data);
	}
	
	public static function unserialize($data, $object)
	{
		foreach(unserialize($data) as $name => $value)
		{
			if(strpos($value,'/'))
			{
				$parts = explode(':', $value, 2);
				$parts[0] = explode('/', $parts[0], 2);
				if($parts[0][0] == 'object')
				{
					$objName = $parts[0][1];
					$value = new $objName();
					Model::unserialize($parts[1], $value);
				}
			}
			
			$object->$name = $value;
		}
		
		return $object;
	}
	
	public function to_bool($val)
	{
	    return !!$val;
	}
	
	public function to_date($val)
	{
	    return date('Y-m-d', $val);
	}
	
	public function to_time($val)
	{
	    return date('H:i:s', $val);
	}
	
	public function to_datetime($val)
	{
	    return date('Y-m-d H:i:s', $val);
	}
	
	public function beginTransaction()
	{
		$this->_PDO->beginTransaction();
	}
	
	public function commit()
	{
		$this->_PDO->commit();
	}
	
	public function rollback()
	{
		$this->_PDO->rollBack();
	}
	
	public function prepare($query)
	{
		try
		{
			$this->_query = $this->_PDO->prepare($query);
			$this->_curParamID = 0;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	public function bind($name, $value = false, $forceValue = false)
	{
		if($forceValue == false && $value === false)
			list($value, $name) = array($name, ++$this->_curParamID);
		elseif(is_numeric($name))
			$name = ++$this->_curParamID;
		
		
		if(is_int($value))
			$param = PDO::PARAM_INT;
		elseif(is_bool($value))
			$param = PDO::PARAM_BOOL;
		elseif(is_null($value))
			$param = PDO::PARAM_NULL;
		elseif(is_string($value))
			$param = PDO::PARAM_STR;
		else
			$param = FALSE;
		
		$this->_query->bindValue($name, $value, $param);
	}
	
	public function execute()
	{
		$values = array();
		$i = 1;
		
		foreach(func_get_args() as $arg)
		{
			if(is_array($arg))
			{
				foreach($arg as $k => $v)
					$this->bind($k, $v, true);
			}
			else
				$this->bind($arg);
		}
		
		$ret = false;
	    try
	    {
			Debug::query($this->_query->queryString);
			$ret = $this->_query->execute();
		}
	    catch(PDOException $e)
	    {
			throw $e;
		}
	        
	    if($ret == FALSE)
	    {
			$error = $this->_query->errorInfo();
			throw new SQLException($this->_query->queryString, $error[2], $error[0], $error[1]);
	    }
	        
   	    return $ret;
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
		$this->_query->closeCursor();
		$this->_curParamID = 0;
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

class DatabaseHandler
{
	private static $_pdo;
	public static function get()
	{
		if(self::$_pdo === NULL)
		{
			try
			{
				if(DB_ENGINE != 'sqlite')
					self::$_pdo = new PDO(DB_ENGINE.':host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_DBNAME, DB_USER, DB_PW);
				else
				{
					self::$_pdo = new PDO(DB_ENGINE.':'.DB_FILE);
					self::$_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				}
			        
				self::$_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				self::$_pdo->exec('SET NAMES utf8');
			}
			catch(PDOException $e)
			{
				throw $e;
			}
		}
		
		return self::$_pdo;
	}
}

?>
