setFuncs=[];
bannercount=0;
banners=[];
function setStars() {
	wrapper2=document.getElementById("wrapper2");
	stars1=document.getElementById("stars1");
	stars2=document.getElementById("stars2");
	stars3=document.getElementById("stars3");
	stars1.style.height=Math.max(wrapper2.scrollHeight,stars1.clientHeight);
	stars2.style.height=Math.max(wrapper2.scrollHeight,stars2.clientHeight);
	stars3.style.height=Math.max(wrapper2.scrollHeight,stars3.clientHeight);
}
function setup() {
	setupNoPosts();
	posts={};
	textBox=$("#text");
	updatePosts();
	updateInterval=setInterval(updatePosts,500);
}
function setupNoPosts() {
	setupGeneral();
	document.getElementById("tagSearchButton").addEventListener('click',tagSearch);
	document.getElementById("tagSearch").addEventListener('keypress',function(e){if(e.which==13||e.keyCode==13){tagSearch()}});
}
function setupGeneral() {
	isThin=window.innerWidth<$(49.625).toPx();
	if(loggedin) {
		profileIcon=document.getElementById("profile");
		$.getJSON("getSkin.php",function(data) {
			profileIcon.style="background-image: url("+data.skin+"), url("+data.skin+");";
		})
	}
	/*if(isAdmin) {
		statusbutton=$("#serverstatus");
		statusInter=setInterval(updateServerStatus,1000);
	}*/
	stellarInterval=setInterval(setStars,100);
	wrapper=document.getElementById("wrapper");
	wrapper.addEventListener("scroll",checkBottom,{passive:true})
	for(var i=0; i<setFuncs.length; i++) {
		(setFuncs[i])();
	}
	//$("#navbarspacer")[0].style="height: "+$("#navbarwrapper")[0].offsetHeight+"px;";
	//wrapper.addEventListener("scroll",function(){$("#navbarwrapper")[0].style="top: "+wrapper.scrollTop+"px;"},{passive:true});isThin=window.innerWidth<$(49.625).toPx();
}
function tagSearch() {
	var gotten=$("#tagSearch").val();
	if((/^[a-z0-9]+$/).test(gotten)) {
		location.href="./blog.php?tag="+gotten;
	}
	else {
		alert("Tags must be alphanumeric. (Only include letters and numbers.)");
	}
}
latestId=-1;
oldestId=Infinity;
loadFinished=false;
function updatePosts() {
	$.getJSON("getPosts.php",{start:latestId,count:25,sort:"ASC"},function(data) {
		for(var i=0; i<data.posts.length; i++) {
			showPost(i,data,"prepend")
		}
		if(data.posts.length>0) {
			oldestId=Math.min(data.posts[0].id,oldestId);
			latestId=data.posts[data.posts.length-1].id;
			loadFinished=true;
		}
		styleBox=document.getElementById("userstyles");
		var styleStr=""
		for(var i=0; i<data.styles.length; i++) {
			styleStr+="\n.card[user="+data.styles[i].username+"] {\n	color: #"+data.styles[i].forecolor+";\n	background-color: #"+data.styles[i].backcolor+";\n}";
		}
		styleStr+="\n";
		if(styleBox.innerHTML!==styleStr) styleBox.innerHTML=styleStr;
	});
}
function checkBottom(e) {
	if(loadFinished && wrapper.scrollTop+wrapper.clientHeight>=wrapper.scrollHeight) {
		getOld();
	}
}
function getOld() {
	$.getJSON("getPosts.php",{start:oldestId,count:25,sort:"DESC"},function(data) {
		for(var i=0; i<data.posts.length; i++) {
			showPost(i,data,"append");
		}
		if(data.posts.length>0) {
			oldestId=data.posts[data.posts.length-1].id;
		}
	});
}
urlRegEx=/(https?:\/\/)?((?:[a-z0-9%]+\.)+(?:[a-z]+))((?:\/[a-z0-9%]+)*)\/?(\?(?:[a-z0-9%]+=[a-z0-9%]+)?(?:&[a-z0-9%]+=[a-z0-9%]+)*)?/gi;
linkRegEx="<a href=\"$&\" target=\"_blank\">$&</a>";
function showPost(i,data,side) {
	var fullTime=data.posts[i].time.split(" ");
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
	var t=data.posts[i].time.split(/[- :]/);
	var d=Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
	textBox[side](genCard(data.posts[i].username,data.posts[i].id,'./people.php?target='+data.posts[i].username,data.posts[i].username,data.posts[i].topic,sentTime,data.posts[i].content.replace(urlRegEx,linkRegEx),data.posts[i].owned));
	$(".delete[card="+data.posts[i].id+"]").click(deletePost);
	var stuffing=$(".card[card="+data.posts[i].id+"] .stuffing");
	if($(stuffing.height()).toEm()>10 || isThin) {
		stuffing.after("<div class=\"show\">Show More</div>");
		$(".card[card="+data.posts[i].id+"] .show").click(toggleShow);
	}
	if($(stuffing.height()).toEm()>10) {
		stuffing.addClass("long");
	}
}
function genCard(styleUser,cardId,href,title,topic,time,content,canEdit) {
	var edit="";
	if(canEdit) edit='<div class="delete" card="'+cardId+'">delete</div>&nbsp<a href="./edit.php?id='+cardId+'" class="edit">edit</a>';
	return '<div class="card" user="'+styleUser+'" card="'+cardId+'">\
		<div class="postmeta">\
			<a class="h" href="'+href+'">'+title+'</a> \
			<div class="time">'+time+'</div>\
			<div class="topic">'+topic+'</div> \
		</div>\
		<div class="stuffing">'+content+'</div>\
		<div class="footer">'+edit+'</div>\
	</div>';
}
function deletePost(e) {
	var target=e.target.attributes.card.value;
	if(confirm("Are you sure you want to delete this post?")) {
		$.get("./deletePost.php",{target:target},function(data){if(data=="true") {$(".card[card="+target+"]").remove()} else {console.log(data)}});
	}
}
function toggleShow(e) {
	$(".card[card="+e.target.parentElement.attributes.card.value+"] .stuffing.long").toggleClass("extended");
	if(isThin) $(".card[card="+e.target.parentElement.attributes.card.value+"] .postmeta").toggleClass("extended");
	if(e.target.innerHTML=="Show More") {
		e.target.innerHTML="Show Less"
	}
	else if(e.target.innerHTML=="Show Less") {
		e.target.innerHTML="Show More"
	}
}
function updateServerStatus() {
	$.getJSON("getServerStatus.php",function(data){
		statusbutton.removeClass("clickable");
		statusbutton.off("click");
		statusbutton.removeClass("on");
		statusbutton.removeClass("off");
		statusbutton.removeClass("failed");
		if(!statusbutton.hasClass("waiting") || data.on) {
			if(data.on) {
				statusbutton.removeClass("waiting");
				statusbutton.addClass("on");
				statusbutton.text("Server is ON.");
			}
			else {
				statusbutton.addClass("off");
				if(isAdmin) {
					statusbutton.text("Server is OFF. Click to turn on.");
					statusbutton.addClass("clickable");
					statusbutton.click(turnOnServer);
				}
				else {
					statusbutton.text("Server is OFF.");
				}
			}
		}
	}).fail(function( jqxhr, textStatus, error ) {
		statusbutton.addClass("fail");
		statusbutton.text("Failed to get server status.");
		clearInterval(statusInter);
		var err = textStatus + ", " + error;
		console.log( "Request Failed: " + err );
	});
}
function turnOnServer() {
	$.getJSON("turnOnServer.php",function(data){

	}).fail(function( jqxhr, textStatus, error ) {
		var err = textStatus + ", " + error;
		console.log( "Request Failed: " + err );
	});
	statusbutton.text("Turning on the server. This may take a minute.");
	statusbutton.addClass("waiting");
	setTimeout(function(){statusbutton.removeClass("waiting")},60000);
}
function addBanner(txt) {
	$("#bannerholder").append("<div class=\"scriptbanner\" id=\"banner-"+bannercount+"\">"+txt+"</div>");
	banners.push(bannercount);
	bannercount++;
	setTimeout(removeBanner,5000);
}
function removeBanner() {
	$("#banner-"+banners.splice(0,1)[0]).remove();
}
