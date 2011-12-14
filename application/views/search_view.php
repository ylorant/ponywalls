<!DOCTYPE html>
<html>
	<head>
		<title>Ponywalls</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="generator" content="Geany 0.20" />
		<script type="text/javascript" src="static/js/jquery.js"></script>
		<script type="text/javascript" src="static/js/effects.js"></script>
		<link rel="stylesheet" href="static/css/mainhead.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/search.css" type="text/css" media="screen" />
		<link rel="shortcut icon" type="image/x-icon" href="static/images/favicon.ico" />
	</head>
	
	<body>
		
		<div class="header"><div class="topbar">
			<ul class="rfloat">
				<li>Blacklist</li>
				<li>Ratings</li>
			
				<?php if(!isset($logged)) { ?>
					<li><a onmouseover="toggleLoginDialog();" onclick="toggleLoginDialog();">Login</a></li>
					<li><a onmouseover="toggleRegisterDialog();" onclick="toggleRegisterDialog();">Register</a></li>
				<?php } else { ?>
					<li>Welcome back, <?php echo $userData['login']; ?> !</li>
					<li><a href="logout">Logout</a></li>
				<?php } ?>
			</ul>
		</div>
		<div id="loginBox">
		<form method="post" action="login">
			Username : <input type="text" name="login" /><br />
			Password : <input type="password" name="password" /><br />
			<input type="submit" value="Login" class="button rainbowdash rfloat" />
		</form>
		</div>
		<div id="registerBox">
		<form method="post" action="register">
			Username : <input type="text" name="login" /><br />
			Password : <input type="password" name="password" /><br />
			Type again : <input type="password" name="passwordcheck" /><br />
			<input type="submit" value="Register" class="button pinkiepie rfloat" />
		</form>
		</div>
			<a href="index"><img src="static/images/logo.png" /></a>
		</div>
		<div class="content">
			<div class="thumbnail-list">
				<?php foreach($results as $result) { 
					$result['filename'] = explode('.', $result['filename']);
					array_pop($result['filename']);
					array_push($result['filename'], 'png');
					$result['filename'] = join('.', $result['filename']);
					?>
					<div class="thumbnail">
						
						<a href="view/<?php echo $result['id']; ?>"><img src="static/thumbs/<?php echo $result['filename']; ?>" /></a>
						<div class="size">
							<?php echo $result['size']; ?>
						</div>
					</div>
				<?php } ?>
				<div style="clear:both"></div>
			</div>
		</div>
	</body>
</html>
