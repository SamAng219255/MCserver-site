<?php
session_start();
require 'db.php';
$query = $pdo->prepare('SELECT `nation`,`username` FROM `mcstuff`.`users` WHERE `permissions`>0 AND `username`=?;');
$query->bindValue(1, $_SESSION['username'], PDO::PARAM_STR);
$query->execute();
$row = $query->fetch(PDO::FETCH_BOTH);
$sql = $pdo->prepare("INSERT INTO `mcstuff`.`nations` (`id`, `name`, `ruler`, `desc`) VALUES ('0', :name, :ruler, '');");
$query->bindValue('name', $row['nation'], PDO::PARAM_STR);
$query->bindValue('ruler', $row['username'], PDO::PARAM_STR);
$query->execute();
echo  '<meta http-equiv="refresh" content="0; URL=./nation_edit.php?nation='.$row['nation'].'">';
?>