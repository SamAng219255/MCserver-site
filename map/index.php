<?php
	session_start();
	if ((isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) || (!isset($_SESSION['last_active']) && isset($_SESSION['loggedin']))) {
		session_unset();
		session_destroy();
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
			$nation=$row[5];
			$character=$row[6];
			$prefix=$row[7];
			$suffix=$row[8];
			$loggedin=true;
		}
	}
	else {
		$uuid=0;
		$_SESSION['permissions']=$permissions=0;
		$forecolor="000000";
		$backcolor="404040";
		$nation="";
		$character="";
		$prefix="";
		$suffix="";
	}
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="./theme.css">
	<script src="../jquery.js"></script>
	<script src="../pxem.jQuery.js"></script>
	<script src="../jquery.form.min.js"></script>
	<script src="hammer.min.js"></script>
	<script src="../mobileDetect.js"></script>
	<script src="tiling.js"></script>
	<title>Map - AmospiaCraft</title>
	<link href="../img/icon.png" rel="shortcut icon">
	<?php if($permissions>0) echo '<script>isAdmin=true;</script>'; echo '<script>nation="'.$nation.'";</script>'; ?>
	<!--<meta name="viewport">-->
	<style id="nationstyles"></style>
</head>
<body onkeydown="return move(event)" onload="setup();" style="touch-action:none" id="body">
	<div id="bannerholder"></div>
	<canvas height=640 width=1152 id="mcmap"></canvas>
	<div id="tileStorage"><img id="default-img" src="img/stone.png"></div>
	<div id="infoTxtBox"><div id="infoTxt"></div><div id="editpin">Edit</div></div>
	<div id="jumpMenu">
		<div onclick="closeJumpMenu()"></div>
		<div>
			<div onkeydown="jumpCoordFunc(event)" id="jumpCoordForm">
				<label for="jumpCoordX">X: </label>
				<input type="number" placeholder="x" id="jumpCoordX" step="1">
				&nbsp;&nbsp;
				<label for="jumpCoordZ">Z: </label>
				<input type="number" placeholder="z" id="jumpCoordZ" step="1">
				<br>
				<label for="jumpCoordD">Dimension: </label>
				<input type="number" placeholder="Dim." id="jumpCoordD" step="1" value="0">
			</div>
			<div class="divider"><span>OR</span></div>
			<div id="jumpPinForm">
				<label for="jumpPin">Pin Name: </label>
				<select type="text" onchange="jumpPinFunc(event)" placeholder="Pin Name" id="jumpPin"><option value=''></option></select>
			</div>
		</div>
	</div>
	<div id="trpvMenu" class="trpMenu">
		<div onclick="closeTrpMenu(0)"></div>
		<div>
			<dl>
				<dt>Name:</dt>
				<dd class="trp-name"></dd>
				<dt>Nation:</dt>
				<dd class="trp-owner"></dd>
				<dt>Number of Units:</dt>
				<dd class="trp-size"></dd>
				<dt>Strength:</dt>
				<dd class="trp-power"></dd>
				<dt>Health:</dt>
				<dd class="trp-health"></dd>
				<dt>Level:</dt>
				<dd class="trp-lvl"></dd>
				<dt>Type:</dt>
				<dd class="trp-type"></dd>
				<td>Position:</td>
				<dd class="trp-position"></dd>
				<td>Commander Bonuses:</td>
				<dd class="trp-bonuses"></dd>
				<dt>Movement:</dt>
				<dd class="trp-move"></dd>
				<dt>Remaining Movement:</dt>
				<dd class="trp-moveleft"></dd>
				<dt>Sprite:</dt>
				<dd><canvas height="64" width="64" id="trpvspritecnv"></canvas></dd>
			</dl>
			<input type="submit" value="Edit" onclick="switchEdit()" id="editbtn">
		</div>
	</div>
	<div id="trpeMenu" class="trpMenu">
		<div onclick="closeTrpMenu(1)"></div>
		<div>
			<dl>
				<dt>Name:</dt>
				<dd><input type="text" id="trpe-name"></dd>
				<dt>Nation:</dt>
				<dd><input type="text" id="trpe-owner"></dd>
				<dt>Number of Units:</dt>
				<dd class="trp-size"></dd>
				<dt>Strength:</dt>
				<dd class="trp-power"></dd>
				<dt>Health:</dt>
				<dd class="trp-health"></dd>
				<dt>Level:</dt>
				<dd class="trp-lvl"></dd>
				<dt>Type:</dt>
				<dd class="trp-type"></dd>
				<td>Position:</td>
				<dd class="trp-position"></dd>
				<td>Commander Bonuses:</td>
				<dd class="trp-bonuses"></dd>
				<dt>Movement:</dt>
				<dd class="trp-move"></dd>
				<dt>Remaining Movement:</dt>
				<dd class="trp-moveleft"></dd>
				<dt>Sprite:</dt>
				<dd><canvas height="64" width="64" id="trpespritecnv"></canvas></dd>
			</dl>
			<input type="submit" value="Save" onclick="editTrp()">
			<input type="submit" value="Delete" onclick="deleteTrp()">
		</div>
	</div>
	<div id="trpnMenu" class="trpMenu">
		<div onclick="closeTrpMenu(2)"></div>
		<div>
			<dl>
				<dt>Name:</dt>
				<dd><input type="text" id="trpn-name"></dd>
				<dt>Nation:</dt>
				<dd><input type="text" id="trpn-owner"></dd>
				<dt>Number of Units:</dt>
				<dd><input type="number" class="trpn-calc" id="trpn-size"></dd>
				<dt>Resources:</dt>
				<dd><input type="number" class="trpn-calc" id="trpn-cost"></dd>
				<dd title="Increases cost by 50% but doubles movement and allows a Hit &amp; Run attack. Used for units focused on maneuverability.">Mobility Unit: <input type="checkbox" class="trpn-calc" id="trpn-mobility"></dd>
				<dt>Strength:</dt>
				<dd id="trpn-power"></dd>
				<dd title="Halves the strength of the unit but allows a long range attack.">Ranged Unit: <input type="checkbox" class="trpn-calc" id="trpn-ranged"></dd>
				<dt>Position:</dt>
				<dd>X:<input type="number" id="trpn-x">, Z:<input type="number" id="trpn-z"></dd>
				<dt>Movement:</dt>
				<dd id="trpn-move"></dd>
				<dt>Sprite:</dt>
				<dd><div id="spritemenu"></div><canvas height="64" width="64" id="trpnspritecnv"></canvas></dd>
				<dd>OR</dd>
				<dd>Upload a new sprite: <form action="uploadSprite.php" method="post" enctype="multipart/form-data" id="uploadarmy"><input type="file" name="sprite" id="spriteupload"><input type="submit" value="Upload"><input type="hidden" name="spritetype" value="army"></form></dd>
			</dl>
			<input type="submit" value="Create" onclick="createTrp()">
		</div>
	</div>
	<div id="commanderMenu">
		<div onclick="closeCommanderMenu()"></div>
		<div>
			<div class="tab active" id="tab-1">
				<div class="tabbit tab-tab">View</div>
				<div class="tabbit tab-content" id="comm-view">
				</div>
			</div>
			<div class="tab" id="tab-2">
				<div class="tabbit tab-tab">Add</div>
				<div class="tabbit tab-content">
					<dl>
						<dt>Name:</dt>
						<dd><input type="text" id="comm-name"></dd>
						<dt>Nation:</dt>
						<dd><input type="text" id="comm-ntn"></dd>
						<dt>Specials:</dt>
						<dd><ul id="comm-spec"></ul></dd>
					</dl>
					<input type="submit" value="Add" onclick="createComm()">
				</div>
			</div>
			<div class="tab" id="tab-3">
				<div class="tabbit tab-tab">Manage</div>
				<div class="tabbit tab-content" id="comm-manage">
				</div>
			</div>
		</div>
	</div>
	<div id="instr" onclick="closeInstMenu()" class="hide">
		<div>
			<p>Controls:</p>
			<ul>
				<li>Use the <b>Arrow Keys</b> or the <b>WASD</b> Keys to move the map and the <b>Q</b> and <b>E</b> keys to switch dimension or press <b>Shift</b> to jump to a location.</li>
				<li>Use the <b>Scroll Wheel</b> to zoom the map in or out.</li>
				<li>Press <b>Enter</b> to toggle pin visibility.</li>
				<li><b>Click</b> on a pin or map square to show information about that location.</li>
				<li>Press <b>Tab</b> to toggle this menu.</li>
			</ul>
		</div>
	</div>
	<div id="pinMenu">
		<div onclick="closePinMenu()"></div>
		<div>
			<dl>
				<dt>Location Name:</dt>
				<dd><input type="text" id="pin-name"></dd>
				<dt>X Coordinate:</dt>
				<dd><input type="number" id="pin-x"></dd>
				<dt>Z Coordinate:</dt>
				<dd><input type="number" id="pin-z"></dd>
				<dt>Dimension Id:</dt>
				<dd>
					<select id="pin-dimen">
						<option value="0">Overworld</option>
						<option value="-1">Nether</option>
						<option value="1">The End</option>
					</select>
				</dd>
				<dt>Description:</dt>
				<dd><textarea id="pin-desc"></textarea></dd>
				<dt>Icon Type:</dt>
				<dd>
					<select id="pinicon" class="pinicon">
						<option>default</option>
						<option>custom</option>
						<option>hidden</option>
					</select>
				</dd>
				<dt class="icondata icon-default shown">Color:</dt>
				<dd class="icondata icon-default shown"><input type="color" id="pinColor"></dd>
				<dt class="icondata icon-custom">Sprite:</dt>
				<dd class="icondata icon-custom"><div id="pinspritemenu"></div><canvas height="48" width="48" id="pinspritecnv"></canvas></dd>
				<dd class="icondata icon-custom">OR</dd>
				<dd class="icondata icon-custom">Upload a new sprite: <form action="uploadSprite.php" method="post" enctype="multipart/form-data" id="uploadpin"><input type="file" name="sprite"><input type="submit" value="Upload"><input type="hidden" name="spritetype" value="pin"></form></dd>
			</dl>
			<input type="submit" class="submit submit-create" value="Create" onclick="createPin()">
			<input type="submit" class="submit submit-change" value="Save" onclick="changePin()">
			<input type="submit" class="submit submit-change" value="Delete" onclick="deletePin()">
		</div>
	</div>
	<div id="sideMenu">
		<button id="infobutton" title="Toggle a screen to show hotkeys."></button>
		<button id="jumpbutton" title="Jump to a coordinate or pin."></button>
		<button id="pinbutton" title="Toggle pin visibility."></button>
		<?php
			if($permissions>0) {
				echo '<button id="addPinbutton" title="Create a new pin."></button>';
				echo '<button id="addTroopbutton" title="Create a new Army."></button>';
				echo '<button id="commanderBtn" title="Manage Comanders."></button>';
			}
			else if(!isset($_SESSION['username'])) {
				echo '<a href="../login.php"><button id="loginButton" title="Log In."></button></a>';
			}
		?>
	</div>
	<div id="jumpMenuMobile">
		<div onclick="closeJumpMenu()"></div>
		<div>
			<div onkeydown="jumpCoordFunc(event)" id="jumpCoordForm">
				<small>Enter a coordinate to jump to that position on the map…</small><br>
				<label for="jumpCoordXMobile">X: </label>
				<input type="number" placeholder="x" id="jumpCoordXMobile" step="1">
				&nbsp;&nbsp;
				<label for="jumpCoordZMobile">Z: </label>
				<input type="number" placeholder="z" id="jumpCoordZMobile" step="1">
				<br>
				<label for="jumpCoordDMobile">Dimension: </label>
				<input type="number" placeholder="Dim." id="jumpCoordDMobile" step="1" value="0">
			</div>
			<div class="divider"><span>OR</span></div>
			<div id="jumpPinForm">
				<small>choose a pin to jump to its location.</small><br>
				<select type="text" onchange="jumpPinFunc(event)" placeholder="Pin Name" id="jumpPinMobile"><option value=''></option></select>
			</div>
		</div>
	</div>
</body>
</html>