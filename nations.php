<?php require './pageStart.php'; ?>
		<title>Nations - AmospiaCraft</title>
		<div id="nations">
			<?php
				if(isset($_GET["nation"])) {
					require 'nationCard.php';
				}
				else {
					$styles='';
					$nationquery="SELECT `name`,`ruler`,`hasflag` FROM `mcstuff`.`nations`;";
					foreach($pdo->query($nationquery, PDO::FETCH_BOTH) as $row) {
						$flag='default_flag.png';
						if($row[2]=='1') {
							$flag='flags/'.$row[0];
						}
						echo '<div class="card" user="'.$row[1].'"><table><tr><td rowspan="2" class="flag" style="background-image: url(\'./img/'.$flag.'\');">';
						echo '</td><td><div class="postmeta"><div class="h">'.$row[0].'</div></div></td></tr><tr><td><div class="stuffing">';
						echo '<span>Ruler: <a href="./people.php?target='.$row[1].'">'.$row[1].'</a></span><br><a href="./nations.php?nation='.$row[0].'">See More</a>';
						echo '</div></td></tr></table></div>';
					}
					$stylequery="SELECT `username`,`forecolor`,`backcolor`,`skin`,`permissions` FROM `mcstuff`.`users` WHERE `permissions`>0;";
					foreach($pdo->query($stylequery, PDO::FETCH_BOTH) as $row) {
						$styles.='
.card[user='.$row[0].'] {
	color: #'.$row[1].';
	background-color: #'.$row[2].';
}';
					}
					echo '<style>'.$styles.'
</style>';
				}
			?>
		</div>
<?php require './pageEnd.php';?>