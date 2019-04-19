<?php
session_start();
require 'db.php';

if($_SESSION['permissions']>0) {
	if($_FILES['sprite']["error"]==0) {
		$target_dir = "img/uploads/";
		$imageFileType = strtolower(pathinfo(basename($_FILES["sprite"]["name"]),PATHINFO_EXTENSION));
		$filename=substr(md5(mt_rand()),0,32);
		$target_file = $target_dir.$filename;
		// Check if image file is a actual image or fake image
		$check = getimagesize($_FILES["sprite"]["tmp_name"]);
		if($check !== false) {
			// Check if file already exists
			if($_FILES["sprite"]["size"] > 500000) {
				echo '{"status":2,"text":"Your file is too large."}';
			}
			// Allow certain file formats
			elseif($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
				echo '{"status":3,"text":"Only JPG, JPEG, PNG & GIF files are allowed."}';
			}
			else {
				// if everything is ok, try to upload file
				if (move_uploaded_file($_FILES["sprite"]["tmp_name"], $target_file)) {
					$sql="INSERT INTO `mcstuff`.`sprites` (`id`,`type`,`name`,`width`,`height`) VALUES ('0','army','".$filename."','".$check[0]."','".$check[1]."');";
					if(mysqli_query($conn,$sql)) {
						echo '{"status":0,"text":"The file '.basename($_FILES["sprite"]["name"]).' has been uploaded.","id":'.mysqli_insert_id($conn).',"name":"'.$filename.'"}';
					}
					else {
						echo '{"status":1,"text":"An unknown error occurred while saving sprite data.","sql":"'.$sql.'"}';
					}
				}
				else {
					echo '{"status":4,"text":"There was an error uploading your file.","tmpname":"'.$_FILES["sprite"]["tmp_name"].'"}';
				}
			}
		}
		else {
			echo '{"status":5,"text":"File is not an image."}';
		}
	}
}

?>