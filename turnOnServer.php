<?php
session_start();

if($_SESSION['permissions']>=1) {
	$response=shell_exec("./turnOnServer");
	echo '{"allowed":true,"response":"'.json_encode($response).'"}';
}
else {
	echo '{"allowed":false}';
}

?>