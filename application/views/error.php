<!DOCTYPE html>
<html>
	<head>
		<title>Error - Ponywalls</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="generator" content="Geany 0.20" />
		<script type="text/javascript" src="static/js/jquery.js"></script>
		<script type="text/javascript" src="static/js/effects.js"></script>
		<link rel="stylesheet" href="static/css/buttons.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/mainhead.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/search.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/debug.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/error.css" type="text/css" media="screen" />
		<link rel="shortcut icon" type="image/x-icon" href="static/images/favicon.ico" />
	</head>
	
	<body onresize="updateWallSize();">
		<div class="header">
			<a href="index"><img class="logo" src="static/images/logo.png" /></a>
			<form method="post" action="search" class="search">
				<input type="text" name="search" /> <input type="submit" class="button twilightsparkle" value="Search" />
				<select name="searchtype" class="button pinkiepie">
					<option value="exclusive">All the terms</option>
					<option value="inclusive">One of the terms</option>
				</select>
			</form>
		</div>
		<div class="content">
			<h1>Oops, An error occured.</h1>
		</div>
		<?php include('debug.php'); ?>
	</body>
</html>
