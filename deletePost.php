<?php
	session_start();
	require "db.php";
	if(is_numeric($_GET['target'])) {
		$query="SELECT `username`,`id` FROM `mcstuff`.`posts` WHERE `id`='".$_GET['target']."';";
		if($queryresult=mysqli_query($conn,$query)) {
			$row=mysqli_fetch_row($queryresult);
			if($row[0]==$_SESSION['username']) {
				$sql="DELETE FROM `mcstuff`.`posts` WHERE `id`='".$_GET['target']."';";
				if(mysqli_query($conn,$sql)) {
					echo 'true';
				}
				else {
					echo 'SQL error during post delete. SQL: '.$sql;
				}
			}
			else {
				echo 'You do not own this post.';
			}
		}
		else {
			echo 'SQL error during ownership check. SQL: '.$query;
		}
	}
	else {
		echo '"target" is not a number.';
	}
?>