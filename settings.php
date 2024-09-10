<?php
if(isset($_POST['save'])) {
	$forecolor=substr($_POST['forecolor'],1);
	$backcolor=substr($_POST['backcolor'],1);
	$nation=$_POST['nation'];
	$character=$_POST['character'];
	$prefix=$_POST['prefix'];
	$suffix=$_POST['suffix'];
	$settingsql=$pdo->prepare("UPDATE `mcstuff`.`users` SET `forecolor`=:forecolor,`backcolor`=:backcolor,`nation`=:nation,`character`=:character,`prefix`=:prefix,`suffix`=:suffix WHERE `username`=:username;");
	$settingsql->bindValue('forecolor', $forecolor, PDO::PARAM_STR);
	$settingsql->bindValue('backcolor', $backcolor, PDO::PARAM_STR);
	$settingsql->bindValue('character', $character, PDO::PARAM_STR);
	$settingsql->bindValue('nation', $nation, PDO::PARAM_STR);
	$settingsql->bindValue('prefix', $prefix, PDO::PARAM_STR);
	$settingsql->bindValue('suffix', $suffix, PDO::PARAM_STR);
	$settingsql->bindValue('username', $username, PDO::PARAM_STR);
	$settingsql->execute();
}


echo '<form method="POST">
	<input type="color" name="forecolor" value="#'.$forecolor.'"><br>
	<span>Foreground Color</span><br>
	<input type="color" name="backcolor" value="#'.$backcolor.'"><br>
	<span>Background Color</span><br>
	<input type="text" name="nation" value="'.$nation.'" maxlength="32"><br>
	<span>Nation Name</span><br>
	<input type="text" name="character" value="'.$character.'" maxlength="32"><br>
	<span>Character Name</span><br>
	<input type="text" name="prefix" value="'.$prefix.'" maxlength="32"><br>
	<span>Prefix</span><br>
	<input type="text" name="suffix" value="'.$suffix.'" maxlength="32"><br>
	<span>Suffix</span><br>
	<input type="submit" name="save" value="Save">
</form>';

$nationquery=$pdo->prepare("SELECT `name`,`ruler` FROM `mcstuff`.`nations` WHERE `ruler`=?;");
$nationquery->bindValue(1, $_SESSION['username'], PDO::PARAM_STR);
$nationquery->execute();
if($nationquery->rowCount()>0) {
	$nationrow=$nationquery->fetch(PDO::FETCH_BOTH);
	echo '<a href="./nation_edit.php?nation='.$nationrow[0].'">Edit your nation.</a>';
}
else {
	echo '<a href="./create_nation.php">Create your nation.</a>';
}
?>