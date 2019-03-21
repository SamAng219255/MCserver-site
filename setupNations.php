<?php

require 'db.php';
$query="SELECT `nation`,`username` FROM `mcstuff`.`users` WHERE `permissions`>0;";
$queryresult=mysqli_query($conn,$query);
for($i=0; $i<$queryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($queryresult);
	$sql="INSERT INTO `mcstuff`.`nations` (`id`,`name`,`ruler`,`desc`) VALUES ('0','".$row[0]."','".$row[1]."','');";
	echo "<p>".$sql."<br>";
	var_dump(mysqli_query($conn,$sql));
	echo "</p>";
}

?>