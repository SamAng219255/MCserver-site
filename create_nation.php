<?php
session_start();
require 'db.php';
$query="SELECT `nation`,`username` FROM `mcstuff`.`users` WHERE `permissions`>0 AND `username`='".$_SESSION['username']."';";
$queryresult=mysqli_query($conn,$query);
$row=mysqli_fetch_row($queryresult);
$sql="INSERT INTO `mcstuff`.`nations` (`id`,`name`,`ruler`,`desc`) VALUES ('0','".$row[0]."','".$row[1]."','');";
$res=mysqli_query($conn,$sql);
/*echo "<p>".$sql."<br>";
var_dump($res);
echo "</p>";*/
echo  '<meta http-equiv="refresh" content="0; URL=./nation_edit.php?nation='.$row[0].'">';
?>