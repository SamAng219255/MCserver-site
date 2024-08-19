<?php session_start();
if ((isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) || (!isset($_SESSION['last_active']) && isset($_SESSION['loggedin']))) {
	session_unset();
	session_destroy();
}
$_SESSION['last_active']=time();?>{"posts":[<?php
require 'db.php';
$startId=$_GET['start'];
$postCount=$_GET['count'];
$order=$_GET['sort'];
if($order=='ASC') {
	$orderInv='DESC';
	$glt='>';
}
elseif($order=='DESC') {
	$orderInv='ASC';
	$glt='<';
}
$restriction='';
$restrictionValue='';
if(isset($_SESSION['topic'])) {
	$restriction=" AND `topic`=:resval";
	$restrictionValue=$_SESSION['topic'];
}
elseif(isset($_SESSION['tag'])) {
	$restriction=" AND `tags` LIKE :resval";
	$restrictionValue='%,'.str_replace('%','\\%',$_SESSION['tag']).',%';
}
elseif(isset($_SESSION['poster'])) {
	$restriction=" AND `username`=:resval";
	$restrictionValue=$_SESSION['poster'];
}
if($_SESSION['permissions']==0) {
	$restriction.=" AND NOT (`tags` LIKE '%,admin,%' OR `topic`='admin')";
}
$query=$pdo->prepare("SELECT * FROM (SELECT `username`,`topic`,`tags`,`content`,`time`,`id` FROM `mcstuff`.`posts` WHERE id".$glt.':startId'.$restriction." ORDER BY id DESC LIMIT :postCount) AS `table` ORDER by id ".$order.";");
$query->bindValue('startId', intval($startId), PDO::PARAM_INT);
if($restrictionValue !== '') $query->bindValue('resval', $restrictionValue, PDO::PARAM_STR);
$query->bindValue('postCount', intval($postCount), PDO::PARAM_INT);
$query->execute();
$first=true;
foreach($query->fetchAll(PDO::FETCH_BOTH) as $row) {
	$owned='false';
	if(isset($_SESSION['username']) && $_SESSION['username']==$row[0]) {
		$owned='true';
	}
	if($first)
		$first=false;
	else
		echo ',';
	echo '{"username":"'.$row[0].'",';
	echo '"topic":"'.$row[1].'",';
	echo '"tags":"'.$row[2].'",';
	echo '"content":"'.str_replace("	","\\t",htmlspecialchars(str_replace(array("\r\n","\r","\n",'
'), "\\n", $row[3]))).'",';
	echo '"time":"'.$row[4].'",';
	echo '"id":"'.$row[5].'",';
	echo '"owned":'.$owned.'}';
}
?>],"styles":[<?php
;
$first=true;
foreach($pdo->query("SELECT `username`,`forecolor`,`backcolor`,`permissions` FROM `mcstuff`.`users` WHERE `permissions`>0;", PDO::FETCH_BOTH) as $row) {
	if($first)
		$first=false;
	else
		echo ',';
	echo '{"username":"'.$row[0].'",';
	echo '"forecolor":"'.$row[1].'",';
	echo '"backcolor":"'.$row[2].'"}';
}
?>]<?php echo ',"sql":{"prepared":"'.$query->queryString.'","startId":"'.intval($startId).'","restrictionValue":"'.$restrictionValue.'","postCount":"'.intval($postCount).'"}'; ?>}