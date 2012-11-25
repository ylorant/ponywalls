<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $search; ?> - Ponywalls</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="generator" content="Geany 0.20" />
		<script type="text/javascript" src="static/js/jquery.js"></script>
		<script type="text/javascript" src="static/js/core.js"></script>
		<script type="text/javascript" src="static/js/effects.js"></script>
		<script type="text/javascript" src="system/utils/debug.js"></script>
		<link rel="stylesheet" href="static/css/buttons.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/mainhead.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/search.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/debug.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/dialog.css" type="text/css" media="screen" />
		<link rel="shortcut icon" type="image/x-icon" href="static/images/favicon.ico" />
	</head>
	
	<body>
		<div class="header">
			<div class="topbar">
				<ul class="rfloat">
					
					<li>Blacklist</li>
					<li>Ratings</li>
				
					<?php if(!isset($logged)): ?>
						<li><a onclick="ponywalls.toggleLoginDialog();">Login</a></li>
						<li><a onclick="ponywalls.toggleRegisterDialog();">Register</a></li>
					<?php else: ?>
						<li>Welcome back, <?php echo $user->login; ?> !</li>
						<li><a href="users/logout?returnUrl=<?php echo CURRENT_PAGE; ?>">Logout</a></li>
						
					<?php endif; ?>
					<?php 
					global $config;
					if($config['debug'] === true): ?>
						<li><a onclick="debug.toggleWindow();">Debug window<?php if(Debug::countExceptions() > 0) echo ' ('.Debug::countExceptions().')'; ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div id="loginBox">
				<form method="post" action="users/login?returnUrl=<?php echo CURRENT_PAGE; ?>">
					Username : <input type="text" name="login" /><br />
					Password : <input type="password" name="password" /><br />
					<input type="submit" value="Login" class="button rainbowdash rfloat" />
					<input type="hidden" name="redirectUrl" value="<?php echo CURRENT_PAGE; ?>" />
				</form>
			</div>
			<div id="registerBox">
				<form method="post" action="users/register?returnUrl=<?php echo CURRENT_PAGE; ?>">
					Username : <input type="text" name="login" /><br />
					Password : <input type="password" name="password" /><br />
					Type again : <input type="password" name="passwordcheck" /><br />
					<input type="submit" value="Register" class="button pinkiepie rfloat" />
				</form>
			</div>
			<a href="index"><img class="logo" src="static/images/logo.png" /></a>
			<form method="post" action="search" class="search">
				<input type="text" name="search" value="<?php echo $search; ?>" /> <input type="submit" class="button twilightsparkle" value="Search" />
				<select name="searchtype" class="button pinkiepie">
					<option value="exclusive">All the terms</option>
					<option value="inclusive"<?php if(isset($inclusive) && $inclusive) echo ' selected="selected"'; ?>>One of the terms</option>
				</select>
			</form>
		</div>
		<div class="content">
			<?php if(count($results) > 0): ?>
				<div class="thumbnail-list">
					<?php 
					foreach($results as $result): 
						$filename = $result->getThumb();
						?>
						<div class="thumbnail">
							
							<a href="view/<?php echo $result->id; ?>"><img src="static/thumbs/<?php echo $filename; ?>" /></a>
							<div class="size">
								<?php echo $result->size; ?>
							</div>
						</div>
					<?php endforeach; ?>
					<div style="clear:both"></div>
				</div>
			<?php else: ?>
				<div class="notfound" id="view">
					<img src="static/images/notfound2.png" />
					<h1>No wallpapers found. It makes Derpy sad.</h1>
				</div>
			<?php endif; ?>
		</div>
	<script type="text/javascript">
			ponywalls.updateWallSize();
		</script>
	</body>
</html>
