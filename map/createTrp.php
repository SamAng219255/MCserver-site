<?php
session_start();
require 'db.php';

if($_SESSION['permissions']>0) {
	$name=mysqli_real_escape_string($conn,$_GET['name']);
	$checkquery="SELECT `id`,`name` FROM `mcstuff`.`troops` WHERE `name`='".mysqli_real_escape_string($conn,$name)."';";
	if($checkqueryresult=mysqli_query($conn,$checkquery)) {
		if($checkqueryresult->num_rows<1) {
			$owner=$_SESSION['username'];
			$nation=mysqli_real_escape_string($conn,$_GET['owner']);
			$size=intval($_GET['size']);
			$invest=$cost=intval($_GET['cost']);
			$power=floor(($cost+$size)/1000.0);
			$move=max(min(pow(2,log(1000.0*$power/$size)),100000.0/$size),1)*3;
			$mobiletxt='0';
			if($_GET['mobility']=='true') {
				$move*=2;
				$cost=floor(1.5*$cost);
				$mobiletxt='1';
			}
			$rangetxt='0';
			if($_GET['ranged']=='true') {
				$power/=2;
				$rangetxt='1';
			}
			$usescustom='0';
			if($_GET['customsprite']=='true') {
				$usescustom='1';
			}
			$move=floor($move);
			$x=intval($_GET['x']);
			$z=intval($_GET['z']);
			$sprite=intval($_GET['sprite']);
			$sql="INSERT INTO `mcstuff`.`troops` (`id`,`owner`,`nation`,`name`,`size`,`power`,`x`,`y`,`move`,`moveleft`,`sprite`,`mobile`,`ranged`,`cost`,`origsize`,`customsprite`) VALUES ('0','".$owner."','".$nation."','".$name."','".$size."','".$power."','".$x."','".$z."','".$move."','".$move."','".$sprite."','".$mobiletxt."','".$rangetxt."','".$invest."','".$size."','".$usescustom."');";
			$nationquery="SELECT `name`,`troopresource`,`ruler` FROM `mcstuff`.`nations` WHERE `ruler`='".$owner."';";
			if($nationqueryresult=mysqli_query($conn,$nationquery)) {
				if($nationqueryresult->num_rows>0) {
					$row=mysqli_fetch_row($nationqueryresult);
					$nationname=$row[0];
					$troopresource=$row[1];
					if($nationname==$nation) {
						$resourcequery="SELECT `ntnlwlth`,`nation`,`type` FROM `mcstuff`.`resources` WHERE `nation`='".$nationname."' AND`type`='".$troopresource."';";
						if($resourcequeryresult=mysqli_query($conn,$resourcequery)) {
							$row=mysqli_fetch_row($resourcequeryresult);
							$cash=intval($row[0]);
							if($cash>=$cost) {
								$costsql="UPDATE `mcstuff`.`resources` SET `ntnlwlth`='".($cash-$cost)."' WHERE `nation`='".$nationname."' AND`type`='".$troopresource."';";
								if(mysqli_query($conn,$costsql)) {
									if(mysqli_query($conn,$sql)) {
										echo '{"action":"create","status":0,"text":"Army created.","sql":"'.$sql.'"}';
									}
									else {
										echo '{"action":"create","status":1,"text":"An unknown error occurred while creating army.","sql":"'.$sql.'"}';
									}
								}
								else {
									echo '{"action":"create","status":1,"text":"An unknown error occurred while subtracting spent troop limiting resource.","sql":"'.$costsql.'"}';
								}
							}
							else {
								echo '{"action":"create","status":3,"text":"You do not have enough of your limiting resource to create that unit."}';
							}
						}
						else {
							echo '{"action":"create","status":1,"text":"An unknown error occurred while checking troop limiting resource.","sql":"'.$resourcequery.'"}';
						}
					}
					else {
						if(mysqli_query($conn,$sql)) {
							echo '{"action":"create","status":0,"text":"NPC army created.","sql":"'.$sql.'"}';
						}
						else {
							echo '{"action":"create","status":1,"text":"An unknown error occurred while creating NPC army.","sql":"'.$sql.'"}';
						}
					}
				}
				else {
					if(mysqli_query($conn,$sql)) {
						echo '{"action":"create","status":0,"text":"Army created by user with no nation.","sql":"'.$sql.'"}';
					}
					else {
						echo '{"action":"create","status":1,"text":"An unknown error occurred while creating army by user with no nation.","sql":"'.$sql.'"}';
					}
				}
			}
			else {
				echo '{"action":"create","status":1,"text":"An unknown error occurred while finding user nation.","sql":"'.$nationquery.'"}';
			}
		}
		else {
			echo '{"action":"create","status":2,"text":"There is already a unit with that name."}';
		}
	}
	else {
		echo '{"action":"create","status":1,"text":"An unknown error occurred while checking for name redundancy.","sql":"'.$checkquery.'"}';
	}
}

?>