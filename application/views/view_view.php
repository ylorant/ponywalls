<?php global $config; ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Ponywalls</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="generator" content="Geany 0.20" />
		<script type="text/javascript" src="static/js/jquery.js"></script>
		<script type="text/javascript" src="static/js/effects.js"></script>
		<script>
			const BASE_URL = "<?php echo $config['base_url']; ?>";
		</script>
		<script type="text/javascript" src="static/js/ajax.js"></script>
		<link rel="stylesheet" href="static/css/buttons.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/mainhead.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/view.css" type="text/css" media="screen" />
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
					<li><a href="members/logout">Logout</a></li>
				<?php } ?>
			</ul>
		</div>
		<div id="loginBox">
		<form method="post" action="members/login">
			Username : <input type="text" name="login" /><br />
			Password : <input type="password" name="password" /><br />
			<input type="submit" value="Login" class="button rainbowdash rfloat" />
		</form>
		</div>
		<div id="registerBox">
		<form method="post" action="members/register">
			Username : <input type="text" name="login" /><br />
			Password : <input type="password" name="password" /><br />
			Type again : <input type="password" name="passwordcheck" /><br />
			<input type="submit" value="Register" class="button pinkiepie rfloat" />
		</form>
		</div>
			<a href="index"><img class="logo" src="static/images/logo.png" /></a>
			<form method="post" action="search" class="search">
				<input type="text" name="search"  placeholder="Type your keywords here..." /> <input type="submit" class="button twilightsparkle" value="Search" />
				<select name="searchtype" class="button pinkiepie">
					<option value="exclusive">All the terms</option>
					<option value="inclusive">One of the terms</option>
				</select>
			</form>
			<div class="rfloat comments">
				<a class="button fluttershy" onclick="toggleData();"><img src="static/images/info.png" /> Info</a>
				<a class="button rainbowdash" onclick="toggleComments();"><img src="static/images/comments.png" /> Comments</a>
			</div>
		</div>
		<div id="data"><form method="post" id="dataEditForm" action="wallpapers/edit/<?php echo $wallpaper['id']; ?>">
			<div id="dataShow">
				<p class="tag category"><img src="static/images/category.png" /> <strong>Tags</strong></p> <p class="separator"> </p>
				<?php foreach($wallpaper['keywords'] as $keyword) { ?>
					<p class="tag"><img src="static/images/tag.png" /> <a href="search/<?php echo $keyword; ?>"><?php echo $keyword; ?></a></p>
				<?php } ?>
			</div>
			<div id="editDataForm">
				<p class="tag category"><img src="static/images/category.png" /> <strong>Tags</strong></p> <p class="separator"> </p><input type="text" name="tags" value="<?php echo join(' ', $wallpaper['keywords']); ?>" />
			</div>
			<div style="clear:both;"></div>
			<hr />
			<div class="rfloat button twilightsparkle" onclick="editImageInfo();" id="infoEditButton"><img src="static/images/edit.png" /> Edit</div>
			</form>
		</div>
		<div class="content">
			<div id="view">
				<a href="static/wall/<?php echo $wallpaper['filename']; ?>" target="_blank"><img src="static/wall/<?php echo $wallpaper['filename']; ?>" /></a>
			</div>
		</div>
		<script type="text/javascript">
			updateWallSize();
		</script>
	</body>
</html>
