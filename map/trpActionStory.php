<?php
session_start();
require 'db.php';

$dmgMod=15;
$luck=0;
$trpId=intval($_GET['id']);
$trpquery="SELECT `id`,`owner`,`nation`,`name`,`size`,`power`,`health`,`x`,`y`,`move`,`moveleft`,`sprite`,`mobile`,`ranged`,`state`,`cost`,`origsize`,`xp`,`bonuses`,`aiding`,`battle` FROM `mcstuff`.`troops` WHERE `id`=".$trpId.";";
$trpcommquery="SELECT `special`,`xp`,`army` FROM `mcstuff`.`commanders` WHERE `army`='".$trpId."';";
if(isset($_GET['target'])) {
	$tarId=intval($_GET['target']);
	$tarquery="SELECT `id`,`owner`,`nation`,`name`,`size`,`power`,`health`,`x`,`y`,`move`,`moveleft`,`sprite`,`mobile`,`ranged`,`state`,`cost`,`origsize`,`xp`,`bonuses`,`aiding`,`battle` FROM `mcstuff`.`troops` WHERE `id`=".$tarId.";";
	$tarcommquery="SELECT `special`,`xp`,`army` FROM `mcstuff`.`commanders` WHERE `army`='".$tarId."';";
}
if($trpqueryresult=mysqli_query($conn,$trpquery)) {
	if($trpcommqueryresult=mysqli_query($conn,$trpcommquery)) {
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
					'aiding' => intval($row[19]),
					'battle' => intval($row[20]),
					'lvl' => sqrt(0.25+2*intval($row[17]))-0.5,
					'bonuses' => explode(',',$row[18]),
					'bonusdata' => array_fill_keys(explode(',',$row[18]),0),
					'atckbonus' => 1,
					'dfnsbonus' => 1,
					'strgbonus' => 0
				);
				$trp['atckbonus']*=1+50/(1+exp(5-$trp['lvl']));
				for($i=0; $i<$trpcommqueryresult->num_rows; $i++) {
					$row=mysqli_fetch_row($trpcommqueryresult);
					foreach(explode(',',$row[0]) as $bonus) {
						if(in_array($bonus,$trp['bonuses'])) $trp['bonusdata'][$bonus]=max($trp['bonusdata'][$bonus],sqrt(0.25+2*intval($row[1]))-0.5);
					}
				}
				if(in_array('lucky',$trp['bonuses'])) {
					$luck+=0.56/(1+exp((3.77-$trp['bonusdata']['lucky'])/2.5));
				}
				$trpnomanleft=0;
				if(in_array('nomanleft',$trp['bonuses'])) {
					$trpnomanleft=56/(1+exp((3.77-$trp['bonusdata']['nomanleft'])/2.5));
				}
				$trphelpquery="SELECT `power` FROM `mcstuff`.`troops` WHERE `state`='2' AND `aiding`='".mysqli_real_escape_string($conn,$trp['name'])."';";
				if($trphelpqueryresult=mysqli_query($conn,$trphelpquery)) {
					for($i=0; $i<$trphelpqueryresult->num_rows; $i++) {
						$trp['strgbonus']+=intval($mysqli_fetch_row($trphelpqueryresult)[0])/2;
					}
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
						'aiding' => intval($row[19]),
						'battle' => intval($row[20]),
						'lvl' => sqrt(0.25+2*intval($row[17]))-0.5,
						'bonuses' => explode(',',$row[18]),
						'bonusdata' => array_fill_keys(explode(',',$row[18]),0),
						'atckbonus' => 1,
						'dfnsbonus' => 1,
						'strgbonus' => 0
					);
					$tar['atckbonus']*=1+50/(1+exp(5-$tar['lvl']));
					for($i=0; $i<$tarcommqueryresult->num_rows; $i++) {
						$row=mysqli_fetch_row($tarcommqueryresult);
						foreach(explode(',',$row[0]) as $bonus) {
							if(in_array($bonus,$tar['bonuses'])) $tar['bonusdata'][$bonus]=max($tar['bonusdata'][$bonus],sqrt(0.25+2*intval($row[1]))-0.5);
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
					$tarhelpquery="SELECT `power` FROM `mcstuff`.`troops` WHERE `state`='2' AND `aiding`='".mysqli_real_escape_string($conn,$tar['name'])."';";
					if($tarhelpqueryresult=mysqli_query($conn,$tarhelpquery)) {
						for($i=0; $i<$tarhelpqueryresult->num_rows; $i++) {
							$tar['strgbonus']+=intval($mysqli_fetch_row($tarhelpqueryresult)[0])/2;
						}
					}
				}
				if($trp['owner']==$_SESSION['username']) {
					if($_GET['action']=='fortify') {
						$effectsql="UPDATE `mcstuff`.`troops` SET `state`='1' WHERE `id`=".$trp['id'].";";
						if(mysqli_query($conn,$effectsql)) {
							echo '{"action":"'.$_GET['action'].'","status":0,"text":"Army successfully changed to fortified state."}';
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while changing army to fortified state.","sql":"'.$effectsql.'"}';
						}
					}
					elseif($_GET['action']=='rest') {
						$newhealth=sprintf("%.3f",floatval($_GET['newHealth']));
						$effectsql="UPDATE `mcstuff`.`troops` SET `health`='".$newhealth."' WHERE `id`=".$trp['id'].";";
						if(mysqli_query($conn,$effectsql)) {
							echo '{"action":"'.$_GET['action'].'","status":0,"text":"Army successfully set army health.","sql":"'.$effectsql.'"}';
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while setting army health.","sql":"'.$effectsql.'"}';
						}
					}
					elseif($_GET['action']=='move') {
						$newX=intval($_GET['x']);
						$newY=intval($_GET['z']);
						$deltaX=$newX-$trp['x'];
						$deltaY=$newY-$trp['y'];
						$effectonesql="UPDATE `mcstuff`.`troops` SET `state`='0',`x`='".$newX."',`y`='".$newY."' WHERE `id`='".$trp['id']."';";
						$effecttwosql="UPDATE `mcstuff`.`troops` SET `x` = `x` + {$deltaX}, `y` = `y` + {$deltaY} WHERE `state`='5' AND `aiding`='".mysqli_real_escape_string($conn,$trp['name'])."';";
						if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$effecttwosql)) {
							echo '{"action":"'.$_GET['action'].'","status":0,"text":"Army successfully set army position."}';
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while setting army position.","sql1":"'.$effectonesql.'","sql2":"'.$effecttwosql.'"}';
						}
					}
					elseif ($_GET['action']=='update_battle') {
						$returndata=array("action"=>$_GET['action']);
						$armyCount=count($_GET['armies']);
						for ($i=0; $i<$armyCount; $i++) { 
							$newhealth=sprintf("%.3f",floatval($_GET['health_values'][$i]));
							$healtheffectsql="UPDATE `mcstuff`.`troops` SET `health`='{$newhealth}' WHERE `name`='{$_GET['armies'][$i]}' AND `state`='6' AND `battle`='{$trp['battle']}';";
							$returndata['sql'.$i]=$healtheffectsql;
							if(mysqli_query($conn,$healtheffectsql)) {
								$returndata['text'.$i]='Army successfully set army health.';
							}
							else {
								$returndata['status']=1;
								$returndata['text'.$i]='An unknown error occured while setting army health.';
							}
						}
						echo json_encode($returndata);
					}
					elseif ($_GET['action']=='exit_battle') {
						$returndata=array("action"=>$_GET['action'],"status"=>0,"text"=>"");

						$armyCount=count($_GET['armies']);
						for ($i=0; $i<$armyCount; $i++) { 
							$newhealth=sprintf("%.3f",floatval($_GET['health_values'][$i]));
							$healtheffectsql="UPDATE `mcstuff`.`troops` SET `health`='{$newhealth}' WHERE `name`='{$_GET['armies'][$i]}' AND `state`='6' AND `battle`='{$trp['battle']}';";
							$returndata['sql'.$i]=$healtheffectsql;
							if(mysqli_query($conn,$healtheffectsql)) {
								$returndata['text'.$i]='Army successfully set army health.';
							}
							else {
								$returndata['status']=1;
								$returndata['text'.$i]='An unknown error occured while setting army health.';
							}
						}

						$effectsql="UPDATE `mcstuff`.`troops` SET `state`='0' WHERE `state`='6' AND `battle`='{$trp['battle']}';";
						$returndata['sql']=$effectsql;
						if(mysqli_query($conn,$effectsql)) {
							$returndata['text']="Battle finished. Setting health may have encountered an error.";
						}
						else {
							$returndata['status']=1;
							$returndata['text']="An unknown error occured while exiting battle.";
						}

						echo json_encode($returndata);
					}
					elseif ($_GET['action']=='enter_battle') {
						$escapedTrp=mysqli_real_escape_string($conn,$trp['name']);
						$escapedTar=mysqli_real_escape_string($conn,$tar['name']);
						$effectonesql="UPDATE `mcstuff`.`troops` SET `state`='6',`battle`=(SELECT * FROM (SELECT MAX(`battle`)+1 FROM `mcstuff`.`troops`) wrappertable) WHERE `id`='{$trp['id']}';";
						$effecttwosql="UPDATE `mcstuff`.`troops` SET `state`='6',`battle`=(SELECT * FROM (SELECT `battle` FROM `mcstuff`.`troops` WHERE `id`='{$trp['id']}') wrappertable) WHERE `id`='{$tar['id']}';";
						$effecttresql="UPDATE `mcstuff`.`troops` SET `state`='6',`battle`=(SELECT * FROM (SELECT `battle` FROM `mcstuff`.`troops` WHERE `id`='{$trp['id']}') wrappertable) WHERE `state`='5' AND `aiding`='{$escapedTrp}';";
						$effectforsql="UPDATE `mcstuff`.`troops` SET `state`='6',`battle`=(SELECT * FROM (SELECT `battle` FROM `mcstuff`.`troops` WHERE `id`='{$trp['id']}') wrappertable) WHERE `state`='5' AND `aiding`='{$escapedTar}';";
						if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$effecttwosql) && mysqli_query($conn,$effecttresql) && mysqli_query($conn,$effectforsql)) {
							echo '{"action":"'.$_GET['action'].'","status":0,"text":"Battle successfully started."}';
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while entering battle.","sql1":"'.$effectonesql.'","sql2":"'.$effecttwosql.'","sql3":"'.$effecttresql.'","sql4":"'.$effectforsql.'"}';
						}
					}
					elseif($_GET['action']=='attack') {
						$trpDmg=0;
						$tarDmg=0;
						$trp['health']=floatval($_GET['newHealth0']);
						$tar['health']=floatval($_GET['newHealth1']);
						$returndata=array("action"=>$_GET['action'],"status1"=>0,"status2"=>0,"text1"=>"","text2"=>"","sql1"=>"","sql2"=>"","newHealth0"=>$trp['health'],"newHealth1"=>$tar['health']);
						if($trp['health']>0 && $trp['size']>0) {
							$effectonesql="UPDATE `mcstuff`.`troops` SET `health`='".sprintf("%.3f",$trp['health'])."' WHERE `id`=".$trp['id'].";";
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
						else {
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
						if($tar['health']>0 && $tar['size']>0) {
							$effectonesql="UPDATE `mcstuff`.`troops` SET `health`='".sprintf("%.3f",$tar['health'])."' WHERE `id`=".$tar['id'].";";
							$returndata['sql1']=$effectonesql;
							if(mysqli_query($conn,$effectonesql)) {
								$returndata['status2']=0;
								$returndata['text2']='Successfully set enemy damage.';
							}
							else {
								$returndata['status2']=1;
								$returndata['text2']='An unknown error occured while setting enemy army damage.';
							}
						}
						else {
							$effectonesql="DELETE FROM `mcstuff`.`troops` WHERE `id`='".$tar['id']."';";
							$commandersql="UPDATE `mcstuff`.`commanders` SET `army`=0,`xp`=`xp`+1 WHERE `army`='".$tar['id']."';";
							$returndata['sql1']=$effectonesql.' '.$commandersql;
							if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$commandersql)) {
								$returndata['status2']=100;
								$returndata['text2']='Enemy army died.';
							}
							else {
								$returndata['status2']=1;
								$returndata['text2']='An unknown error occured while removing dead enemy army.';
							}
						}
						echo json_encode($returndata);
					}
					elseif($_GET['action']=='shoot') {
						$trpDmg=0;
						$tarDmg=0;
						$tar['health']=floatval($_GET['newHealth']);
						$returndata=array("action"=>$_GET['action'],"status"=>0,"text"=>"","sql"=>"","newHealth"=>$tar['health']);
						if($tar['health']>0 && $tar['size']>0) {
							$effectonesql="UPDATE `mcstuff`.`troops` SET `health`='".sprintf("%.3f",$tar['health'])."' WHERE `id`=".$tar['id'].";";
							$returndata['sql1']=$effectonesql;
							if(mysqli_query($conn,$effectonesql)) {
								$returndata['status']=0;
								$returndata['text']='Successfully set enemy damage.';
							}
							else {
								$returndata['status']=1;
								$returndata['text']='An unknown error occured while setting enemy army damage.';
							}
						}
						else {
							$effectonesql="DELETE FROM `mcstuff`.`troops` WHERE `id`='".$tar['id']."';";
							$commandersql="UPDATE `mcstuff`.`commanders` SET `army`=0,`xp`=`xp`+1 WHERE `army`='".$tar['id']."';";
							$returndata['sql1']=$effectonesql.' '.$commandersql;
							if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$commandersql)) {
								$returndata['status']=100;
								$returndata['text']='Enemy army died.';
							}
							else {
								$returndata['status']=1;
								$returndata['text']='An unknown error occured while removing dead enemy army.';
							}
						}
						echo json_encode($returndata);
					}
					elseif($_GET['action']=='aid') {
						$effectonesql="UPDATE `mcstuff`.`troops` SET `state`='5',`aiding`='".mysqli_real_escape_string($conn,$tar['name'])."' WHERE `id`=".$trp['id'].";";
						$effecttwosql="UPDATE `mcstuff`.`troops` SET `aiding`='".mysqli_real_escape_string($conn,$tar['name'])."' WHERE `state`='5' AND `aiding`='".mysqli_real_escape_string($conn,$trp['name'])."';";
						if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$effecttwosql)) {
							echo '{"action":"'.$_GET['action'].'","status":0,"text":"Army successfully changed to aiding state."}';
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while changing army to aiding state.","sql":"'.$effectsql.'"}';
						}
					}
					elseif($_GET['action']=='heal') {
						$newhealth=sprintf("%.3f",floatval($_GET['newHealth']));
						$effectsql="UPDATE `mcstuff`.`troops` SET `health`='".$newhealth."' WHERE `id`=".$tar['id'].";";
						if(mysqli_query($conn,$effectsql)) {
							echo '{"action":"'.$_GET['action'].'","status":0,"text":"Army successfully set army health.","sql":"'.$effectsql.'"}';
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while setting army health.","sql":"'.$effectsql.'"}';
						}
					}
					elseif($_GET['action']=='merge') {
						$tap=$tar->getArrayCopy();
						$tap['size']+=$trp['size'];
						$tap['power']=floor(($tar['power']*$tar['size']+$trp['power']*$trp['size'])/$tap['size']);
						$tap['health']=($tar['health']*$tar['size']+$trp['health']*$trp['size'])/$tap['size'];
						$tap['move']=floor(($tar['move']*$tar['size']+$trp['move']*$trp['size'])/$tap['size']);
						$tap['moveleft']=floor(($tar['moveleft']*$tar['size']+$trp['moveleft']*$trp['size'])/$tap['size']);
						$tap['cost']+=$trp['cost'];
						$tap['origsize']+=$trp['origsize'];
						$tap['xp']=floor(($tar['xp']*$tar['size']+$trp['xp']*$trp['size'])/$tap['size']);
						$bonusstr='';
						for($i=0; $i<count($tar['bonuses']); $i++) {
							if($bonusstr!='') {
								$bonusstr.=',';
							}
							$bonusstr.=$tar['bonuses'][$i];
						}
						for($i=0; $i<count($trp['bonuses']); $i++) {
							if(!isset($tar['bonusdata'][$trp['bonuses'][$i]])) {
								if($bonusstr!='') {
									$bonusstr.=',';
								}
								$bonusstr.=$tar['bonuses'][$i];
							}
						}
						$effectonesql="UPDATE `mcstuff`.`troops` SET `size`='".$tap['size']."',`power`='".$tap['power']."',`health`='".sprintf("%.3f",$tap['health'])."',`move`='".$tap['move']."',`moveleft`='".$tap['moveleft']."',`cost`='".$tap['cost']."',`origsize`='".$tap['origsize']."',`xp`='".$tap['xp']."',`bonuses`='".$bonusstr."' WHERE `id`='".$tar['id']."';";
						$effecttwosql="DELETE FROM `mcstuff`.`troops` WHERE `id`='".$trp['id']."';";
						$effectohrsql="UPDATE `mcstuff`.`commanders` SET `army`='".$tar['id']."' WHERE `army`='".$trp['id']."';";
						$effectfousql="UPDATE `mcstuff`.`troops` SET `state`='0' WHERE `state`='2' AND `aiding`='".mysqli_real_escape_string($conn,$trp['name'])."';";
						if(mysqli_query($conn,$effectonesql) && mysqli_query($conn,$effecttwosql) && mysqli_query($conn,$effectthrsql) && mysqli_query($conn,$effectfousql)) {
							echo '{"action":"'.$_GET['action'].'","status":0,"text":"Successfully merged armies..","sql":"'.$effectsql.'"}';
						}
						else {
							echo '{"action":"'.$_GET['action'].'","status":1,"text":"An unknown error occured while merging armies.","sql":"'.$effectsql.'"}';
						}
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