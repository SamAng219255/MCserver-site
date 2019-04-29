<?php
session_start();
require 'db.php';

$dmgMod=15;
$luck=0;
$trpId=intval($_GET['id']);
$trpquery="SELECT `id`,`owner`,`nation`,`name`,`size`,`power`,`health`,`x`,`y`,`move`,`moveleft`,`sprite`,`mobile`,`ranged`,`state`,`cost`,`origsize`,`bonuses` FROM `mcstuff`.`troops` WHERE `id`=".$trpId.";";
$trpcommquery="SELECT `special`,`xp`,`army` FROM `mcstuff`.`commanders` WHERE `army`='".$trpId."';";
if(isset($_GET['target'])) {
	$tarId=intval($_GET['target']);
	$tarquery="SELECT `id`,`owner`,`nation`,`name`,`size`,`power`,`health`,`x`,`y`,`move`,`moveleft`,`sprite`,`mobile`,`ranged`,`state`,`cost`,`origsize` FROM `mcstuff`.`troops` WHERE `id`=".$tarId.";";
	$tarcommquery="SELECT `special`,`xp`,`army` FROM `mcstuff`.`commanders` WHERE `army`='".$tarId."';";
}
if($trpqueryresult=mysqli_query($conn,$trpquery)) {
	if($trpcommqueryresult=mysqli_query($conn,$trpquery)) {
		if(!isset($_GET['target']) || $tarqueryresult=mysqli_query($conn,$tarquery)) {
			if(!isset($_GET['target']) || $tarcommqueryresult=mysqli_query($conn,$tarcommquery)) {
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
					'origsize' => intval($row[16]),
					'xp' => intval($row[17]),
					'lvl' => sqrt(0.25+2*intval($row[17]))-0.5,
					'bonuses' => explode(',',$row[18]),
					'bonusdata' => array_fill_keys(explode(',',$row[18]),0),
					'atckbonus' => 1,
					'dfnsbonus' => 1
				);
				$trp['atckbonus']*=1+50/(1+exp(5-$trp['lvl']));
				for($i=0; $i<$trpcommqueryresult->num_rows; $i++) {
					$row=mysqli_fetch_row($trpcommqueryresult);
					foreach(explode(',',$row[0]) as $bonus) {
						$trp['bonusdata'][$bonus]=max($trp['bonusdata'][$bonus],sqrt(0.25+2*intval($row[1]))-0.5);
					}
				}
				if(in_array('lucky',$trp['bonuses'])) {
					$luck+=0.56/(1+exp((3.77-$trp['bonusdata']['lucky'])/2.5));
				}
				$trpnomanleft=0;
				if(in_array('nomanleft',$trp['bonuses'])) {
					$trpnomanleft=56/(1+exp((3.77-$trp['bonusdata']['nomanleft'])/2.5));
				}
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
						'origsize' => intval($row[16]),
						'xp' => intval($row[17]),
						'lvl' => sqrt(0.25+2*intval($row[17]))-0.5,
						'bonuses' => explode(',',$row[18]),
						'bonusdata' => array_fill_keys(explode(',',$row[18]),0),
						'atckbonus' => 1,
						'dfnsbonus' => 1
					);
					$tar['atckbonus']*=1+50/(1+exp(5-$tar['lvl']));
					for($i=0; $i<$tarcommqueryresult->num_rows; $i++) {
						$row=mysqli_fetch_row($tarcommqueryresult);
						foreach(explode(',',$row[0]) as $bonus) {
							$tar['bonusdata'][$bonus]=max($tar['bonusdata'][$bonus],sqrt(0.25+2*intval($row[1]))-0.5);
						}
					}
					if($tar['state']==1) {
						if(in_array('fortify',$tar['bonuses'])) {
							$tempfortmod=1.12/(1+exp((3.77-$tar['bonusdata']['fortify'])/2.5));
							$tar['dfnsbonus']*=1.5+$tempfortmod;
							$tar['atckbonus']*=1.5+$tempfortmod;
						}
						else {
							$tar['dfnsbonus']*=1.5;
							$tar['atckbonus']*=1.5;
						}
					}
					if(in_array('lucky',$tar['bonuses'])) {
						$luck-=0.56/(1+exp((3.77-$tar['bonusdata']['lucky'])/2.5));
					}
					$tarnomanleft=0;
					if(in_array('nomanleft',$tar['bonuses'])) {
						$tarnomanleft=56/(1+exp((3.77-$tar['bonusdata']['nomanleft'])/2.5));
					}
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
								$newhealth=sprintf("%.3f",min(((1+0.56/(1+exp((3.77-$trp['bonusdata']['healing'])/2.5)))*24/(1.0+$trp['size']/$trp['cost']))+$trp['health'],100));
								$effectsql="UPDATE `mcstuff`.`troops` SET `moveleft`='".($trp['moveleft']-2)."',`health`='".$newhealth."' WHERE `id`=".$trp['id'].";";
								if(mysqli_query($conn,$effectsql)) {
									echo '{"action":"'.$_GET['action'].'","status":0,"text":"Army successfully set army health.","sql":"'.$effectsql.'"}';
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
								$trpDmg=0;
								$tarDmg=0;
								$trp['atckbonus']*=1.25;
								if(in_array('open',$trp['bonuses'])) {
									$trp['atckbonus']*=1+1.12/(1+exp((3.77-$trp['bonusdata']['open'])/2.5));
								}
								elseif(in_array('combat',$trp['bonuses'])) {
									$trp['atckbonus']*=1+0.56/(1+exp((3.77-$trp['bonusdata']['combat'])/2.5));
								}
								if(in_array('open',$tar['bonuses'])) {
									$tar['atckbonus']*=1+1.12/(1+exp((3.77-$tar['bonusdata']['open'])/2.5));
								}
								elseif(in_array('combat',$tar['bonuses'])) {
									$tar['atckbonus']*=1+0.56/(1+exp((3.77-$tar['bonusdata']['combat'])/2.5));
								}
								if(in_array('open',$trp['bonuses'])) {
									$trp['atckbonus']*=1+1.12/(1+exp((3.77-$trp['bonusdata']['open'])/2.5));
								}
								elseif(in_array('defense',$trp['bonuses'])) {
									$trp['atckbonus']*=1+0.56/(1+exp((3.77-$trp['bonusdata']['defense'])/2.5));
								}
								if(in_array('open',$tar['bonuses'])) {
									$tar['atckbonus']*=1+1.12/(1+exp((3.77-$tar['bonusdata']['open'])/2.5));
								}
								elseif(in_array('defense',$tar['bonuses'])) {
									$tar['atckbonus']*=1+0.56/(1+exp((3.77-$tar['bonusdata']['defense'])/2.5));
								}
								$tidalstr=ceil(($trp['power']+$tar['power'])/4);
								$rounds=mt_rand(4,12);
								for($i=0; $i<$rounds; $i++) {
									$tides=mt_rand($tidalstr*1000*($luck-1),$tidalstr*1000*($luck+1))/1000.0;
									$tarDmg+=max($dmgMod*sqrt(($trp['power']*$trp['atckbonus']+$tides)/($tar['power']*$tar['dfnsbonus']-$tides))/$rounds,0);
									$trpDmg+=max($dmgMod*sqrt(($tar['power']*$tar['atckbonus']-$tides)/($trp['power']*$trp['dfnsbonus']+$tides))/$rounds,0);
								}
								$trp['health']-=$trpDmg;
								$tar['health']-=$tarDmg;
								$sizeunit=min(ceil($trp['origsize']/20),ceil($tar['origsize']/20));
								$returndata=array("action"=>$_GET['action'],"status1"=>0,"status2"=>0,"text1"=>"","text2"=>"","sql1"=>"","sql2"=>"","dmg1"=>$trpDmg,"dmg2"=>$tarDmg,"losses1"=>0,"losses2"=>0);
								if($trp['health']>0) {
									while(mt_rand(0,10000)/100.0-$trpnomanleft>$trp['health']) {
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
										$effectonesql="UPDATE `mcstuff`.`troops` SET `xp`=`xp`+1,`moveleft`=`moveleft`-2,`state`='0',`health`='".$trp['health']."',`size`='".$trp['size']."',`power`='".floor($trp['power'])."' WHERE `id`=".$trp['id'].";";
										$commandersql="UPDATE `mcstuff`.`commanders` SET `xp`=`xp`+1 WHERE `army`='".$trp['id']."';";
										$returndata['sql1']=$effectonesql.' '.$commandersql;
										if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$commandersql)) {
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
									$commandersql="UPDATE `mcstuff`.`commanders` SET `army`=0,`xp`=`xp`+1 WHERE `army`='".$trp['id']."';";
									$returndata['sql1']=$effectonesql.' '.$commandersql;
									if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$commandersql)) {
										$returndata['status1']=100;
										$returndata['text1']='Army died.';
									}
									else {
										$returndata['status1']=1;
										$returndata['text1']='An unknown error occured while removing dead army.';
									}
								}
								if($tar['health']>0) {
									while(mt_rand(0,10000)/100.0-$tarnomanleft>$tar['health']) {
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
										$effecttwosql="UPDATE `mcstuff`.`troops` SET `xp`=`xp`+1,`health`='".$tar['health']."',`size`='".$tar['size']."',`power`='".floor($tar['power']/1.5)."' WHERE `id`=".$tar['id'].";";
										$commandersql="UPDATE `mcstuff`.`commanders` SET `xp`=`xp`+1 WHERE `army`='".$trp['id']."';";
										$returndata['sql2']=$effecttwosql.' '.$commandersql;
										if(mysqli_query($conn,$effecttwosql) && mysqli_query($conn,$commandersql)) {
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
									$commandersql="UPDATE `mcstuff`.`commanders` SET `army`=0,`xp`=`xp`+1 WHERE `army`='".$tar['id']."';";
									$returndata['sql2']=$effecttwosql.' '.$commandersql;
									if(mysqli_query($conn,$effecttwosql) && mysqli_query($conn,$commandersql)) {
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
								if($trp['mobile']==0) {
									$trp['atckbonus']/=3;
								}
								$trpDmg=0;
								$tarDmg=0;
								if(in_array('mobility',$trp['bonuses'])) {
									$trp['atckbonus']*=1+1.12/(1+exp((3.77-$trp['bonusdata']['mobility'])/2.5));
								}
								elseif(in_array('combat',$trp['bonuses'])) {
									$trp['atckbonus']*=1+0.56/(1+exp((3.77-$trp['bonusdata']['combat'])/2.5));
								}
								if(in_array('mobility',$tar['bonuses'])) {
									$tar['atckbonus']*=1+1.12/(1+exp((3.77-$tar['bonusdata']['mobility'])/2.5));
								}
								elseif(in_array('defense',$tar['bonuses'])) {
									$tar['atckbonus']*=1+0.56/(1+exp((3.77-$tar['bonusdata']['defense'])/2.5));
								}
								$tidalstr=ceil(($trp['power']+$tar['power'])/4);
								$rounds=1;
								for($i=0; $i<$rounds; $i++) {
									$tides=mt_rand($tidalstr*1000*($luck-1),$tidalstr*1000*($luck+1))/1000.0;
									$tarDmg+=max($dmgMod*sqrt(($trp['power']*$trp['atckbonus']+$tides)/($tar['power']*$tar['dfnsbonus']-$tides))/$rounds,0);
								}
								$tar['health']-=$tarDmg;
								$sizeunit=min(ceil($trp['origsize']/20),ceil($tar['origsize']/20));
								$returndata=array("action"=>$_GET['action'],"status1"=>0,"status2"=>0,"text1"=>"","text2"=>"","sql1"=>"","sql2"=>"","dmg1"=>$trpDmg,"dmg2"=>$tarDmg,"losses1"=>0,"losses2"=>0);
								$effectonesql="UPDATE `mcstuff`.`troops` SET `xp`=`xp`+1,`moveleft`=`moveleft`-4,`state`='0' WHERE `id`=".$trp['id'].";";
								$commandersql="UPDATE `mcstuff`.`commanders` SET `xp`=`xp`+1 WHERE `army`='".$trp['id']."';";
								$returndata['sql1']=$effectonesql.' '.$commandersql;
								if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$commandersql)) {
									$returndata['status1']=0;
									$returndata['text1']='Successfully set army movement.';
								}
								else {
									$returndata['status1']=1;
									$returndata['text1']='An unknown error occured while setting army movement.';
								}
								if($tar['health']>0) {
									while(mt_rand(0,10000)/100.0-$tarnomanleft>$tar['health']) {
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
										$effecttwosql="UPDATE `mcstuff`.`troops` SET `xp`=`xp`+1,`health`='".$tar['health']."',`size`='".$tar['size']."',`power`='".floor($tar['power']/1.5)."' WHERE `id`=".$tar['id'].";";
										$commandersql="UPDATE `mcstuff`.`commanders` SET `xp`=`xp`+1 WHERE `army`='".$tar['id']."';";
										$returndata['sql2']=$effecttwosql.' '.$commandersql;
										if(mysqli_query($conn,$effecttwosql) && mysqli_query($conn,$commandersql)) {
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
									$commandersql="UPDATE `mcstuff`.`commanders` SET `army`=0,`xp`=`xp`+1 WHERE `army`='".$tar['id']."';";
									$returndata['sql2']=$effecttwosql.' '.$commandersql;
									if(mysqli_query($conn,$effecttwosql) && mysqli_query($conn,$commandersql)) {
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
								if($trp['ranged']==0) {
									$trp['atckbonus']/=3;
								}
								$trpDmg=0;
								$tarDmg=0;
								$trp['atckbonus']*=1.25;
								if(in_array('ranged',$trp['bonuses'])) {
									$trp['atckbonus']*=1+1.12/(1+exp((3.77-$trp['bonusdata']['ranged'])/2.5));
								}
								elseif(in_array('combat',$trp['bonuses'])) {
									$trp['atckbonus']*=1+0.56/(1+exp((3.77-$trp['bonusdata']['combat'])/2.5));
								}
								if(in_array('open',$tar['bonuses'])) {
									$tar['atckbonus']*=1+1.12/(1+exp((3.77-$tar['bonusdata']['open'])/2.5));
								}
								elseif(in_array('ranged',$tar['bonuses'])) {
									$tar['atckbonus']*=1+0.56/(1+exp((3.77-$tar['bonusdata']['ranged'])/2.5));
								}
								$tidalstr=ceil(($trp['power']+$tar['power'])/4);
								$rounds=mt_rand(2,6);
								for($i=0; $i<$rounds; $i++) {
									$tides=mt_rand($tidalstr*1000*($luck-1),$tidalstr*1000*($luck+1))/1000.0;
									$tarDmg+=max($dmgMod*sqrt(($trp['power']*$trp['atckbonus']+$tides)/($tar['power']*$tar['dfnsbonus']-$tides))/$rounds,0);
								}
								$tar['health']-=$tarDmg;
								$sizeunit=min(ceil($trp['origsize']/20),ceil($tar['origsize']/20));
								$returndata=array("action"=>$_GET['action'],"status1"=>0,"status2"=>0,"text1"=>"","text2"=>"","sql1"=>"","sql2"=>"","dmg1"=>$trpDmg,"dmg2"=>$tarDmg,"losses1"=>0,"losses2"=>0);
								$effectonesql="UPDATE `mcstuff`.`troops` SET `xp`=`xp`+1,`moveleft`=`moveleft`-2,`state`='0' WHERE `id`=".$trp['id'].";";
								$commandersql="UPDATE `mcstuff`.`commanders` SET `xp`=`xp`+1 WHERE `army`='".$trp['id']."';";
								$returndata['sql1']=$effectonesql.' '.$commandersql;
								if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$commandersql)) {
									$returndata['status1']=0;
									$returndata['text1']='Successfully set army movement.';
								}
								else {
									$returndata['status1']=1;
									$returndata['text1']='An unknown error occured while setting army movement.';
								}
								if($tar['health']>0) {
									while(mt_rand(0,10000)/100.0-$tarnomanleft>$tar['health']) {
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
										$effecttwosql="UPDATE `mcstuff`.`troops` SET `xp`=`xp`+1,`health`='".$tar['health']."',`size`='".$tar['size']."',`power`='".floor($tar['power'])."' WHERE `id`=".$tar['id'].";";
										$commandersql="UPDATE `mcstuff`.`commanders` SET `xp`=`xp`+1 WHERE `army`='".$tar['id']."';";
										$returndata['sql2']=$effecttwosql.' '.$commandersql;
										if(mysqli_query($conn,$effecttwosql) && mysqli_query($conn,$commandersql)) {
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
									$commandersql="UPDATE `mcstuff`.`commanders` SET `army`=0,`xp`=`xp`+1 WHERE `army`='".$tar['id']."';";
									$returndata['sql2']=$effecttwosql.' '.$commandersql;
									if(mysqli_query($conn,$effecttwosql) && mysqli_query($conn,$commandersql)) {
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
				echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while getting enemy army commander data.","sql":"'.$tarcommquery.'"}';
			}
		}
		else {
			echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while getting enemy army data.","sql":"'.$tarquery.'"}';
		}
	}
	else {
		echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while getting army commander data.","sql":"'.$trpcommquery.'"}';
	}
}
else {
	echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while getting army data.","sql":"'.$trpquery.'"}';
}

?>