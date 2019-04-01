<?php
session_start();

if($_SESSION['permissions']>=1) {
	$response=shell_exec("./getServerStatus");
	echo '{"on":'.($response>0 ? 'true' : 'false').',"allowed":true,"response":"'.$response.'"}';
}
else {
	echo '{"on":false,"allowed":false}';
}

?>