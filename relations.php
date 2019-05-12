<?php require './pageStart.php'; ?>
		<title>Relations - AmospiaCraft</title>
		<link rel="stylesheet" type="text/css" href="./gridColors.css">
		<div id="nations">
			<span id="h">Relations Grid</span>
			<div id="gridHold" class="loading"><span>Loading...</span></div>
		</div>
		<script>
			function atl(arr) {
				return (""+arr).replace(/,/g,",\n");
			}
			function buildGrid() {
				$.getJSON("getRelations.php",{},function(data) {
					console.log(relationData=data);
					var grid="<table><tr><th>Nation</th><th>Allies</th><th>Friends</th><th>Friendly</th><th>Neutral</th><th>Unfriendly</th><th>Enemies</th><th>At War</th></tr>";
					for(var i=0; i<data.index.length; i++) {
						if(isAdmin && (data.player[data.index[i]]==undefined || data.player[data.index[i]]==username))
							grid+="<tr class=\"editrow\">";
						else
							grid+="<tr>";
						grid+="<td>"+data.index[i]+"</td>";
						for(var j=0; j<7; j++) {
							grid+="<td>"+atl(data.relations[data.index[i]][j])+"</td>";
						}
						grid+="</tr>";
					}
					grid+="</table>";
					$("#gridHold").empty();
					$("#gridHold").removeClass("loading");
					$("#gridHold").append(grid);
					$("#gridHold .editrow td:not(:nth-child(1)):not(:nth-child(9))").click(editRow);
					console.log("Grid built.");
				});
			}
			setFuncs.push(buildGrid);
			function editRow(e) {
				if (e.target !== this)
					return;
				edittingNation=this.parentElement.children[0].innerHTML;
				var col=editCol=[...this.parentElement.children].indexOf(this)-1;
				edittingRow=[...this.parentElement.parentElement.children].indexOf(this.parentElement);
				var newElem=$('<textarea id="relation-'+col+'">'+atl(relationData.relations[edittingNation][col])+'</textarea>')[0];
				newElem.addEventListener("focusout",saveRow);
				$(this).append(newElem);
				newElem.focus();
				newElem.selectionStart=atl(relationData.relations[edittingNation][col]).length;
			}
			function saveRow(e) {
				console.log("save",foobarbaz=e);
				$.getJSON("setRelations.php",{nation1:edittingNation,nation2:$(this).val().replace(/(?:, ?\n|,| ?\n) ?/g,",").split(","),relation:editCol,row:edittingRow},function(data) {
					console.log(data);
					if(data.response.status==0) {
						//relationData.relations[data.input.nation1][data.input.relation]=data.input.nation2;
						//$("#gridHold tr:nth-child("+(parseInt(data.input.row)+1)+")>td:nth-child("+(parseInt(data.input.relation)+2)+")").text(atl(relationData.relations[data.input.nation1][data.input.relation]));
						buildGrid();
					}
					else {
						addBanner(data.response.text);
					}
				});
				$(this).remove();
			}
		</script>
<?php require './pageEnd.php';?>