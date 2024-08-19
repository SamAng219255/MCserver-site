<?php
	session_start();
	if ((isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) || (!isset($_SESSION['last_active']) && isset($_SESSION['loggedin']))) {
		session_unset();
		session_destroy();
	}
	$_SESSION['last_active']=time();
	$referer='./';
	if(isset($_SERVER['HTTP_REFERER'])) {
		$referer=$_SERVER['HTTP_REFERER'];
	}
	if(isset($_POST['referer'])) {
		$referer=$_POST['referer'];
	}
?>
<html>
<head>
	<title>Login or Sign Up - AmospiaCraft</title>
	<link rel="stylesheet" type="text/css" href="./login.css">
	<link href="./img/sign.png" rel="shortcut icon">
</head>
<body>
	<?php
		function addBanner($bannerTxt) {
			echo '<div class="banner">'.$bannerTxt.'</div>';
		}
		require 'db.php';
		$signupFailed=false;
		if(isset($_POST['signup'])) {
			$url = 'https://api.mojang.com/users/profiles/minecraft/'.$_POST['username'];
			$options = array(
				'http' => array(
					'header' => "Content-type: application/json",
					'method' => 'GET',
					'content' => '',
				),
			);
			$context = stream_context_create($options);
			$profile = false;
			$now = time();
			if(!isset($_SESSION['nextlogin'])) $_SESSION['nextlogin'] = time() - 1;
			if($now >= $_SESSION['nextlogin']) {
				$profile = json_decode(file_get_contents($url, false, $context));
				$_SESSION['nextlogin'] = time() + 1;
			}
			else {
				addBanner('Request too soon. Wait 30 seconds before trying again.');
				$_SESSION['nextlogin'] = time() + 30;
			}
			$query = $pdo->prepare("SELECT `username` FROM `mcstuff`.`users` WHERE username=?;");
			if($profile!=false) {
				$query->bindValue(1, $profile->name, PDO::PARAM_STR);
				$query->execute();
			}
			$hashed=password_hash($_POST['password'],PASSWORD_DEFAULT);
			$signupFailed=true;
			if($profile==false) {
				addBanner('Username not found.');
			}
			elseif($_POST['password']!==$_POST['password2']) {
				addBanner('Passwords do not match.');
			}
			elseif($query->rowCount()>0) {
				addBanner('Username is already in use.');
			}
			else {
				/*$allowedUsers=["redstonetardis42","petrok9001","lhibscher349","list","kagetora0","luckyknight68","1999sam1999","enddragon9","lewisthekiller","gentleworks","153norc","sugargizmo","silverleafnight","pharaohcrab","enderninja7","drn21","d_hex","aquatailz","skinz123","thedragonslain","thetotorotacos","greenhouscreeper","thepartygod","smallsmelt300","antraveler","lightningpwr28","patientneutral","thebiganthony","EnglishMuffin1","chkinnuggetbutt"];*/
				$admin=0;
				/*if(in_array(strtolower($profile->name), $allowedUsers)) {
					$admin='1';
				}*/
				$sql=$pdo->prepare("INSERT INTO `mcstuff`.`users` (`id`,`username`,`uuid`,`password`,`ip`,`permissions`) VALUES (0,:name,:id,:hashed,:ip,:admin);");
				$sql->bindValue('name', $profile->name, PDO::PARAM_STR);
				$sql->bindValue('id', $profile->id, PDO::PARAM_STR);
				$sql->bindValue('hashed', $hashed, PDO::PARAM_STR);
				$sql->bindValue('ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
				$sql->bindValue('admin', $admin, PDO::PARAM_INT);
				if($sql->execute()) {
					$_SESSION['username']=$profile->name;
					$_POST['username']=$_SESSION['username'];
					$_POST['signin']=true;
					$signupFailed=false;
				}
				else {
					addBanner('Unknown Error.');
				}
			}
		}
		if(isset($_POST['signin'])) {
			$query=$pdo->prepare("SELECT `password` FROM `mcstuff`.`users` WHERE `username`=?;");
			$query->bindValue(1, $_POST['username'], PDO::PARAM_STR);
			$query->execute();
			if(!($_POST['username']!='' && $_POST['password']!='')) {
				addBanner('Username or Password missing.');
			}
			elseif($query->rowCount()<1) {
				addBanner('Invalid Username or Password.');
			}
			elseif(password_verify($_POST['password'],$query->fetch(PDO::FETCH_BOTH)[0])) {
				$_SESSION['username']=$_POST['username'];
				$ipsql=$pdo->prepare("UPDATE `mcstuff`.`users` SET `ip`=:ip WHERE `username`=:username;");
				$ipsql->bindValue('ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
				$ipsql->bindValue('username', $_SESSION['username'], PDO::PARAM_STR);
				$ipsql->execute();
				echo  '<meta http-equiv="refresh" content="0; URL='.$referer.'">';
			}
			else {
				addBanner('Invalid Username or Password.');
			}
		}
	?>
	<div id="lcp" class="cp"><form class="loginform" method="post">
		<span class="h">Log In</span><hr>
		<small>To log in, use your username for minecraft and your password you created when you created your account here.</small><br>
		<label for="frminuser">Minecraft Username:</label><br>
		<input type="text" id="frminuser" placeholder="Username" name="username" required maxlength=16 autocomplete="username"><br>
		<label for="frminpass">Password:</label><br>
		<input type="password" id="frminpass" placeholder="Password" name="password" required maxlength=16 autocomplete="current-password"><br>
		<?php echo '<input type="hidden" name="referer" value="'.$referer.'">'; ?>
		<input type="submit" value="Sign In" name="signin"><br>
	</form></div>
	<div id="rcp" class="cp"><form class="loginform" method="post">
		<span class="h">Create Account</span><hr>
		<div class="col">
			<label for="frmupusr">Minecraft Username:</label><br>
			<?php
				if($signupFailed)
					echo '<input type="text" id="frmupusr" placeholder="Username" name="username" required maxlength=16 autocomplete="new-username" value="'.$_POST['username'].'"><br>';
				else
					echo '<input type="text" id="frmupusr" placeholder="Username" name="username" required maxlength=16 autocomplete="new-username"><br>';
			?>
			<small>Enter your username for Minecraft as it appears in-game, not your Microsoft information you use to log-in to Minecraft. This will be used as your username for this website.</small><br>
		</div>
		<div class="col">
			<label for="frmuppass">Password:</label><br>
			<input type="password" id="frmuppass" placeholder="New Password" name="password" required maxlength=16 autocomplete="new-password"><br>
			<label for="frmupword">Retype Password:</label><br>
			<input type="password" id="frmupword" placeholder="Retype Password" name="password2" required maxlength=16 autocomplete="new-password"><br>
			<?php echo '<input type="hidden" name="referer" value="'.$referer.'">'; ?>
			<input type="submit" value="Sign Up" name="signup"><br>
		</div>
	</form></div>
</body>
</html>
