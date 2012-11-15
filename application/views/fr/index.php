<!DOCTYPE html>
<html>
	<head>
		<title>Ponywalls</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="generator" content="Geany 0.20" />
		<script type="text/javascript" src="static/js/jquery.js"></script>
		<script type="text/javascript" src="static/js/effects.js"></script>
		<link rel="stylesheet" href="static/css/buttons.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/index.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="static/css/debug.css" type="text/css" media="screen" />
		<link rel="shortcut icon" type="image/x-icon" href="static/images/favicon.ico" />
	</head>
	
	<body id="body">
		<?php if(isset($message)) { ?>
			<div id="shadow"></div>
			<div id="dialog_<?php echo $message; ?>" class="dialog">
				<?php if($message == 'error') { ?>
					<img src="static/images/derpy_<?php echo $message; ?>.png" alt="<?php echo $message; ?>" />
					<h1>Derp, an error occured.</h1>
					<p>
						According to Derpy Hooves, the error is :				
					</p>
				<?php } else if($message == 'confirm') { ?>
					<img src="static/images/derpy_<?php echo $message; ?>.png" alt="<?php echo $message; ?>" />
					<h1>I brought you a letter !</h1>
					<p>
						According to myself, the message is :
					</p>
				<?php } ?>
				<div class="dialogMessage <?php echo $message; ?>"><?php echo $errorMsg; ?></div>
				<div class="closebutton twilightsparkle" onclick="closeDialog('<?php echo $message; ?>');">Close</div>
			</div>
		<?php } else { ?>
			<div id="shadow" class="nodisplay"></div>
		<?php } ?>
		<div class="topbar">
			<ul>
				<li>Liste noire</li>
				<li>Notes</li>
			</ul>
			<ul class="rfloat">
				<?php if(!isset($logged)) { ?>
					<li><a onclick="toggleLoginDialog();">Connexion</a></li>
					<li><a onclick="toggleRegisterDialog();">Enregistrement</a></li>
				<?php } else { ?>
					<li>Bonjour, <?php echo $user->login; ?> !</li>
					<li><a href="members/logout">Déconnexion</a></li>
				<?php } ?>
			</ul>
		</div>
		<div id="loginBox">
		<form method="post" action="users/login">
			Nom d'utilisateur : <input type="text" name="login" /><br />
			Mot de passe : <input type="password" name="password" /><br />
			<input type="submit" value="Connexion" class="button rainbowdash rfloat" />
		</form>
		</div>
		<div id="registerBox">
		<form method="post" action="users/register">
			Nom d'utilisateur : <input type="text" name="login" /><br />
			Mot de passe : <input type="password" name="password" /><br />
			Vérification : <input type="password" name="passwordcheck" /><br />
			<input type="submit" value="Valider" class="button pinkiepie rfloat" />
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
				<h1>Envoyer un fond d'écran</h1>
				<img src="static/images/celestia_message.png" id="celestiasmessage" alt="message" />
				<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" />
				<!--<label for="filetags">Tag it : </label><input type="text" name="tags" id="filetags" /><br />-->
				<br />
				<div class="uploadnotes">
					Notes : 
					<ul>
						<li>- Les seuls formats acceptés sont JPEG, PNG, GIF et BMP avec une limite de 4 MiB.</li>
						<li>- Séparez les mots-clés par un espace. Utilisez l'underscore '_' pour les mots-clés composés.</li>
					</ul>
				</div>
				<label for="uploadimg">Fichier : </label><input type="file" class="button pinkiepie" name="file" id="uploadimg" /><br />
				<div class="uploadbuttonbox">
					<input type="submit" class="button rainbowdash rfloat" onclick="toggleUploadWait();" id="sumbitupload" value="Envoyer" />
					<input type="button" onclick="hideUploadDialog();" class="button twilightsparkle rfloat" value="Annuler" />
				</div>
			</form>
			<div id="uploadwait" class="nodisplay"><img src="static/images/sending.png"><h1>Envoi...</h1></div>
		</div>
		<div class="options">
			<div style="float:right;width:40%;text-align:right;">
				<a onclick="showUploadDialog();" class="button fluttershy">Envoyer une image</a>
			</div>
			<div style="float:left; width:60%;">
				<a href="random" class="button pinkiepie">Aléatoires</a>
				<span class="button rainbowdash">Chouettes</span>
				<a href="latest" class="button twilightsparkle">Derniers</a>
			</div>
			<div style="clear:both;"></div>
			</div>
			<div class="footer">
				<?php 
				global $config;
				if($config['debug'] === true): ?>
					<a onclick="$('.debug-window').toggle();">Fenêtre de debug<?php if(Debug::countExceptions() > 0) echo ' <span class="debug-red">('.Debug::countExceptions().')</span>'; ?></a> -
				<?php endif; ?>
			<a href="contact">Contact</a> - <a href="terms">Conditions d'utilisation</a> - <a href="http://blog.ponywalls.net">Blog</a> - Code source
			</div>
	</body>
	
	<script type="text/javascript" src="static/js/fileupload.js"></script>
</html>
