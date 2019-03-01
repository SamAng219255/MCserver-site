<html>
<head>
	<link rel="stylesheet" type="text/css" href="./theme.css">
	<script src="../jquery.js"></script>
	<script src="hammer.min.js"></script>
	<script src="mobileDetect.js"></script>
	<script src="tiling.js"></script>
	<title>Map - AmospiaCraft</title>
	<link href="../img/icon.png" rel="shortcut icon">
	<!--<meta name="viewport">-->
</head>
<body onkeydown="return move(event)" onload="setup();" style="touch-action:none" id="body">
	<canvas height=640 width=1152 id="mcmap"></canvas>
	<div id="tileStorage"><img id="default-img" src="img/stone.png"></div>
	<div id="infoTxt"></div>
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
	<div id="instr">
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
	<div id="sideMenu">
		<button id="jumpbutton"></button>
		<button id="pinbutton"></button>
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