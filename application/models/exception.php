<?php
namespace Model;

class Exception extends Model
{
	private $id = null;
	private $errno = 0;
	private $message = "";
	private $file = __FILE__;
	private $line = __LINE__;
	private $stack = "";
	private $query = null;
	private $sqlcode = null;
	private $time = 0;
	
	public function __init($id = null)
	{
		if($id != null)
			$this->load($id);
	}
	
	public function __set($key, $val)
	{
		if(isset($this->$key))
			$this->$key = $val;
		else
			throw new UnknownPropertyException($key);
	}
	
	public function __get($key)
	{
		if(isset($this->$key))
			return $this->$key;
		else
			throw new UnknownPropertyException($key);
	}
	
	public function load($id)
	{
		$this->prepare('SELECT errno, message, file, line, stack, query, sqlcode, time FROM exceptions WHERE id = ?');
		$this->execute($id);
		
		if($data = $this->fetch())
		{
			foreach($data as $key => $el)
				$this->$key = $el;
		}
	}
	
	public function create()
	{
		$this->prepare('INSERT INTO exceptions(file, line, stack, query, sqlcode, errno, message, time) VALUES(?, ?, ?, ?, ?, ?, ?, ?)');
		$this->execute($this->file, $this->line, $this->stack, $this->query, $this->sqlcode, $this->errno, $this->message, $this->time);
		
		$this->id = $this->lastInsertID();
	}
	
	public function save()
	{
		if($this->id === null)
			$this->create();
		else
		{
			$this->prepare('UPDATE exceptions SET file = ?, line = ?, stack = ?, query = ?, sqlcode = ?, errno = ?, message = ?, time = ? WHERE id = ?');
			$this->execute($this->file, $this->line, $this->stack, $this->query, $this->sqlcode, $this->errno, $this->message, $this->id, $this->time);
		}
	}
}
