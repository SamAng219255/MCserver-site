<?php
session_start();

if($_SESSION['permissions']>=1) {
	shell_exec("./turnOnServer");
	echo '{"allowed":true}';
}
else {
	echo '{"allowed":false}';
}

?>