<?php

require 'db.php';
$query=$pdo->prepare("SELECT `nation`,`username` FROM `mcstuff`.`users` WHERE `permissions`>0;");
$query->execute();
$sql=$pdo->prepare("INSERT INTO `mcstuff`.`nations` (`id`,`name`,`ruler`,`desc`) VALUES ('0',:name,:ruler,'');");
for($i=0; $i<$query->rowCount(); $i++) {
	$row=$query->fetch(PDO::FETCH_BOTH);
	$sql->bindValue('name', $row[0], PDO::PARAM_STR);
	$sql->bindValue('ruler', $row[1], PDO::PARAM_STR);
	$sql->execute();
}

?>