setFuncs=[];
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
	if(loggedin) {
		profileIcon=document.getElementById("profile");
		$.getJSON("getSkin.php",function(data) {
			profileIcon.style="background-image: url("+data.skin+"), url("+data.skin+");";
		})
	}
	stellarInterval=setInterval(setStars,100);
	wrapper=document.getElementById("wrapper");
	wrapper.addEventListener("scroll",checkBottom,{passive:true})
	for(var i=0; i<setFuncs.length; i++) {
		(setFuncs[i])();
	}
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
		styleBox.innerHTML=styleStr;
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
urlRegEx=/(https?:\/\/)?((?:[a-z0-9%]+)(?:\.[a-z0-9%]+)+)((?:\/[a-z0-9%]+)*)\/?(\?(?:[a-z0-9%]+=[a-z0-9%]+)?(?:&[a-z0-9%]+=[a-z0-9%]+)*)?/gi;
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
	var edit="";
	if(data.posts[i].owned) edit='<div class="delete" card="'+data.posts[i].id+'">delete</div>&nbsp<a href="./edit.php?id='+data.posts[i].id+'" class="edit">edit</a>';
	textBox[side]('<div class="card" user="'+data.posts[i].username+'" card="'+data.posts[i].id+'"><div class="postmeta"><a class="h" href="./people.php?target='+data.posts[i].username+'">'+data.posts[i].username+'</a> <div class="topic">'+data.posts[i].topic+'</div> <div class="time">'+sentTime+', '+getTimeOnServer(d).yr+'</div></div><div class="stuffing">'+data.posts[i].content.replace(urlRegEx,linkRegEx)+'</div><div class="footer">'+edit+'</div></div>');
	$(".delete[card="+data.posts[i].id+"]").click(deletePost);
	var stuffing=$(".card[card="+data.posts[i].id+"] .stuffing");
	if($(stuffing.height()).toEm()>10) {
		stuffing.addClass("long");
		stuffing.after("<div class=\"show\">Show More</div>");
		$(".card[card="+data.posts[i].id+"] .show").click(toggleShow);
	}
}
function deletePost(e) {
	var target=e.target.attributes.card.value;
	if(confirm("Are you sure you want to delete this post?")) {
		$.get("./deletePost.php",{target:target},function(data){if(data=="true") {$(".card[card="+target+"]").remove()} else {console.log(data)}});
	}
}
function toggleShow(e) {
	$(".card[card="+e.target.parentElement.attributes.card.value+"] .stuffing").toggleClass("extended");
	if(e.target.innerHTML=="Show More") {
		e.target.innerHTML="Show Less"
	}
	else if(e.target.innerHTML=="Show Less") {
		e.target.innerHTML="Show More"
	}
}

