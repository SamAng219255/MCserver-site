<?php
session_start();
require 'db.php';

$nation=mysqli_real_escape_string($conn,$_GET['nation1']);
$data=array('input'=>$_GET,'response'=>array('status'=>98,'text'=>'This should not be returned.'));
if($_SESSION['permissions']>0) {
	$ownerquery="SELECT `ruler` FROM `mcstuff`.`nations` WHERE `name`='".$nation."';";
	if($ownerqueryresult=mysqli_query($conn,$ownerquery)) {
		if($ownerqueryresult->num_rows==0 || mysqli_fetch_row($ownerqueryresult)[0]==$_SESSION['username']) {
			$clearsql="DELETE FROM `mcstuff`.`relations` WHERE `relation`=".(intval($_GET['relation'])+1)." AND (`nation1`='".$nation."' OR `nation2`='".$nation."');";
			if(mysqli_query($conn,$clearsql)) {
				$values='';
				$natlength=count($_GET['nation2']);
				for($i=0; $i<$natlength; $i++) {
					if($_GET['nation2'][$i]!='') {
						if($values!='') {
							$values.=', ';
						}
						$values.="('0','".$nation."','".mysqli_real_escape_string($conn,$_GET['nation2'][$i])."',".(intval($_GET['relation'])+1).")";
					}
				}
				if($values!='') {
					$sql='INSERT INTO `mcstuff`.`relations` (`id`,`nation1`,`nation2`,`relation`) VALUES '.$values.';';
					if(mysqli_query($conn,$sql)) {
						$data['response']=array('status'=>0,'text'=>'Successfully set nation relations.', 'sql'=>$sql);
					}
					else {
						$data['response']=array('status'=>1,'text'=>'An error occured while setting relations.', 'error'=>mysqli_error($conn), 'sql'=>$sql);
					}
				}
				else {
					$data['response']=array('status'=>0,'text'=>'Successfully cleared nation relations.', 'sql'=>$clearsql);
				}
			}
			else {
				$data['response']=array('status'=>1,'text'=>'An error occured while resetting relations.', 'error'=>mysqli_error($conn), 'sql'=>$clearsql);
			}
		}
		else {
			$data['response']=array('status'=>2,'text'=>'You can not make decisions for that nation.');
		}
	}
	else {
		$data['response']=array('status'=>1,'text'=>'An error occured while checking nation ownership.', 'error'=>mysqli_error($conn), 'sql'=>$ownerquery);
	}
}
else {
	$data['response']=array('status'=>99,'text'=>'Invalid permissions.');
}

echo json_encode($data);

?>