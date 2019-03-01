<?php
require 'pageStart.php';
?>

		<div class="body">
			<div class="card">
				<div class="postmeta">
					<div class="time" id="time"></div>
					<script>
						timeElem=document.getElementById("time");
						timeObj=getTimeOnServer();
						timeElem.innerHTML="In-Game Date: "+timeObj.day+" "+timeObj.monStr+" "+timeObj.yr;
						timeInter=setInterval(function(){
							timeObj=getTimeOnServer();
							timeElem.innerHTML="In-Game Date: "+timeObj.day+" "+timeObj.monStr+" "+timeObj.yr;
						},1000);
					</script>
				</div>
				<div class="stuffing">	AmospiaCraft, found at adventure.amospia.com:25564, is an occsionally laggy vanilla minecraft server where me, my friends and my friends' friends build many things from massive empires with eleborate cultures, histories, and international relations to random chess boards in a black void, a sword in a stone, and a decent city built to 1/4 scale.
	I intend to open this server, at the very least, for viewing by the public but a couple matters need ironing out first.</div>
			</div>
			<div class="card">
				<table>
					<tr>
						<td rowspan="2" style="background-image: url('./img/pack.png'); background-size: auto 100%; background-position: center; width: 10%; background-repeat: no-repeat;">
						</td>
						<td>
							<div class="postmeta">
								<div class="h">Resource Pack</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="stuffing">I've spent much work on this pack. Every texture and model was either selected from the upcoming official textures or built by me.
<a href="resources.zip">Download</a></div>
						</td>
					</tr>
				</table>
			</div>
		</div>

<?php
require 'pageEnd.php';
?>