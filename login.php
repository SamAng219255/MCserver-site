<?php
	session_start();
	if ((isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) || (!isset($_SESSION['last_active']) && isset($_SESSION['loggedin']))) {
		session_unset();
		session_destroy();
	}
	$_SESSION['last_active']=time();
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
		if(isset($_POST['signup'])) {$url = "https://authserver.mojang.com/authenticate";
			$data = array();
			$data['agent'] = array("name" => "Minecraft", "version" => 1);
			$data['username'] = $_POST['email'];
			$data['password'] = $_POST['passwordm'];
			$options = array(
				'http' => array(
					'header' => "Content-type: application/json",
					'method' => 'POST',
					'content' => json_encode($data),
				),
			);
			$context = stream_context_create($options);
			$result = json_decode(file_get_contents($url, false, $context));
			$profile = $result->selectedProfile;
			$query="SELECT `username` from `mcstuff`.`users` where username='".$profile->name."';";
			$hashed=password_hash($_POST['password'],PASSWORD_DEFAULT);
			if($result==false) {
				addBanner('Invalid Minecraft Login');
			}
			elseif($_POST['password']!==$_POST['password2']) {
				addBanner('Passwords do not match.');
			}
			elseif(mysqli_query($conn,$query)->num_rows>0) {
				addBanner('Username is taken.');
			}
			else {
				$allowedUsers=["redstonetardis42","petrok9001","lhibscher349","list","kagetora0","luckyknight68","1999sam1999","enddragon9","lewisthekiller","gentleworks","153norc","sugargizmo","silverleafnight","pharaohcrab","enderninja7","drn21","d_hex","aquatailz","skinz123","thedragonslain","thetotorotacos","greenhouscreeper","thepartygod","smallsmelt300","antraveler","lightningpwr28","patientneutral",”thebiganthony”];
				$admin='0';
				if(in_array(strtolower($profile->name), $allowedUsers)) {
					$admin='1';
				}
				$sql="INSERT INTO `mcstuff`.`users` (`id`,`username`,`uuid`,`password`,`ip`,`permissions`) VALUES (0,'".$profile->name."','".$profile->id."','".$hashed."','".$_SERVER['REMOTE_ADDR']."','".$admin."');";
				if(mysqli_query($conn,$sql)) {
					$_SESSION['username']=$profile->name;
					$_POST['username']=$_SESSION['username'];
					$_POST['signin']=true;
				}
				else {
					addBanner('Unknown Error.');
				}
			}
		}
		if(isset($_POST['signin'])) {
			$query="SELECT `username`,`password` FROM `mcstuff`.`users` WHERE username='".addslashes($_POST['username'])."';";
			$queryresult=mysqli_query($conn,$query);
			if(!($_POST['username']!='' && $_POST['password']!='')) {
				addBanner('Username or Password missing.');
			}
			elseif($queryresult->num_rows<1) {
				addBanner('Invalid Username. or Password.');
			}
			elseif(password_verify($_POST['password'],mysqli_fetch_row($queryresult)[1])) {
				$_SESSION['username']=addslashes($_POST['username']);
				$ipsql="UPDATE `mcstuff`.`users` SET `ip`='".$_SERVER['REMOTE_ADDR']."' WHERE `username`='".$_SESSION['username']."';";
				mysqli_query($conn,$ipsql);
				echo  '<meta http-equiv="refresh" content="0; URL=./">';
			}
			else {
				addBanner('Invalid Username or Password.');
			}
		}
	?>
	<div id="lcp" class="cp"><form class="loginform" method="post">
		<span class="h">Log In</span><hr>
		<small>To log in, use your username (not email) for minecraft and your password you created when you created your account here.</small><br>
		<label for="frminuser">Minecraft Username:</label><br>
		<input type="text" id="frminuser" placeholder="Username" name="username" required maxlength=16 autocomplete="username"><br>
		<label for="frminpass">Password:</label><br>
		<input type="password" id="frminpass" placeholder="Password" name="password" required maxlength=16 autocomplete="current-password"><br>
		<input type="submit" value="Sign In" name="signin"><br>
	</form></div>
	<div id="rcp" class="cp"><form class="loginform" method="post">
		<span class="h">Create Account</span><hr>
		<div class="col">
			<label for="frmupmail">Minecraft Email:</label><br>
			<input type="email" id="frmupmail" placeholder="someone@example.com" name="email" required autocomplete="email"><br>
			<label for="frminmine">Minecraft Password:</label><br>
			<input type="password" id="frminmine" placeholder="Password" name="passwordm" required maxlength=16 autocomplete="current-password"><br>
			<small>This information will not be recorded. It will only be used to fetch your username and skin and to verify your account.</small><br>
		</div>
		<div class="col">
			<label for="frmuppass">Password:</label><br>
			<input type="password" id="frmuppass" placeholder="New Password" name="password" required maxlength=16 autocomplete="new-password"><br>
			<label for="frmupword">Retype Password:</label><br>
			<input type="password" id="frmupword" placeholder="Retype Password" name="password2" required maxlength=16 autocomplete="new-password"><br>
			<input type="submit" value="Sign Up" name="signup"><br>
		</div>
	</form></div>
</body>
</html>
