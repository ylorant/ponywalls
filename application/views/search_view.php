<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $search; ?> - Ponywalls</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="generator" content="Geany 0.20" />
		<script type="text/javascript" src="static/js/jquery.js"></script>
		<script type="text/javascript" src="static/js/effects.js"></script>
		<link rel="stylesheet" href="static/css/buttons.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/mainhead.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/search.css" type="text/css" media="screen" />
		<link rel="shortcut icon" type="image/x-icon" href="static/images/favicon.ico" />
	</head>
	
	<body onresize="updateWallSize();">
		
		<div class="header"><div class="topbar">
			<ul class="rfloat">
				<li>Blacklist</li>
				<li>Ratings</li>
			
				<?php if(!isset($logged)) { ?>
					<li><a onclick="toggleLoginDialog();">Login</a></li>
					<li><a onclick="toggleRegisterDialog();">Register</a></li>
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
			<a href="index"><img class="logo" src="static/images/logo.png" /></a>
			<form method="post" action="search" class="search">
				<input type="text" name="search" value="<?php echo $search; ?>" /> <input type="submit" class="button twilightsparkle" value="Search" />
				<select name="searchtype" class="button pinkiepie">
					<option value="exclusive">All the terms</option>
					<option value="inclusive"<?php if(isset($inclusive) && $inclusive) echo ' selected="selected"'; ?>>One of the terms</option>
				</select>
			</form>
			<div class="rfloat topbutton">
				<a class="button pinkiepie" onclick="toggleData();"><img src="static/images/info.png" /> Related</a>
			</div>
			<div class="contextzone">
				<div id="dataShow">
				<p class="tag category"><img src="static/images/category.png" /> <strong>Tags</strong></p> <p class="separator"> </p>
				<?php foreach($wrelated as $keyword) { ?>
					<p class="tag"><img src="static/images/tag.png" /> <a href="search/<?php echo $keyword; ?>"><?php echo $keyword; ?></a></p>
				<?php } ?>
			</div>
			</div>
		</div>
		<div class="content">
			<?php if(count($results) > 0) { ?>
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
			<?php } else { ?>
				<div class="notfound" id="view">
					<img src="static/images/notfound2.png" />
					<h1>No wallpapers found. It makes Derpy sad.</h1>
				</div>
			<?php } ?>
		</div>
		<script type="text/javascript">
			updateWallSize();
		</script>
	</body>
</html>
