<?php require 'pageStart.php'; ?>
		<title>Edit Post - AmospiaCraft</title>
		<?php
			if(isset($_SESSION['username']) && $permissions>0 && isset($_POST['editting'])) {
				$postquery=$pdo->prepare("SELECT `username` FROM `mcstuff`.`posts` WHERE `id`=?;");
				$postquery->bindValue(1, $_POST['id'], PDO::PARAM_INT);
				if($postquery->execute() && $postquery->fetch()[0]==$_SESSION['username']) {
					$tags=',';
					preg_match_all('/\B#[a-zA-Z0-9]+\b/', $_POST['content'], $matches);
					$matchCount=count($matches[0]);
					for($i=0; $i<$matchCount; $i++) {
							
						$testtag=trim(substr($matches[0][$i],1));
						if($testtag!='') {
							$tags.=$testtag.',';
						}
					}
					$sql=$pdo->prepare("UPDATE `mcstuff`.`posts` SET `topic`=:topic,`tags`=:tags,`content`=:content WHERE `id`=:id;");
					$sql->bindValue('topic', $_POST['topic'], PDO::PARAM_STR);
					$sql->bindValue('tags', $tags, PDO::PARAM_STR);
					$sql->bindValue('content', $_POST['content'], PDO::PARAM_STR);
					$sql->bindValue('id', $_POST['id'], PDO::PARAM_INT);
					$sql->execute();
				}
				echo '<meta http-equiv="refresh" content="0; URL=./blog.php">';
			}
		?>
		<script>
			function setTime() {
				var fullTime=postedTime.split(" ");
				var sentTime=fullTime[0];
				var time=new Date();
				var yr=time.getFullYear()+"";
				var mon=(time.getMonth()+1)+"";
				var day=time.getDate()+"";
				if(mon.length<2) {
					mon=0+mon;
				}
				if(day.length<2) {
					day=0+day;
				}
				var date=yr+"-"+mon+"-"+day;
				if(date==fullTime[0]) {
					sentTime=fullTime[1];
				}
				var t=postedTime.split(/[- :]/);
				var d=Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
				$(".time")[0].innerHTML=sentTime+", "+getTimeOnServer(d).yr;
			}
			setFuncs.push(setTime);
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
		<?php
			if(is_numeric($_GET['id'])) {
				$postquery=$pdo->prepare("SELECT `id`,`username`,`topic`,`tags`,`content`,`time` FROM `mcstuff`.`posts` WHERE `id`=?;");
				$postquery->bindValue(1, $_GET['id'], PDO::PARAM_INT);
				$postquery->execute();
				$row=$postquery->fetch(PDO::FETCH_BOTH);
				$oldid=$row[0];
				$oldusername=$row[1];
				$oldtopic=$row[2];
				$oldtags=$row[3];
				$oldcontent=$row[4];
				$oldtime=$row[5];
				if($oldusername!=$_SESSION['username']) {
					echo '<meta http-equiv="refresh" content="0; URL=./blog.php">';
				}
				echo '<script>postedTime="'.$oldtime.'"</script>';
			}
			else {
				echo '<meta http-equiv="refresh" content="0; URL=./blog.php">';
			}

		?>
		<div id="postoptions">
			<form method="POST">
				<?php echo '<input type="hidden" name="id" value="'.$oldid.'">'; ?>
				<div class="card" user="'+data.posts[i].username+'">
					<div class="postmeta">
						<div class="h"><?php echo $_SESSION['username']; ?></div>
						<div class="topic">
							<select name="topic">
								<?php $topicCount=count($topics); for($i=0; $i<$topicCount; $i++) {echo '<option value="'.$topics[$i].'" '.($oldtopic==$topics[$i]?'selected="selected"':'').'>'.$topics[$i].'</option>';} ?>
							</select>
							<small>(Click to change.)</small>
						</div>
						<div class="time">12:00:00</div>
					</div>
					<div class="stuffing"><textarea placeholder="Type Here." id="texttyping" name="content" required><?php echo $oldcontent; ?></textarea></div>
				</div>
				<input type="submit" name="editting" value="Save">
			</form>
		</div>
<?php require 'pageEnd.php';?>