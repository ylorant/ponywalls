<?php
namespace Controller;
use \Model\Wallpaper;
use \Model\Model;
use \Model\User;
use \Plugin\JSON;
use \Debug;

class Wallpapers extends Controller
{
	private $user;
	
	public function __construct()
	{
		if(!empty($_SESSION['user']))
			$this->user = Model::unserialize($_SESSION['user'], new User());
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
			
			//Simulate a regular upload, to call the regular upload method, much shorter than copy the code in the method below
			$_FILES = array();
			$_FILES['file'] = array('size' => filesize('static/tmp/'.$fn), 'tmp_name' => 'static/tmp/'.$fn, 'error' => 0, 'name' => $fn);
			$_POST = array();
			$_POST['tags'] = urldecode($tags);
			$ret = $this->add(TRUE);
			if(!$ret && is_file('static/tmp/'.$fn))
				unlink('static/tmp/'.$fn);
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
				$this->redirect('index');
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
		if(!in_array($mime, $allowed_mimetypes))
		{
			$_SESSION['message'] = array('error', 'File type not allowed : '.$mime);
			if(!$ajax)
				header('Location:index');
			return FALSE;
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
		
		$md5 = md5_file($dest_filename);
		$wallpaper = new Wallpaper();
		
		//Checking if the MD5 already exists in the database, i.e. if the wallpaper has already been uploaded.
		if(!$wallpaper->MD5Exists($md5))
		{
			unlink($dest_filename); //Deleting the final file.
			$_SESSION['message'] = array('error', 'This wallpaper already exists');
			if(!$ajax)
				header('Location:index');
			return FALSE;
		}
		
		
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
		
		//Calculating the thumbnail size, for a maximum size of 200*125
		//The resizing is calculated according the original size, to let it fit while not deforming the thumb (adding black borders if necessary)
		$sx = imagesx($src_image);
		$sy = imagesy($src_image);
		
		$dx = 200; $dy = 125;
		
		if($sx > 1.6 * $sy)
			$dy = (200 * $sy) / $sx; //Using a cross product
		else
			$dx = (125 * $sx) / $sy;
		
		$dst_image = imagecreatetruecolor(200, 125);
		imagecopyresampled($dst_image, $src_image, 100 - ($dx / 2), 63 - ($dy / 2), 0, 0, $dx, $dy, $sx, $sy); //Takes a lot of time.
		
		imagepng($dst_image, 'static/thumbs/'.$dest_name.'.png');
		
		//Adding entry in database
		$wallpaper->filename = $dest_name.'.'.$dest_ext;
		$wallpaper->orig_filename = $orig_filename;
		$wallpaper->size = $sx.'x'.$sy;
		$wallpaper->keywords = explode(' ', $_POST['tags']);
		$wallpaper->poster = (isset($_SESSION['id']) ? $_SESSION['id'] : NULL);
		$wallpaper->md5 = $md5;
		$wallpaper->time = time();
		
		$wallpaper->create();
		
		$_SESSION['message'] = array('confirm', 'Your wallpaper is uploaded!');
		if(!$ajax)
			header('Location:index');
		
		return TRUE;
	}
	
	//Updating wallpaper info.
	public function edit($id)
	{
		if(!isset($_POST['tags'], $_POST['rating'], $_POST['author'], $_POST['source']))
		{
			$_SESSION['message'] = array('error', 'Unknown error.');
			$this->redirect('view/'.$id);
			return FALSE;
		}
		
		$wall = new Wallpaper($id);
		
		if(empty($wall->id))
		{
			$_SESSION['message'] = array('error', 'This wallpaper does not exists.');
			$this->redirect('index');
			return FALSE;
		}
		
		if($wall->statusKey != $_GET["key"])
		{
			$_SESSION['message'] = array('error', 'This wallpaper has been modified. Please try again.');
			$this->redirect('view/'.$id);
			return FALSE;
		}
		
		if(!in_array($_POST['rating'], array('s', 'e', 'q')))
			$_POST['rating'] = $wall['rating'];
		
		$wall->rating = $_POST['rating'];
		$wall->keywords = explode(' ', $_POST['tags']);
		$wall->author = $_POST['author'];
		$wall->source = $_POST['source'];
		$wall->save();
		
		return header('Location:'.BASE_URL.'view/'.$id);
	}
	
	//Yay wallpapers (adding score)
	public function yay($id)
	{
		$json = new JSON();
		$json->result = false;
		$json->key = $wall->statusKey;
		
		if(empty($this->user))
			$json->message = "You need to be logged to Yay wallpapers.";
		else
		{
			$wall = new Wallpaper($id);
			
			if(empty($wall->id))
				$json->message = 'This wallpaper does not exists.';
			elseif($wall->statusKey != $_POST["key"])
				$json->message = 'This wallpaper has been modified. Please try again.';
			elseif(!$wall->addScore($this->user))
				$json->message = 'An unknown error occured.';
			else
			{
				$json->result = true;
				$json->score = ++$wall->score;
				$json->key = $wall->computeStatusKey();
			}
		}
		
		Debug::terminate();
		$json->debug = (string) Debug::getInstance();
		
		echo $json;
	}
	
	//Hush wallpapers (removing score)
	public function hush($id)
	{
		$wall = new Wallpaper($id);
		
		$json = new JSON();
		$json->result = false;
		$json->key = $wall->statusKey;
		
		if(empty($this->user))
			$json->message = "You need to be logged to Hush wallpapers.";
		else
		{
			
			if(empty($wall->id))
				$json->message = 'This wallpaper does not exists.';
			elseif($wall->statusKey != $_POST["key"])
				$json->message = 'This wallpaper has been modified. Please try again.';
			elseif(!$wall->removeScore($this->user))
				$json->message = 'An unknown error occured.';
			else
			{
				$json->result = true;
				$json->score = --$wall->score;
				$json->key = $wall->computeStatusKey();
			}
		}
		
		Debug::terminate();
		$json->debug = (string) Debug::getInstance();
		
		echo $json;
	}
}
