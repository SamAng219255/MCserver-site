<?php
session_start();
require 'db.php';

$data=array("index"=>array(), "relations"=>array(), "player"=>array());

$nationlist=array();
$nationsquery="SELECT `nation`,`nation` as `ruler` FROM `mcstuff`.`troops` UNION SELECT `nation2`,`nation2` FROM `mcstuff`.`relations` UNION SELECT `name`,`ruler` FROM `mcstuff`.`nations` ORDER BY `nation`;";
foreach($pdo->query($nationsquery, PDO::FETCH_BOTH) as $row) {
	if(!isset($data['relations'][$row[0]])) {
		array_push($data['index'],$row[0]);
		$data['relations'][$row[0]]=array(array(),array(),array(),array(),array(),array(),array());
	}
	if($row[0]!=$row[1]) {
		$data['player'][$row[0]]=$row[1];
	}
}
$relationquery="SELECT `nation1`,`nation2`,`relation`-1 FROM `mcstuff`.`relations`;";
foreach($pdo->query($relationquery, PDO::FETCH_BOTH) as $row) {
	for($j=0; $j<2; $j++) {
		if(!isset($data['relations'][$row[$j]])) {
			array_push($data['index'],$row[$j]);
			$data['relations'][$row[$j]]=array(array(),array(),array(),array(),array(),array(),array());
		}
		array_push($data['relations'][$row[$j]][intval($row[2])],$row[1-$j]);
	}
}
$nationcount=count($data['index']);
for($i=0; $i<$nationcount; $i++) {
	for($j=0; $j<7; $j++) {
		sort($data['relations'][$data['index'][$i]][$j]);
	}
}

echo json_encode($data);

?>