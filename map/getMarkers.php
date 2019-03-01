<?php

require 'db.php';

echo '[';

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
	echo '{"id":'.$row[0].',"user":"'.$row[1].'","name":"'.str_replace('"','\\"',$row[2]).'","desc":"'.str_replace('"','\\"',$row[3]).'","x":'.$row[4].',"z":'.$row[5].',"dimension":'.$row[6].'}';
}}

echo ']';

?>
