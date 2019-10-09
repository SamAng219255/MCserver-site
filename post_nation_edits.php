<?php
	$sql="UPDATE `mcstuff`.`nations` SET `name`='".mysqli_real_escape_string($conn,$_POST['name'])."',`population`='".$_POST['population']."',`parent`='".mysqli_real_escape_string($conn,$_POST['parent'])."',`desc`='".mysqli_real_escape_string($conn,$_POST['desc'])."',`showruler`='".mysqli_real_escape_string($conn,$_POST['showruler'])."',`showflag`='".mysqli_real_escape_string($conn,$_POST['showflag'])."',`showpopul`='".mysqli_real_escape_string($conn,$_POST['showpopul'])."',`showparent`='".mysqli_real_escape_string($conn,$_POST['showparent'])."',`troopresource`='".mysqli_real_escape_string($conn,$_POST['troopresource'])."' WHERE `name`='".mysqli_real_escape_string($conn,$_GET['nation'])."';";
	mysqli_query($conn,$sql);

	
	$nationsql="UPDATE `mcstuff`.`nations` SET `nation`='".mysqli_real_escape_string($conn,$_POST['name'])."' WHERE `nation`='".mysqli_real_escape_string($conn,$_GET['nation'])."' OR `username`='".$_SESSION['username']."';";
	mysqli_query($conn,$nationsql);

	$resetsql="DELETE FROM `mcstuff`.`resources` WHERE `nation`='".mysqli_real_escape_string($conn,$_GET['nation'])."';";
	mysqli_query($conn,$resetsql);

	for($i=0; $i<$_POST['resourcecount']; $i++) {
		$hidden='0';
		if(isset($_POST['hidden-'.$i])) $hidden='1';
		$resourcesql="INSERT INTO `mcstuff`.`resources` (`id`,`nation`,`unit`,`type`,`ntnlwlth`,`ctznwlth`,`ntnlincome`,`ctznincome`,`tax`,`showwlth`,`showncm`,`showntnl`,`showctzn`,`showtax`,`desc`,`hide`) VALUES ('0','".mysqli_real_escape_string($conn,$_POST['name'])."','".mysqli_real_escape_string($conn,$_POST['unit-'.$i])."','".mysqli_real_escape_string($conn,$_POST['name-'.$i])."','".$_POST['ntnlwlth-'.$i]."','".$_POST['ctznwlth-'.$i]."','".$_POST['ntnlincome-'.$i]."','".$_POST['ctznincome-'.$i]."','".$_POST['tax-'.$i]."','".mysqli_real_escape_string($conn,$_POST['showwlth-'.$i])."','".mysqli_real_escape_string($conn,$_POST['showncm-'.$i])."','".mysqli_real_escape_string($conn,$_POST['showntnl-'.$i])."','".mysqli_real_escape_string($conn,$_POST['showctzn-'.$i])."','".mysqli_real_escape_string($conn,$_POST['showtax-'.$i])."','".mysqli_real_escape_string($conn,$_POST['desc-'.$i])."','".$hidden."');";
		mysqli_query($conn,$resourcesql);
	}

	if($_FILES['flag']["error"]==0) {
		$target_dir = "img/flags/";
		$imageFileType = strtolower(pathinfo(basename($_FILES["flag"]["name"]),PATHINFO_EXTENSION));
		$target_file = $target_dir . $_POST['name'];
		$uploadOk = 1;
		// Check if image file is a actual image or fake image
		$check = getimagesize($_FILES["flag"]["tmp_name"]);
		if($check !== false) {
			// Check if file already exists
			if($_FILES["flag"]["size"] > 500000) {
				addBanner("Sorry, your file is too large.");
			}
			// Allow certain file formats
			elseif($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
				addBanner("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
			}
			else {
				// if everything is ok, try to upload file
				if(file_exists($target_file)) unlink($target_file);
				if (move_uploaded_file($_FILES["flag"]["tmp_name"], $target_file)) {
					addBanner("The file ". basename($_FILES["flag"]["name"]) . " has been uploaded.");
					$flagsql="UPDATE `mcstuff`.`nations` SET `hasflag`='1' WHERE `name`='".mysqli_real_escape_string($conn,$_GET['nation'])."';";
					mysqli_query($conn,$flagsql);
				} else {
					addBanner("Sorry, there was an error uploading your file.");
				}
			}
		}
		else {
			addBanner("File is not an image.");
		}
	}

	echo '<meta http-equiv="refresh" content="0; URL=nations.php?nation='.$_POST['name'].'">';
?>