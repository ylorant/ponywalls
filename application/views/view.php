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
		<script>
			const BASE_URL = "<?php echo $config['base_url']; ?>";
		</script>
		<link rel="stylesheet" href="static/css/buttons.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/mainhead.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/view.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/debug.css" type="text/css" media="screen" />
		<link rel="shortcut icon" type="image/x-icon" href="static/images/favicon.ico" />
	</head>
	
	<body onresize="ponywalls.updateWallSize();">
		
		<div class="header"><div class="topbar">
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
				<input type="text" name="search"  placeholder="Type your keywords here..." /> <input type="submit" class="button twilightsparkle" value="Search" />
				<select name="searchtype" class="button pinkiepie">
					<option value="exclusive">All the terms</option>
					<option value="inclusive">One of the terms</option>
				</select>
			</form>
			<div class="rfloat topbutton">
				<a class="button fluttershy" onclick="ponywalls.toggleImageData();"><img src="static/images/info.png" /> Toggle info</a>
				<a class="button rainbowdash" onclick="ponywalls.toggleComments();"><img src="static/images/comments.png" /> Comments</a>
			</div>
		</div>
		<div class="contextzone">
			<form method="post" id="dataEditForm" action="wallpapers/edit/<?php echo $wallpaper->id; ?>">
				<div id="dataShow">
					<p>
						<strong><img src="static/images/category.png" /></strong>
						<span>Tags</span>
					</p>
					<?php foreach($wallpaper->keywords as $keyword): ?>
						<p>
							<strong><img src="static/images/tag.png" /></strong>
							<span>
								<a href="search/<?php echo $keyword; ?>">
									<?php echo (strlen($keyword) > 30) ? substr($keyword, 0, 27).'...' : $keyword; ?>
								</a>
							</span>
						</p>
					<?php endforeach; ?>
					<hr />
					<div class="infobox">
						<p>
							<strong><img src="static/images/category.png" /> Posted on</strong>
							<span><?php echo date('m-d-Y \a\t H:i', $wallpaper->postTime); ?></span>
						</p>
						<p>
							<strong><img src="static/images/category.png" /> Last edit on</strong>
							<span><?php echo date('m-d-Y \a\t H:i', $wallpaper->editTime); ?></span>
						</p>
						<p>
							<strong><img src="static/images/category.png" /> Posted by</strong>
							<span><?php echo $wallpaper->poster->login; ?></span>
						</p>
						<p>
							<strong><img src="static/images/category.png" /> Rating</strong>
							<span class="<?php echo $wallpaper->rating_str; ?>">
								<a href="search/rating:<?php echo $wallpaper->rating; ?>"><?php echo $wallpaper->rating_str; ?></a>
							</span>
						</p>
						<p>
							<strong><img src="static/images/category.png" /> Size</strong>
							<?php echo ($wallpaper->size ? '<span>'.$wallpaper->size.'</span>' : ''); ?>
						</p>
						<p>
							<strong><img src="static/images/category.png" /> Ratio</strong>
							<?php echo ($wallpaper->ratio ? '<span>'.$wallpaper->ratio.'</span>' : ''); ?>
						</p>
						<p>
							<strong><img src="static/images/category.png" /> Author</strong>
							<?php echo ($wallpaper->author ? '<span>'.$wallpaper->author.'</span>' : ''); ?>
						</p>
						<p>
							<strong><img src="static/images/category.png" /> Source</strong>
							<?php if($wallpaper->source): ?>
								<?php if($wallpaper->source_url != null): ?>
									<span><a href="<?php echo $wallpaper->source_url; ?>"><?php echo $wallpaper->source; ?></a></span>
								<?php else: echo '<span>'.$wallpaper->source.'</span>'; endif; ?>
							<?php endif; ?>
						</p>
						<p>
							<strong><img src="static/images/category.png" /> Yay !</strong>
							<span id="yay-score"><?php echo $wallpaper->score; ?></span>
							<?php if(isset($logged)): ?>
								<?php if(!$yayed): ?>
									<div class="lfloat button rainbowdash" data-key="<?php echo $wallpaper->statusKey; ?>" onclick="ponywalls.yay(<?php echo $wallpaper->id; ?>, this);" id="yayButton">
										Louder !
									</div>
								<?php else: ?>
									<div class="lfloat button bigmac" data-key="<?php echo $wallpaper->statusKey; ?>" onclick="ponywalls.hush(<?php echo $wallpaper->id; ?>, this);" id="yayButton">
										Hush
									</div>
								<?php endif; ?>
							<?php endif; ?>
						</p>
					</div>
				</div>
				<div class="bottompanel">
					<hr />
					<div class="rfloat button twilightsparkle" onclick="ponywalls.editImageInfo();" id="infoEditButton"><img src="static/images/edit.png" /> Edit</div>
					<div class="rfloat button bigmac" onclick="ponywalls.editImageInfo();" id="flagButton"><img src="static/images/trash.png" /> Delete</div>
				</div>
				<div id="editDataForm">
					<p>
						<strong><img src="static/images/category.png" /></strong>
						<span>Tags</span>
					</p>
					<input type="text" name="tags" value="<?php echo join(' ', $wallpaper->keywords); ?>" />
					
					<p>
						<strong><img src="static/images/category.png" /></strong>
						<span>Rating</span>
					</p>
					<select name="rating" class="button rainbowdash">
						<option value="s"<?php echo $wallpaper->rating == 's' ? ' selected="selected"' : ''; ?>>Safe</option>
						<option value="q"<?php echo $wallpaper->rating == 'q' ? ' selected="selected"' : ''; ?>>Questionable</option>
						<option value="e"<?php echo $wallpaper->rating == 'e' ? ' selected="selected"' : ''; ?>>Explicit</option>
					</select>
					
					<p>
						<strong><img src="static/images/category.png" /></strong>
						<span>Author</span>
					</p>
					<input type="text" name="author" value="<?php echo $wallpaper->author; ?>" />
					
					<p>
						<strong><img src="static/images/category.png" /></strong>
						<span>Source</span>
					</p>
					<input type="text" name="source" value="<?php echo $wallpaper->source_url; ?>" />
					<input type="hidden" name="key" value="<?php echo $wallpaper->statusKey; ?>" />
				</div>
			</form>
		</div>
		<div class="content">
			<div id="view">
				<a href="static/wall/<?php echo $wallpaper->filename; ?>" target="_blank"><img src="static/wall/<?php echo $wallpaper->filename; ?>" /></a>
			</div>
		</div>
		<script type="text/javascript">
			ponywalls.updateWallSize();
		</script>
	</body>
</html>
