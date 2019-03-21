<?php
	$sql="UPDATE `mcstuff`.`nations` SET `name`='".mysqli_real_escape_string($conn,$_POST['name'])."',`population`='".$_POST['population']."',`parent`='".mysqli_real_escape_string($conn,$_POST['parent'])."',`desc`='".mysqli_real_escape_string($conn,$_POST['desc'])."',`showruler`='".mysqli_real_escape_string($conn,$_POST['showruler'])."',`showflag`='".mysqli_real_escape_string($conn,$_POST['showflag'])."',`showpopul`='".mysqli_real_escape_string($conn,$_POST['showpopul'])."',`showparent`='".mysqli_real_escape_string($conn,$_POST['showparent'])."' WHERE `name`='".mysqli_real_escape_string($conn,$_GET['nation'])."';";
	mysqli_query($conn,$sql);

	
	$nationsql="UPDATE `mcstuff`.`nations` SET `nation`='".mysqli_real_escape_string($conn,$_POST['name'])."' WHERE `nation`='".mysqli_real_escape_string($conn,$_GET['nation'])."' OR `name`='".$_SESSION['username']."';";
	mysqli_query($conn,$nationsql);

	$resetsql="DELETE FROM `mcstuff`.`resources` WHERE `nation`='".mysqli_real_escape_string($conn,$_GET['nation'])."';";
	mysqli_query($conn,$resetsql);

	for($i=0; $i<$_POST['resourcecount']; $i++) {
		$resourcesql="INSERT INTO `mcstuff`.`resources` (`id`,`nation`,`unit`,`type`,`ntnlwlth`,`ctznwlth`,`ntnlincome`,`ctznincome`,`tax`,`showwlth`,`showncm`,`showntnl`,`showctzn`,`showtax`,`desc`) VALUES ('0','".mysqli_real_escape_string($conn,$_POST['name'])."','".mysqli_real_escape_string($conn,$_POST['unit-'.$i])."','".mysqli_real_escape_string($conn,$_POST['name-'.$i])."','".$_POST['ntnlwlth-'.$i]."','".$_POST['ctznwlth-'.$i]."','".$_POST['ntnlincome-'.$i]."','".$_POST['ctznincome-'.$i]."','".$_POST['tax-'.$i]."','".mysqli_real_escape_string($conn,$_POST['showwlth-'.$i])."','".mysqli_real_escape_string($conn,$_POST['showncm-'.$i])."','".mysqli_real_escape_string($conn,$_POST['showntnl-'.$i])."','".mysqli_real_escape_string($conn,$_POST['showctzn-'.$i])."','".mysqli_real_escape_string($conn,$_POST['showtax-'.$i])."','".mysqli_real_escape_string($conn,$_POST['desc-'.$i])."');";
		mysqli_query($conn,$resourcesql);
	}
?>