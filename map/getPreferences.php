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

if(isset($_SESSION['username'])) {
	$query="SELECT `scrollmode` FROM `mcstuff`.`users` WHERE `username`='{$_SESSION['username']}';";
	if($queryresult=mysqli_query($conn,$query)) {
		$row=mysqli_fetch_row($queryresult);
		echo '{"status":0,"text":"Preferences successfully fetched.","scrollmode":'.($row[0]=='1'?'true':'false').'}';
	}
	else {
		echo '{"status":1,"text":"An unknown error occured while fetching user preferences.","sql":"'.$query.'","scrollmode":false}';
	}
}
else {
	echo '{"status":-1,"text":"No user is logged in.","scrollmode":false}';
}
?>
