<?php
session_start();
require 'db.php';

$dmgMod=15;
$trpId=intval($_GET['id']);
$trpquery="SELECT `id`,`owner`,`nation`,`name`,`size`,`power`,`health`,`x`,`y`,`move`,`moveleft`,`sprite`,`mobile`,`ranged`,`state`,`cost`,`origsize` FROM `mcstuff`.`troops` WHERE `id`=".$trpId.";";
if(isset($_GET['target'])) {
	$tarId=intval($_GET['target']);
	$tarquery="SELECT `id`,`owner`,`nation`,`name`,`size`,`power`,`health`,`x`,`y`,`move`,`moveleft`,`sprite`,`mobile`,`ranged`,`state`,`cost`,`origsize` FROM `mcstuff`.`troops` WHERE `id`=".$tarId.";";
}
if($trpqueryresult=mysqli_query($conn,$trpquery)) {
	if(!isset($_GET['target']) || $tarqueryresult=mysqli_query($conn,$tarquery)) {
		$row=mysqli_fetch_row($trpqueryresult);
		$trp=array(
			'id' => intval($row[0]),
			'owner' => $row[1],
			'nation' => $row[2],
			'name' => $row[3],
			'size' => intval($row[4]),
			'power' => intval($row[5]),
			'health' => floatval($row[6]),
			'x' => intval($row[7]),
			'y' => intval($row[8]),
			'move' => intval($row[9]),
			'moveleft' => intval($row[10]),
			'sprite' => intval($row[11]),
			'mobile' => intval($row[12]),
			'ranged' => intval($row[13]),
			'state' => intval($row[14]),
			'cost' => intval($row[15]),
			'origsize' => intval($row[16])
		);
		if(isset($_GET['target'])) {
			$row=mysqli_fetch_row($tarqueryresult);
			$tar=array(
				'id' => intval($row[0]),
				'owner' => $row[1],
				'nation' => $row[2],
				'name' => $row[3],
				'size' => intval($row[4]),
				'power' => intval($row[5]),
				'health' => floatval($row[6]),
				'x' => intval($row[7]),
				'y' => intval($row[8]),
				'move' => intval($row[9]),
				'moveleft' => intval($row[10]),
				'sprite' => intval($row[11]),
				'mobile' => intval($row[12]),
				'ranged' => intval($row[13]),
				'state' => intval($row[14]),
				'cost' => intval($row[15]),
				'origsize' => intval($row[16])
			);
		}
		if($trp['owner']==$_SESSION['username']) {
			if(!isset($_GET['target']) || pow($trp['x']-$tar['x'],2)+pow($trp['y']-$tar['y'],2)<=4096) {
				if($_GET['action']=='fortify') {
					if($trp['moveleft']>=2) {
						$effectsql="UPDATE `mcstuff`.`troops` SET `moveleft`='".($trp['moveleft']-2)."',`state`='1' WHERE `id`=".$trp['id'].";";
						if(mysqli_query($conn,$effectsql)) {
							echo '{"action":"'.$_GET['action'].'","status":0,"text":"Army successfully changed to fortified state."}';
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while changing army to fortified state.","sql":"'.$effectsql.'"}';
						}
					}
					else {
						echo '{"action":"'.$_GET['action'].'","status":4,"text":"Your army does not have enough moves left."}';
					}
				}
				elseif($_GET['action']=='heal') {
					if($trp['moveleft']>=2) {
						$effectsql="UPDATE `mcstuff`.`troops` SET `moveleft`='".($trp['moveleft']-2)."',`health`='".sprintf("%.3f",min(0.24/(1.0+$trp['size']/$trp['cost'])+$trp['health']),100)."' WHERE `id`=".$trp['id'].";";
						if(mysqli_query($conn,$effectsql)) {
							echo '{"action":"'.$_GET['action'].'","status":0,"text":"Army successfully set army health."}';
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while setting army health.","sql":"'.$effectsql.'"}';
						}
					}
					else {
						echo '{"action":"'.$_GET['action'].'","status":4,"text":"Your army does not have enough moves left."}';
					}
				}
				elseif($_GET['action']=='move') {
					if($trp['moveleft']>=1) {
						if(pow($trp['x']-intval($_GET['x']),2)+pow($trp['y']-intval($_GET['z']),2)<=4096) {
							$effectsql="UPDATE `mcstuff`.`troops` SET `moveleft`='".($trp['moveleft']-1)."',`state`='0',`x`='".intval($_GET['x'])."',`y`='".intval($_GET['z'])."' WHERE `id`='".$trp['id']."';";
							if(mysqli_query($conn,$effectsql)) {
								echo '{"action":"'.$_GET['action'].'","status":0,"text":"Army successfully set army position."}';
							}
							else {
								echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while setting army position.","sql":"'.$effectsql.'"}';
							}
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":5,"text":"Your army is too far away."}';
						}
					}
					else {
						echo '{"action":"'.$_GET['action'].'","status":4,"text":"Your army does not have enough moves left."}';
					}
				}
				elseif($_GET['action']=='attack') {
					if($trp['moveleft']>=2) {
						$defmod=1;
						if($tar['state']==1) {
							$defmod*=1.5;
						}
						$trpDmg=0;
						$tarDmg=0;
						$tidalstr=ceil(($trp['power']+$tar['power'])/8);
						$rounds=mt_rand(5,15);
						for($i=0; $i<$rounds; $i++) {
							$tides=mt_rand(-$tidalstr*1000,$tidalstr*1000)/1000.0;
							$tarDmg+=$dmgMod*sqrt(($trp['power']*1.25+$tides)/(($tar['power']-$tides)*$defmod))/$rounds;
							$trpDmg+=$dmgMod*sqrt($defmod*($tar['power']-$tides)/($trp['power']+$tides))/$rounds;
						}
						$trp['health']-=$trpDmg;
						$tar['health']-=$tarDmg;
						$sizeunit=min(ceil($trp['origsize']/20),ceil($tar['origsize']/20));
						$returndata=array("action"=>$_GET['action'],"status1"=>0,"status2"=>0,"text1"=>"","text2"=>"","sql1"=>"","sql2"=>"","dmg1"=>$trpDmg,"dmg2"=>$tarDmg,"losses1"=>0,"losses2"=>0);
						if($trp['health']>0) {
							while(mt_rand(0,10000)/100.0>$trp['health']) {
								$oldsize=$trp['size'];
								$trp['size']-=$sizeunit;
								$trp['power']*=$trp['size']/$oldsize;
								$returndata['losses1']+=$sizeunit;
								if($trp['size']<=0) {
									break;
								}
								$trp['health']*=$oldsize/$trp['size'];
							}
							if($trp['size']>0) {
								$effectonesql="UPDATE `mcstuff`.`troops` SET `moveleft`='".($trp['moveleft']-2)."',`state`='0',`health`='".$trp['health']."',`size`='".$trp['size']."',`power`='".floor($trp['power'])."' WHERE `id`=".$trp['id'].";";
								$returndata['sql1']=$effectonesql;
								if(mysqli_query($conn,$effectonesql)) {
									$returndata['status1']=0;
									$returndata['text1']='Successfully set army damage.';
								}
								else {
									$returndata['status1']=1;
									$returndata['text1']='An unknown error occured while setting army damage.';
								}
							}
						}
						if($trp['health']<=0 || $trp['size']<=0) {
							$effectonesql="DELETE FROM `mcstuff`.`troops` WHERE `id`='".$trp['id']."';";
							$returndata['sql1']=$effectonesql;
							if(mysqli_query($conn,$effectonesql)) {
								$returndata['status1']=100;
								$returndata['text1']='Army died.';
							}
							else {
								$returndata['status1']=1;
								$returndata['text1']='An unknown error occured while removing dead army.';
							}
						}
						if($tar['health']>0) {
							while(mt_rand(0,10000)/100.0>$tar['health']) {
								$oldsize=$tar['size'];
								$tar['size']-=$sizeunit;
								$tar['power']*=$tar['size']/$oldsize;
								$returndata['losses2']+=$sizeunit;
								if($tar['size']<=0) {
									break;
								}
								$tar['health']*=$oldsize/$tar['size'];
							}
							if($tar['size']>0) {
								$effecttwosql="UPDATE `mcstuff`.`troops` SET `health`='".$tar['health']."',`size`='".$tar['size']."',`power`='".floor($tar['power']/1.5)."' WHERE `id`=".$tar['id'].";";
								$returndata['sql2']=$effecttwosql;
								if(mysqli_query($conn,$effecttwosql)) {
									$returndata['status2']=0;
									$returndata['text2']='Successfully set enemy damage.';
								}
								else {
									$returndata['status2']=1;
									$returndata['text2']='An unknown error occured while setting enemy damage.';
								}
							}
						}
						if($tar['health']<=0 || $tar['size']<=0) {
							$effecttwosql="DELETE FROM `mcstuff`.`troops` WHERE `id`='".$tar['id']."';";
							$returndata['sql2']=$effecttwosql;
							if(mysqli_query($conn,$effecttwosql)) {
								$returndata['status2']=100;
								$returndata['text2']='Enemy army died.';
							}
							else {
								$returndata['status2']=1;
								$returndata['text2']='An unknown error occured while removing dead enemy.';
							}
						}
						echo json_encode($returndata);
					}
					else {
						echo '{"action":"'.$_GET['action'].'","status":4,"text":"Your army does not have enough moves left."}';
					}
				}
				elseif($_GET['action']=='hitrun') {
					if($trp['moveleft']>=4) {
						$defmod=1;
						if($tar['state']==1) {
							$defmod*=1.5;
						}
						$trpDmg=0;
						$tarDmg=0;
						$tidalstr=ceil(($trp['power']+$tar['power'])/8);
						$rounds=1;
						for($i=0; $i<$rounds; $i++) {
							$tides=mt_rand(-$tidalstr*1000,$tidalstr*1000)/1000.0;
							$tarDmg+=$dmgMod*sqrt(($trp['power']+$tides)/(($tar['power']-$tides)*$defmod))/$rounds;
						}
						$tar['health']-=$tarDmg;
						$sizeunit=min(ceil($trp['origsize']/20),ceil($tar['origsize']/20));
						$returndata=array("action"=>$_GET['action'],"status1"=>0,"status2"=>0,"text1"=>"","text2"=>"","sql1"=>"","sql2"=>"","dmg1"=>$trpDmg,"dmg2"=>$tarDmg,"losses1"=>0,"losses2"=>0);
						$effectonesql="UPDATE `mcstuff`.`troops` SET `moveleft`='".($trp['moveleft']-4)."',`state`='0' WHERE `id`=".$trp['id'].";";
						$returndata['sql1']=$effectonesql;
						if(mysqli_query($conn,$effectonesql)) {
							$returndata['status1']=0;
							$returndata['text1']='Successfully set army movement.';
						}
						else {
							$returndata['status1']=1;
							$returndata['text1']='An unknown error occured while setting army movement.';
						}
						if($tar['health']>0) {
							while(mt_rand(0,10000)/100.0>$tar['health']) {
								$oldsize=$tar['size'];
								$tar['size']-=$sizeunit;
								$tar['power']*=$tar['size']/$oldsize;
								$returndata['losses2']+=$sizeunit;
								if($tar['size']<=0) {
									break;
								}
								$tar['health']*=$oldsize/$tar['size'];
							}
							if($tar['size']>0) {
								$effecttwosql="UPDATE `mcstuff`.`troops` SET `health`='".$tar['health']."',`size`='".$tar['size']."',`power`='".floor($tar['power']/1.5)."' WHERE `id`=".$tar['id'].";";
								$returndata['sql2']=$effecttwosql;
								if(mysqli_query($conn,$effecttwosql)) {
									$returndata['status2']=0;
									$returndata['text2']='Successfully set enemy damage.';
								}
								else {
									$returndata['status2']=1;
									$returndata['text2']='An unknown error occured while setting enemy damage.';
								}
							}
						}
						if($tar['health']<=0 || $tar['size']<=0) {
							$effecttwosql="DELETE FROM `mcstuff`.`troops` WHERE `id`='".$tar['id']."';";
							$returndata['sql2']=$effecttwosql;
							if(mysqli_query($conn,$effecttwosql)) {
								$returndata['status2']=100;
								$returndata['text2']='Enemy army died.';
							}
							else {
								$returndata['status2']=1;
								$returndata['text2']='An unknown error occured while removing dead enemy.';
							}
						}
						echo json_encode($returndata);
					}
					else {
						echo '{"action":"'.$_GET['action'].'","status":4,"text":"Your army does not have enough moves left."}';
					}
				}
				elseif($_GET['action']=='shoot') {
					if($trp['moveleft']>=2) {
						$defmod=1;
						if($tar['state']==1) {
							$defmod*=1.5;
						}
						$trpDmg=0;
						$tarDmg=0;
						$tidalstr=ceil(($trp['power']+$tar['power'])/8);
						$rounds=mt_rand(3,8);
						for($i=0; $i<$rounds; $i++) {
							$tides=mt_rand(-$tidalstr*1000,$tidalstr*1000)/1000.0;
							$tarDmg+=$dmgMod*sqrt(($trp['power']*1.25+$tides)/(($tar['power']-$tides)*$defmod))/$rounds;
						}
						$tar['health']-=$tarDmg;
						$sizeunit=min(ceil($trp['origsize']/20),ceil($tar['origsize']/20));
						$returndata=array("action"=>$_GET['action'],"status1"=>0,"status2"=>0,"text1"=>"","text2"=>"","sql1"=>"","sql2"=>"","dmg1"=>$trpDmg,"dmg2"=>$tarDmg,"losses1"=>0,"losses2"=>0);
						$effectonesql="UPDATE `mcstuff`.`troops` SET `moveleft`='".($trp['moveleft']-2)."',`state`='0' WHERE `id`=".$trp['id'].";";
						$returndata['sql1']=$effectonesql;
						if(mysqli_query($conn,$effectonesql)) {
							$returndata['status1']=0;
							$returndata['text1']='Successfully set army movement.';
						}
						else {
							$returndata['status1']=1;
							$returndata['text1']='An unknown error occured while setting army movement.';
						}
						if($tar['health']>0) {
							while(mt_rand(0,10000)/100.0>$tar['health']) {
								$oldsize=$tar['size'];
								$tar['size']-=$sizeunit;
								$tar['power']*=$tar['size']/$oldsize;
								$returndata['losses2']+=$sizeunit;
								if($tar['size']<=0) {
									break;
								}
								$tar['health']*=$oldsize/$tar['size'];
							}
							if($tar['size']>0) {
								$effecttwosql="UPDATE `mcstuff`.`troops` SET `health`='".$tar['health']."',`size`='".$tar['size']."',`power`='".floor($tar['power'])."' WHERE `id`=".$tar['id'].";";
								$returndata['sql2']=$effecttwosql;
								if(mysqli_query($conn,$effecttwosql)) {
									$returndata['status2']=0;
									$returndata['text2']='Successfully set enemy damage.';
								}
								else {
									$returndata['status2']=1;
									$returndata['text2']='An unknown error occured while setting enemy damage.';
								}
							}
						}
						if($tar['health']<=0 || $tar['size']<=0) {
							$effecttwosql="DELETE FROM `mcstuff`.`troops` WHERE `id`='".$tar['id']."';";
							$returndata['sql2']=$effecttwosql;
							if(mysqli_query($conn,$effecttwosql)) {
								$returndata['status2']=100;
								$returndata['text2']='Enemy army died.';
							}
							else {
								$returndata['status2']=1;
								$returndata['text2']='An unknown error occured while removing dead enemy.';
							}
						}
						echo json_encode($returndata);
					}
					else {
						echo '{"action":"'.$_GET['action'].'","status":4,"text":"Your army does not have enough moves left."}';
					}
				}
			}
			else {
				echo '{"action":"'.$_GET['action'].'","status":3,"text":"Enemy army is too far away."}';
			}

		}
		else {
			echo '{"action":"'.$_GET['action'].'","status":2,"text":"You do not own that army."}';
		}
	}
	else {
		echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while getting enemy army data.","sql":"'.$tarquery.'"}';
	}
}
else {
	echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while getting army data.","sql":"'.$trpquery.'"}';
}

?>