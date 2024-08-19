<?php
	session_start();
	if ((isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) || (!isset($_SESSION['last_active']) && isset($_SESSION['loggedin']))) {
		session_unset();
		session_destroy();
	}
	if(isset($_POST['logout'])) {
		session_unset();
		session_destroy();
	}
	$_SESSION['last_active']=time();
	$loggedin=false;
	require 'db.php';
	if(isset($_SESSION['username'])) {
		$query=$pdo->prepare("SELECT `username`,`uuid`,`permissions`,`forecolor`,`backcolor`,`nation`,`character`,`prefix`,`suffix`,`skin` FROM `mcstuff`.`users` WHERE `username`=?;");
		$query->bindValue(1, $_SESSION['username'], PDO::PARAM_STR);
		if($query->execute()) {
			$row=$query->fetch(PDO::FETCH_BOTH);
			$uuid=$row[1];
			$permissions=intval($row[2]);
			$forecolor=$row[3];
			$backcolor=$row[4];
			$nation=$row[5];
			$character=$row[6];
			$prefix=$row[7];
			$suffix=$row[8];
			$loggedin=true;
		}
	}
	$topics=array('General','War','Trade','Alliances','Politics','Characters','History','Physics','Meta');
	if($loggedin && $permissions>0) {
		array_push($topics,'Admin');
	}
	require 'model.php';
?>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>AmospiaCraft</title>
	<link rel="stylesheet" type="text/css" href="./theme.css">
	<link rel="stylesheet" type="text/css" href="./model.css">
	<link href="./img/sign.png" rel="shortcut icon">
	<script src="jquery.js"></script>
	<script src="pxem.jQuery.js"></script>
	<script src="getTimeOnServer.js"></script>
	<script src="loadposts.js"></script>
	<?php if($loggedin) {echo '<script>username="'.$_SESSION['username'].'"; loggedin=true;</script>';} else {echo '<script>loggedin=false;</script>';}?>
	<style>#profile{cursor: initial;}</style>
</head>