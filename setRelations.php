<?php
session_start();
require 'db.php';

$nation=$_GET['nation1'];
$data=array('input'=>$_GET,'response'=>array('status'=>98,'text'=>'This should not be returned.'));
if($_SESSION['permissions']>0) {
	$ownerquery=$pdo->prepare("SELECT `ruler` FROM `mcstuff`.`nations` WHERE `name`=?;");
	$ownerquery->bindValue(1, $nation, PDO::PARAM_STR);
	if($ownerquery->execute()) {
		if($ownerquery->rowCount()==0 || $ownerquery->fetch(PDO::FETCH_BOTH)[0]==$_SESSION['username']) {
			$clearsql=$pdo->prepare("DELETE FROM `mcstuff`.`relations` WHERE `relation`=:rel AND (`nation1`=:nat1 OR `nation2`=:nat2);");
			$clearsql->bindValue('rel', intval($_GET['relation'])+1, PDO::PARAM_INT);
			$clearsql->bindValue('nat1', $nation, PDO::PARAM_STR);
			$clearsql->bindValue('nat2', $nation, PDO::PARAM_STR);
			if($clearsql->execute()) {
				$sql=$pdo->prepare('INSERT INTO `mcstuff`.`relations` (`id`,`nation1`,`nation2`,`relation`) VALUES (\'0\',:nat1,:nat2,:rel);');
				$sql->bindValue('nat1', $nation, PDO::PARAM_STR);
				$sql->bindValue('rel', intval($_GET['relation'])+1, PDO::PARAM_INT);
				$natlength=count($_GET['nation2']);
				$updated=false;
				$successful=true;
				for($i=0; $i<$natlength; $i++) {
					if($_GET['nation2'][$i]!='') {
						$updated=true;
						$sql->bindValue('nat2', $_GET['nation2'][$i], PDO::PARAM_STR);
						if(!($sql->execute())) {
							$data['response']=array('status'=>1,'text'=>'An error occured while setting relations.', 'error'=>$pdo->errorInfo()[2], 'sql'=>$sql->queryString);
							$successful=false;
							break;
						}
					}
				}
				if($successful) {
					if($updated) {
						$data['response']=array('status'=>0,'text'=>'Successfully set nation relations.', 'sql'=>$sql);
					}
					else {
						$data['response']=array('status'=>0,'text'=>'Successfully cleared nation relations.', 'sql'=>$clearsql->queryString);
					}
				}
			}
			else {
				$data['response']=array('status'=>1,'text'=>'An error occured while resetting relations.', 'error'=>$pdo->errorInfo()[2], 'sql'=>$clearsql->queryString);
			}
		}
		else {
			$data['response']=array('status'=>2,'text'=>'You can not make decisions for that nation.');
		}
	}
	else {
		$data['response']=array('status'=>1,'text'=>'An error occured while checking nation ownership.', 'error'=>$pdo->errorInfo()[2], 'sql'=>$ownerquery->queryString);
	}
}
else {
	$data['response']=array('status'=>99,'text'=>'Invalid permissions.');
}

echo json_encode($data);

?>