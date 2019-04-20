<?php

if(!isset($conn)) {
	require 'db.php';
}
$dateread=fopen("dateupdated.txt","r");
$dateupdated=intval(fread($dateread,16));
fclose($dateread);
$newdate=date_timestamp_get(date_create());
if($newdate-$dateupdated>=86400) {
	$days=floor(($newdate-$dateupdated)/86400);
	$setdate=$dateupdated+($days*86400);
	$datewrite=fopen("dateupdated.txt","w");
	fwrite($datewrite,strval($setdate));
	fclose($datewrite);
	$movesql="UPDATE `mcstuff`.`troops` SET `moveleft`=`move`;";
	mysqli_query($conn,$movesql);
	$nationsql="SELECT `name`,`troopresource` FROM `mcstuff`.`nations`;";
	$queryresult=mysqli_query($conn,$nationsql);
	for($i=0; $i<$queryresult->num_rows; $i++) {
		$row=mysqli_fetch_row($queryresult);
		$resourcesql="UPDATE `mcstuff`.`resources` SET `ntnlwlth`=".$days."*`ntnlincome`+`ntnlwlth` WHERE `nation`='".$row[0]."' AND `type`='".$row[1]."';";
		mysqli_query($conn,$resourcesql);
	}
}

?>