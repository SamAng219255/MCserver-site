<?php
	session_set_cookie_params(['samesite' => 'Secure']);
	try {
		session_start();
	}
	catch(Exception $e) {
		session_unset();
		session_destroy();
	}
	if ((isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) || (!isset($_SESSION['last_active']) && isset($_SESSION['loggedin']))) {
		unset($_SESSION['last_active']);
		unset($_SESSION['loggedin']);
		unset($_SESSION['username']);
		unset($_SESSION['permissions']);
		unset($_SESSION['nation']);
		unset($_SESSION['topic']);
		unset($_SESSION['tag']);
		unset($_SESSION['poster']);
	}
	if(isset($_POST['logout'])) {
		unset($_SESSION['last_active']);
		unset($_SESSION['loggedin']);
		unset($_SESSION['username']);
		unset($_SESSION['permissions']);
		unset($_SESSION['nation']);
		unset($_SESSION['topic']);
		unset($_SESSION['tag']);
		unset($_SESSION['poster']);
	}
	$_SESSION['last_active']=time();
	$loggedin=false;
	require 'db.php';
	function addBanner($bannerTxt) {
		echo '<div class="banner">'.$bannerTxt.'</div>';
	}
	if(isset($_SESSION['username'])) {
		$query="SELECT `username`,`uuid`,`permissions`,`forecolor`,`backcolor`,`nation`,`character`,`prefix`,`suffix`,`skin` FROM `mcstuff`.`users` WHERE `username`='".$_SESSION['username']."';";
		if($queryresult=mysqli_query($conn,$query)) {
			$row=mysqli_fetch_row($queryresult);
			$uuid=$row[1];
			$_SESSION['permissions']=$permissions=intval($row[2]);
			$forecolor=$row[3];
			$backcolor=$row[4];
			$_SESSION['nation']=$nation=$row[5];
			$character=$row[6];
			$prefix=$row[7];
			$suffix=$row[8];
			$loggedin=true;
		}
	}
	require 'model.php';
	$setupMethod='General';
	$hasPosts=array('blog.php');
	$hasSortInit=array('edit.php','post.php');
	$hasSort=array_merge($hasSortInit,$hasPosts);
	$hasNations=array('nations.php','relations.php','nation_edit.php');
	$pathstuff=explode('/',$_SERVER['SCRIPT_NAME']);
	$currentPage=$pathstuff[count($pathstuff)-1];
	if(in_array($currentPage,$hasSort)) {
		$setupMethod='NoPosts';
		$topics=array('General','War','Trade','Alliances','Politics','Characters','History','Physics','Meta');
		if($loggedin && $permissions>0) {
			array_push($topics,'Admin');
		}
		if(in_array($currentPage,$hasPosts)) {
			$setupMethod='';
			if(isset($_GET['topic']) && trim($_GET['topic'])!='') {
				$_SESSION['topic']=$_GET['topic'];
			}
			else {
				unset($_SESSION['topic']);
			}
			if(isset($_GET['tag']) && trim($_GET['tag'])!='') {
				$_SESSION['tag']=$_GET['tag'];
			}
			else {
				unset($_SESSION['tag']);
			}
			if(isset($_GET['poster']) && trim($_GET['poster'])!='') {
				$_SESSION['poster']=$_GET['poster'];
			}
			else {
				unset($_SESSION['poster']);
			}
		}
	}
	require 'dailyrefresh.php';
	function comma($str) {
		return strrev(preg_replace('/\d{3}(?=\d)/','$0,',strrev($str)));
	}
?>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="./theme.css">
	<link rel="stylesheet" type="text/css" href="./model.css">
	<?php
		if(in_array($currentPage,$hasSort)) {
			echo '<link href="./img/sign.png" rel="shortcut icon">';
		}
		else {
			echo '<link href="./img/icon.png" rel="shortcut icon">';
		}
	?>
	<script src="jquery.js"></script>
	<script src="pxem.jQuery.js"></script>
	<script src="getTimeOnServerOld.js"></script>
	<script src="getTimeOnServer.js"></script>
	<script src="loadposts.js"></script>
	<script src="mobileDetect.js"></script>
	<script>
		function showMenu(targetMenu) {
			$("#"+targetMenu+"Menu.mobilemenu").addClass("shown");
		}
		function hideMenu(targetMenu) {
			$("#"+targetMenu+"Menu.mobilemenu").removeClass("shown");
		}
		function setupMobile() {
			wasSmall=true;
			menuButton=document.createElement("span");
			$("#pages").prepend(menuButton);
			menuButton.onclick=function(){showMenu("navigation")};
			$(".mobilemenu>a").click(function(){hideMenu('navigation')});
			$("#tagchoice>div.dropdown.dropdown-left").addClass("dropdown-right");
			$("#tagchoice>div.dropdown.dropdown-left").removeClass("dropdown-left");
		}
		windowMoving=false;
		window.onresize = function(e) {
			if(windowMoving) {
				clearInterval(movingWindow);
			}
			else {
				windowMoving=true;
			}
			movingWindow=setTimeout(function() {
				windowMoving=false;
				smallScreen=$(innerWidth).toEm()<49.625;
				if(smallScreen && !wasSmall) {
					setupMobile();
				}
				else if(!smallScreen) {
					menuButton.remove();
					wasSmall=false;
					$("#tagchoice>div.dropdown.dropdown-right").addClass("dropdown-left");
					$("#tagchoice>div.dropdown.dropdown-right").removeClass("dropdown-right");
				}
			},100);
		};
		smallScreen=$(innerWidth).toEm()<49.625;
		wasSmall=smallScreen;
		if(isNaN($(innerWidth).toEm())) screenCheck=setInterval(function(){if(!isNaN($(innerWidth).toEm())) {clearInterval(screenCheck); smallScreen=$(innerWidth).toEm()<49.625; wasSmall=smallScreen; if(smallScreen) {setupMobile()}}},0);
		else setupMobile();
	</script>
	<script src="april_first.js"></script>
	<?php if($loggedin) {echo '<script>username="'.$_SESSION['username'].'"; loggedin=true; isAdmin='.($loggedin && $permissions>0 ? 'true' : 'false').';</script>';} else {echo '<script>loggedin=false; isAdmin='.($loggedin && $permissions>0 ? 'true' : 'false').';</script>';}?>
	<style>#profile{cursor: initial;}</style>
</head>
<?php echo '<body onload="setup'.$setupMethod.'()">' ?>
	<div id="bannerholder"></div>
	<?php if($setupMethod=='') {echo '<style id="userstyles"></style>';} ?>
	<?php
		if(in_array($currentPage,$hasPosts)) {
			//echo '<div id="blocker" onclick="hideBlocker()">Do you like TNT?<br>(Click anywhere to continue to the page.)</div>';
		}
	?>
	<div id="navbarwrapper">
		<div id="navigation">
			<div id="pages">
				<a href="./">
					<span>AmsopiaCraft</span>
				</a>
				<a href="./blog.php">
					<span>Posts</span>
				</a>
				<a href="./map/" target="_blank">
					<span>Map</span>
				</a>
				<a href="./people.php">
					<span>People</span>
				</a>
				<a href="./nations.php">
					<span>Nations</span>
				</a>
			</div>
			<div id="options">
				<div id="profile">
					<div class="lighten"></div>
					<div class="dropdown">
						<?php
							if($loggedin) {
								echo '<a href="post.php">Create Post</a>';
								echo '<a href="profile.php">Profile</a>';
								echo '<a><form method="POST"><input name="logout" type="submit" value="Log Out"></form></a>';
							}
							else {
								echo '<a href="login.php">Log In</a>';
							}
						?>
					</div>
				</div>
				<?php //if($loggedin && $permissions>0) {echo '<div id="postbutton"><a href="post.php"><div class="lighten"></div></a></div>';}?>
			</div>
		</div><?php
		if($loggedin && $permissions>0) require 'statusBar.php';
		if(in_array($currentPage,$hasSort)) require 'sortBar.php';
		if(in_array($currentPage,$hasNations)) require 'nationBar.php';
		?>
	</div>
	<div id="wrapper"><div id="wrapper2">
		<div id="stars1"></div>
		<div id="stars2"></div>
		<div id="stars3"></div>