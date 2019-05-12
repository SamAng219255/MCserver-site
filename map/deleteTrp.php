<?php
session_start();
require 'db.php';

if($_SESSION['permissions']>0) {
	$query="SELECT `owner`,`id` FROM `mcstuff`.`troops` WHERE `id`='".intval($_GET['id'])."';";
	if($queryresult=mysqli_query($conn,$query)) {
		if($queryresult->num_rows>0) {
			$row=mysqli_fetch_row($queryresult);
			if($row[0]==$_SESSION['username']) {
				$sql="DELETE FROM `mcstuff`.`troops` WHERE `id`='".intval($_GET['id'])."';";
				if(mysqli_query($conn,$sql)) {
					echo '{"action":"delete","status":0,"text":"The army has been deleted."}';
				}
				else {
					echo '{"action":"delete","status":1,"text":"An unkown error occured while deleting army.","sql":"'.$checkquery.'"}';
				}
			}
			else {
				echo '{"action":"delete","status":3,"text":"You do not own that unit."}';
			}
		}
		else {
			echo '{"action":"delete","status":2,"text":"No unit with that id exists."}';
		}
	}
	else {
		echo '{"action":"delete","status":1,"text":"An unknown error occured while checking army ownership.","sql":"'.$checkquery.'"}';
	}
}
?>