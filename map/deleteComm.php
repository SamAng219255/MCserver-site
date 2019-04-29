<?php
session_start();
require 'db.php';

echo '{"input":'.json_encode($_GET).',"response":';

$id=intval($_GET['id']);

if($_SESSION['permissions']>0) {
	$ownerquery="SELECT `owner` FROM `mcstuff`.`commanders` WHERE `id`='".$id."'";
	if($ownerqueryresult=mysqli_query($conn,$ownerquery)) {
		if(mysqli_fetch_row($ownerqueryresult)[0]==$_SESSION['username']) {
			$oldbonusquery="SELECT `special` FROM `mcstuff`.`commanders` WHERE `army`=(SELECT `army` FROM `mcstuff`.`commanders` WHERE `id`='".$id."') AND `id`!='".$id."';";
			if($oldbonusqueryresult=mysqli_query($conn,$oldbonusquery)) {
				$oldbonuses='';
				for($i=0; $i<$oldbonusqueryresult->num_rows; $i++) {
					if($i>0) {
						$oldbonuses.=',';
					}
					$oldbonusrow=mysqli_fetch_row($oldbonusqueryresult);
					$oldbonuses.=$oldbonusrow[0];
				}
				$oldbonussql="UPDATE `mcstuff`.`troops` SET `bonuses`='".$oldbonuses."' WHERE `id`=(SELECT `army` FROM `mcstuff`.`commanders` WHERE `id`='".mysqli_real_escape_string($conn,$_GET['id'])."');";
				if(mysqli_query($conn,$oldbonussql)) {
					$sql="DELETE FROM `mcstuff`.`commanders` WHERE `id`='".$id."';";
					if(mysqli_query($conn,$sql)) {
						echo '{"status":0,"text":"Commander deleted.","sql":"'.$sql.'","sql1":"'.$oldbonusquery.'","sql2":"'.$oldbonussql.'"}';
					}
					else {
						echo '{"status":1,"text":"An unknown error occurred while deleting commander.","sql":"'.$sql.'"}';
					}
				}
				else {
					echo '{"status":1,"text":"An unknown error occurred while resetting army commander bonuses.","sql":"'.$bonussql.'"}';
				}
			}
			else {
				echo '{"status":1,"text":"An unknown error occurred while retrieving army commander bonuses.","sql":"'.$oldbonusquery.'"}';
			}
		}
		else {
			echo '{"status":2,"text":"You do not own that commander."}';
		}
	}
	else {
		echo '{"status":1,"text":"An unknown error occurred while checking commander owner."}';
	}
}
else {
	echo '{"status":99,"text":"Invalid permissions."}';
}

echo '}';

?>