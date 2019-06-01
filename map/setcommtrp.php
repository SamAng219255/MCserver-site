<?php
session_start();
require 'db.php';

echo '{"input":'.json_encode($_GET).',"response":';

if($_SESSION['permissions']>0) {
	$id=intval($_GET['id']);
	$ownerquery="SELECT `owner`,`nation` FROM `mcstuff`.`commanders` WHERE `id`='".$id."';";
	if($ownerqueryresult=mysqli_query($conn,$ownerquery)) {
		$ownerrow=mysqli_fetch_row($ownerqueryresult);
		if($ownerrow[0]==$_SESSION['username']) {
			$oldbonusquery="SELECT `special` FROM `mcstuff`.`commanders` WHERE `army`=(SELECT `army` FROM `mcstuff`.`commanders` WHERE `id`='".mysqli_real_escape_string($conn,$id)."') AND `id`!='".mysqli_real_escape_string($conn,$id)."';";
			if($oldbonusqueryresult=mysqli_query($conn,$oldbonusquery)) {
				$oldbonuses='';
				for($i=0; $i<$oldbonusqueryresult->num_rows; $i++) {
					if($i>0) {
						$oldbonuses.=',';
					}
					$oldbonusrow=mysqli_fetch_row($oldbonusqueryresult);
					$oldbonuses.=$oldbonusrow[0];
				}
				$oldbonussql="UPDATE `mcstuff`.`troops` SET `bonuses`='".$oldbonuses."' WHERE `id`=(SELECT `army` FROM `mcstuff`.`commanders` WHERE `id`='".mysqli_real_escape_string($conn,$id)."');";
				if(mysqli_query($conn,$oldbonussql)) {
					if($_GET['armyname']!='') {
						$tarquery="SELECT `id`,`owner`,`name`,`nation` FROM `mcstuff`.`troops` WHERE `name`='".mysqli_real_escape_string($conn,$_GET['armyname'])."';";
						if($tarqueryresult=mysqli_query($conn,$tarquery)) {
							if($tarqueryresult->num_rows>0) {
								$tarrow=mysqli_fetch_row($tarqueryresult);
								$relationquery="SELECT `relation`+0 FROM `mcstuff`.`relations` WHERE (`nation1`='".$ownerrow[1]."' AND `nation2`='".$tarrow[3]."') OR (`nation2`='".$ownerrow[1]."' AND `nation1`='".$tarrow[3]."');";
								if($ownerrow[1]==$tarrow[3] || $relationqueryresult=mysqli_query($conn,$relationquery)) {
									if($ownerrow[1]==$tarrow[3] || intval(mysqli_fetch_row($relationqueryresult)[0])<4) {
										$sql="UPDATE `mcstuff`.`commanders` SET `army`='".$tarrow[0]."' WHERE `id`='".mysqli_real_escape_string($conn,$id)."';";
										if(mysqli_query($conn,$sql)) {
											$bonusquery="SELECT `special`,`army` FROM `mcstuff`.`commanders` WHERE `army`='".$tarrow[0]."';";
											if($bonusqueryresult=mysqli_query($conn,$bonusquery)) {
												$bonuses='';
												for($i=0; $i<$bonusqueryresult->num_rows; $i++) {
													if($i>0) {
														$bonuses.=',';
													}
													$bonusrow=mysqli_fetch_row($bonusqueryresult);
													$bonuses.=$bonusrow[0];
												}
												$bonussql="UPDATE `mcstuff`.`troops` SET `bonuses`='".$bonuses."' WHERE `id`='".$tarrow[0]."';";
												if(mysqli_query($conn,$bonussql)) {
													echo '{"status":0,"Successfully set commander army and updating army commander bonuses.","sql":"'.$bonussql.'","sql1":"'.$bonusquery.'"}';
												}
												else {
													echo '{"status":1,"text":"An unknown error occurred while setting army commander bonuses.","sql":"'.$bonussql.'"}';
												}
											}
											else {
												echo '{"status":1,"text":"An unknown error occurred while retrieving army commander bonuses.","sql":"'.$bonusquery.'"}';
											}
										}
										else {
											echo '{"status":1,"text":"An unknown error occurred while setting commander army.","sql":"'.$sql.'"}';
										}
									}
									else {
										echo '{"status":3,"text":"You do not own that army."}';
									}
								}
								else {
									echo '{"status":1,"text":"A SQL error occurred while checking nations relation.","sql":"'.$relationquery.'","error":"'.mysqli_error($conn).'"}';
								}
							}
							else {
								echo '{"status":2,"text":"Could not find an army by that name."}';
							}
						}
						else {
							echo '{"status":1,"text":"An unknown error occurred while getting army.","sql":"'.$tarquery.'"}';
						}
					}
					else {
						$sql="UPDATE `mcstuff`.`commanders` SET `army`='0' WHERE `id`='".mysqli_real_escape_string($conn,$id)."';";
						if(mysqli_query($conn,$sql)) {
							echo '{"status":0,"text":"Commander army reset.","sql":"'.$sql.'","sql1":"'.$oldbonusquery.'","sql2":"'.$oldbonussql.'"}';
						}
						else {
							echo '{"status":1,"text":"An unknown error occurred while resetting commander army.","sql":"'.$sql.'"}';
						}
					}
				}
				else {
					echo '{"status":1,"text":"An unknown error occurred while setting old army commander bonuses.","sql":"'.$bonussql.'"}';
				}
			}
			else {
				echo '{"status":1,"text":"An unknown error occurred while retrieving old army commander bonuses.","sql":"'.$oldbonusquery.'"}';
			}
		}
		else {
			echo '{"status":4,"text":"You do not own that commander."}';
		}
	}
	else {
		echo '{"status":1,"text":"An unknown error occurred while checking commander owner."}';
	}
}
else {
	echo '{"status":99,"text":"Invalid permissions."}';
}

echo '}';

?>
