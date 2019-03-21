<?php require 'pageStart.php'; ?>
		<title>Profile - AmospiaCraft</title>
		<div id="settings">
			<div id="settingscontainer">
				<input type="button" id="updateskin" value="Update Skin"><br>
				<span id="updateskinstatus"></span>
				<script>
					document.getElementById("updateskin").addEventListener("click",function(){
						updateskinstatus=document.getElementById("updateskinstatus");
						updateskinstatus.innerHTML="Updating Skin.";
						setTimeout(function(){
							if(updateskinstatus.innerHTML=="Updating Skin.") {
								updateskinstatus.innerHTML="Updating Skin. (This may take a while.)";
							}
						},5000);
						$.getJSON("getSkin.php?new",function(data) {
							console.log(data);
							if(data.updated) {
								updateskinstatus.innerHTML="Your skin has been updated.";
								profileIcon.style="background-image: url("+data.skin+"), url("+data.skin+");";
							}
							else {
								updateskinstatus.innerHTML="I'm sorry. The server was not able to access your skin.";
							}
						})
					})
				</script>
				<?php if($loggedin && $permissions>0){ require 'settings.php'; } ?>
				<form method="POST"><input name="logout" type="submit" value="Log Out"></form>
			</div>
		</div>
<?php require 'pageEnd.php';?>