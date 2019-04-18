<?php
session_start();
if ((isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) || (!isset($_SESSION['last_active']) && isset($_SESSION['loggedin']))) {
	session_unset();
	session_destroy();
}
$_SESSION['last_active']=time();

if($_SESSION['permissions']>=1) {
	$response=shell_exec("./getServerStatus");
	echo '{"on":'.($response>0 ? 'true' : 'false').',"allowed":true,"response":'.json_encode($response).'}';
}
else {
	echo '{"on":false,"allowed":false}';
}

?>