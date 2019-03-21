<?php require './pageStart.php'; ?>
		<?php if(isset($_POST['editting'])) {require 'post_nation_edits.php';} ?>
		<style>
			#nation.card>.stuffing {
				white-space: normal;
			}
			#nation.card>.stuffing .desc {
				white-space: pre-wrap;
			}
			#resources {
				box-shadow: inset 0 0 10px #000000;
				background-color: rgba(0,0,0,0.25);
				padding: 1rem;
				border-radius: 0.5rem;
				white-space: pre;
			}
			#resources>span {
				font-weight: bold;
				font-size: 1.5em;
				margin: 2rem;
			}
			.card table th {
				text-align: left;
			}
			#addresource {
				cursor: pointer;
			}
			input[type=submit] {
				margin-left: 1rem;
			}
		</style>
		<script>
			newCardId=0;
			function addresource() {
				$.get("./echo_resource_card.php",{"argv":true,"nation":nation,"unit":"gp","type":"Gold","ntnlwlth":"1000000","ctznwlth":"1000","ntnlincome":"10000","ctznincome":"10","tax":"2","showwlth":"false","showncm":"false","showntnl":"false","showctzn":"false","showtax":"false","desc":"","i":newCardId},addresourceTrue);
				newCardId++;
				$("*[name=resourcecount]").val(newCardId);
			}
			function addresourceTrue(data) {
				$("#resourceholder").append(data);
			}
			function deleteresource(targetCardId) {
				$("#resource-"+targetCardId).remove();
				for(var i=targetCardId+1; i<newCardId; i++) {
					$("#resource-"+i).attr("id","resource-"+(i-1));
					$("*[name=desc-"+i+"]").attr("name","desc-"+(i-1));
					$("*[name=ntnlwlth-"+i+"]").attr("name","ctnlwlth-"+(i-1));
					$("*[name=ctznwlth-"+i+"]").attr("name","ctznwlth-"+(i-1));
					$("*[name=ntnlincome-"+i+"]").attr("name","ntnlincome-"+(i-1));
					$("*[name=ctznincome-"+i+"]").attr("name","ctznincome-"+(i-1));
					$("*[name=tax-"+i+"]").attr("name","tax-"+(i-1));
					$("*[name=showwlth-"+i+"]").attr("name","showwlth-"+(i-1));
					$("*[name=showncm-"+i+"]").attr("name","showncm-"+(i-1));
					$("*[name=showntnl-"+i+"]").attr("name","showntnl-"+(i-1));
					$("*[name=showctzn-"+i+"]").attr("name","showctzn-"+(i-1));
					$("*[name=showtax-"+i+"]").attr("name","showtax-"+(i-1));
					$("*[onclick=\"deleteresource("+i+")\"]").attr("onclick","deleteresource("+(i-1)+")");
				}
				newCardId--;
				$("*[name=resourcecount]").val(newCardId);
			}
		</script>
		<title>Nations - AmospiaCraft</title>
		<form method="POST" enctype="multipart/form-data"><div id="nations">
			<?php

				$nationquery="SELECT `name`,`ruler`,`hasflag`,`population`,`parent`,`desc`,`showruler`,`showflag`,`showpopul`,`showparent` FROM `mcstuff`.`nations` WHERE `name`='".mysqli_real_escape_string($conn,$_GET['nation'])."';";
				$nationqueryresult=mysqli_query($conn,$nationquery);
				$nationrow=mysqli_fetch_row($nationqueryresult);
				$name=$nationrow[0];
				$ruler=$nationrow[1];
				$hasflag=$nationrow[2]==1;
				$population=intval($nationrow[3]);
				$parentcountry=$nationrow[4];
				if($parentcountry=='') $parentcountry='none';
				$description=$nationrow[5];
				$showruler=(($ruler!='' || $nationrow[6]=='show') && $nationrow[6]!='hide');
				$showflag=(($hasflag || $nationrow[7]=='show') && $nationrow[7]!='hide');
				$showpopul=(($population!=0 || $nationrow[8]=='show') && $nationrow[8]!='hide');
				$showparent=(($parentcountry!='none' || $nationrow[9]=='show') && $nationrow[9]!='hide');
				$showany=$showruler || $showflag || $showpopul || $showparent;
				$rulerquery="SELECT `username`,`forecolor`,`backcolor`,`character`,`prefix`,`suffix`,`skin` FROM `mcstuff`.`users` WHERE `username`='".$ruler."';";
				$rulerqueryresult=mysqli_query($conn,$rulerquery);
				$rulerrow=mysqli_fetch_row($rulerqueryresult);
				$forecolor=$rulerrow[1];
				$backcolor=$rulerrow[2];
				$character=$rulerrow[3];
				$prefix=$rulerrow[4];
				$suffix=$rulerrow[5];
				$skin=$rulerrow[6];

				if($_SESSION['username']!=$ruler) {
					echo '<meta http-equiv="refresh" content="0; URL=./nations.php">';
				}

			?>
			<?php echo '<style>.card {color: #'.$forecolor.';background-color: #'.$backcolor.';}</style>'; ?>
			<div class="card" id="nation">
				<div class="postmeta">
					<div class="h">
						<?php echo '<input type="text" name="name" value="'.$name.'">'; ?>
					</div>
				</div>
				<div class="stuffing">
					<?php echo '<textarea class="desc" name="desc" placeholder="Description">'.$description.'</textarea>'; ?>
					<div id="data">
						<dl>
							<dt>Ruler:</dt>
								<dd><?php echo $prefix.' '.$character.' '.$suffix.' ('.$ruler.')'; ?></dd>
								<dd><?php
									if($nationrow[6]=='show') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="show" name="showruler"'.$checked.'> Show</label>';
									if($nationrow[6]=='hide') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="hide" name="showruler"'.$checked.'> Hide</label>';
									if($nationrow[6]=='false') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="false" name="showruler"'.$checked.'> Auto</label>';
								?></dd>
							<dt>Parent Country:</dt>
								<dd><?php echo '<input type="text" name="parent" value="'.$parentcountry.'">'; ?></dd>
								<dd><?php
									if($nationrow[9]=='show') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="show" name="showparent"'.$checked.'> Show</label>';
									if($nationrow[9]=='hide') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="hide" name="showparent"'.$checked.'> Hide</label>';
									if($nationrow[9]=='false') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="false" name="showparent"'.$checked.'> Auto</label>';
								?></dd>
							<dt>Flag:</dt>
								<dd>
									<?php echo '<img id="flagimg" src="./img/flags/'.$name.'">'; ?>
									<br>
									<input type="file" id="flag" name="flag">
								</dd>
								<dd><?php
									if($nationrow[7]=='show') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="show" name="showflag"'.$checked.'> Show</label>';
									if($nationrow[7]=='hide') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="hide" name="showflag"'.$checked.'> Hide</label>';
									if($nationrow[7]=='false') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="false" name="showflag"'.$checked.'> Auto</label>';
								?></dd>
							<dt>Population:</dt>
								<dd><?php echo '<input type="number" name="population" value="'.$population.'">'; ?></dd>
								<dd><?php
									if($nationrow[8]=='show') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="show" name="showpopul"'.$checked.'> Show</label>';
									if($nationrow[8]=='hide') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="hide" name="showpopul"'.$checked.'> Hide</label>';
									if($nationrow[8]=='false') $checked='checked';
									else $checked='';
									echo '<label><input type="radio" value="false" name="showpopul"'.$checked.'> Auto</label>';
								?></dd>
						</dl>

						<?php
							$resourcequery="SELECT `nation`,`unit`,`type`,`ntnlwlth`,`ctznwlth`,`ntnlincome`,`ctznincome`,`tax`,`showwlth`,`showncm`,`showntnl`,`showctzn`,`showtax`,`desc` FROM `mcstuff`.`resources` WHERE `nation`='".mysqli_real_escape_string($conn,$name)."';";
							$resourcequeryresult=mysqli_query($conn,$resourcequery);
							echo '<div id="resources"><span id="h">Resources:</span><div id="resourceholder">';
							require 'echo_resource_card.php';
							for($i=0; $i<$resourcequeryresult->num_rows; $i++) {
								$row=mysqli_fetch_row($resourcequeryresult);
								echoResourceCard($row,$i);
							}
							echo '</div><script>newCardId='.$resourcequeryresult->num_rows.'; nation="'.$name.'"</script><input type="hidden" name="resourcecount" value="'.$resourcequeryresult->num_rows.'">';
							echo '
<span id="addresource" onclick="addresource()">Add Resource</span>';
							echo '</div>';
						?>

					</div>
				</div>
				<div class="footer">
					
				</div>
			</div>
			<input type="submit" name="editting" value="Save">
		</div></form>
<?php require './pageEnd.php';?>