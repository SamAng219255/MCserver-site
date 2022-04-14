<?php
session_start();
if ((isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) || (!isset($_SESSION['last_active']) && isset($_SESSION['loggedin']))) {
	unset($_SESSION['last_active']);
	unset($_SESSION['loggedin']);
	unset($_SESSION['username']);
	unset($_SESSION['permissions']);
	unset($_SESSION['nation']);
	unset($_SESSION['topic']);
	unset($_SESSION['tag']);
	unset($_SESSION['poster']);
}
$_SESSION['last_active']=time();
require 'db.php';

$_SESSION['scrollmode']=($_GET['scrollmode']==='1'?true:false);

if(isset($_SESSION['username'])) {
	$scrollmodeStr=($_SESSION['scrollmode']?'1':'0');
	$sql="UPDATE `mcstuff`.`users` SET `scrollmode`='{$scrollmodeStr}' WHERE `username`='{$_SESSION['username']}';";
	if(mysqli_query($conn,$sql)) {
		echo '{"status":0,"text":"Preferences successfully set."}';
	}
	else {
		echo '{"status":1,"text":"An unknown error occured while setting user preferences.","sql":"'.$sql.'"}';
	}
}
else {
	echo '{"status":-1,"text":"No user is logged in."}';
}
?>
