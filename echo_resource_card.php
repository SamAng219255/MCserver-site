<?
function echoResourceCard($row,$i) {
	$tablerow1="";
	$tablerow2="";
	$tablerow3="";
	if(($row[3]!='0' || $row[5]!='0' || $row[10]=='show') && $row[10]!='hide') {
		$tablerow1.='<th>Citizen Wealth:</th><td>'.$row[4].$row[1].'</td>';
		$tablerow2.='<th>Citizen Income:</th><td>'.$row[6].$row[1].'</td>';
		$tablerow3.='<th>Tax:</th><td>'.$row[7].$row[1].'</td></tr>';
	}
	else {
		if($row[8]=='show') {
			$tablerow1.='<th>Citizen Wealth:</th><td>'.$row[4].$row[1].'</td>';
		}
		if($row[9]=='show') {
			$tablerow2.='<th>Citizen Income:</th><td>'.$row[6].$row[1].'</td>';
		}
		if($row[12]=='show') {
			$tablerow3.='<th>Tax:</th><td>'.$row[7].$row[1].'</td></tr>';
		}
	}
	if(($row[3]!='0' || $row[5]!='0' || $row[10]=='show') && $row[10]!='hide') {
		$tablerow1='<th>National Wealth:</th><td>'.$row[3].$row[1].'</td>'.$tablerow1;
		$tablerow2='<th>National Income:</th><td>'.$row[5].$row[1].'</td>'.$tablerow2;
		if($tablerow3!='') $tablerow3='<th></th><td></td>'.$tablerow3;
	}
	else {
		if($row[8]=='show') {
			$tablerow1='<th>National Wealth:</th><td>'.$row[3].$row[1].'</td>'.$tablerow1;
		}
		if($row[9]=='show') {
			$tablerow2='<th>National Income:</th><td>'.$row[5].$row[1].'</td>'.$tablerow2;
		}
	}
	echo '<div class="card" id="resource-'.$i.'"><div class="postmeta"><div class="h">';
	echo '<input type="text" name="name-'.$i.'" value="'.$row[2].'">';
	echo '</div><div class="unit">Unit: <input type="text" name="unit-'.$i.'" value="'.$row[1].'"></div></div><div class="stuffing">';
	echo '<textarea name="desc-'.$i.'" placeholder="Description">'.$row[13].'</textarea>';
	echo '<table>';
	echo '<tr><th>National Wealth:</th><td><input type="number" name="ntnlwlth-'.$i.'" value="'.$row[3].'"></td><th>Citizen Wealth:</th><td><input type="number" name="ctznwlth-'.$i.'" value="'.$row[4].'"></td></tr>';
	echo '<tr><th>National Income:</th><td><input type="number" name="ntnlincome-'.$i.'" value="'.$row[5].'"></td><th>Citizen Income:</th><td><input type="number" name="ctznincome-'.$i.'" value="'.$row[6].'"></td></tr>';
	echo '<tr><th></th><td></td><th>Tax:</th><td><input type="number" name="tax-'.$i.'" value="'.$row[7].'"></td></tr>';
	echo '</table>';
	echo '<dl>';
	echo '<dt>Wealth:</dt><dd>';
	if($row[8]=='show') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="show" name="showwlth-'.$i.'"'.$checked.'> Show</label>';
	if($row[8]=='hide') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="hide" name="showwlth-'.$i.'"'.$checked.'> Hide</label>';
	if($row[8]=='false') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="false" name="showwlth-'.$i.'"'.$checked.'> Auto</label>';
	echo '</dd>';
	echo '<dt>Income:</dt><dd>';
	if($row[9]=='show') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="show" name="showncm-'.$i.'"'.$checked.'> Show</label>';
	if($row[9]=='hide') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="hide" name="showncm-'.$i.'"'.$checked.'> Hide</label>';
	if($row[9]=='false') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="false" name="showncm-'.$i.'"'.$checked.'> Auto</label>';
	echo '</dd>';
	echo '<dt>Nation:</dt><dd>';
	if($row[10]=='show') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="show" name="showntnl-'.$i.'"'.$checked.'> Show</label>';
	if($row[10]=='hide') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="hide" name="showntnl-'.$i.'"'.$checked.'> Hide</label>';
	if($row[10]=='false') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="false" name="showntnl-'.$i.'"'.$checked.'> Auto</label>';
	echo '</dd>';
	echo '<dt>Citizen:</dt><dd>';
	if($row[11]=='show') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="show" name="showctzn-'.$i.'"'.$checked.'> Show</label>';
	if($row[11]=='hide') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="hide" name="showctzn-'.$i.'"'.$checked.'> Hide</label>';
	if($row[11]=='false') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="false" name="showctzn-'.$i.'"'.$checked.'> Auto</label>';
	echo '</dd>';
	echo '<dt>Tax:</dt><dd>';
	if($row[12]=='show') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="show" name="showtax-'.$i.'"'.$checked.'> Show</label>';
	if($row[12]=='hide') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="hide" name="showtax-'.$i.'"'.$checked.'> Hide</label>';
	if($row[12]=='false') $checked='checked';
	else $checked='';
	echo '<label><input type="radio" value="false" name="showtax-'.$i.'"'.$checked.'> Auto</label>';
	echo '</dd>';
	echo '</dl>';
	echo '</div><div class="footer"><span class="delete" onclick="deleteresource('.$i.')">Delete</span></div></div>';
}
if(isset($_GET['argv'])) {
	echoResourceCard([$_GET['nation'],$_GET['unit'],$_GET['type'],$_GET['ntnlwlth'],$_GET['ctznwlth'],$_GET['ntnlincome'],$_GET['ctznincome'],$_GET['tax'],$_GET['showwlth'],$_GET['showncm'],$_GET['showntnl'],$_GET['showctzn'],$_GET['showtax'],$_GET['desc']],$_GET['i']);
}
?>