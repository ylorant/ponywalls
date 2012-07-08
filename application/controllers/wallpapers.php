<?php

class Wallpapers extends Controller
{
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
			if(!$ret)
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
		$model = $this->loadModel('Walls_model');
		
		//Checking if the MD5 already exists in the database, i.e. if the wallpaper has already been uploaded.
		if($model->wallpaperMD5Exists($md5))
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
		$model->addWallpaper($dest_name.'.'.$dest_ext, $orig_filename, $sx.'x'.$sy, $_POST['tags'], (isset($_SESSION['id']) ? $_SESSION['id'] : -1), $md5);
		
		$_SESSION['message'] = array('confirm', 'Your wallpaper is uploaded!');
		if(!$ajax)
			header('Location:index');
		
		return TRUE;
	}
	
	//Updating wallpaper info.
	public function edit($id)
	{
		if(!isset($_POST['tags']))
			return header('Location:'.BASE_URL.'view/'.$id);
		
		$model = $this->loadModel('Walls_model');
		$wall = $model->getWallpaper($id);
		
		if(!in_array($_POST['rating'], array('s', 'e', 'q')))
			$_POST['rating'] = $wall['rating'];
		
		$model->updateWallpaper($id, $_POST['tags'], $_POST['rating']);
		
		return header('Location:'.BASE_URL.'view/'.$id);
	}
}
