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
	$isowned='false';
	if(isset($_SESSION['username']) && strtolower($row[1])==strtolower($_SESSION['username'])) {
		$isowned='true';
	}
	echo '{"id":'.$row[0].',"user":"'.$row[1].'","owned":'.$isowned.',"name":"'.str_replace('"','\\"',$row[2]).'","desc":"'.str_replace("	","\\t",htmlspecialchars(str_replace(array("\r\n","\r","\n",'
'), "\\n", $row[3]))).'","x":'.$row[4].',"z":'.$row[5].',"dimension":'.$row[6].',"type":"'.$row[7].'","icondata":"'.$row[8].'"}';
}}

echo '],"troops":[';

$colours=array('default'=>array('fore'=>'000000','back'=>'#c0c0c0'));
$knownnations=array('default');

$query="SELECT `nation`,`forecolor`,`backcolor`,`username` FROM `mcstuff`.`users`;";
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	$colours[$row[0]]=array('fore'=>$row[1],'back'=>$row[2]);
	array_push($knownnations,$row[0]);
}}

$troops=array('0'=>'');

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
	$colour=$colours['default']['fore'];
	if(isset($colours[$row[2]]))
		$colour=$colours[$row[2]]['fore'];
	$isowned='false';
	if(isset($_SESSION['username']) && $row[1]==$_SESSION['username'])
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
	$specs='';
	$specdata=explode(',',$row[19]);
	for($j=0; $j<count($specdata); $j++) {
		if($j>0) {
			$specs.=',';
		}
		$specs.='"'.$specdata[$j].'"';
	}
	echo '{"id":'.$row[0].',"owner":"'.$row[1].'","nation":"'.str_replace('"','\\"',$row[2]).'","name":"'.str_replace('"','\\"',$row[3]).'","size":'.$row[4].',"power":'.$row[5].',"health":'.$row[6].',"x":'.$row[7].',"z":'.$row[8].',"move":'.$row[9].',"moveleft":'.$row[10].',"sprite":'.$row[11].',"state":'.$row[14].',"cost":'.$row[15].',"origsize":'.$row[16].',"xp":'.$row[18].',"bonuses":['.$specs.'],"customsprite":'.$usescustom.',"color":"'.$colour.'","dimension":0,"owned":'.$isowned.',"mobile":'.$mobiletxt.',"ranged":'.$rangedtxt.'}';
	$troops[$row[0]]=str_replace('"','\\"',$row[3]);
}}

echo '],"colors":'.json_encode($colours).',"knownnations":'.json_encode($knownnations).',"sprites":[';

$query="SELECT * FROM `mcstuff`.`sprites`;";
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	if($i>0) {
		echo ',';
	}
	echo '{"id":'.$row[0].',"name":"'.$row[1].'","type":"'.$row[2].'","width":'.$row[3].',"height":'.$row[4].'}';
}}

echo '],"commanders":[';

$query="SELECT * FROM `mcstuff`.`commanders`;";
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	if($i>0) {
		echo ',';
	}
	$specs='';
	$specdata=explode(',',$row[3]);
	for($j=0; $j<count($specdata); $j++) {
		if($j>0) {
			$specs.=',';
		}
		$specs.='"'.$specdata[$j].'"';
	}
	$isowned='false';
	if(isset($_SESSION['username']) && $row[2]==$_SESSION['username'])
		$isowned='true';
	echo '{"id":'.$row[0].',"name":"'.$row[1].'","owner":"'.$row[2].'","special":['.$specs.'],"xp":'.$row[4].',"armyid":'.$row[5].',"armyname":"'.isset($troops[$row[5]]) ? $troops[$row[5]] : '';.'","nation":"'.$row[6].'","owned":'.$isowned.'}';
}}

echo '],"relations":';

$relations=array();
$query="SELECT * FROM (SELECT `nation1`,`nation2`,`relation`+0 FROM `mcstuff`.`relations` UNION SELECT `nation2`,`nation1`,`relation`+0 FROM `mcstuff`.`relations`) AS `relation`;";
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	if(!isset($relations[$row[0]])) {
		$relations[$row[0]]=array();
	}
	$relations[$row[0]][$row[1]]=intval($row[2]);
}}
$_SESSION['relations']=$relations;
echo json_encode($relations);

echo '}';
?>
