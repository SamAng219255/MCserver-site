<?php
session_start();
require 'db.php';

if($_SESSION['permissions']>0) {
	$query="SELECT `owner`,`id` FROM `mcstuff`.`troops` WHERE `id`='".intval($_GET['id'])."';";
	if($queryresult=mysqli_query($conn,$query)) {
		if($queryresult->num_rows>0) {
			$row=mysqli_fetch_row($queryresult);
			if($row[0]==$_SESSION['username']) {
				$sql="UPDATE `mcstuff`.`troops` SET `name`='".mysqli_real_escape_string($conn,$_GET['name'])."',`nation`='".mysqli_real_escape_string($conn,$_GET['owner'])."' WHERE `id`='".intval($_GET['id'])."';";
				if(mysqli_query($conn,$sql)) {
					echo '{"action":"edit","status":0,"text":"The army has been editted."}';
				}
				else {
					echo '{"action":"edit","status":1,"text":"An unkown error occured while editting army.","sql":"'.$checkquery.'"}';
				}
			}
			else {
				echo '{"action":"edit","status":3,"text":"You do not own that unit."}';
			}
		}
		else {
			echo '{"action":"edit","status":2,"text":"No unit with that id exists."}';
		}
	}
	else {
		echo '{"action":"edit","status":1,"text":"An unkown error occured while checking army ownership.","sql":"'.$checkquery.'"}';
	}
}
?>