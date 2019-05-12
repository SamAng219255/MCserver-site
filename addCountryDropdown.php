<?php
	if(session_status()!=PHP_SESSION_ACTIVE) session_start();
	$idset=isset($id);
	if($idset) {
		$idat=$id;
	}
	$id=0;
	if(isset($_GET['dropdownid'])) {
		$id=intval($_GET['dropdownid']);
	}
	elseif(isset($dropdownid) && (!$idset || $dropdownid!=$idat)) {
		$id=$dropdownid;
	}
	elseif($idset) {
		$id=$idat+1;
	}
	$dropdownNationList=$_SESSION['nationlist'];
	if(isset($replacementNationList)) {
		$dropdownNationList=$replacementNationList;
	}
	$echostr='';
	$countrycount=count($dropdownNationList);
	$echostr.='<select name="allies[]" id="dropdown-'.$id.'" class="countrydropdown"><option value="">--Select a Nation--</option>';
	for($j=0; $j<$countrycount; $j++) {
		$isSelected='';
		if(isset($dropdownSelected) && $dropdownSelected==$dropdownNationList[$j] && (!$lastSelected || $dropdownSelected!=$lastSelected)) {
			$isSelected=' selected';
			$lastSelected=$dropdownSelected;
		}
		$echostr.='<option value="'.$dropdownNationList[$j].'"'.$isSelected.'>'.$dropdownNationList[$j].'</option>';
	}
	$echostr.='</select>';
	if(isset($_GET['dropdownid'])) {
		echo json_encode(array('id'=>$id,'html'=>$echostr));
	}
	else {
		echo $echostr;
	}
?>