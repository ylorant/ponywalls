<?php

class Main extends Controller
{
	//Main page (search area mainly)
	public function index()
	{
		$sentenceList = explode("\n", trim(file_get_contents('static/sentences.txt')));
		$template = $this->loadView('main_view');
		$template->set('titleSentence', str_replace('\n', '<br />',$sentenceList[rand(0, count($sentenceList) - 1)]));
		
		if(isset($_SESSION['message']))
		{
			$template->set('message', $_SESSION['message'][0]);
			$template->set('errorMsg', $_SESSION['message'][1]);
			unset($_SESSION['message']);
		}
		
		if(isset($_SESSION['login']))
		{
			$template->set('logged', TRUE);
			$template->set('userData', $_SESSION);
		}
		
		$template->render();
	}
	
	//Login
	public function login()
	{
		if(!isset($_POST['login']))
		{
			$_SESSION['message'] = array('error', 'Unknown error.');
			header('Location:index');
			exit();
		}
		
		$model = $this->loadModel('Users_model');
		$check = $model->checkUser($_POST['login'], $_POST['password']);
		
		if($check === FALSE)
			$_SESSION['message'] = array('error', 'Invalid login.');
		else
			$_SESSION = array_merge($_SESSION, $check);
		
		header('Location:index');
	}
	
	//Logout
	public function logout()
	{
		session_destroy();
		header('Location:index');
	}
	
	//Registering
	public function register()
	{
		if(!isset($_POST['login']))
		{
			$_SESSION['message'] = array('error', 'Unknown error.');
			header('Location:index');
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
		header('Location:index');
	}
	
	//Performing a search
	public function search($data = '')
	{
		global $config;
		
		if(isset($_POST['search']))
		{
			header('Location:search/'.urlencode($_POST['search']));
			exit();
		}
		
		$data = urldecode($data);
		
		if(!$data)
		{
			header('Location:index');
			exit();
		}
		
		$model = $this->loadModel('Walls_model');
		$keywords = explode(' ', $data);
		$results = $model->searchWallpaper($keywords);
		
		$template = $this->loadView('search_view');
		$template->set('results', $results);
		$template->render();
	}
	
	public function view($id)
	{
		$model = $this->loadModel('Walls_model');
		$wall = $model->getWallpaper($id);
		
		header('Location:../static/wall/'.$wall['filename']);
	}
	
	public function add_ajax($tags)
	{
		$fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
		if ($fn)
		{  
			// AJAX call  
			file_put_contents(  
				'static/tmp/' . $fn,  
				file_get_contents('php://input')  
			);
			$_FILES = array();
			$_FILES['file'] = array('size' => filesize('static/tmp/'.$fn), 'tmp_name' => 'static/tmp/'.$fn, 'error' => 0, 'name' => $fn);
			$_POST = array();
			$_POST['tags'] = urldecode($tags);
			$ret = $this->add(TRUE);
			if(!$ret)
			{
				unlink('static/tmp/'.$fn);
/*
				
					unlink('static/wall/'.$fn);
					unlink('static/thumbs/'.$fn);
*/
			}
			echo 'ok';
		}
	}
	
	//Adding a wallpaper
	public function add($ajax = FALSE)
	{
		if(!isset($_FILES['file']))
		{
			$_SESSION['message'] = array('error', 'Unknown error.');
			if(!$ajax)
				header('Location:index');
			return FALSE;
		}
	
		//Checks if an error occured during the image transfer
		if($_FILES['file']['error'] > 0)
		{
			$_SESSION['message'] = array('error', 'Transfer error.');
			if(!$ajax)
				header('Location:index');
			return FALSE;
		}
	
		//Checks the size, in case that the browser failed to limit it
		if($_FILES['file']['size'] > MAX_FILE_SIZE)
		{
			$_SESSION['message'] = array('error', 'File too big.');
			if(!$ajax)
				header('Location:index');
			return FALSE;
		}
	
		//Checking file format (by MIME)
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
		$allowed_mimetypes =  array('image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/x-windows-bmp');
		if(!$ajax)
		{
			if(!in_array($mime, $allowed_mimetypes))
			{
				$_SESSION['message'] = array('error', 'File type not allowed : '.$mime);
				if(!$ajax)
					header('Location:index');
				return FALSE;
			}
		}
		
		//Finally, saving the wallpaper
		$dest_ext = explode('.', $_FILES['file']['name']);
		$dest_ext = array_pop($dest_ext);
		$dest_name = md5(uniqid(rand(), true)); //Boooooh I'm copying code :'(
		$dest_filename = 'static/wall/'.$dest_name.'.'.$dest_ext;
		$orig_filename = $_FILES['file']['name'];
		if(!$ajax)
			move_uploaded_file($_FILES['file']['tmp_name'], $dest_filename);
		else
			rename($_FILES['file']['tmp_name'], $dest_filename);
		
		//Creating thumbnail
		switch($mime)
		{
			case 'image/jpeg':
				$src_image = imagecreatefromjpeg($dest_filename);
				break;
			case 'image/png':
				$src_image = imagecreatefrompng($dest_filename);
				break;
			case 'image/gif':
				$src_image = imagecreatefromgif($dest_filename);
				break;
			case 'image/bmp':
			case 'image/x-windows-bmp':
				$src_image = imagecreatefromwbmp($dest_filename);
				break;
		}
		
		$sx = imagesx($src_image);
		$sy = imagesy($src_image);
		
		$dx = 200; $dy = 125;
		
		if($sx > 1.6 * $sy)
			$dy = (200 * $sy) / $sx; //Using a cross product
		else
			$dx = (125 * $sx) / $sy;
		
		$dst_image = imagecreatetruecolor(200, 125);
		imagecopyresampled($dst_image, $src_image, 100 - ($dx / 2), 63 - ($dy / 2), 0, 0, $dx, $dy, $sx, $sy);
		
		imagepng($dst_image, 'static/thumbs/'.$dest_name.'.png');
		
		//Adding entry in database
		$model = $this->loadModel('Walls_model');
		$model->addWallpaper($dest_name.'.'.$dest_ext, $orig_filename, $sx.'x'.$sy, $_POST['tags'], (isset($_SESSION['id']) ? $_SESSION['id'] : -1));
		
		$_SESSION['message'] = array('confirm', 'Your wallpaper is uploaded!');
		if(!$ajax)
			header('Location:index');
		
		return TRUE;
	}
	
	public function about()
	{
		$template = $this->loadView('main_view');
		$template->render();
	}
    
}

?>
