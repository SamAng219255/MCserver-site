class RotaryButton {
	constructor(icon,name,desc,func,data) {
		this.icon=icon;
		this.name=name;
		this.desc=desc;
		this.action=func;
		if(typeof data == "undefined")
			this.data={};
		else
			this.data=data;
		this.data.name=this.name;
		this.active=0;
		this.x=-Infinity;
		this.y=-Infinity;
		this.copy=function() {
			return new RotaryButton(this.icon,this.name,this.desc,this.action,JSON.parse(JSON.stringify(this.data)));
		}
	}
	static copyGroup (list) {
		var temp=[];
		for(var i=0; i<list.length; i++) {
			temp.push(list[i].copy());
		}
		return temp;
	}
}
rotaryQueue=[];
activeRotaryBtns=[];
timer=(new Date()).getTime();
Math.TAU=Math.PI*2;
dimension=0;
pos=[0,0];
offsetPos=[0,0];
offsetPix=[0,0];
cornerPos=[-576,-320]
mousePos=[0,0];
lastTar=[Infinity,Infinity];
markers=[];
markersVisible=[];
pointsVis=true;
selectedPoint=0;
selectedArmy=-1;
hoverArmy=-1;
targetArmy=-1;
pinsFound=false;
gotoPinPending="";
jumpMenuActive=false;
instMenuActive=false;
trpMenuActive=[false,false,false];
menuActive=false;
showMissingTiles=false;
dragging=false;
didDrag=false;
mobilePinCheck="";
if(isMobile) mobilePinCheck="Mobile";
lastDragTime=(new Date()).getTime();
dragTimeout=-1;
dragEvent=new MouseEvent("mousemove");
lastButtonPress=(new Date()).getTime();
tiles={};
empty=[];
tileDest={};
defaultTile=document.createElement('img');
defaultTile.src='img/default.png';
statusSprites=document.createElement('img');
statusSprites.src='img/status.png';
isAdmin=false;
spriteoptions=[];
trpSprite=0;
bannercount=0;
banners=[];
troops=[];
troopshandelling=false;
troopsaction="";
rotarySprites=[];
actionState="default";
for(var i=0; i<3; i++) {
	rotarySprites.push(document.createElement('img'));
}
rotarySprites[0].src="./img/rotarySprites.png";
rotarySprites[1].src="./img/rotarySpriteShadows.png";
rotarySprites[2].src="./img/rotarySpritesBlack.png";
controlBtns=[
	new RotaryButton(0,"Fortify","Costs 1 move.\nMove the army into defensive position giving an advantage against attackers.",trpAction),
	new RotaryButton(1,"Heal","Costs 1 move.\nSpend some time healling up. Recovers HP.",trpAction),
	new RotaryButton(2,"Move","Costs 1 move.\nRelocate army.",trpAction),
	new RotaryButton(3,"View","View the army's information.",viewTrp)
];
attackBtns=[
	new RotaryButton(4,"Attack","Costs 1 move.\nEngage in combat.",trpAction),
	new RotaryButton(5,"Hit & Run","Costs 2 moves.\nCharge up and attack then pull back.",trpAction),
	new RotaryButton(6,"Shoot","Costs 1 move.\nLaunch a long range attack.",trpAction),
	new RotaryButton(7,"Spy","View the army's information.",function(){
		setTroopMenu(0,targetArmy);
		$("#trpvMenu").addClass("shown");
		trpMenuActive[0]=true;
		menuActive=true;
	})
];
tooltip=new RotaryButton(-1,"","",function(){});
tooltip.active=-1;
document.addEventListener("keydown", move);
$.getJSON("tileIds.json",function (data) {tileIds=data});
window.onresize = function(e) {
	canvasResize();
	moving();
};
lastMoved=(new Date()).getTime();
function moving() {
	lastMoved=(new Date()).getTime();
	setTimeout(function(){if((new Date()).getTime()-lastMoved>=500){canvasResize(); draw();}},501)
}
function setup() {
	//const viewportmeta = document.querySelector('meta[name=viewport]');
	//viewportmeta.setAttribute('content', "width=device-width, initial-scale=0");
	if(isMobile) {
		$("#body").addClass("mobile");
		$("infoButton").addClass("hide");
	}
	sprites=document.createElement("img");
	sprites.src="./img/sprites.png";
	sprites.addEventListener("load",function(){
		for(var i=0; i<spriteoptions.length; i++) {
			spriteoptions[i][1].drawImage(sprites,(i%8)*16,parseInt(i/8)*16,16,16,0,0,64,64);
		}
	});
	spritemenu=$("#spritemenu");
	trpncnv=$("#trpnspritecnv");
	trpecnv=$("#trpespritecnv");
	trpvcnv=$("#trpvspritecnv");
	setInterval(function(){draw(); $.getJSON("getMarkers.php",function(data){troops=data.troops;});},1000);
	var dataStr=window.location.hash.split("#");
	var data={};
	if(dataStr.length>1) {
		var dataArr=dataStr[1].split("&");
		for(var i=0; i<dataArr.length; i++) {
			data[dataArr[i].split("=")[0]]=dataArr[i].split("=")[1];
		}
		if(data["x"]!==undefined && data["z"]!==undefined) {
			pos[0]=parseFloat(data["x"]);
			pos[1]=parseFloat(data["z"]);
			if(pos[0]%1!=0) {
				offsetPos[0]=pos[0]-Math.floor(pos[0]);
				pos[0]=Math.floor(pos[0]);
			}
			if(pos[1]%1!=0) {
				offsetPos[1]=pos[1]-Math.floor(pos[1]);
				pos[1]=Math.floor(pos[1]);
			}
		}
		else if(data["pin"]!==undefined) {
			gotoPin(data["pin"]);
		}
	}
	if(data["dimension"]!==undefined) {
		dimension=parseInt(data["dimension"]);
	}
	width=$(window).width();
	height=$(window).height();
	tileSize=128;
	startingTileSize=128;
	if(data["zoom"]!==undefined) {
		tileSize*=parseFloat(data["zoom"]);
	}
	offsetPix[0]=-1*offsetPos[0]*tileSize;
	offsetPix[1]=-1*offsetPos[1]*tileSize;
	canvasSetup();
	$.getJSON("getMarkers.php",function (data) {
		markers=data.pins;
		for(var i=0; i<markers.length; i++) {
			$("#jumpPin"+mobilePinCheck).append("<option value=\""+markers[i].name+"\">"+markers[i].name+"</option>");
		}
		pinsFound=true;
		if(gotoPinPending!="") {
			gotoPin(gotoPinPending);
		}
		else {
			drawPoints();
		}
		troops=data.troops;
	});
	draw();
	if(!isMobile)
	document.getElementById("infobutton").addEventListener("click",function() {
		if(!menuActive) {
			$("#instr").removeClass("hide");
			instMenuActive=true;
			menuActive=true;
		}
		else if(instMenuActive) {
			$("#instr").addClass("hide");
			instMenuActive=false;
			menuActive=false;
		}
	});
	document.getElementById("jumpbutton").addEventListener("click",function() {
		if(!menuActive) {
			if(isMobile)
				$("#jumpMenuMobile").addClass("shown");
			else
				$("#jumpMenu").addClass("shown");
			jumpMenuActive=true;
			menuActive=true;
		}
		else if(jumpMenuActive) {
			if(isMobile)
				$("#jumpMenuMobile").removeClass("shown");
			else
				$("#jumpMenu").removeClass("shown");
			jumpMenuActive=false;
			menuActive=false;
		}
	});
	document.getElementById("pinbutton").addEventListener("click",function() {
		if(pointsVis && selectedPoint!=0) {
			resetStuff();
		}
		pointsVis=!pointsVis;
		$("#pinbutton").toggleClass("active");
	});
	if(isAdmin)
	document.getElementById("addTroopbutton").addEventListener("click",function() {
		if(!menuActive) {
			setTroopMenu(2,0);
			$("#trpnMenu").addClass("shown");
			trpMenuActive[2]=true;
			menuActive=true;
		}
		else if(trpMenuActive[2]) {
			$("#trpnMenu").removeClass("shown");
			trpMenuActive[2]=false;
			menuActive=false;
		}
	});
	spritemenu[0].addEventListener("mouseleave",function() {
		spritemenu.removeClass("show");
	});
	trpncnv[0].addEventListener("click",function() {
		spritemenu.addClass("show");
	});
	$(".trpn-calc").on("change",setTroopCalcs);
	$(".trpn-calc").on("keyup",setTroopCalcs);
	hammertime=new Hammer.Manager(document.getElementById('mcmap'))
	hammertime.add(new Hammer.Pan());
	hammertime.add(new Hammer.Pinch());
	hammertime.on('pinchstart',function(e) {
		pinchScale=tileSize;
	});
	hammertime.get('pan').set({ direction: Hammer.DIRECTION_ALL });
	hammertime.add( new Hammer.Tap({ event: 'singletap' }) );
	hammertime.on('pinchmove',function(e) {
		tileSize=e.scale*pinchScale;
		offsetPix[0]=-1*offsetPos[0]*tileSize;
		offsetPix[1]=-1*offsetPos[1]*tileSize;
		setHash();
		cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
		draw();
		redrawHighlight();
	});
	hammertime.on("panstart", dragStart);
	hammertime.on("panmove", drag);
	hammertime.on("singletap", highlight);
	document.getElementById('mcmap').addEventListener("mousemove",highlighttroops)
}
function dragStart(e) {
	mousePos[0]=e.center.x;
	mousePos[1]=e.center.y;
}
function drag(e) {
	didDrag=true;
	var delta=[0,0];
	delta[0]=e.center.x-mousePos[0];
	delta[1]=e.center.y-mousePos[1];
	mousePos[0]=e.center.x;
	mousePos[1]=e.center.y;
	var tempPos=[pos[0]+offsetPos[0],pos[1]+offsetPos[1]];
	for(var i=0; i<2; i++) {
		tempPos[i]-=delta[i]/tileSize;
		pos[i]=Math.floor(tempPos[i]);
		offsetPos[i]=tempPos[i]-pos[i];
		offsetPix[i]=-1*offsetPos[i]*tileSize;
	}
	setHash();
	cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
	draw();
	redrawHighlight();
}
function canvasSetup() {
	canvasResize();
	canvas.addEventListener('wheel', function(event) {zoom(event); return false;}, {passive:false});
	mapCnv=document.createElement('canvas');
	mapCnv.width=width;
	mapCnv.height=height;
	mapCtx=mapCnv.getContext('2d');
	mapCtx.imageSmoothingEnabled=false;
	boxCnv=document.createElement('canvas');
	boxCnv.width=width;
	boxCnv.height=height;
	boxCtx=boxCnv.getContext('2d');
	boxCtx.imageSmoothingEnabled=false;
	pinCnv=document.createElement('canvas');
	pinCnv.width=width;
	pinCnv.height=height;
	pinCtx=pinCnv.getContext('2d');
	pinCtx.imageSmoothingEnabled=false;
	trpCnv=document.createElement('canvas');
	trpCnv.width=width;
	trpCnv.height=height;
	trpCtx=trpCnv.getContext('2d');
	trpCtx.imageSmoothingEnabled=false;
	uiCnv=document.createElement('canvas');
	uiCnv.width=width;
	uiCnv.height=height;
	uiCtx=uiCnv.getContext('2d');
	uiCtx.imageSmoothingEnabled=false;
	trpnCnv=trpncnv[0];
	trpeCnv=trpecnv[0];
	trpvCnv=trpvcnv[0];
	trpnCtx=trpnCnv.getContext('2d');
	trpeCtx=trpeCnv.getContext('2d');
	trpvCtx=trpvCnv.getContext('2d');
	trpnCtx.imageSmoothingEnabled=false;
	trpeCtx.imageSmoothingEnabled=false;
	trpvCtx.imageSmoothingEnabled=false;
	for(var i=0; i<8; i++) {
		spriteoptions.push([document.createElement("canvas")]);
		spriteoptions[i][0].width=spriteoptions[i][0].height=64;
		spriteoptions[i][0].i=i;
		spriteoptions[i].push(spriteoptions[i][0].getContext("2d"));
		spriteoptions[i][1].imageSmoothingEnabled=false;
		spriteoptions[i][0].addEventListener("click",function(event){
			trpnCtx.clearRect(0,0,64,64);
			trpnCtx.drawImage(sprites,(event.target.i%8)*16,parseInt(event.target.i/8)*16,16,16,0,0,64,64);
			trpSprite=event.target.i;
			spritemenu.removeClass("show");
		});
		spritemenu.append(spriteoptions[i][0]);
	}
}
function canvasResize() {
	canvas=document.getElementById("mcmap");
	if(typeof mapCnv != "undefined") {
		width=mapCnv.width=boxCnv.width=pinCnv.width=trpCnv.width=uiCnv.width=canvas.width=$(window).width();
		height=mapCnv.height=boxCnv.height=pinCnv.height=trpCnv.height=uiCnv.height=canvas.height=$(window).height();
		mapCtx.imageSmoothingEnabled=false;
		boxCtx.imageSmoothingEnabled=false;
		pinCtx.imageSmoothingEnabled=false;
		trpCtx.imageSmoothingEnabled=false;
		uiCtx.imageSmoothingEnabled=false;
	}
	else {
		width=canvas.width=$(window).width();
		height=canvas.height=$(window).height();
	}
	ctx=canvas.getContext('2d');
	ctx.imageSmoothingEnabled=false;
	cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
}
function gotoPin(pinName) {
	if(pinsFound) {
		for(var i=0; i<markers.length; i++) {
			if(markers[i].name==pinName) {
				pos[0]=(markers[i].x+0.5)/128;
				pos[1]=(markers[i].z+0.5)/128;
				offsetPos[0]=pos[0]-Math.floor(pos[0]);
				offsetPos[1]=pos[1]-Math.floor(pos[1]);
				pos[0]=Math.floor(pos[0]);
				pos[1]=Math.floor(pos[1]);
				offsetPix[0]=-1*offsetPos[0]*tileSize;
				offsetPix[1]=-1*offsetPos[1]*tileSize;
				cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
				dimension=markers[i].dimension;
			}
		}
		setHash();
		drawPoints();
		draw();
	}
	else {
		gotoPinPending=pinName;
	}
}
function gotoPoint(x,z,d) {
	dimension=d;
	pos[0]=Math.floor(x);
	pos[1]=Math.floor(z);
	offsetPos[0]=x-pos[0];
	offsetPos[1]=z-pos[1];
	offsetPix[0]=-1*offsetPos[0]*tileSize;
	offsetPix[1]=-1*offsetPos[1]*tileSize;
	cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
	setHash();
	draw();
	drawPoints();
}
function jumpPinFunc(e) {
	if($("#jumpPin"+mobilePinCheck).val()!='') {
		gotoPin($("#jumpPin"+mobilePinCheck).val());
		setTimeout(function(){jumpMenuActive=false; menuActive=false;},1);
		$("#jumpMenu"+mobilePinCheck).removeClass("shown");
		document.getElementById("jumpPin"+mobilePinCheck).selectedIndex=0;
		//viewportmeta.setAttribute('content', "width=device-width, initial-scale=0");
	}
}
function jumpCoordFunc(e) {
	if(jumpMenuActive && e.path[1].id=="jumpCoordForm"+mobilePinCheck && e.keyCode==13) {
		gotoPoint((parseFloat($("#jumpCoordX"+mobilePinCheck).val())/128),(parseFloat($("#jumpCoordZ"+mobilePinCheck).val())/128),(parseInt($("#jumpCoordD"+mobilePinCheck).val())));
		setTimeout(function(){jumpMenuActive=false; menuActive=false;},1);
		$("#jumpMenu"+mobilePinCheck).removeClass("shown");
	}
}
function draw() {
	mapCtx.clearRect(0,0,width,height);
	tileDelta=[pos[0]-cornerPos[0],pos[1]-cornerPos[1]];
	for(var i=-Math.ceil(tileDelta[0]); i<=Math.ceil(tileDelta[0])+1; i++) {
		for(var j=-Math.ceil(tileDelta[1]); j<=Math.ceil(tileDelta[1])+1; j++) {
			var x=(Math.floor(pos[0])+i);
			var y=(Math.floor(pos[1])+j);
			var key="d"+dimension+"x"+x+"y"+y;
			if(tileIds[dimension+"_"+x+"_"+y]==undefined) {
				if(!showMissingTiles) {
					mapCtx.drawImage(defaultTile,offsetPix[0]+(Math.floor(i)+tileDelta[0])*tileSize,offsetPix[1]+(Math.floor(j)+tileDelta[1])*tileSize,tileSize,tileSize);
				}
			}
			else if(tiles[key]==undefined) {
				tileDest[key]=[i,j];
				tiles[key]=document.createElement('img');
				tiles[key].src="img/tile."+dimension+"."+x+"."+y+".png";
				tiles[key].onerror=function() {
					empty.push(key);
					if (this.src != 'img/default.png') {
						this.src = 'img/default.png'
					};
					preventDefault();
				}
				tiles[key].onload=function () {
					var foo=this.src.split(".");
					var bar=tileDest["d"+dimension+"x"+foo[foo.length-3]+"y"+foo[foo.length-2]];
					delete tileDest["d"+dimension+"x"+foo[foo.length-3]+"y"+foo[foo.length-2]];
					mapCtx.drawImage(this,offsetPix[0]+(Math.floor(bar[0])+tileDelta[0])*tileSize,offsetPix[1]+(Math.floor(bar[1])+tileDelta[1])*tileSize,tileSize,tileSize);
					drawMain();
				}
			}
			else {
				mapCtx.drawImage(tiles[key],offsetPix[0]+(i+tileDelta[0])*tileSize,offsetPix[1]+(j+tileDelta[1])*tileSize,tileSize,tileSize);
			}
		}
	}
	drawPoints();
}
function drawMain() {
	ctx.clearRect(0,0,width,height);
	ctx.drawImage(mapCnv,0,0);
	ctx.drawImage(boxCnv,0,0);
	ctx.drawImage(trpCnv,0,0);
	ctx.drawImage(pinCnv,0,0);
	ctx.drawImage(uiCnv,0,0);
}
function move(e) {
	var allowNormalExecution=true;
	var time=(new Date()).getTime();
	if(time-lastButtonPress>100) {
		if(!menuActive) {
			if((e.keyCode>36 && e.keyCode<41) || e.keyCode==87 || e.keyCode==65 || e.keyCode==83 || e.keyCode==68) {
				boxCtx.clearRect(0,0,width,height);
				resetStuff();
			}
			if(e.keyCode==37 || e.keyCode==65) {
				pos[0]-=Math.max(Math.floor(startingTileSize/tileSize),1);
				Object.keys(tileDest).forEach(function(key){tileDest[key][0]++});
			}
			else if(e.keyCode==38 || e.keyCode==87) {
				pos[1]-=Math.max(Math.floor(startingTileSize/tileSize),1);
				Object.keys(tileDest).forEach(function(key){tileDest[key][1]++});
			}
			else if(e.keyCode==39 || e.keyCode==68) {
				pos[0]+=Math.max(Math.floor(startingTileSize/tileSize),1);
				Object.keys(tileDest).forEach(function(key){tileDest[key][0]--});
			}
			else if(e.keyCode==40 || e.keyCode==83) {
				pos[1]+=Math.max(Math.floor(startingTileSize/tileSize),1);
				Object.keys(tileDest).forEach(function(key){tileDest[key][1]--});
			}
			else if(e.keyCode==13) {
				if(pointsVis && selectedPoint!=0) {
					resetStuff();
				}
				pointsVis=!pointsVis;
			}
			else if(e.keyCode==16) {
				$("#jumpMenu").addClass("shown");
				jumpMenuActive=true;
				menuActive=true;
			}
			else if(e.keyCode==9) {
				$("#instr").removeClass("hide");
				instMenuActive=true;
				menuActive=true;
				allowNormalExecution=false;
			}
			else if(e.keyCode==81) {
				dimension--;
				while(dimension<-1) {
					dimension+=3
				}
				setHash();
				draw();
			}
			else if(e.keyCode==69) {
				dimension++;
				while(dimension>1) {
					dimension-=3
				}
				setHash();
				draw();
			}
			else {
				console.log(e.keyCode);
			}
			if((e.keyCode>36 && e.keyCode<41) || e.keyCode==87 || e.keyCode==65 || e.keyCode==83 || e.keyCode==68) {
				cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
				setHash();
				draw();
			}
			drawPoints();
		}
		else if(jumpMenuActive && e.keyCode==27) {
			$("#jumpMenu").removeClass("shown");
			jumpMenuActive=false;
			menuActive=false;
		}
		else if(instMenuActive && (e.keyCode==27 || e.keyCode==9)) {
			$("#instr").addClass("hide");
			instMenuActive=false;
			menuActive=false;
			if(e.keyCode==9) {allowNormalExecution=false;}
		}
	}
	lastButtonPress=time;
	return allowNormalExecution;
}
function closeJumpMenu() {
	$("#jumpMenu").removeClass("shown");
	$("#jumpMenuMobile").removeClass("shown");
	jumpMenuActive=false;
	menuActive=false;
}
function closeInstMenu() {
	$("#instr").addClass("hide");
	instMenuActive=false;
	menuActive=false;
}
function closeTrpMenu(which) {
	var foo="ven";
	$("#trp"+foo[which]+"Menu").removeClass("shown");
	trpMenuActive[which]=false;
	menuActive=false;
	$("#editbtn").removeClass("show");
}
function highlight(e) {
	var rawX=e.center.x-offsetPix[0];
	var rawY=e.center.y-offsetPix[1];
	var xf = Math.floor((rawX-width/2)/tileSize+0.5)-cornerPos[0]+pos[0];
	var yf = Math.floor((rawY-height/2)/tileSize+0.5)-cornerPos[1]+pos[1];
	var x = Math.floor(xf);
	var y = Math.floor(yf);
	var xCor=Math.floor(xf+cornerPos[0]);
	var yCor=Math.floor(yf+cornerPos[1]);
	var xXct=rawX+(cornerPos[0]*tileSize);
	var yXct=rawY+(cornerPos[1]*tileSize);
	var newX=Math.floor(((rawX-width/2)/tileSize+pos[0])*128);
	var newZ=Math.floor(((rawY-height/2)/tileSize+pos[1])*128);
	boxCtx.clearRect(0,0,width,height);
	var clickedMark=false;
	var whichMark=-1;
	var markDist=Infinity;
	var clickedTrp=false;
	var whichTrp=-1;
	var trpDist=Infinity;
	var clickedBtn=false;
	var whichBtn=-1;
	var btnDist=Infinity;
	if(pointsVis) {
		for(var i=0; i<markers.length; i++) {
			var dist=Math.sqrt(Math.pow(xXct-(markers[i].x+0.5)/128*tileSize-tileSize/2,2)+Math.pow(yXct-(markers[i].z+0.5)/128*tileSize-tileSize/2,2));
			if(dist<markDist && (((dist<15 && selectedPoint==markers[i].id) || dist<10)) || (((dist<60 && selectedPoint==markers[i].id) || dist<40) && isMobile)) {
				clickedMark=true;
				whichMark=i;
				markDist=dist;
			}
		}
		for(var i=0; i<troops.length; i++) {
			var dist=Math.sqrt(Math.pow(xXct-(troops[i].x+0.5)/128*tileSize-tileSize/2,2)+Math.pow(yXct-(troops[i].z+0.5)/128*tileSize-tileSize/2,2));
			if(dist<trpDist && (((dist<26 && (selectedArmy==troops[i].id || targetArmy==troops[i].id)) || (dist<22 && hoverArmy==troops[i].id) || dist<18)) || (((dist<102 && (selectedArmy==troops[i].id || targetArmy==troops[i].id)) || (dist<85 && hoverArmy==troops[i].id) || dist<72) && isMobile)) {
				clickedTrp=true;
				whichTrp=i;
				trpDist=dist;
			}
		}
	}
	for(var i=0; i<activeRotaryBtns.length; i++) {
		//drawCircle(ctx,activeRotaryBtns[i].x,activeRotaryBtns[i].y,activeRotaryBtns[i].radius,"#ffff00");
		var dist=Math.sqrt(Math.pow(e.center.x-activeRotaryBtns[i].x,2)+Math.pow(e.center.y-activeRotaryBtns[i].y,2));
		if(dist<btnDist && dist<activeRotaryBtns[i].radius) {
			clickedBtn=true;
			whichBtn=i;
			btnDist=dist;
		}
	}
	//drawCircle(ctx,e.center.x,e.center.y,16,"#0000ff");
	if(clickedBtn) {
		activeRotaryBtns[whichBtn].action(activeRotaryBtns[whichBtn].data);
	}
	else if(clickedTrp && (actionState!="move" || selectedArmy==whichTrp)) {
		if(selectedArmy!=whichTrp) {
			if(selectedArmy!=-1) {
				if(targetArmy!=whichTrp)
					targetArmy=whichTrp;
				else
					targetArmy=-1;
			}
			else {
				selectedPoint=0;
				lastTar=[Infinity,Infinity];
				selectedArmy=whichTrp;
				$("#infoTxt")[0].innerHTML="<b>"+troops[whichTrp].name+"</b><br>Nation: "+troops[whichTrp].nation+", Size: "+troops[whichTrp].size+", Strength: "+troops[whichTrp].power+"<br><span onclick=\"viewTrp()\">Details</span>";
				$("#infoTxt").addClass("shown");
			}
		}
		else {
			if(actionState!="move") {
				selectedArmy=-1;
				resetStuff();
			}
			else {
				actionState="default";
				drawUI();
			}
		}
		drawPoints();
	}
	else if(actionState=="move" && Math.pow(newX-troops[selectedArmy].x,2)+Math.pow(newZ-troops[selectedArmy].z,2)<=16384) {
		console.log("Passed",newX,newZ,",",troops[selectedArmy].x,troops[selectedArmy].z);
		sendTrpAction({id:troops[selectedArmy].id,x:newX,z:newZ,action:"move"});
		troops[selectedArmy].x=newX;
		troops[selectedArmy].z=newZ;
		selectedArmy=-1;
		actionState="default";
		drawUI();
		resetStuff();
	}
	else if(actionState=="move") {
		console.log("Failed",newX,newZ,",",troops[selectedArmy].x,troops[selectedArmy].z);
		addBanner("Too far away.");
	}
	else if(clickedMark) {
		if(selectedPoint!=markers[whichMark].id) {
			selectedArmy=-1;
			actionState="default";
			lastTar=[Infinity,Infinity];
			selectedPoint=markers[whichMark].id;
			$("#infoTxt")[0].innerHTML="<b>"+markers[whichMark].name+"</b>: "+markers[whichMark].desc;
			$("#infoTxt").addClass("shown");
		}
		else {
			selectedPoint=0;
			resetStuff();
		}
		drawPoints();
	}
	else if(lastTar[0]!=xCor || lastTar[1]!=yCor) {
		resetStuff();
		lastTar=[xCor,yCor];
		boxCtx.strokeStyle="#000000";
		boxCtx.lineWidth=8;
		boxCtx.strokeRect(offsetPix[0]+xf*tileSize, offsetPix[1]+yf*tileSize, tileSize, tileSize);
		boxCtx.strokeStyle="#FFFFFF";
		boxCtx.lineWidth=4;
		boxCtx.strokeRect(offsetPix[0]+xf*tileSize, offsetPix[1]+yf*tileSize, tileSize, tileSize);
		$("#infoTxt")[0].innerHTML="Highlighted tile ("+xCor+", "+yCor+"), centered on ("+(xCor*128)+", "+(yCor*128)+"), coordinates ("+((xCor*128)-64)+", "+((yCor*128)-64)+") to ("+((xCor*128)+63)+", "+((yCor*128)+63)+").<br>Map ID: "+mapIds(xCor,yCor);
		$("#infoTxt").addClass("shown");
		drawMain();
	}
	else {
		resetStuff();
	}
}
function highlighttroops(e) {
	var rawX=e.x-offsetPix[0];
	var rawY=e.y-offsetPix[1];
	var xf = Math.floor((rawX-width/2)/tileSize+0.5)-cornerPos[0]+pos[0];
	var yf = Math.floor((rawY-height/2)/tileSize+0.5)-cornerPos[1]+pos[1];
	var x = Math.floor(xf);
	var y = Math.floor(yf);
	var xXct=rawX+(cornerPos[0]*tileSize);
	var yXct=rawY+(cornerPos[1]*tileSize);
	var clickedTrp=false;
	var whichTrp=-1;
	var trpDist=Infinity;
	var clickedBtn=false;
	var whichBtn=-1;
	var btnDist=Infinity;
	if(pointsVis && actionState!="move") {
		for(var i=0; i<troops.length; i++) {
			var dist=Math.sqrt(Math.pow(xXct-(troops[i].x+0.5)/128*tileSize-tileSize/2,2)+Math.pow(yXct-(troops[i].z+0.5)/128*tileSize-tileSize/2,2));
			if(dist<trpDist && (((dist<26 && hoverArmy==troops[i].id) || dist<18)) || (((dist<102 && hoverArmy==troops[i].id) || dist<72) && isMobile)) {
				clickedTrp=true;
				whichTrp=i;
				trpDist=dist;
			}
		}
	}
	for(var i=0; i<activeRotaryBtns.length; i++) {
		var dist=Math.sqrt(Math.pow(e.x-activeRotaryBtns[i].x,2)+Math.pow(e.y-activeRotaryBtns[i].y,2));
		if(activeRotaryBtns[i].active<2 && dist<btnDist && dist<activeRotaryBtns[i].radius) {
			clickedBtn=true;
			whichBtn=i;
			btnDist=dist;
		}
	}
	var trpPos;
	if(selectedArmy>-1)
		trpPos=[offsetPix[0]+(troops[selectedArmy].x+0.5)/128*tileSize-cornerPos[0]*tileSize+tileSize/2,offsetPix[1]+(troops[selectedArmy].z+0.5)/128*tileSize-cornerPos[1]*tileSize+tileSize/2];
	if(clickedTrp || clickedBtn || (actionState=="move" && Math.sqrt(Math.pow(trpPos[0]-e.x,2)+Math.pow(trpPos[1]-e.y,2))<=tileSize))
		$("#mcmap").addClass("pointer");
	else
		$("#mcmap").removeClass("pointer");
	if(clickedTrp && !clickedBtn)
		hoverArmy=whichTrp;
	else
		hoverArmy=-1;
	if(clickedBtn)
		tooltip=activeRotaryBtns[whichBtn].copy();
	else
		tooltip.active=-1;
	mouseCoords=[e.x,e.y];
	drawTroops();
}
function redrawHighlight() {
	if(lastTar[0]<Infinity) {
		boxCtx.clearRect(0,0,width,height);
		var xf = lastTar[0]-cornerPos[0];
		var yf = lastTar[1]-cornerPos[1];
		var x = Math.floor(xf);
		var y = Math.floor(yf);
		var xCor=lastTar[0];;
		var yCor=lastTar[1];;
		boxCtx.strokeStyle="#000000";
		boxCtx.lineWidth=8;
		boxCtx.strokeRect(offsetPix[0]+xf*tileSize, offsetPix[1]+yf*tileSize, tileSize, tileSize);
		boxCtx.strokeStyle="#FFFFFF";
		boxCtx.lineWidth=4;
		boxCtx.strokeRect(offsetPix[0]+xf*tileSize, offsetPix[1]+yf*tileSize, tileSize, tileSize);
		$("#infoTxt")[0].innerHTML="Highlighted tile ("+xCor+", "+yCor+"), centered on ("+(xCor*128)+", "+(yCor*128)+"), coordinates ("+((xCor*128)-64)+", "+((yCor*128)-64)+") to ("+((xCor*128)+63)+", "+((yCor*128)+63)+").<br>Map ID: "+mapIds(xCor,yCor);
		$("#infoTxt").addClass("shown");
		drawMain();
	}
}
function zoom(e) {
	if(Math.abs(e.deltaY)>Math.abs(e.deltaX)) {
		tileSize*=Math.pow(2,-e.deltaY/100);
		tileSize=Math.max(12.8,tileSize);
		offsetPix[0]=-1*offsetPos[0]*tileSize;
		offsetPix[1]=-1*offsetPos[1]*tileSize;
		setHash();
		cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
		draw();
		redrawHighlight();
	}
}
function mapIds(x,y) {
	return tileIds[dimension+"_"+x+"_"+y];
}
function drawCircle(CTX,xPos,yPos,radius,color) {
	CTX.beginPath()
	CTX.lineWidth=0;
	CTX.fillStyle=color;
	CTX.arc(xPos, yPos, radius, 0, 2*Math.PI, true);
	CTX.fill();
}
function checkMarkerVisibility() {//removed feature: buggy and unnecessary
	markersVisible=[];
	var posRel=[pos[0]*128,pos[1]*128];
	var delta=[(pos[0]-cornerPos[0])*128,(pos[1]-cornerPos[1])*128];
	for(var i=0; i<markers.length; i++) {
		if(markers[i].x>=cornerPos[0]-1 && markers[i].x<=cornerPos[0]+width/tileSize*128+1 && markers[i].z>=cornerPos[1]-1 && markers[i].z<=cornerPos[1]+height/tileSize*128+1) {
			markersVisible.push(markers[i].id);
		}
	}
}
function drawPoints() {
	//checkMarkerVisibility();
	pinCtx.clearRect(0,0,width,height);
	if(pointsVis) {
		var posRel=[cornerPos[0]*tileSize,cornerPos[1]*tileSize];
		for(var i=0; i<markers.length; i++) {
			if(markers[i].dimension==dimension) {
				var posAdj=[offsetPix[0]+(markers[i].x+0.5)/128*tileSize-posRel[0]+tileSize/2,offsetPix[1]+(markers[i].z+0.5)/128*tileSize-posRel[1]+tileSize/2];
				var sizeMod=1;
				if(markers[i].id==selectedPoint) {
					sizeMod=Math.sqrt(2);
				}
				if(isMobile) sizeMod*=2;
				drawCircle(pinCtx,posAdj[0]+(2*sizeMod),posAdj[1]+(2*sizeMod),10*sizeMod,"#000000");
				drawCircle(pinCtx,posAdj[0],posAdj[1],10*sizeMod,"#ff0000");
				drawCircle(pinCtx,posAdj[0]-(3*sizeMod),posAdj[1]-(3*sizeMod),4*sizeMod,"#ff8080");
			}
		}
	}
	drawTroops();
	drawMain();
}
function resetStuff() {
	$("#infoTxt")[0].innerHTML="";
	$("#infoTxt").removeClass("shown");
	selectedPoint=0;
	selectedArmy=-1;
	hoverArmy=-1;
	targetArmy=-1;
	drawPoints();
	lastTar=[Infinity,Infinity];
	actionState="default";
	rotaryQueue=[];
}
function setHash() {
	history.replaceState(undefined, undefined, "#x="+(pos[0]+offsetPos[0])+"&z="+(pos[1]+offsetPos[1])+"&zoom="+tileSize/128+"&dimension="+dimension);
}
function drawTroops() {
	trpCtx.clearRect(0,0,width,height);
	if(pointsVis) {
		var posRel=[cornerPos[0]*tileSize,cornerPos[1]*tileSize];
		for(var i=0; i<troops.length; i++) {
			if(troops[i].dimension==dimension) {
				var posAdj=[offsetPix[0]+(troops[i].x+0.5)/128*tileSize-posRel[0]+tileSize/2,offsetPix[1]+(troops[i].z+0.5)/128*tileSize-posRel[1]+tileSize/2];
				var sizeMod=1;
				var mobMod=1;
				if(i==hoverArmy) {
					sizeMod=Math.sqrt(Math.SQRT2);
				}
				if(i==selectedArmy || i==targetArmy) {
					sizeMod=Math.SQRT2;
				}
				if(isMobile) {
					sizeMod*=2;
					mobMod=2;
				}
				drawCircle(trpCtx,posAdj[0],posAdj[1],18*sizeMod,"#"+troops[i].color);
				trpCtx.drawImage(sprites,(troops[i].sprite%8)*16,parseInt(troops[i].sprite/8)*16,16,16,posAdj[0]-16*sizeMod,posAdj[1]-16*sizeMod,32*sizeMod,32*sizeMod);
				if(troops[i].state>0)
					trpCtx.drawImage(statusSprites,((troops[i].state-1)%4)*64,parseInt((troops[i].state-1)/4)*64,64,64,posAdj[0],posAdj[1],16*sizeMod,16*sizeMod);
				if(i==selectedArmy) {
					var trpBtns=RotaryButton.copyGroup(controlBtns);
					if(!troops[i].owned) {
						for(var j=0; j<3; j++) {
							trpBtns[j].active=1;
							trpBtns[j].data.owned=false;
						}
					}
					if(troops[selectedArmy].moveleft<1) {
						for(var j=0; j<3; j++) {
							trpBtns[j].active=1;
							trpBtns[j].data.moveless=false;
						}
					}
					scheduleRotary(posAdj[0],posAdj[1],mobMod*32,mobMod*96,trpBtns);
				}
				if(i==targetArmy) {
					var trpBtns=RotaryButton.copyGroup(attackBtns);
					if(!troops[selectedArmy].owned) {
						for(var j=0; j<3; j++) {
							trpBtns[j].active=1;
							trpBtns[j].data.owned=false;
						}
					}
					if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)>16384) {
						for(var j=0; j<3; j++) {
							trpBtns[j].active=1;
							trpBtns[j].data.toofar=true;
						}
					}
					if(troops[selectedArmy].moveleft<1) {
						trpBtns[0].active=1;
						trpBtns[0].data.moveless=true;
						trpBtns[2].active=1;
						trpBtns[2].data.moveless=true;
					}
					if(troops[selectedArmy].moveleft<2) {
						trpBtns[1].active=1;
						trpBtns[1].data.moveless=true;
					}
					if(!troops[selectedArmy].mobile) {
						trpBtns[1].active=2;
					}
					if(!troops[selectedArmy].ranged) {
						trpBtns[2].active=2;
					}
					scheduleRotary(posAdj[0],posAdj[1],mobMod*32,mobMod*96,trpBtns);
				}
			}
		}
	}
	drawUI();
	drawMain();
}
function scheduleRotary(x,y,iRadius,oRadius,btns) {
	rotaryQueue.push([x,y,iRadius,oRadius,btns]);
}
function drawUI() {
	if(selectedArmy>=troops.length)
		selectedArmy=-1;
	uiCtx.clearRect(0,0,width,height);
	//uiCnv.width=uiCnv.width; // Hack way of clearing it cause bugs.
	activeRotaryBtns=[];
	while(rotaryQueue.length>0 && actionState!="move") {
		var stuff=rotaryQueue.splice(0,1)[0];
		drawRotary(stuff[0],stuff[1],stuff[2],stuff[3],stuff[4]);
	}
	if(tooltip.active>-1 && actionState!="move") {
		var fontSize=16;
		uiCtx.font=fontSize+"px pixel";
		uiCtx.fillStyle="#808080";
		uiCtx.strokeStyle="#404040";
		var lines=tooltip.desc.split("\n");
		lines.splice(0,0,tooltip.name);
		if(tooltip.data.owned!=undefined && !tooltip.data.owned) {
			lines.splice(lines.length,0,"You can not perform this action because you do not own this army.");
		}
		if(tooltip.data.toofar!=undefined && tooltip.data.toofar) {
			lines.splice(lines.length,0,"Your army is out of range.");
		}
		if(tooltip.data.moveless!=undefined && tooltip.data.moveless) {
			lines.splice(lines.length,0,"Your army does not have enough moves left.");
		}
		var newwidth=0;
		var testwidth;
		var margin=$(4.5).toPx();
		for(var i=0; i<lines.length; i++) {
			var shortened=false;
			for(var j=lines[i].length; j>1; j--) {
				if((testwidth=uiCtx.measureText(lines[i].slice(0,j)).width)<(width-mouseCoords[0]-margin) && (lines[i][j]==" " || j==lines[i].length)) {
					shortened=j!=lines[i].length;
					break;
				}
			}
			if(shortened) {
				lines.splice(i,1,lines[i].slice(0,j),lines[i].slice(j));
			}
		}
		for(var i=0; i<lines.length; i++)
			if((testwidth=uiCtx.measureText(lines[i]).width)>newwidth && testwidth<(width-mouseCoords[0]-margin))
				newwidth=testwidth;
		uiCtx.fillRect(mouseCoords[0],mouseCoords[1],newwidth+fontSize,1.125*lines.length*fontSize+fontSize);
		uiCtx.lineWidth=2;
		uiCtx.beginPath();
		uiCtx.strokeRect(mouseCoords[0],mouseCoords[1],newwidth+fontSize,1.125*lines.length*fontSize+fontSize);
		uiCtx.stroke();
		uiCtx.fillStyle="#000000";
		for(var i=0; i<lines.length; i++) {
			uiCtx.fillText(lines[i],mouseCoords[0]+fontSize/2,mouseCoords[1]+3*fontSize/2+fontSize*1.125*i);
		}
	}
	if(actionState=="move") {
		boxCtx.clearRect(0,0,width,height);
		var posRel=[cornerPos[0]*tileSize,cornerPos[1]*tileSize];
		var posAdj=[offsetPix[0]+(troops[selectedArmy].x+0.5)/128*tileSize-posRel[0]+tileSize/2,offsetPix[1]+(troops[selectedArmy].z+0.5)/128*tileSize-posRel[1]+tileSize/2];
		drawCircle(boxCtx,posAdj[0],posAdj[1],tileSize,"rgba("+parseInt(troops[selectedArmy].color.slice(0,2),16)+","+parseInt(troops[selectedArmy].color.slice(2,4),16)+","+parseInt(troops[selectedArmy].color.slice(4,6),16)+",0.5)");
		boxCtx.beginPath();
		boxCtx.strokeStyle="#"+troops[selectedArmy].color;
		boxCtx.lineWidth=4;
		boxCtx.arc(posAdj[0], posAdj[1], tileSize, 0, 2*Math.PI, true);
		boxCtx.stroke();
	}
}
function drawRotary(x,y,iRadius,oRadius,btns) {
	var menuwidth=oRadius-iRadius;
	var radius=(iRadius+oRadius)/2;
	var btnSize=menuwidth/Math.SQRT2;
	uiCtx.beginPath();
	uiCtx.strokeStyle="rgba(0,0,0,0.75)";
	uiCtx.lineWidth=menuwidth;
	uiCtx.arc(x, y, radius, 0, 2*Math.PI, true);
	uiCtx.stroke();
	for(var i=0; i<btns.length; i++) {
		btns[i].x=x-radius*Math.sin(Math.TAU*i/4);
		btns[i].y=y-radius*Math.cos(Math.TAU*i/4);
		btns[i].radius=menuwidth/2;
		uiCtx.drawImage(rotarySprites[btns[i].active],(btns[i].icon%4)*128,parseInt(btns[i].icon/4)*128,128,128,btns[i].x-btnSize/2,btns[i].y-btnSize/2,btnSize,btnSize);
		activeRotaryBtns.push(btns[i]);
	}
}
function trpAction(data) {
	console.log("trpAction",data);
	if(data.name=="Fortify") {
		if(troops[selectedArmy].moveleft>=1)
			sendTrpAction({id:troops[selectedArmy].id,action:"fortify"});
		else
			addBanner("That unit does not have enough movement left to perform that action.");
	}
	else if(data.name=="Heal") {
		if(troops[selectedArmy].moveleft>=1)
			sendTrpAction({id:troops[selectedArmy].id,action:"heal"});
		else
			addBanner("That unit does not have enough movement left to perform that action.");
	}
	else if(data.name=="Move") {
		actionState="move";
		drawUI();
	}
	else if(data.name=="Attack") {
		if(troops[selectedArmy].moveleft>=1)
			if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)<=16384)
				sendTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"attack"});
			else
				addBanner("Too far away.");
		else
			addBanner("That unit does not have enough movement left to perform that action.");
	}
	else if(data.name=="Hit & Run") {
		if(troops[selectedArmy].moveleft>=2)
			if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)<=16384)
				sendTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"hitrun"});
			else
				addBanner("Too far away.");
		else
			addBanner("That unit does not have enough movement left to perform that action.");
	}
	else if(data.name=="Shoot") {
		if(troops[selectedArmy].moveleft>=1)
			if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)<=16384)
				sendTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"shoot"});
			else
				addBanner("Too far away.");
		else
			addBanner("That unit does not have enough movement left to perform that action.");
	}
	if(data.name!="Move") {
		selectedArmy=-1;
		resetStuff();
	}
	targetArmy=-1;
}
function sendTrpAction(data) {
	$.getJSON("trpAction.php",data,function (data) {
		console.log(data);
		if(data.status>0) {
			addBanner(data.text);
		}
		if(data.status1>0) {
			addBanner(data.text1);
		}
		if(data.status2>0) {
			addBanner(data.text2);
		}
	});
}
function viewTrp() {
	setTroopMenu(0,selectedArmy);
	$("#trpvMenu").addClass("shown");
	trpMenuActive[0]=true;
	menuActive=true;
}
function switchEdit() {
	setTroopMenu(1,selectedArmy);
	$("#trpeMenu").addClass("shown");
	trpMenuActive[1]=true;
	$("#trpvMenu").removeClass("shown");
	trpMenuActive[0]=false;
	$("#editbtn").removeClass("show");
}
function setTroopMenu(kind,id) {
	var foo="ven";
	var targetMenu=$("#trp"+foo[kind]+"Menu");
	if(kind!=2)
		activeTrpId=troops[id].id;
	if(kind==2) {
		$("#trpn-name").val("");
		$("#trpn-owner").val(nation);
		$("#trpn-size").val(1);
		$("#trpn-cost").val(0);
		$("#trpn-mobility").prop("checked",false);
		$("#trpn-ranged").prop("checked",false);
		$("#trpn-x").val(parseInt((offsetPos[0]+pos[0])*128));
		$("#trpn-z").val(parseInt((offsetPos[1]+pos[1])*128));
		trpnCtx.clearRect(0,0,64,64);
		trpnCtx.drawImage(sprites,(0%8)*16,parseInt(0/8)*16,16,16,0,0,64,64);
		trpSprite=0;
		spritemenu.removeClass("show");
		setTroopCalcs();
	}
	else if(kind==1) {
		$("#trpe-name").val(troops[id].name);
		$("#trpe-owner").val(troops[id].nation);
		$(".trp-size").text(troops[id].size);
		$(".trp-power").text(troops[id].power);
		$(".trp-health").text(troops[id].health+"%");
		var type="";
		if(troops[id].mobile) {
			if(type.length>0)
				type+=", ";
			type="Mobility";
		}
		if(troops[id].ranged) {
			if(type.length>0)
				type+=", ";
			type+="Ranged"
		}
		if(type.length==0) {
			type="Basic";
		}
		$(".trp-type").text(type);
		$(".trp-position").text("X: "+troops[id].x+", Z: "+troops[id].z);
		$(".trp-move").text(troops[id].move);
		$(".trp-moveleft").text(troops[id].moveleft);
		trpeCtx.clearRect(0,0,64,64);
		trpeCtx.drawImage(sprites,(troops[id].sprite%8)*16,parseInt(troops[id].sprite/8)*16,16,16,0,0,64,64);
	}
	else if(kind==0) {
		$(".trp-name").text(troops[id].name);
		$(".trp-owner").text(troops[id].nation);
		$(".trp-size").text(troops[id].size);
		$(".trp-power").text(troops[id].power);
		$(".trp-health").text(troops[id].health+"%");
		var type="";
		if(troops[id].mobile) {
			if(type.length>0)
				type+=", ";
			type="Mobility";
		}
		if(troops[id].ranged) {
			if(type.length>0)
				type+=", ";
			type+="Ranged"
		}
		if(type.length==0) {
			type="Basic";
		}
		$(".trp-type").text(type);
		$(".trp-position").text("X: "+troops[id].x+", Z: "+troops[id].z);
		$(".trp-move").text(troops[id].move);
		$(".trp-moveleft").text(troops[id].moveleft);
		trpvCtx.clearRect(0,0,64,64);
		trpvCtx.drawImage(sprites,(troops[id].sprite%8)*16,parseInt(troops[id].sprite/8)*16,16,16,0,0,64,64);
		if(troops[id].owned)
			$("#editbtn").addClass("show");
		else
			$("#editbtn").removeClass("show");
	}
}
function setTroopCalcs() {
	var tempPower=parseInt((parseInt($("#trpn-cost").val())+parseInt($("#trpn-size").val()))/1000);
	var tempMove=Math.min(Math.pow(2,Math.log(1000*tempPower/parseInt($("#trpn-size").val()))),Math.max(parseInt(100000/parseInt($("#trpn-size").val())),1));
	if($("#trpn-mobility").prop("checked"))
		tempMove*=2;
	if($("#trpn-ranged").prop("checked"))
		tempPower/=2;
	$("#trpn-power").text(parseInt(tempPower));
	$("#trpn-move").text(parseInt(tempMove));
}
function createTrp() {
	var data={};
	data.name=$("#trpn-name").val();
	data.owner=$("#trpn-owner").val();
	data.size=$("#trpn-size").val();
	data.cost=$("#trpn-cost").val();
	data.mobility=$("#trpn-mobility").prop("checked");
	data.ranged=$("#trpn-ranged").prop("checked");
	data.x=$("#trpn-x").val();
	data.z=$("#trpn-z").val();
	data.sprite=trpSprite;
	$.getJSON("createTrp.php",data,checkTrpResponse);
}
function editTrp() {
	var data={id:activeTrpId};
	data.name=$("#trpe-name").val();
	data.owner=$("#trpe-owner").val();
	$.getJSON("editTrp.php",data,checkTrpResponse);
}
function deleteTrp() {
	var data={id:activeTrpId};
	$.getJSON("deleteTrp.php",data,checkTrpResponse);
	resetStuff();
}
function moveTrp(x,z) {
	$.getJSON("moveTrp.php",{id:activeTrpId,x:x,z:z},checkTrpResponse);
}
function checkTrpResponse(data) {
	console.log(data);
	var foo="ven";
	if(data.status==0) {
		menuActive=false;
		for(var i=0; i<trpMenuActive.length; i++) {
			trpMenuActive[i]=false;
			$("#trp"+foo[i]+"Menu").removeClass("shown");
		}
	}
	if(data.action!="move")
		addBanner(data.text);
	$.getJSON("getMarkers.php",function (data) {
		troops=data.troops;
	});
}
function addBanner(txt) {
	$("#bannerholder").append("<div class=\"banner\" id=\"banner-"+bannercount+"\">"+txt+"</div>");
	banners.push(bannercount);
	bannercount++;
	setTimeout(removeBanner,5000);
}
function removeBanner() {
	$("#banner-"+banners.splice(0,1)[0]).remove();
}


