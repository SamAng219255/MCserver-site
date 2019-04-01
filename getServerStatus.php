<?php
session_start();

if($_SESSION['permissions']>=1) {
	echo '{"on":'.(shell_exec("./getServerStatus")>0 ? 'true' : 'false').'}';
}
else {
	echo '{"on":false}';
}

?>