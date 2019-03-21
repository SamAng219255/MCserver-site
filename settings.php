<?php
if(isset($_POST['save'])) {
	$forecolor=substr($_POST['forecolor'],1);
	$backcolor=substr($_POST['backcolor'],1);
	$nation=$_POST['nation'];
	$character=$_POST['character'];
	$prefix=$_POST['prefix'];
	$suffix=$_POST['suffix'];
	$settingsql="UPDATE `mcstuff`.`users` SET `forecolor`='".mysqli_real_escape_string($conn,$forecolor)."',`backcolor`='".mysqli_real_escape_string($conn,$backcolor)."',`nation`='".mysqli_real_escape_string($conn,$nation)."',`character`='".mysqli_real_escape_string($conn,$character)."',`prefix`='".mysqli_real_escape_string($conn,$prefix)."',`suffix`='".mysqli_real_escape_string($conn,$suffix)."' WHERE `username`='".$_SESSION['username']."';";
	mysqli_query($conn,$settingsql);
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

$nationquery="SELECT `name`,`ruler` FROM `mcstuff`.`nations` WHERE `ruler`='".$_SESSION['username']."';";
$nationqueryresult=mysqli_query($conn,$nationquery);
if($nationqueryresult->num_rows>0) {
	$nationrow=mysqli_fetch_row($nationqueryresult);
	echo '<a href="./nation_edit.php?nation='.$nationrow[0].'">Edit your nation.</a>';
}
else {
	echo '<a href="./create_nation.php">Create your nation.</a>';
}
?>