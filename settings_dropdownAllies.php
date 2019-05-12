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
	$alliessql="UPDATE `mcstuff`.`nations` SET `allies`='".mysqli_real_escape_string($conn,json_encode($_POST['allies']))."' WHERE `ruler`='".$_SESSION['username']."';";
	mysqli_query($conn,$alliessql);
}

$nationlist=array();
$nationsquery="SELECT `nation` FROM `mcstuff`.`troops` UNION SELECT `name` FROM `mcstuff`.`nations` ORDER BY `nation`;";
$nationsqueryresult=mysqli_query($conn,$nationsquery);
for($i=0; $i<$nationsqueryresult->num_rows; $i++) {
	$row=mysqli_fetch_row($nationsqueryresult);
	if($row[0]!=$nation) {
		array_push($nationlist,$row[0]);
	}
}
$nationquery="SELECT `name`,`ruler`,`allies` FROM `mcstuff`.`nations` WHERE `ruler`='".$_SESSION['username']."';";
$nationqueryresult=mysqli_query($conn,$nationquery);
$allies=array();
if($nationqueryresult->num_rows>0) {
	$nationrow=mysqli_fetch_row($nationqueryresult);
	$allies=json_decode($nationrow[2]);
}
$_SESSION['nationlist']=$nationlist;
echo '<script>nationlist='.json_encode($nationlist).'; initAllies='.$nationrow[2].'</script>';

echo '<form method="POST">
	<input type="color" name="forecolor" value="#'.$forecolor.'">
	<span>Foreground Color</span>
	<input type="color" name="backcolor" value="#'.$backcolor.'">
	<span>Background Color</span>
	<input type="text" name="nation" value="'.$nation.'" maxlength="32">
	<span>Nation Name</span>
	<input type="text" name="character" value="'.$character.'" maxlength="32">
	<span>Character Name</span>
	<input type="text" name="prefix" value="'.$prefix.'" maxlength="32">
	<span>Prefix</span>
	<input type="text" name="suffix" value="'.$suffix.'" maxlength="32">
	<span>Suffix</span>
	<br><br>
	<span>Allies</span>
	<div id="dropdownholder">';
$allyCount=count($allies);
if($allyCount>0) {
	for($i=0; $i<$allyCount; $i++) {
		if($allies[$i]!='') {
			$dropdownSelected=$allies[$i];
			$dropdownid=$i;
			require 'addCountryDropdown.php';
		}
	}
}
require 'addCountryDropdown.php';
echo'</div>
	<input type="submit" name="save" value="Save">
</form>';

if($nationqueryresult->num_rows>0) {
	echo '<a href="./nation_edit.php?nation='.$nationrow[0].'">Edit your nation.</a>';
}
else {
	echo '<a href="./create_nation.php">Create your nation.</a>';
}
?>
<script>
	allyIds=[];
	allyList={};
	currentAllyListId=0;
	for(var i=0; i<initAllies.length; i++) {
		if(initAllies[i]!="") {
			allyIds.push(i);
			allyList[i]=initAllies[i];
			currentAllyListId=i+1;
			$("option[value=\""+initAllies[i]+"\"]:not(:selected)").remove();
		}
	}
	$('.countrydropdown').on('change',checkAddDropdown);
	function checkAddDropdown(e) {
		var tarId=parseInt(e.target.id.split('-')[1]);
		if(e.target.value!='') {
			if(currentAllyListId==tarId) {
				$.getJSON('addCountryDropdown.php',{dropdownid:++currentAllyListId},function(data) {
					var newDropdown=$(data.html);
					newDropdown.on('change',checkAddDropdown);
					$('#dropdown-'+(data.id-1)).after(newDropdown);
					for(var i=0; i<allyIds.length; i++) {
						$("option[value=\""+allyList[allyIds[i]]+"\"]:not(:selected)").remove();
					}
				});
			}
			if(allyList[tarId]==undefined) {
				allyIds.push(tarId);
			}
			else {
				$("option[value=\""+allyList[tarId]+"\"]:not(:selected)").remove();
				$(".countrydropdown option:first-child").after('<option value="'+allyList[tarId]+'">'+allyList[tarId]+'</option>');
			}
			allyList[tarId]=e.target.value;
			$("option[value=\""+e.target.value+"\"]:not(:selected)").remove();
		}
		else if(allyList[tarId]!=undefined) {
			$("option[value=\""+allyList[tarId]+"\"]:not(:selected)").remove();
			$(".countrydropdown option:first-child").after('<option value="'+allyList[tarId]+'">'+allyList[tarId]+'</option>');
			allyIds.splice(allyIds.indexOf(tarId),1);
			delete allyList[tarId];
			$(e.target).remove();
		}
	}
</script>