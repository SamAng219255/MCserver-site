<?php
session_start();
require 'db.php';

echo '{"input":'.json_encode($_GET).',"response":';

if($_SESSION['permissions']>0) {
	$name=mysqli_real_escape_string($conn,$_GET['name']);
	$checkquery="SELECT `id`,`name` FROM `mcstuff`.`commanders` WHERE `name`='".mysqli_real_escape_string($conn,$name)."';";
	if($checkqueryresult=mysqli_query($conn,$checkquery)) {
		if($checkqueryresult->num_rows<1) {
			$specs='';
			for($i=0; $i<count($_GET['specials']); $i++) {
				if($i>0) {
					$specs.=',';
				}
				$specs.=$_GET['specials'][$i];
			}
			$sql="INSERT INTO `mcstuff`.`commanders` (`id`,`name`,`owner`,`special`,`xp`,`army`,`nation`) VALUES ('0','".mysqli_real_escape_string($conn,$_GET['name'])."','".$_SESSION['username']."','".$specs."','0','0','".mysqli_real_escape_string($conn,$_GET['nation'])."');";
			if(mysqli_query($conn,$sql)) {
				echo '{"status":0,"text":"Commander added.","sql":"'.$sql.'"}';
			}
			else {
				echo '{"status":1,"text":"An unknown error occurred while adding commander.","sql":"'.$sql.'"}';
			}
		}
		else {
			echo '{"status":2,"text":"There is already a commander with that name."}';
		}
	}
	else {
		echo '{"status":1,"text":"An unknown error occurred while checking for name redundancy.","sql":"'.$checkquery.'"}';
	}
}
else {
	echo '{"status":99,"text":"Invalid permissions."}';
}

echo '}';

?>