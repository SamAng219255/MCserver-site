<?php
session_start();
require 'db.php';

$response=array('input'=>$_GET,'output'=>array('status'=>-1,'text'=>'This should not appear.'));

if($_SESSION['permissions']>0) {
	if(isset($_GET['id'])) {
		$ownerquery="SELECT `user`,`id`,`name` FROM `mcstuff`.`mappoints` WHERE `id`=".mysqli_real_escape_string($conn,$_GET['id']).";";
		if($ownerqueryresult=mysqli_query($conn,$ownerquery)) {
			$failed=false;
			$row=mysqli_fetch_row($ownerqueryresult);
			$isowned=$row[0]==$_SESSION['username'];
			$deletedname=$row[2];
		}
		else {
			$failed=true;
		}
	}
	if(!isset($_GET['id']) || (!$failed && $isowned)) {
		if($_GET['mode']=='create') {
			$sql="INSERT INTO `mcstuff`.`mappoints` (`id`,`user`,`name`,`desc`,`x`,`z`,`dimension`,`type`,`icondata`) VALUES ('0','".$_SESSION['username']."','".mysqli_real_escape_string($conn,$_GET['name'])."','".mysqli_real_escape_string($conn,$_GET['desc'])."','".mysqli_real_escape_string($conn,$_GET['x'])."','".mysqli_real_escape_string($conn,$_GET['z'])."','".mysqli_real_escape_string($conn,$_GET['dimension'])."','".mysqli_real_escape_string($conn,$_GET['type'])."','".mysqli_real_escape_string($conn,$_GET['icondata'])."');";
			if(mysqli_query($conn,$sql)) {
				$response['output']['status']=0;
				$response['output']['text']='Sucessfully created the pin "'.$_GET['name'].'".';
				$response['output']['sql']=$sql;
			}
			else {
				$response['output']['status']=1;
				$response['output']['text']='A SQL error occurred while creating pin.';
				$response['output']['sql']=$sql;
				$response['output']['error']=mysqli_error($conn);
			}
		}
		else if($_GET['mode']=='change') {
			$sql="UPDATE `mcstuff`.`mappoints` SET `user`='".$_SESSION['username']."',`name`='".mysqli_real_escape_string($conn,$_GET['name'])."',`desc`='".mysqli_real_escape_string($conn,$_GET['desc'])."',`x`='".mysqli_real_escape_string($conn,$_GET['x'])."',`z`='".mysqli_real_escape_string($conn,$_GET['z'])."',`dimension`='".mysqli_real_escape_string($conn,$_GET['dimension'])."',`type`='".mysqli_real_escape_string($conn,$_GET['type'])."',`icondata`='".mysqli_real_escape_string($conn,$_GET['icondata'])."' WHERE `id`=".mysqli_real_escape_string($conn,$_GET['id']).";";
			if(mysqli_query($conn,$sql)) {
				$response['output']['status']=0;
				$response['output']['text']='Sucessfully changed the pin "'.$_GET['name'].'".';
				$response['output']['sql']=$sql;
			}
			else {
				$response['output']['status']=1;
				$response['output']['text']='A SQL error occurred while changing pin.';
				$response['output']['sql']=$sql;
				$response['output']['error']=mysqli_error($conn);
			}
		}
		else if($_GET['mode']=='delete') {
			$sql="DELETE FROM `mcstuff`.`mappoints` WHERE `id`=".mysqli_real_escape_string($conn,$_GET['id']).";";
			if(mysqli_query($conn,$sql)) {
				$response['output']['status']=0;
				$response['output']['text']='Sucessfully deleted the pin "'.$deletedname.'".';
				$response['output']['sql']=$sql;
			}
			else {
				$response['output']['status']=1;
				$response['output']['text']='A SQL error occurred while deleting pin.';
				$response['output']['sql']=$sql;
				$response['output']['error']=mysqli_error($conn);
			}
		}
	}
	elseif ($failed) {
		$response['output']['status']=1;
		$response['output']['text']='A SQL error occurred while checking pin ownership.';
		$response['output']['sql']=$ownerquery;
		$response['output']['error']=mysqli_error($conn);
	}
	else {
		$response['output']['status']=3;
		$response['output']['text']='You do not own that pin.';
		$response['output']['sql']=$ownerquery;
	}
}
else {
	$response['output']['status']=2;
	$response['output']['text']='You do not permission to do that.';
}

echo json_encode($response);

?>