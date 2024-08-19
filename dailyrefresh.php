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
	$pdo->exec("UPDATE `mcstuff`.`troops` SET `moveleft`=`move`;");
	$queryresult=$pdo->query("SELECT `name`,`troopresource` FROM `mcstuff`.`nations`;", PDO::FETCH_BOTH);
	foreach($pdo->query("SELECT `name`,`troopresource` FROM `mcstuff`.`nations`;", PDO::FETCH_BOTH) as $row) {
		$resourcesql = $pdo->prepare("UPDATE `mcstuff`.`resources` SET `ntnlwlth` = $days * `ntnlincome` + `ntnlwlth` WHERE `nation` = :nation AND `type` = :type;");
		$resourcesql->bindValue('nation', $row['name'], PDO::PARAM_STR);
		$resourcesql->bindValue('type', $row['troopresource'], PDO::PARAM_STR);
		$resourcesql->execute();
	}
}

?>