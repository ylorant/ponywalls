<?php

class Users_model extends Model
{
	public function checkUser($login, $password)
	{
		$password = sha1(md5($password));
		$this->prepare('SELECT id, login, password FROM users WHERE login = ? AND password = ?');
		
		$this->execute(array($login, $password));
		
		if($data = $this->fetch())
			return $data;
		else
			return FALSE;
	}
	
	public function userExists($user)
	{
		$this->prepare('SELECT id FROM users WHERE login = ?');
		$this->execute(array($user));
		
		if($this->fetch())
			return TRUE;
		else
			return FALSE;
	}
	
	public function createUser($login, $password)
	{
		$password = sha1(md5($password));
		$login = htmlentities($login);
		$this->prepare('INSERT INTO users (login, password) VALUES(?, ?)');
		$this->execute(array($login, $password));
		
		$return = array('login' => $login, 'password' => $password, 'id' => $this->lastInsertID());
		
		return $return;
	}
}
