<?php session_start(); ?>{"posts":[<?php
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
if(isset($_SESSION['topic'])) {
	$restriction=" AND `topic`='".mysqli_real_escape_string($conn,$_SESSION['topic'])."'";
}
elseif(isset($_SESSION['tag'])) {
	$restriction=" AND `tags` LIKE '%,".mysqli_real_escape_string($conn,$_SESSION['tag']).",%'";
}
elseif(isset($_SESSION['poster'])) {
	$restriction=" AND `username`='".mysqli_real_escape_string($conn,$_SESSION['poster'])."'";
}
if($_SESSION['permissions']==0) {
	$restriction.=" AND NOT (`tags` LIKE '%,admin,%' OR `topic`='admin')";
}
$query="SELECT * FROM (SELECT `username`,`topic`,`tags`,`content`,`time`,`id` FROM `mcstuff`.`posts` WHERE id".$glt.$startId.$restriction." ORDER BY id DESC LIMIT ".$postCount.") AS `table` ORDER by id ".$order.";";
$queryresult=mysqli_query($conn,$query);
for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	$owned='false';
	if(isset($_SESSION['username']) && $_SESSION['username']==$row[0]) {
		$owned='true';
	}
	if($i>0) {
		echo ',';
	}
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
$stylequery="SELECT `username`,`forecolor`,`backcolor`,`permissions` FROM `mcstuff`.`users` WHERE `permissions`>0;";
$stylequeryresult=mysqli_query($conn,$stylequery);
for($i=0; $i<$stylequeryresult->num_rows; $i++) {
	if($i>0) {
		echo ',';
	}
	$row=mysqli_fetch_row($stylequeryresult);
	echo '{"username":"'.$row[0].'",';
	echo '"forecolor":"'.$row[1].'",';
	echo '"backcolor":"'.$row[2].'"}';
}
?>]<?php echo ',"sql":"'.$query.'"'; ?>}