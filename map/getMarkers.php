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

$data=[];

//echo '{"pins":[';
$data['pins']=[];

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
$query="SELECT `id`,`user`,`name`,`desc`,`x`,`z`,`dimension`,`type`,`icondata` FROM `mcstuff`.`mappoints` ORDER BY `name` ASC;";
if($restrictions!=='') {
	$query="SELECT `id`,`user`,`name`,`desc`,`x`,`z`,`dimension`,`type`,`icondata` FROM `mcstuff`.`mappoints` WHERE ".$restrictions." ORDER BY `name` ASC;";
}
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	/*if($i>0) {
		echo ',';
	}*/
	$isowned='false';
	if(isset($_SESSION['username']) && strtolower($row[1])==strtolower($_SESSION['username'])) {
		$isowned='true';
	}
	/*echo '{"id":'.$row[0].',"user":"'.$row[1].'","owned":'.$isowned.',"name":"'.str_replace('"','\\"',$row[2]).'","desc":"'.str_replace("	","\\t",htmlspecialchars(str_replace(array("\r\n","\r","\n",'
'), "\\n", $row[3]))).'","x":'.$row[4].',"z":'.$row[5].',"dimension":'.$row[6].',"type":"'.$row[7].'","icondata":"'.$row[8].'"}';*/
	array_push($data['pins'], [
		'id' => intval($row[0]),
		'user' => $row[1],
		'owned' => $isowned,
		'name' => str_replace('"','\\"',$row[2]),
		'desc' => str_replace("	","\\t",htmlspecialchars(str_replace(array("\r\n","\r","\n",'
'), "\\n", $row[3]))),
		'x' => intval($row[4]),
		'z' => intval($row[5]),
		'dimension' => intval($row[6]),
		'type' => $row[7],
		'icondata' => $row[8]
	]);
}}

//echo '],"troops":[';
$data['troops']=[];

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

$query="SELECT `id`,`owner`,`nation`,`name`,`size`,`power`,`health`,`x`,`y`,`move`,`moveleft`,`sprite`,`mobile`,`ranged`,`state`,`cost`,`origsize`,`customsprite`,`xp`,`bonuses`,`aiding`,`battle` FROM `mcstuff`.`troops` ORDER BY `name` ASC;";
if($restrictions!=='') {
	$query="SELECT `id`,`owner`,`nation`,`name`,`size`,`power`,`health`,`x`,`y`,`move`,`moveleft`,`sprite`,`mobile`,`ranged`,`state`,`cost`,`origsize`,`customsprite`,`xp`,`bonuses`,`aiding`,`battle` FROM `mcstuff`.`troops` WHERE ".$restrictions." ORDER BY `name` ASC;";
}
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	/*if($i>0) {
		echo ',';
	}*/
	$colour=$colours['default']['fore'];
	if(isset($colours[$row[2]]))
		$colour=$colours[$row[2]]['fore'];
	$isowned=(isset($_SESSION['username']) && $row[1]==$_SESSION['username']);
	$mobiletxt=($row[12]=='1');
	$rangedtxt=($row[13]=='1');
	$usescustom=$row[17]=='1';
	/*$specs='';
	$specdata=explode(',',$row[19]);
	for($j=0; $j<count($specdata); $j++) {
		if($j>0) {
			$specs.=',';
		}
		$specs.='"'.$specdata[$j].'"';
	}*/
	//echo '{"id":'.$row[0].',"owner":"'.$row[1].'","nation":"'.str_replace('"','\\"',$row[2]).'","name":"'.str_replace('"','\\"',$row[3]).'","size":'.$row[4].',"power":'.$row[5].',"health":'.$row[6].',"x":'.$row[7].',"z":'.$row[8].',"move":'.$row[9].',"moveleft":'.$row[10].',"sprite":'.$row[11].',"state":'.$row[14].',"cost":'.$row[15].',"origsize":'.$row[16].',"xp":'.$row[18].',"bonuses":['.$specs.'],"customsprite":'.$usescustom.',"color":"'.$colour.'","dimension":0,"owned":'.$isowned.',"mobile":'.$mobiletxt.',"ranged":'.$rangedtxt.'}';
	array_push($data['troops'],[
		'id' => intval($row[0]),
		'owner' => $row[1],
		'nation' => str_replace('"','\\"',$row[2]),
		'name' => str_replace('"','\\"',$row[3]),
		'size' => intval($row[4]),
		'power' => intval($row[5]),
		'health' => floatval($row[6]),
		'x' => intval($row[7]),
		'z' => intval($row[8]),
		'move' => intval($row[9]),
		'moveleft' => intval($row[10]),
		'sprite' => intval($row[11]),
		'state' => intval($row[14]),
		'cost' => intval($row[15]),
		'origsize' => intval($row[16]),
		'xp' => intval($row[18]),
		'aiding' => intval($row[20]),
		'battle' => intval($row[21]),
		'bonuses' => explode(',',$row[19]),//[$specs],
		'customsprite' => $usescustom,
		'color' => $colour,
		'dimension' => 0,
		'owned' => $isowned,
		'mobile' => $mobiletxt,
		'ranged' => $rangedtxt
	]);
	$troops[$row[0]]=str_replace('"','\\"',$row[3]);
}}

//echo '],"colors":'.json_encode($colours).',"knownnations":'.json_encode($knownnations).',"sprites":[';
$data['colors']=$colours;
$data['knownnations']=$knownnations;

$data['sprites']=[];
$query="SELECT `id`,`name`,`type`,`width`,`height` FROM `mcstuff`.`sprites`;";
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	/*if($i>0) {
		echo ',';
	}*/
	//echo '{"id":'.$row[0].',"name":"'.$row[1].'","type":"'.$row[2].'","width":'.$row[3].',"height":'.$row[4].'}';
	array_push($data['sprites'], [
		'id' => intval($row[0]),
		'name' => $row[1],
		'type' => $row[2],
		'width' => intval($row[3]),
		'height' => intval($row[4]) 
	]);
}}

//echo '],"commanders":[';

$data['commanders']=[];
$query="SELECT `id`,`name`,`owner`,`special`,`xp`,`army`,`nation` FROM `mcstuff`.`commanders`;";
$queryresult=mysqli_query($conn,$query);
if($queryresult) {for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	/*if($i>0) {
		echo ',';
	}*/
	/*$specs='';
	$specdata=explode(',',$row[3]);
	for($j=0; $j<count($specdata); $j++) {
		if($j>0) {
			$specs.=',';
		}
		$specs.='"'.$specdata[$j].'"';
	}*/
	$isowned='false';
	if(isset($_SESSION['username']) && $row[2]==$_SESSION['username'])
		$isowned='true';
	//echo '{"id":'.$row[0].',"name":"'.$row[1].'","owner":"'.$row[2].'","special":['.$specs.'],"xp":'.$row[4].',"armyid":'.$row[5].',"armyname":"'.(array_key_exists($row[5],$troops) ? $troops[$row[5]] : '').'","nation":"'.$row[6].'","owned":'.$isowned.'}';
	array_push($data['commanders'], [
		'id' => intval($row[0]),
		'name' =>  $row[1],
		'owner' =>  $row[2],
		'special' =>  explode(',',$row[3]),//$specs,
		'xp' => intval($row[4]),
		'armyid' => intval($row[5]),
		'armyname' => (array_key_exists($row[5],$troops) ? $troops[$row[5]] : ''),
		'nation' => $row[6],
		'owned' => $isowned
	]);
}}

//echo '],"relations":';

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
//echo json_encode($relations);
$data['relations']=$relations;

//echo '}';
echo json_encode($data);
?>
