<?php
	session_start();
	require "db.php";
	if(is_numeric($_GET['target'])) {
		$query=$pdo->prepare("SELECT `username`,`id` FROM `mcstuff`.`posts` WHERE `id`=?;");
		$query->bindValue(1, $_GET['target'], PDO::PARAM_STR);
		if($query->execute()) {
			$row=$query->fetch(PDO::FETCH_BOTH);
			if($row[0]==$_SESSION['username']) {
				$sql=$pdo->prepare("DELETE FROM `mcstuff`.`posts` WHERE `id`=?;");
				$sql->bindValue(1, $_GET['target'], PDO::PARAM_STR);
				if($sql->execute()) {
					echo 'true';
				}
				else {
					echo 'SQL error during post delete. SQL: '.$sql->queryString;
				}
			}
			else {
				echo 'You do not own this post.';
			}
		}
		else {
			echo 'SQL error during ownership check. SQL: '.$query->queryString;
		}
	}
	else {
		echo '"target" is not a number.';
	}
?>