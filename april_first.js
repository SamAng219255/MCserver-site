falling=[];
sound=new Audio('img/tnt.ogg');
cardWidth=888;
offsetLeft=67;
nextTarget=latestId;
nextTargetObj={};
getNew=true;
function startFall() {
	nextTarget=dropCard(latestId);
	setInterval(fall,1);
}
function dropCard(targetId) {
	var tarId=targetId;
	var target=$("div.card[card="+tarId+"]");
	while(tarId>=0 && target.length<1) {
		tarId--;
		target=$("div.card[card="+tarId+"]");
	}
	if(tarId<0) {
		return -1;
	}
	var newFake=target.clone();
	target.addClass("notthere");
	var pos=target[0].offsetTop;
	falling.push({y:pos,x:0,vel:-0.5,hor:Math.random()*2-1,obj:newFake,creationTime:(new Date()).getTime()});
	newFake.addClass("falling");
	cardWidth=target.width();
	offsetLeft=target[0].offsetLeft;
	newFake[0].style="top: "+pos+"px; width: "+cardWidth+"px; left: "+offsetLeft+"px;";
	wrapper2.prepend(newFake[0]);
	var audio=(new Audio('img/tnt.ogg'));
	var promise = audio.play();;
	if (promise !== undefined) {
		promise.catch(error => {
			audio.play();
		});
	}
	return tarId;
}
function fall() {
	var min=Infinity;
	for(var i=0; i<falling.length; i++) {
		falling[i].x+=100*(falling[i].hor/1000);
		falling[i].y+=1000*(falling[i].vel/1000+0.0000049);
		falling[i].vel+=0.0049;
		falling[i].obj[0].style="top: "+falling[i].y+"px; width: "+cardWidth+"px; left: "+(offsetLeft+falling[i].x)+"px;"
		if(falling[i].y<min) min=falling[i].y;
		if((new Date()).getTime()-falling[i].creationTime>4000) {
			(new Audio('img/explode'+parseInt(Math.random()*4+1)+'.ogg')).play();
			falling[i].obj.remove();
			falling.splice(i,1);
			i--;
		}
	}
	if(getNew && $("div.card[card="+nextTarget+"]")[0].offsetTop<min) {
		dropCard(nextTarget);
		getNew=getNextDown();
	}
}
function getNextDown() {
	nextTarget--;
	nextTargetObj=$("div.card[card="+nextTarget+"]");
	while(nextTarget>=0 && nextTargetObj.length<1) {
		nextTarget--;
		nextTargetObj=$("div.card[card="+nextTarget+"]");
	}
	return nextTargetObj.length>0;
}
function hideBlocker() {
	$("#blocker").remove();
	setTimeout(startFall,parseInt(Math.random()*10000));
}