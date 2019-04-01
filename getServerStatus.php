<?php
session_start();

if($_SESSION['permissions']>=1) {
	echo '{"on":'.(shell_exec("./getServerStatus")>0 ? 'true' : 'false').',"allowed":true}';
}
else {
	echo '{"on":false,"allowed":false}';
}

?>