<?php require 'pageStart.php'; ?>
		<title>Post - AmospiaCraft</title>
		<?php
			if(isset($_SESSION['username']) && $permissions>0 && isset($_POST['posting'])) {
				$tags=',';
				preg_match_all('/\B#[a-zA-Z0-9]+\b/', $_POST['content'], $matches);
				$matchCount=count($matches[0]);
				for($i=0; $i<$matchCount; $i++) {
						
					$testtag=trim(substr($matches[0][$i],1));
					if($testtag!='') {
						$tags.=$testtag.',';
					}
				}
				$sql=$pdo->prepare("INSERT INTO `mcstuff`.`posts` (`id`,`username`,`topic`,`tags`,`content`) VALUES ('0',:username,:topic,:tags,:content);");
				$sql->bindValue('username', $_SESSION['username'], PDO::PARAM_STR);
				$sql->bindValue('topic', $_POST['topic'], PDO::PARAM_STR);
				$sql->bindValue('tags', $tags, PDO::PARAM_STR);
				$sql->bindValue('content', $_POST['content'], PDO::PARAM_STR);
				if($sql->execute()) {
					echo '<meta http-equiv="refresh" content="0; URL=./blog.php">';
				}
				else {
					addBanner('Unknown Error While Posting.<br>Your post did not get posted.');
				}
			}
		?>
		<script>
			function setTime() {
				var time=new Date();
				var hr=time.getHours()+"";
				var min=time.getMinutes()+"";
				var sec=time.getSeconds()+"";
				if(hr.length<2) {
					hr=0+hr;
				}
				if(min.length<2) {
					min=0+min;
				}
				if(sec.length<2) {
					sec=0+sec;
				}
				$(".time")[0].innerHTML=hr+":"+min+":"+sec+", "+getTimeOnServer().yr;
			}
			setFuncs.push(setTime);
			setInterval(setTime,500);
		</script>
		<?php
			if($loggedin) {
				echo '<style>.card{background-color:#'.$backcolor.'; color:#'.$forecolor.';}</style>';
			}
		?>
		<?php if($loggedin) {echo '<script>username="'.$_SESSION['username'].'"; loggedin=true;</script>';} else {echo '<script>loggedin=false;</script>';} ?>
		<?php
			if(!$loggedin){echo  '<meta http-equiv="refresh" content="0; URL=./login.php">';}
			elseif($permissions<1){echo  '<meta http-equiv="refresh" content="0; URL=./blog.php">';}
		?>
		<style>
			input[type=submit] {
				margin-left: 1rem;
			}
		</style>
		<div id="postoptions">
			<form method="POST">
				<div class="card" user="'+data.posts[i].username+'">
					<div class="postmeta">
						<div class="h"><?php echo $_SESSION['username']; ?></div>
						<div class="topic">
							<?php if(isset($_POST['topic'])) {echo '<select name="topic" value="'.$_POST['topic'].'">';} else {echo '<select name="topic">';} ?>
								<?php $topicCount=count($topics); for($i=0; $i<$topicCount; $i++) {echo '<option value="'.$topics[$i].'">'.$topics[$i].'</option>';} ?>
							</select>
							<small>(Click to change.)</small>
						</div>
						<div class="time">12:00:00</div>
					</div>
					<div class="stuffing"><textarea placeholder="Type Here." id="texttyping" name="content" required><?php if(isset($_POST['content'])) {
							echo $_POST['content'];
						} ?></textarea></div>
				</div>
				<input type="submit" name="posting" value="Post">
			</form>
		</div>
<?php require 'pageEnd.php';?>