<?php global $config; ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Ponywalls</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="generator" content="Geany 0.20" />
		<script type="text/javascript" src="static/js/jquery.js"></script>
		<script type="text/javascript" src="static/js/core.js"></script>
		<script type="text/javascript" src="static/js/effects.js"></script>
		<script type="text/javascript" src="system/utils/debug.js"></script>
		<script type="text/javascript">
			const BASE_URL = "<?php echo $config['base_url']; ?>";
			
			<?php if(isset($message)): ?>
				$(document).ready(function()
				{
					ponywalls.dialog({
						type: "<?php echo $message; ?>",
						message: "<?php echo $errorMsg; ?>"
					});
				});
			<?php endif; ?>
		</script>
		<link rel="stylesheet" href="static/css/buttons.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/index.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/debug.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/dialog.css" type="text/css" media="screen" />
		<link rel="shortcut icon" type="image/x-icon" href="static/images/favicon.ico" />
	</head>
	
	<body id="body">
		<div class="topbar">
			<ul>
				<li>Blacklist</li>
				<li>Ratings</li>
			</ul>
			<ul class="rfloat">
				<?php if(!isset($logged)) { ?>
					<li><a onclick="ponywalls.toggleLoginDialog();">Login</a></li>
					<li><a onclick="ponywalls.toggleRegisterDialog();">Register</a></li>
				<?php } else { ?>
					<li>Welcome back, <?php echo $user->login; ?> !</li>
					<li><a href="users/logout">Logout</a></li>
				<?php } ?>
			</ul>
		</div>
		<div id="loginBox">
		<form method="post" action="users/login">
			Username : <input type="text" name="login" /><br />
			Password : <input type="password" name="password" /><br />
			<input type="submit" value="Login" class="button rainbowdash rfloat" />
		</form>
		</div>
		<div id="registerBox">
		<form method="post" action="users/register">
			Username : <input type="text" name="login" /><br />
			Password : <input type="password" name="password" /><br />
			Type again : <input type="password" name="passwordcheck" /><br />
			<input type="submit" value="Register" class="button pinkiepie rfloat" />
		</form>
		</div>
		<div class="header">
			<img src="static/images/logo.png" />
			<p class="splashtext">
			<?php echo $titleSentence; ?>
			</p>
		</div>
		<div class="search">
			<form method="post" action="search">
				<input type="text" name="search" placeholder="Type your keywords here..."  /> <input type="button" onclick="this.form.submit();" value="Search" class="button twilightsparkle" />
			</form>
		</div>
		<div id="imguploaddialog" class="dialog upload nodisplay">
			<form method="post" id="uploadform" action="wallpapers/add" enctype="multipart/form-data">
				<h1>Upload a wallpaper</h1>
				<img src="static/images/celestia_message.png" id="celestiasmessage" alt="message" />
				<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" />
				<!--<label for="filetags">Tag it : </label><input type="text" name="tags" id="filetags" /><br />-->
				<br />
				<div class="uploadnotes">
					Notes : 
					<ul>
						<li>- Only PNG, JPEG, GIF and BMP images are allowed, up to 2 MiB.</li>
						<li>- Separate tags with a space. Use an underscore '_' for multiple words.</li>
					</ul>
				</div>
				<label for="uploadimg">Select the file : </label><input type="file" class="button pinkiepie" name="file" id="uploadimg" /><br />
				<div class="uploadbuttonbox">
					<input type="submit" class="button rainbowdash rfloat" onclick="ponywalls.toggleUploadWait();" id="sumbitupload" value="Upload" />
					<input type="button" onclick="ponywalls.hideUploadDialog();" class="button twilightsparkle rfloat" value="Cancel" />
				</div>
			</form>
			<div id="uploadwait" class="nodisplay"><img src="static/images/sending.png"><h1>Uploading...</h1></div>
		</div>
		<div class="options">
			<div style="float:right;width:40%;text-align:right;">
				<a onclick="ponywalls.showUploadDialog();" class="button fluttershy">Submit a picture</a>
			</div>
			<div style="float:left; width:60%;">
				<a href="random" class="button pinkiepie">Random</a>
				<a href="coolest" class="button rainbowdash">Coolest</a>
				<a href="latest" class="button twilightsparkle">Latest</a>
			</div>
			<div style="clear:both;"></div>
			</div>
			<div class="footer">
				<?php 
				global $config;
				if($config['debug'] === true): ?>
					<a onclick="debug.toggleWindow();">Debug window<?php if(Debug::countExceptions() > 0) echo ' <span class="debug-red">('.Debug::countExceptions().')</span>'; ?></a> -
				<?php endif; ?>
			<a href="contact">Contact</a> - <a href="terms">Terms and conditions</a> - <a href="http://blog.ponywalls.net">Blog</a> - Source code
			</div>
	</body>
	
	<script type="text/javascript" src="static/js/fileupload.js"></script>
</html>
