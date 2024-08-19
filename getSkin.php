<?php
	session_start();
	require 'db.php';
	function getSkin() {
		global $_SESSION, $pdo;
		$skinquery=$pdo->prepare("SELECT `skin` FROM `mcstuff`.`users` WHERE `username`=?;");
		$skinquery->bindValue(1, $_SESSION['username'], PDO::PARAM_STR);
		$skinquery->execute();
		echo '{"skin":"'.$skinquery->fetch(PDO::FETCH_BOTH)[0].'","updated":false}';
	}
	if(isset($_GET['new'])) {
		$query=$pdo->prepare("SELECT `uuid` FROM `mcstuff`.`users` WHERE `username`=?;");
		$query->bindValue(1, $_SESSION['username'], PDO::PARAM_STR);
		$query->execute();
		$uuid=$query->fetch(PDO::FETCH_BOTH)[0];
		$firstjson=file_get_contents('https://sessionserver.mojang.com/session/minecraft/profile/'.$uuid);
		for($i=0; $i<6; $i++) {
			if($firstjson) {
				break;
			}
			else {
				sleep(10);
				$firstjson=file_get_contents('https://sessionserver.mojang.com/session/minecraft/profile/'.$uuid);
			}
		}
		if($firstjson) {
			$firstobj=json_decode($firstjson);
			$base64='';
			$propCount=count($firstobj->properties);
			for($i=0; $i<$propCount; $i++) {
				if($firstobj->properties[$i]->name=='textures') {
					$base64=$firstobj->properties[$i]->value;
				}
			}
			$secondjson=base64_decode($base64);
			$secondobj=json_decode($secondjson);
			$skin=$secondobj->textures->SKIN->url;
			$sql=$pdo->prepare("UPDATE `mcstuff`.`users` SET `skin`=:skin WHERE `username`=:username;");
			$sql->bindValue('skin', $skin, PDO::PARAM_STR);
			$sql->bindValue('username', $_SESSION['username'], PDO::PARAM_STR);
			$sql->execute();
			echo '{"skin":"'.$skin.'","updated":true}';
		}
		else {
			getSkin();
		}
	}
	else {
		getSkin();
	}
	
?>