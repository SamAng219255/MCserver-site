<?php require 'pageStart.php'; ?>
		<title>People - AmospiaCraft</title>
		<div id="people">
			<?php
				$styles='';
				$stylequery="SELECT `username`,`forecolor`,`backcolor`,`nation`,`character`,`prefix`,`suffix`,`permissions`,`skin` FROM `mcstuff`.`users` WHERE `permissions`>0;";
				$stylequeryresult=mysqli_query($conn,$stylequery);
				for($i=0; $i<$stylequeryresult->num_rows; $i++) {
					$row=mysqli_fetch_row($stylequeryresult);
					echo '<div class="card" user="'.$row[0].'"><table><tr><td rowspan="2"><div class="image">';
					addModel($row[0]);
					echo '</div></td><td><div class="postmeta"><div class="h">'.$row[0].'</div></div></td></tr><tr><td><div class="stuffing">';
					echo '<span>Character: '.$row[5].' '.$row[4].' '.$row[6].'</span><br><span>Nation: <a href="./nations.php?nation='.$row[3].'">'.$row[3].'</a></span><br><a href="./blog.php?poster='.$row[0].'">Click here to see their posts.</a>';
					echo '</div></td></tr></table></div>';
					$styles.='
.card[user='.$row[0].'] {
	color: #'.$row[1].';
	background-color: #'.$row[2].';
}
.model[user='.$row[0].'] .face {
	background-image: url("'.$row[8].'");
}';
				}
				echo '<style>'.$styles.'
</style>';
			?>
		</div>
		<script>
			if(location.hash!="") {
				wrapper.scrollTo(0,$(".card[user="+location.hash.substr(1)+"]")[0].offsetTop);
			}
		</script>
<?php require 'pageEnd.php';?>