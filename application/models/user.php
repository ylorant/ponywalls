<?php
namespace Model;

class User extends Model
{
	public $id;
	public $login;
	public $password;
	
	public function __init($id = null)
	{
		if($id !== null)
			$this->load($id);
	}
	
	public function load($id)
	{
		$this->prepare('SELECT id, login, password FROM users WHERE id = ?');
		$this->execute($id);
		
		if($data = $this->fetch())
		{
			$this->id = $data['id'];
			$this->login = $data['login'];
			$this->password = $data['password'];
			
			return TRUE;
		}
		else
			return FALSE;
	}
	
	public function check($login, $password)
	{
		
		$password = self::hashPassword($password);
		$this->prepare('SELECT id FROM users WHERE login = ? AND password = ?');
		
		$this->execute(array($login, $password));
		
		if($data = $this->fetch())
			return $data['id'];
		else
			return FALSE;
	}
	
	public function exists($user)
	{
		$this->prepare('SELECT id FROM users WHERE login = ?');
		$this->execute(array($user));
		
		if($this->fetch())
			return TRUE;
		else
			return FALSE;
	}
	
	public function create()
	{
		$this->prepare('INSERT INTO users (login, password) VALUES(?, ?)');
		$this->execute(array($this->login, $this->password));
		$this->id = $this->lastInsertID();
		
		return $this->lastInsertID();
	}
	
	public static function hashPassword($password)
	{
		global $config;
		
		return sha1($config['hash_salt'].$password);
	}
}
