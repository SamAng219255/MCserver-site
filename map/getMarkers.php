<?php
session_start();
if ((isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) || (!isset($_SESSION['last_active']) && isset($_SESSION['loggedin']))) {
	session_unset();
	session_destroy();
}
$_SESSION['last_active']=time();
require 'db.php';

echo '{"pins":[';

$restrictions='';
if(isset($_GET['xMin'])) {
	$restrictions="`x`>".$_GET['xMin'];
}
if(isset($_GET['xMax'])) {
	if($restrictions!=='') {
		$restrictions.=' AND ';
	}
	$restrictions.="`x`<".$_GET['xMax'];
}
if(isset($_GET['zMin'])) {
	if($restrictions!=='') {
		$restrictions.=' AND ';
	}
	$restrictions.="`z`>".$_GET['zMin'];
}
if(isset($_GET['zMax'])) {
	if($restrictions!=='') {
		$restrictions.=' AND ';
	}
	$restrictions.="`z`<".$_GET['zMax'];
}
$query="SELECT * FROM `mcstuff`.`mappoints` ORDER BY `name` ASC;";
if($restrictions!=='') {
	$query="SELECT * FROM `mcstuff`.`mappoints` WHERE ".$restrictions." ORDER BY `name` ASC;";
}
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	if($i>0) {
		echo ',';
	}
	echo '{"id":'.$row[0].',"user":"'.$row[1].'","name":"'.str_replace('"','\\"',$row[2]).'","desc":"'.str_replace("	","\\t",htmlspecialchars(str_replace(array("\r\n","\r","\n",'
'), "\\n", $row[3]))).'","x":'.$row[4].',"z":'.$row[5].',"dimension":'.$row[6].'}';
}}

echo '],"troops":[';

$colours=array('default'=>'000000');

$query="SELECT `nation`,`forecolor` FROM `mcstuff`.`users`;";
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	$colours[$row[0]]=$row[1];
}}

$query="SELECT * FROM `mcstuff`.`troops` ORDER BY `name` ASC;";
if($restrictions!=='') {
	$query="SELECT * FROM `mcstuff`.`troops` WHERE ".$restrictions." ORDER BY `name` ASC;";
}
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	if($i>0) {
		echo ',';
	}
	$colour="000000";
	if(isset($colours[$row[2]]))
		$colour=$colours[$row[2]];
	$isowned='false';
	if($row[1]==$_SESSION['username'])
		$isowned='true';
	$mobiletxt='false';
	if($row[12]=='1')
		$mobiletxt='true';
	$rangedtxt='false';
	if($row[13]=='1')
		$rangedtxt='true';
	$usescustom='false';
	if($row[17]=='1')
		$usescustom='true';
	echo '{"id":'.$row[0].',"owner":"'.$row[1].'","nation":"'.str_replace('"','\\"',$row[2]).'","name":"'.str_replace('"','\\"',$row[3]).'","size":'.$row[4].',"power":'.$row[5].',"health":'.$row[6].',"x":'.$row[7].',"z":'.$row[8].',"move":'.$row[9].',"moveleft":'.$row[10].',"sprite":'.$row[11].',"state":'.$row[14].',"cost":'.$row[15].',"origsize":'.$row[16].',"customsprite":'.$usescustom.',"color":"'.$colour.'","dimension":0,"owned":'.$isowned.',"mobile":'.$mobiletxt.',"ranged":'.$rangedtxt.'}';
}}

echo '],"colors":'.json_encode($colours).',"sprites":[';

$query="SELECT * FROM `mcstuff`.`sprites`;";
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	if($row[2]=='army') {
		if($i>0) {
			echo ',';
		}
		echo '{"id":'.$row[0].',"name":"'.$row[1].'","type":"'.$row[2].'","width":'.$row[3].',"height":'.$row[4].'}';
	}
}}

echo ']}';

?>
