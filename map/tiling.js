timer=(new Date()).getTime();
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
pinsFound=false;
gotoPinPending="";
jumpMenuActive=false;
instMenuActive=true;
menuActive=true;
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
document.addEventListener("keydown", move);
$.getJSON("tileIds.json",function (data) {tileIds=data});
window.onresize = function(e) {
	canvasResize();
	moving();
};
window.onwheel=function(e) {e.preventDefault(); return false;};
window.ontouchmove=function(e) {e.preventDefault(); return false;};
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
		$("#instr").addClass("hide");
		instMenuActive=false;
		menuActive=false;
		$("#sideMenu").addClass("shown");
	}
	setInterval(draw,1000)
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
		markers=data;
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
	});
	draw();
	document.getElementById("jumpbutton").addEventListener("click",function() {
		$("#jumpMenuMobile").addClass("shown");
		jumpMenuActive=true;
		menuActive=true;
	});
	document.getElementById("pinbutton").addEventListener("click",function() {
		if(pointsVis && selectedPoint!=0) {
			resetStuff();
		}
		pointsVis=!pointsVis;
		$("#pinbutton").toggleClass("active");
	});
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
	canvas.addEventListener('mousewheel', function(event) {zoom(event); return false;}, false);
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
}
function canvasResize() {
	canvas=document.getElementById("mcmap");
	if(typeof mapCnv != "undefined") {
		width=mapCnv.width=boxCnv.width=pinCnv.width=canvas.width=$(window).width();
		height=mapCnv.height=boxCnv.height=pinCnv.height=canvas.height=$(window).height();
		mapCtx.imageSmoothingEnabled=false;
		boxCtx.imageSmoothingEnabled=false;
		pinCtx.imageSmoothingEnabled=false;
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
	ctx.drawImage(pinCnv,0,0);
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
	boxCtx.clearRect(0,0,width,height);
	var clickedMark=false;
	var whichMark=-1;
	var markDist=Infinity;
	if(pointsVis) {
		for(var i=0; i<markers.length; i++) {
			var dist=Math.sqrt(Math.pow(xXct-(markers[i].x+0.5)/128*tileSize-tileSize/2,2)+Math.pow(yXct-(markers[i].z+0.5)/128*tileSize-tileSize/2,2));
			if(dist<markDist && (((dist<15 && selectedPoint==markers[i].id) || dist<10)) || (((dist<60 && selectedPoint==markers[i].id) || dist<40) && isMobile)) {
				clickedMark=true;
				whichMark=i;
				markDist=dist;
			}
		}
	}
	if(clickedMark) {
		if(selectedPoint!=markers[whichMark].id) {
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
	drawMain();
}
function resetStuff() {
	$("#infoTxt")[0].innerHTML="";
	$("#infoTxt").removeClass("shown");
	selectedPoint=0;
	drawPoints();
	lastTar=[Infinity,Infinity];
}
function setHash() {
	history.replaceState(undefined, undefined, "#x="+(pos[0]+offsetPos[0])+"&z="+(pos[1]+offsetPos[1])+"&zoom="+tileSize/128+"&dimension="+dimension);
}


