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
	<link rel="stylesheet" type="text/css" href="./theme.css">
	<style>
		#login {
			height: 15em;
			width: 40em;
			background-color: #808080;
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%,-50%);
			text-align: center;
			padding-top: 5em;
		}
		#form {
			height: 16em;
			width: 26em;
			background-color: #808080;
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%,-50%);
			padding: 2em;
		}
		input[name="name"] {
			width: 15em;
		}
		input[name="x"] {
			width: 6em;
		}
		input[name="z"] {
			width: 6em;
		}
		input[name="dim"] {
			width: 7em;
		}
		textarea[name="desc"] {
			height: 12em;
			width: 20.25em;
			resize: none;
		}
		.banner {
			visibility: hidden;
			background-color: blue;
			width: 50%;
			position: relative;
			margin: auto;
			padding: 1em;
			text-align: center;
			background-image: url("../img/planks_birch.png");
			background-size: auto 100%;
			animation: bannerfade 10s;
			animation-timing-function: linear;
		}
		@keyframes bannerfade{
			0% {visibility: visible; opacity: 1}
			90% {visibility: visible; opacity: 1}
			100% {visibility: visible; opacity: 0}
		}
	</style>
</head>
<body>
	<?php
		$bannerCount=0;
		function addBanner($bannerTxt) {
			echo '<div class="banner">'.$bannerTxt.'</div>';
		}
		require 'db.php';
		//addBanner('test');
		if(isset($_POST['name'])) {
			$_SESSION['name']=mysqli_real_escape_string($conn,$_POST['name']);
			$_SESSION['desc']=mysqli_real_escape_string($conn,$_POST['desc']);
			$_SESSION['x']=$_POST['x'];
			$_SESSION['z']=$_POST['z'];
			$_SESSION['dimension']=$_POST['dimension'];
			if(!isset($_SESSION['user'])) {
				addBanner('It appears that your session has expired. Please log in to finish submitting.');
			}
		}
		var_dump($_SESSION);
		if (isset($_SESSION['user'])) {
			if(isset($_SESSION['name'])) {
				$sql="INSERT INTO `mcstuff`.`mappoints` (`id`,`user`,`name`,`desc`,`x`,`z`,`dimension`) VALUES ('0','".$_SESSION['user']."','".$_SESSION['name']."','".$_SESSION['desc']."','".$_SESSION['x']."','".$_SESSION['z']."','".$_SESSION['dimension']."');";
				if(mysqli_query($conn,$sql)) {
					addBanner('You have successfully submitted the pin "'.$_SESSION['name'].'".');
				}
				else {
					addBanner('An error has occured while submitting the pin "'.$_SESSION['name'].'".');
				}
				unset($_SESSION['name']);
			}
			require 'form.php';
		}
		elseif(isset($_POST['username'])) {
			$url = "https://authserver.mojang.com/authenticate";
			$data = array();
			$data['agent'] = array("name" => "Minecraft", "version" => 1);
			$data['username'] = $_POST['username'];
			$data['password'] = $_POST['password'];
			$options = array(
				'http' => array(
					'header' => "Content-type: application/json",
					'method' => 'POST',
					'content' => json_encode($data),
				),
			);
			$context = stream_context_create($options);
			$result = json_decode(file_get_contents($url, false, $context));
			if($result==false) {
				addBanner('Invalid Login');
				echo '<div id="login">
		<p>You will need to login with your minecraft account:</p>
		<form method="POST">
			<input type="email" name="username" value="" required="" autocomplete="email" placeholder="Email">
			<input type="password" name="password" required="" autocomplete="current-password" placeholder="Password">
			<input type="submit" value="Login">
		</form>
	</div>';
			}
			else {
				$profile = $result->selectedProfile;
				$allowedUsers=["redstonetardis42","petrok9001","skinz123","list","kagetora0","thedragonslain","luckyknight68","1999sam1999","greenhouscreeper","enddragon9","lewisthekiller","gentleworks","antraveler","153norc","lightningpwr28","sugargizmo","silverleafnight","pharaohcrab","patientneutral","enderninja7","drn21","d_hex","aquatailz"];
				if(in_array(strtolower($profile->name), $allowedUsers)) {
					$_SESSION['user']=strtolower($profile->name);
					require 'db.php';
					if(isset($_SESSION['name'])) {
						$sql="INSERT INTO `mcstuff`.`mappoints` (`id`,`user`,`name`,`desc`,`x`,`z`,`dimension`) VALUES ('0','".$_SESSION['user']."','".$_SESSION['name']."','".$_SESSION['desc']."','".$_SESSION['x']."','".$_SESSION['z']."','".$_SESSION['dimension']."');";
						if(mysqli_query($conn,$sql)) {
							addBanner('You have successfully submitted the pin "'.$_SESSION['name'].'".');
						}
						else {
							addBanner('An error has occured while submitting the pin "'.$_SESSION['name'].'".');
						}
						unset($_SESSION['name']);
					}
					require 'form.php';
				}
				else {
					addBanner('That user is not allowed to submit map locations.');
					echo '<div id="login">
		<p>You will need to login with your minecraft account:</p>
		<form method="POST">
			<input type="email" name="username" value="" required="" autocomplete="email" placeholder="Email">
			<input type="password" name="password" required="" autocomplete="current-password" placeholder="Password">
			<input type="submit" value="Login">
		</form>
	</div>';
				}
			}
		}
		else {
			echo '<div id="login">
		<p>You will need to login with your minecraft account:</p>
		<form method="POST">
			<input type="email" name="username" value="" required="" autocomplete="email" placeholder="Email">
			<input type="password" name="password" required="" autocomplete="current-password" placeholder="Password">
			<input type="submit" value="Login">
		</form>
	</div>';
		}
	?>
</body>
</html>
