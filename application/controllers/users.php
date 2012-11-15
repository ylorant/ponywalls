<?php
namespace Controller;
use \Model\User;
use \Model\Model;
use \Debug;

class Users extends Controller
{
	public function index()
	{
		$this->redirect('index');
	}
	
	//Login
	public function login()
	{
		if(!isset($_POST['login']))
		{
			$_SESSION['message'] = array('error', 'Unknown error.');
			$this->redirect('index');
			exit();
		}
		
		$user = new User();
		$id = $user->check($_POST['login'], $_POST['password']);
		
		if($id === FALSE)
			$_SESSION['message'] = array('error', 'Invalid login.');
		else
		{
			$user->load($id);
			$_SESSION['user'] = Model::serialize($user);
		}
		
		$url = 'index';
		if(!empty($_GET['returnUrl']))
			$url = trim($_GET['returnUrl'],'/');
		$this->redirect($url);
	}
	
	//Logout
	public function logout()
	{
		session_destroy();
		
		$url = 'index';
		if(!empty($_GET['returnUrl']))
			$url = trim($_GET['returnUrl'],'/');
		$this->redirect($url);
	}
	
	//Registering
	public function register()
	{
		if(!isset($_POST['login']))
		{
			$_SESSION['message'] = array('error', 'Unknown error.');
			$this->redirect('index');
			exit();
		}
		
		$user = new User();
		
		if(empty($_POST['login']) || empty($_POST['password']))
			$_SESSION['message'] = array('error', 'Fields are missing.');
		else if($user->exists($_POST['login']))
			$_SESSION['message'] = array('error', 'Username already taken.');
		else if($_POST['password'] != $_POST['passwordcheck'])
			$_SESSION['message'] = array('error', 'Passwords are not the same.');
		else
		{
			$user->login = htmlentities($_POST['login']);
			$user->password = User::hashPassword($_POST['password']);
			$user->create();
			$_SESSION['message'] = array('confirm', 'Welcome to Ponywalls, '.$user->login.' !');
			$_SESSION['user'] = Model::serialize($user);
		}
		
		$url = 'index';
		if(!empty($_GET['returnUrl']))
			$url = trim($_GET['returnUrl'],'/');
		$this->redirect($url);
	}
}
