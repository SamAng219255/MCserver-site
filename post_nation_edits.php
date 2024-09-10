<?php
	$sql=$pdo->prepare('UPDATE `mcstuff`.`nations` SET `name`=:name,`population`=:population,`parent`=:parent,`desc`=:desc,`showruler`=:showruler,`showflag`=:showflag,`showpopul`=:showpopul,`showparent`=:showparent,`troopresource`=:troopresource WHERE `name`=:getname;');
	$sql->bindValue('name', $_POST['name'], PDO::PARAM_STR);
	$sql->bindValue('population', $_POST['population'], PDO::PARAM_INT);
	$sql->bindValue('parent', $_POST['parent'], PDO::PARAM_STR);
	$sql->bindValue('desc', $_POST['desc'], PDO::PARAM_STR);
	$sql->bindValue('showruler', $_POST['showruler'], PDO::PARAM_STR);
	$sql->bindValue('showflag', $_POST['showflag'], PDO::PARAM_STR);
	$sql->bindValue('showpopul', $_POST['showpopul'], PDO::PARAM_STR);
	$sql->bindValue('showparent', $_POST['showparent'], PDO::PARAM_STR);
	$sql->bindValue('troopresource', $_POST['troopresource'], PDO::PARAM_STR);
	$sql->bindValue('getname', $_GET['nation'], PDO::PARAM_STR);
	$sql->execute();

	
	$nationsql=$pdo->prepare('UPDATE `mcstuff`.`users` SET `nation`=:name WHERE `nation`=:nation OR `username`=:username;');
	$nationsql->bindValue('name', $_POST['name'], PDO::PARAM_STR);
	$nationsql->bindValue('nation', $_GET['nation'], PDO::PARAM_STR);
	$nationsql->bindValue('username', $_SESSION['username'], PDO::PARAM_STR);
	$nationsql->execute();

	$resetsql=$pdo->prepare("DELETE FROM `mcstuff`.`resources` WHERE `nation`=?;");
	$resetsql->bindValue(1, $_GET['nation'], PDO::PARAM_STR);
	$resetsql->execute();

	for($i=0; $i<$_POST['resourcecount']; $i++) {
		$hidden=0;
		if(isset($_POST['hidden-'.$i])) $hidden=1;
		$resourcesql=$pdo->prepare("INSERT INTO `mcstuff`.`resources` (`id`,`nation`,`unit`,`type`,`ntnlwlth`,`ctznwlth`,`ntnlincome`,`ctznincome`,`tax`,`showwlth`,`showncm`,`showntnl`,`showctzn`,`showtax`,`desc`,`hide`) VALUES ('0',:nation,:unit,:name,:ntnlwlth,:ctznwlth,:ntnlincome,:ctznincome,:tax,:showwlth,:showncm,:showntnl,:showctzn,:showtax,:desc,:hidden);");
		$resourcesql->bindValue('nation', $_POST['name'], PDO::PARAM_STR);
		$resourcesql->bindValue('unit', $_POST['unit-'.$i], PDO::PARAM_STR);
		$resourcesql->bindValue('name', $_POST['name-'.$i], PDO::PARAM_STR);
		$resourcesql->bindValue('ntnlwlth', $_POST['ntnlwlth-'.$i], PDO::PARAM_INT);
		$resourcesql->bindValue('ctznwlth', $_POST['ctznwlth-'.$i], PDO::PARAM_INT);
		$resourcesql->bindValue('ntnlincome', $_POST['ntnlincome-'.$i], PDO::PARAM_INT);
		$resourcesql->bindValue('ctznincome', $_POST['ctznincome-'.$i], PDO::PARAM_INT);
		$resourcesql->bindValue('tax', $_POST['tax-'.$i], PDO::PARAM_INT);
		$resourcesql->bindValue('showwlth', $_POST['showwlth-'.$i], PDO::PARAM_STR);
		$resourcesql->bindValue('showncm', $_POST['showncm-'.$i], PDO::PARAM_STR);
		$resourcesql->bindValue('showntnl', $_POST['showntnl-'.$i], PDO::PARAM_STR);
		$resourcesql->bindValue('showctzn', $_POST['showctzn-'.$i], PDO::PARAM_STR);
		$resourcesql->bindValue('showtax', $_POST['showtax-'.$i], PDO::PARAM_STR);
		$resourcesql->bindValue('desc', $_POST['desc-'.$i], PDO::PARAM_STR);
		$resourcesql->bindValue('hidden', $hidden, PDO::PARAM_INT);
		$resourcesql->execute();
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
					$flagsql=$pdo->prepare("UPDATE `mcstuff`.`nations` SET `hasflag`='1' WHERE `name`=?;");
					$flagsql->bindValue(1, $_GET['nation'], PDO::PARAM_STR);
					$flagsql->execute();
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