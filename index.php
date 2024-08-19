<?php
require 'pageStart.php';
?>

		<title>AmospiaCraft</title>
		<div class="body">
			<div class="card">
				<div class="postmeta">
					<div class="time" id="time" style="display: inline-block"></div>
					<script>
						timeElem=document.getElementById("time");
						timeElem.onclick=function(e) {
							$("body").toggleClass("show-maya");
						}
						getTimeOnServer(function(date) {
							timeObj=date;
							timeElem.innerHTML=`In-Game Date: <span class="non-maya">${timeObj}</span><span class="maya">${timeObj.maya}</span>`;
						});
					</script>
				</div>
				<div class="stuffing">	AmospiaCraft, found at adventure.amospia.com:25564, is an occsionally laggy vanilla minecraft server where me, my friends and my friends' friends build many things from massive empires with eleborate cultures, histories, and international relations to random chess boards in a black void, a sword in a stone, and a decent city built to 1/4 scale.
	I intend to open this server, at the very least, for viewing by the public but a couple matters need ironing out first.</div>
			</div>

<?php
require 'pageEnd.php';
?>
