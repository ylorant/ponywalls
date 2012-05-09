<?php

class Members extends Controller
{
	public function index()
	{
		header('Location: '.BASE_URL.'index');
	}
	
	//Login
	public function login()
	{
		if(!isset($_POST['login']))
		{
			$_SESSION['message'] = array('error', 'Unknown error.');
			header('Location: '.BASE_URL.'index');
			exit();
		}
		
		$model = $this->loadModel('Users_model');
		$check = $model->checkUser($_POST['login'], $_POST['password']);
		
		if($check === FALSE)
			$_SESSION['message'] = array('error', 'Invalid login.');
		else
			$_SESSION = array_merge($_SESSION, $check);
		
		header('Location: '.BASE_URL.'index');
	}
	
	//Logout
	public function logout()
	{
		session_destroy();
		header('Location: '.BASE_URL.'index');
	}
	
	//Registering
	public function register()
	{
		if(!isset($_POST['login']))
		{
			$_SESSION['message'] = array('error', 'Unknown error.');
			header('Location: '.BASE_URL.'index');
			exit();
		}
		
		$model = $this->loadModel('Users_model');
		
		if(empty($_POST['login']) || empty($_POST['password']))
			$_SESSION['message'] = array('error', 'Fields are missing.');
		else if($model->userExists($_POST['login']))
			$_SESSION['message'] = array('error', 'Username already taken.');
		else if($_POST['password'] != $_POST['passwordcheck'])
			$_SESSION['message'] = array('error', 'Passwords are not the same.');
		else
		{
			$user = $model->createUser($_POST['login'],$_POST['password']);
			$_SESSION['message'] = array('confirm', 'Welcome to Ponywalls, '.$user['login'].' !');
			$_SESSION['login'] = $user['login'];
			$_SESSION['password'] = $user['password'];
			$_SESSION['id'] = $user['id'];
		}
		header('Location: '.BASE_URL.'index');
	}
}
