<?php

	$nationquery=$pdo->prepare("SELECT `name`,`ruler`,`hasflag`,`population`,`parent`,`desc`,`showruler`,`showflag`,`showpopul`,`showparent` FROM `mcstuff`.`nations` WHERE `name`=?;");
	$nationquery->bindValue(1, $_GET['nation'], PDO::PARAM_STR);
	$nationquery->execute();
	$nationrow=$nationquery->fetch(PDO::FETCH_BOTH);
	$name=$nationrow[0];
	$ruler=$nationrow[1];
	$hasflag=$nationrow[2]=='1';
	$population=intval($nationrow[3]);
	$parentcountry=$nationrow[4];
	if($parentcountry=='') $parentcountry='none';
	$description=$nationrow[5];
	$showruler=(($ruler!='' || $nationrow[6]=='show') && $nationrow[6]!='hide');
	$showflag=(($hasflag || $nationrow[7]=='show') && $nationrow[7]!='hide');
	$showpopul=(($population!=0 || $nationrow[8]=='show') && $nationrow[8]!='hide');
	$showparent=(($parentcountry!='none' || $nationrow[9]=='show') && $nationrow[9]!='hide');
	$showany=$showruler || $showflag || $showpopul || $showparent;
	$rulerquery=$pdo->prepare("SELECT `username`,`forecolor`,`backcolor`,`character`,`prefix`,`suffix`,`skin` FROM `mcstuff`.`users` WHERE `username`=?;");
	$rulerquery->bindValue(1, $ruler, PDO::PARAM_STR);
	$rulerquery->execute();
	$rulerrow=$rulerquery->fetch(PDO::FETCH_BOTH);
	$forecolor=$rulerrow[1];
	$backcolor=$rulerrow[2];
	$character=$rulerrow[3];
	$prefix=$rulerrow[4];
	$suffix=$rulerrow[5];
	$skin=$rulerrow[6];

?>
<style>

	#nation.card>.stuffing {
		white-space: normal;
	}
	#nation.card>.stuffing .desc {
		white-space: pre-wrap;
	}
	#resources {
		box-shadow: inset 0 0 10px #000000;
		background-color: rgba(0,0,0,0.25);
		padding: 1rem;
		border-radius: 0.5rem;
	}
	.card table th {
		text-align: left;
	}

</style>
<?php echo '<style>.card {color: #'.$forecolor.';background-color: #'.$backcolor.';}</style>'; ?>
<div class="card" id="nation">
	<div class="postmeta">
		<div class="h">
			<?php echo $name; ?>
		</div>
	</div>
	<div class="stuffing">
		<?php if($description!='') echo '<div class="desc">'.$description.'</div>'; ?>
		<div id="data">
			<?php
				if($showany) {
					echo '<dl>';
					if($showruler) echo '<dt>Ruler:</dt><dd>'.$prefix.' '.$character.' '.$suffix.' ('.$ruler.')</dd>';
					if($showparent) echo '<dt>Parent Country:</dt><dd>'.$parentcountry.'</dd>';
					if($showflag) echo '<dt>Flag:</dt><dd><img id="flag" src="./img/flags/'.$name.'"></dd>';
					if($showpopul) echo '<dt>Population:</dt><dd>'.comma($population).'</dd>';
					echo '</dl>';
				}
			?>

			<?php
				$resourcequery="SELECT `nation`,`unit`,`type`,`ntnlwlth`,`ctznwlth`,`ntnlincome`,`ctznincome`,`tax`,`showwlth`,`showncm`,`showntnl`,`showctzn`,`showtax`,`desc`,`hide` FROM `mcstuff`.`resources` WHERE `nation`=?";
				if(isset($_SESSION['username']) && $_SESSION['username']==$ruler)
					$resourcequery.=";";
				else
					$resourcequery.=" AND `hide`=0;";
				$resourcequerypdo=$pdo->prepare($resourcequery);
				$resourcequerypdo->bindValue(1, $name, PDO::PARAM_STR);
				$resourcequerypdo->execute();
				if($resourcequerypdo->rowCount()>0) {
					echo '<div id="resources"><span id="h">Resources:</span>';
					foreach($resourcequerypdo->fetchAll(PDO::FETCH_BOTH) as $row) {
						$tablerow1="";
						$tablerow2="";
						$tablerow3="";
						if(($row[3]!='0' || $row[5]!='0' || $row[11]=='show') && $row[11]!='hide') {
							$tablerow1.='<th>Citizen Wealth:</th><td>'.comma($row[4]).$row[1].'</td>';
							$tablerow2.='<th>Citizen Income:</th><td>'.comma($row[6]).$row[1].'</td>';
							$tablerow3.='<th>Tax:</th><td>'.comma($row[7]).$row[1].'</td></tr>';
						}
						if(($row[3]!='0' || $row[5]!='0' || $row[10]=='show') && $row[10]!='hide') {
							$tablerow1='<th>National Wealth:</th><td>'.comma($row[3]).$row[1].'</td>'.$tablerow1;
							$tablerow2='<th>National Income:</th><td>'.comma($row[5]).$row[1].'</td>'.$tablerow2;
							if($tablerow3!='') $tablerow3='<th></th><td></td>'.$tablerow3;
						}
						echo '<div class="card"><div class="postmeta"><div class="h">';
						echo $row[2];
						echo '</div></div><div class="stuffing">';
						if($row[13]!='') echo '<div>'.$row[13].'</div>';
						echo '<table>';
						if(($tablerow1!='' || $row[8]=='show') && $row[8]!='hide') echo '<tr>'.$tablerow1.'</tr>';
						if(($tablerow2!='' || $row[9]=='show') && $row[9]!='hide') echo '<tr>'.$tablerow2.'</tr>';
						if(($tablerow3!='' || $row[12]=='show') && $row[12]!='hide') echo '<tr>'.$tablerow3.'</tr>';
						echo '</table>';
						if($row[14]=='1') echo '<div>(Hidden)</div>';
						echo '</div></div>';
					}
					echo '</div>';
				}
			?>
		</div>
	</div>
	<div class="footer">
		<?php
			if(isset($_SESSION['username']) && $_SESSION['username']==$ruler) {
				echo '<a href="./nation_edit.php?nation='.$name.'" class="edit">edit</a>';
			}
		?>
	</div>
</div>