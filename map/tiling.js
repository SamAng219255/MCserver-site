class RotaryButton {
	constructor(icon,name,desc,func,data) {
		this.icon=icon;
		this.buff=-1;
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
			var tmp=new RotaryButton(this.icon,this.name,this.desc,this.action,JSON.parse(JSON.stringify(this.data)));
			tmp.buff=this.buff;
			return tmp;
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
setHashTimeout=-1;
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
commanderMenuActive=false;
pinMenuActive=false;
genericMenuActive=false;
menuActive=false;
currentpinmenu="";
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
updateBattleHealths=[];
currentBattleHealths=[];
tileDest={};
defaultTile=document.createElement('img');
defaultTile.src='img/default.png';
statusSprites=document.createElement('img');
statusSprites.src='img/status.png';
isAdmin=false;
spriteoptions=[];
trpSprite=0;
isSpriteCustom=false;
custompins={};
pinoptions=[];
bannercount=0;
banners=[];
troops=[];
battles={};
troopshandelling=false;
troopsaction="";
newsprite=document.createElement("img");
rotarySprites=[];
actionState="default";
lastCustSprite=0;
commanders=[];
shownCommanders={};
pinSpritesAcquired=false;
idsRetrieved=false;
useAltCtrls=false;
baseTileSize=512;
for(var i=0; i<3; i++) {
	rotarySprites.push(document.createElement('img'));
}
rotarySprites[0].src="./img/rotarySprites.png";
rotarySprites[1].src="./img/rotarySpriteShadows.png";
rotarySprites[2].src="./img/rotarySpritesBlack.png";
buffSprites=document.createElement('img');
buffSprites.src="./img/buff.png";
controlBtns_old=[
	new RotaryButton(0,"Fortify","Costs 2 moves.\nMove the army into defensive position giving an advantage against attackers.",trpAction),
	new RotaryButton(1,"Rest","Costs 2 moves.\nSpend some time healing up. Recovers HP.",trpAction),
	new RotaryButton(2,"Move","Costs 1 move.\nRelocate army.",trpAction),
	new RotaryButton(3,"View","View the army's information.",viewTrp)
];
attackBtns_old=[
	new RotaryButton(4,"Attack","Costs 2 moves.\nEngage in combat.",trpAction),
	new RotaryButton(5,"Hit & Run","Costs 4 moves.\nCharge up and attack then pull back avoiding damage.",trpAction),
	new RotaryButton(6,"Shoot","Costs 2 moves.\nLaunch a long range attack.",trpAction),
	new RotaryButton(7,"Spy","View the army's information.",function(){
		setTroopMenu(0,targetArmy);
		$("#trpvMenu").addClass("shown");
		trpMenuActive[0]=true;
		menuActive=true;
	})
];
helpBtns_old=[
	new RotaryButton(0,"Aid","Costs 2 moves.\nMove your army to aid the target army temporarily granting it half your strength.",trpAction),
	new RotaryButton(1,"Heal","Costs 2 moves.\nSpend some time healing the target army.",trpAction),
	new RotaryButton(2,"Merge","Merge the your army with the target army.",trpAction),
	new RotaryButton(3,"View","View the army's information.",function(){
		setTroopMenu(0,targetArmy);
		$("#trpvMenu").addClass("shown");
		trpMenuActive[0]=true;
		menuActive=true;
	})
];
controlBtns=[
	new RotaryButton(3,"View","View the army's information.",viewTrp,{story:true}),
	new RotaryButton(0,"Fortify","Move the army into defensive position giving an advantage against attackers.",trpAction,{story:true}),
	new RotaryButton(1,"Rest","Spend some time healing up.",function(data){
		createPrompt(["Please set the army's new health value."],"Set",function(newHealth,data){
			data.health=parseFloat(newHealth);
			trpAction(data);
		},[data],[troops[selectedArmy].health]);
	},{story:true}),
	new RotaryButton(2,"Move","Relocate army.",trpAction,{story:true})
];
controlBattleBtns=[
	new RotaryButton(3,"View","View the army's information.",viewTrp,{story:true}),
	new RotaryButton(4,"Update Health","Update the current health of the involved armies.",function(data){
		createVariedCountPrompt(updateBattleHealths,"Set",function(newHealths,data){
			for(let i=0; i<newHealths.length; i++) {
				newHealths[i]=parseFloat(newHealths[i]);
			}
			data.healths=newHealths;
			data.armies=[];
			for(let i=0; i<battles[troops[selectedArmy].battle].length; i++) {
				data.armies.push(troops[battles[troops[selectedArmy].battle][i]].name);
			}
			trpAction(data);
		},[data],currentBattleHealths);
	},{story:true}),
	new RotaryButton(4,"Exit Battle","Finish battle and set the final health values of the involved armies.",function(data){
		createVariedCountPrompt(updateBattleHealths,"Set",function(newHealths,data){
			for(let i=0; i<newHealths.length; i++) {
				newHealths[i]=parseFloat(newHealths[i]);
			}
			data.healths=newHealths;
			data.armies=[];
			for(let i=0; i<battles[troops[selectedArmy].battle].length; i++) {
				data.armies.push(troops[battles[troops[selectedArmy].battle][i]].name);
			}
			trpAction(data);
		},[data],currentBattleHealths);
	},{story:true})
];
attackBtns=[
	new RotaryButton(7,"Spy","View the army's information.",function(){
		setTroopMenu(0,targetArmy);
		$("#trpvMenu").addClass("shown");
		trpMenuActive[0]=true;
		menuActive=true;
	},{story:true}),
	new RotaryButton(4,"Enter Battle","Engage in combat.",trpAction,{story:true}),
	new RotaryButton(5,"Attack","Engage in combat, completing immediately, and update the health of the your army and the target army. (Aiding armies will not be affected.)",function(data){
		createPrompt(["Please set the attacking army's new health value.","Please set the attacked army's new health value."],"Set",function(newHealth1,newHealth2,data){
			data.attackerHealth=parseFloat(newHealth1);
			data.targetHealth=parseFloat(newHealth2);
			trpAction(data);
		},[data],[troops[selectedArmy].health,troops[targetArmy].health]);
	},{story:true}),
	new RotaryButton(6,"Shoot","Launch a long range attack and set the target's new health.",function(data){
		createPrompt(["Please set the attacked army's new health value."],"Set",function(newHealth,data){
			data.health=parseFloat(newHealth);
			trpAction(data);
		},[data],[troops[selectedArmy].health]);
	},{story:true})
];
helpBtns=[
	new RotaryButton(0,"Aid","Move your army to aid the target army. (Will follow and join battles with the target army.)",trpAction,{story:true}),
	new RotaryButton(1,"Heal","Spend some time healing the target army.",function(data){
		createPrompt(["Please set the healed army's new health value."],"Set",function(newHealth,data){
			data.health=parseFloat(newHealth);
			trpAction(data);
		},[data],[troops[selectedArmy].health]);
	},{story:true}),
	new RotaryButton(2,"Merge","Merge the your army with the target army.",trpAction,{story:true}),
	new RotaryButton(3,"View","View the army's information.",function(){
		setTroopMenu(0,targetArmy);
		$("#trpvMenu").addClass("shown");
		trpMenuActive[0]=true;
		menuActive=true;
	},{story:true})
];
specials={name:['combat','defense','open','mobility','ranged','healing','fortify','helpful','nomanleft','lucky'],title:['Combat','Defense','Honor','Mobility','Ranged','Healing','Fortification','Helpful','"No Man Left Behind"','Lucky'],desc:['Bonus to attack strength.','Bonus to defense strength.','Higher bonus to standard Attacks.','Higher bonus to Hit & Run.','Higher bonus to Shooting.','Increased healing effectiveness.','Increased bonus from being Fortified','Grants additional strength when aiding another army.','Less likely to lose troops when taking damage.','The tides of battle tend more to your favor.']};
tooltip=new RotaryButton(-1,"","",function(){});
tooltip.active=-1;
document.addEventListener("keydown", move);
$.getJSON("tileIds.json",function (data) {tileIds=data; idsRetrieved=true;});
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
	NumberFormat=Intl.NumberFormat();
	sprites=document.createElement("img");
	sprites.src="./img/sprites.png";
	sprites.addEventListener("load",function(){
		for(var i=0; i<spriteoptions.length; i++) {
			//spriteoptions[i][1].drawImage(sprites,(i%8)*16,parseInt(i/8)*16,16,16,0,0,64,64);
			drawSpriteFromMap(spriteoptions[i][1],0,0,sprites,i,16,64);
		}
	});
	customsprites=[];
	spritemenu=$("#spritemenu");
	pinspritemenu=$("#pinspritemenu");
	trpncnv=$("#trpnspritecnv");
	pincnv=$("#pinspritecnv");
	pinctx=pincnv[0].getContext("2d");
	pinctx.imageSmoothingEnabled=false;
	trpecnv=$("#trpespritecnv");
	trpvcnv=$("#trpvspritecnv");
	setInterval(function(){draw(); $.getJSON("getMarkers.php",function(data){
		troops=data.troops;
		if(selectedArmy!==-1)
			$("#infoTxt")[0].innerHTML="<b>"+troops[selectedArmy].name+"</b><br>Nation: "+troops[selectedArmy].nation+", Size: "+NumberFormat.format(troops[selectedArmy].size)+", Health: "+troops[selectedArmy].health+"%<br><span onclick=\"viewTrp()\">Details</span>";
		battles={};
		for(let i=0; i<troops.length; i++) {
			if(troops[i].state!==6) 
				continue;
			if(battles[troops[i].battle]===undefined)
				battles[troops[i].battle]=[i];
			else
				battles[troops[i].battle].push(i);
		}
		customspritedata=data.sprites;
		for(var i=lastCustSprite+1; i<customspritedata.length; i++) {
			if(customspritedata[i].type=="army") {
				customsprites.push(document.createElement('img'));
				customsprites[customsprites.length-1].src="img/uploads/"+customspritedata[i].name;customsprites.length-1
				customsprites[customsprites.length-1].width=customspritedata[i].width;
				customsprites[customsprites.length-1].height=customspritedata[i].height;
				spriteoptions.push([document.createElement("canvas")]);
				spriteoptions[spriteoptions.length-1][0].width=spriteoptions[spriteoptions.length-1][0].height=64;
				spriteoptions[spriteoptions.length-1][0].i=customsprites.length-1;
				spriteoptions[spriteoptions.length-1][0].custom=true;
				spriteoptions[spriteoptions.length-1].push(spriteoptions[spriteoptions.length-1][0].getContext("2d"));
				spriteoptions[spriteoptions.length-1][1].imageSmoothingEnabled=false;
				spriteoptions[spriteoptions.length-1][0].addEventListener("click",selectSprite);
				spriteoptions[spriteoptions.length-1][0].className="sprite-custom";
				spritemenu.append(spriteoptions[spriteoptions.length-1][0]);
				customsprites[customsprites.length-1].cnvId=spriteoptions.length-1;
				customsprites[customsprites.length-1].addEventListener("load",function(e){
					spriteoptions[e.target.cnvId][1].drawImage(e.target,0,0,64,64)
				});
				if(i>lastCustSprite)
					lastCustSprite=i;
			}
		}
		relations=data.relations;
		commanders=data.commanders;
		setCommanderLists();
		nationColors=data.colors;
		nationColorsIndex=data.knownnations;
		setNationStyles();
	}); $.getJSON("../dailyrefresh.php",console.log)},1000);
	for(var i=0; i<specials.name.length; i++) {
		var elem=$("<li><label title=\""+specials.desc[i]+"\"><input type=\"checkbox\" value=\""+specials.name[i]+"\" class=\"addcommspec\"><span>"+specials.title[i]+"</span></label></li>");
		$("#comm-spec").append(elem);
	}
	$(".addcommspec").on("change",commCheckHandler);
	var dataStr=window.location.hash.split("#");
	var data={};
	if(dataStr.length>1) {
		var dataArr=dataStr[1].split("&");
		for(var i=0; i<dataArr.length; i++) {
			data[dataArr[i].split("=")[0]]=dataArr[i].split("=")[1];
		}
		if(data["x"]!==undefined && data["z"]!==undefined) {
			tempPos[0]=parseFloat(data["x"]);
			tempPos[1]=parseFloat(data["z"]);
			pos[0]=Math.floor(tempPos[0]);
			offsetPos[0]=tempPos[0]-pos[0];
			pos[1]=Math.floor(tempPos[1]);
			offsetPos[1]=tempPos[1]-pos[1];
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
	tileSize=baseTileSize;
	startingTileSize=baseTileSize;
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
		if(selectedArmy!==-1)
			$("#infoTxt")[0].innerHTML="<b>"+troops[selectedArmy].name+"</b><br>Nation: "+troops[selectedArmy].nation+", Size: "+NumberFormat.format(troops[selectedArmy].size)+", Health: "+troops[selectedArmy].health+"%<br><span onclick=\"viewTrp()\">Details</span>";
		battles={};
		for(let i=0; i<troops.length; i++) {
			if(troops[i].state!==6) 
				continue;
			if(battles[troops[i].battle]===undefined)
				battles[troops[i].battle]=[i];
			else
				battles[troops[i].battle].push(i);
		}
		customspritedata=data.sprites;
		customsprites=[];
		var adjustedI=0;
		var pinI=0;
		for(var i=0; i<customspritedata.length; i++) {
			if(customspritedata[i].type=="army") {
				customsprites.push(document.createElement('img'));
				customsprites[adjustedI].src="img/uploads/"+customspritedata[i].name;
				customsprites[adjustedI].width=customspritedata[i].width;
				customsprites[adjustedI].height=customspritedata[i].height;
				customsprites[adjustedI].spriteid=customspritedata[i].id;
				spriteoptions.push([document.createElement("canvas")]);
				spriteoptions[spriteoptions.length-1][0].width=spriteoptions[spriteoptions.length-1][0].height=64;
				spriteoptions[spriteoptions.length-1][0].i=adjustedI;
				spriteoptions[spriteoptions.length-1][0].custom=true;
				spriteoptions[spriteoptions.length-1].push(spriteoptions[spriteoptions.length-1][0].getContext("2d"));
				spriteoptions[spriteoptions.length-1][1].imageSmoothingEnabled=false;
				spriteoptions[spriteoptions.length-1][0].addEventListener("click",selectSprite);
				spriteoptions[spriteoptions.length-1][0].className="sprite-custom";
				spritemenu.append(spriteoptions[spriteoptions.length-1][0]);
				customsprites[adjustedI].cnvId=spriteoptions.length-1;
				customsprites[adjustedI].addEventListener("load",function(e){
					spriteoptions[e.target.cnvId][1].drawImage(e.target,0,0,64,64)
				});
				if(i>lastCustSprite)
					lastCustSprite=i;
				adjustedI++;
			}
			else if(customspritedata[i].type=="pin") {
				var name=customspritedata[i].name;
				custompins[name]={img:document.createElement('img'),i:pinI};
				custompins[name].img.src="img/uploads/"+name;
				custompins[name].img.width=customspritedata[i].width;
				custompins[name].img.height=customspritedata[i].height;
				pinoptions.push({cnv:document.createElement("canvas")});
				pinoptions[pinI].cnv.width=pinoptions[pinI].cnv.height=48;
				pinoptions[pinI].cnv.i=pinI;
				pinoptions[pinI].cnv.spritename=name;
				pinoptions[pinI].ctx=pinoptions[pinI].cnv.getContext("2d");
				pinoptions[pinI].ctx.imageSmoothingEnabled=false;
				pinoptions[pinI].cnv.addEventListener("click",selectPinSprite);
				pinoptions[pinI].cnv.className="sprite-custom sprite-pin";
				custompins[name].img.cnvId=pinI;
				pinspritemenu.append(pinoptions[pinI].cnv);
				custompins[name].img.addEventListener("load",function(e){
					pinoptions[e.target.cnvId].ctx.drawImage(e.target,0,0,48,48);
				});
				pinI++;
			}
		}
		pinSpritesAcquired=true;
		relations=data.relations
		commanders=data.commanders;
		setCommanderLists();
		nationColors=data.colors;
		nationColorsIndex=data.knownnations;
		setNationStyles();
	});
	draw();
	if(!isMobile)
	document.getElementById("infoButton").addEventListener("click",function() {
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
	document.getElementById("jumpButton").addEventListener("click",function() {
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
	document.getElementById("pinButton").addEventListener("click",function() {
		if(pointsVis && selectedPoint!=0) {
			resetStuff();
		}
		pointsVis=!pointsVis;
		$("#pinButton").toggleClass("active");
	});
	if(isAdmin) {
		document.getElementById("addPinButton").addEventListener("click",function() {
			if(!menuActive) {
				resetPinMenu();
				$("#pinMenu").addClass("shown");
				pinMenuActive=true;
				menuActive=true;
			}
			else if(pinMenuActive) {
				$("#pinMenu").removeClass("shown");
				pinMenuActive=false;
				menuActive=false;
			}
		});
		document.getElementById("addTroopButton").addEventListener("click",function() {
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
		document.getElementById("commanderBtn").addEventListener("click",function() {
			if(!menuActive) {
				$("#commanderMenu").addClass("shown");
				commanderMenuActive=true;
				menuActive=true;
			}
			else if(commanderMenuActive) {
				$("#commanderMenu").removeClass("shown");
				commanderMenuActive=false;
				menuActive=false;
			}
		});
	}
	spritemenu[0].addEventListener("mouseleave",function() {
		spritemenu.removeClass("show");
	});
	trpncnv[0].addEventListener("click",function() {
		spritemenu.addClass("show");
		spritemenu[0].scroll(0,0);
	});
	pinspritemenu.on("mouseleave",function() {
		pinspritemenu.removeClass("show");
	});
	pincnv.on("click",function() {
		pinspritemenu.addClass("show");
		pinspritemenu[0].scroll(0,0);
	});
	$(".pinicon").on("change",function(e) {
		$(".icondata").removeClass("shown");
		$(".icon-"+$(e.originalEvent.srcElement).val()).addClass("shown");
	});
	document.getElementById("editpin").addEventListener("click",function(e) {
			if(!menuActive) {
				setPinMenu();
				$("#pinMenu").addClass("shown");
				pinMenuActive=true;
				menuActive=true;
			}
			else if(pinMenuActive) {
				$("#pinMenu").removeClass("shown");
				pinMenuActive=false;
				menuActive=false;
			}
	});
	$("#uploadpin").ajaxForm({dataType:"json",success:function(data){
		console.log(data);
		addBanner(data.text);
		if(data.status==0) {
			resetPinSprites();
		}
	}});
	$("#uploadarmy").ajaxForm({dataType:"json",success:function(data){
		console.log(data);
		addBanner(data.text);
		newsprite=document.createElement("img");
		newsprite.src="img/uploads/"+data.name;
		trpnCtx.clearRect(0,0,64,64);
		newsprite.addEventListener("load",function(){
			trpnCtx.drawImage(newsprite,0,0,64,64);
		});
		isSpriteCustom=true;
		trpSprite=data.id;
	}});
	setupControls();
	$("#comm-ntn").val(nation);
	$.getJSON('getPreferences.php',{},function(data){
		if(data.status>1)
			console.log(data);
		scrollmode.checked=useAltCtrls=data.scrollmode;
		if(scrollmode.checked)
			document.getElementById('scroll-mode-mode').innerHTML='Current: Scrolling will zoom the map.';
		else
			document.getElementById('scroll-mode-mode').innerHTML='Current: Scrolling will move the map.';
	});
	scrollmode=document.getElementById('scroll-mode');
	scrollmode.addEventListener('change',function(e){
		useAltCtrls=scrollmode.checked;
		if(scrollmode.checked)
			document.getElementById('scroll-mode-mode').innerHTML='Current: Scrolling will zoom the map.';
		else
			document.getElementById('scroll-mode-mode').innerHTML='Current: Scrolling will move the map.';
		$.getJSON('setPreferences.php',{scrollmode:useAltCtrls?'1':'0'},function(data){
			console.log(data);
			if(data.status>0)
				addBanner(data.text);
		});
	});
}

panMouseDown=false;
clickMouseDown=false;
mouseLastPos={x:0,y:0};
freshZoom=true;
lastZoom=1;
ignoreMouse=false;
enableDrag=false;
function setupControls() {
	$(".trpn-calc").on("change",setTroopCalcs);
	$(".trpn-calc").on("keyup",setTroopCalcs);
	$(".tab-tab").on("click",function(e){
		$(e.target.parentElement.parentElement.children).removeClass("active");
		$(e.target.parentElement).addClass("active");
	});
	//Setup mouse controls
	document.addEventListener('click', event => {if(event.button===2) event.preventDefault()});
	document.addEventListener('contextmenu', event => event.preventDefault());
	document.getElementById('mcmap').addEventListener('mousedown',function (e) {
		if(ignoreMouse)
			return;
		const newX=e.x;
		const newY=e.y;
		if(e.button==2) {
			panMouseDown=true; 
		}
		else if(e.button==0) {
			clickMouseDown=true;
			control_click(newX, newY);
		}
		mouseLastPos={x:newX, y:newY};
		e.preventDefault();
		return false;
	}, false);
	const mouseupFunc=function (e) {
		if(e.button==2) {
			panMouseDown=false;
		}
		else if(e.button==0) {
			clickMouseDown=false;
		}
		e.preventDefault();
		return false;
	};
	document.getElementById('mcmap').addEventListener('mouseup',mouseupFunc, false);
	document.getElementById('mcmap').addEventListener('mouseleave',mouseupFunc, false);
	document.getElementById('mcmap').addEventListener('mousemove',function (e) {
		curMousePos={x:e.x,y:e.y};
		const newX=e.x;
		const newY=e.y;
		if(panMouseDown) {
			control_pan(newX - mouseLastPos.x, newY - mouseLastPos.y); 
			mouseLastPos={x:newX, y:newY};
		}
		else if(clickMouseDown) {
			control_drag(newX, newY);
		}
		else {
			highlighttroops(e);
		}
		e.preventDefault();
		return false;
	}, false);
	document.getElementById('mcmap').addEventListener('wheel',function (e) {
		if(e.ctrlKey || useAltCtrls^e.shiftKey)
			control_zoom(Math.pow(2,e.wheelDelta/256), e.x, e.y);
		else {
			control_pan(e.wheelDeltaX,e.wheelDeltaY);
		}
		e.preventDefault();
		return false;
	}, false);

	//Setup gesture controls
	hammertime = new Hammer(document.getElementById('mcmap'), {
		recognizers: [
			[Hammer.Pan, {pointers: 2, threshold: 0, direction: Hammer.DIRECTION_ALL}],
			[Hammer.Pinch, {enable: true}, ['pan']],
			[Hammer.Tap],
			[Hammer.Pan, {event: "drag", pointers: 1, threshold: 0, direction: Hammer.DIRECTION_ALL}, ['tap']],
			[Hammer.Press, {event: "startdrag", pointers: 1, time: 0}, ['drag']],
			[Hammer.Press, {event: "startpan", pointers: 2, time: 0}, ['pan']]
		]
	});
	hammertime.on('startpan', function(e) {
		mouseLastPos={x:e.center.x,y:e.center.y};
	});
	hammertime.on('pan', function(e) {
		enableDrag=false;
		control_pan(e.center.x - mouseLastPos.x, e.center.y - mouseLastPos.y);
		mouseLastPos={x:e.center.x,y:e.center.y};
	});
	hammertime.on('pinch', function(e) {
		control_setZoom(e.scale, e.center.x, e.center.y);
		if(e.eventType==4)
			freshZoom=true;
	});
	hammertime.on('tap', function(e) {
		if(e.pointerType!='mouse') {
			control_click(e.center.x, e.center.y);
			ignoreMouse=true;
			setTimeout(function(){ignoreMouse=false;},100);
		}
	});
	hammertime.on('drag', function(e) {if(e.pointerType!='mouse' && enableDrag) control_drag(e.center.x, e.center.y);});
	hammertime.on('startdrag', function(e) {mouseLastPos={x:e.center.x,y:e.center.y}; enableDrag=true;});
}
function control_pan(x,y) {// x: delta x, y: delta y
	var delta=[x,y];
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
function control_zoom(x, xPos, yPos) {// distance zoomed
	const zoom=tileSize/baseTileSize;
	const newZoom=Math.max(zoom*x,0.15);
	const zoomDiff=newZoom/zoom - 1;
	tileSize=newZoom*baseTileSize;
	const tempPos=[pos[0]+offsetPos[0],pos[1]+offsetPos[1]];
	tempPos[0]+=(xPos-width/2)*zoomDiff/tileSize;
	tempPos[1]+=(yPos-height/2)*zoomDiff/tileSize;
	pos[0]=Math.floor(tempPos[0]);
	pos[1]=Math.floor(tempPos[1]);
	offsetPos[0]=tempPos[0]-pos[0];
	offsetPos[1]=tempPos[1]-pos[1];
	offsetPix[0]=-1*offsetPos[0]*tileSize;
	offsetPix[1]=-1*offsetPos[1]*tileSize;
	setHash();
	cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
	draw();
	redrawHighlight();
}
function control_setZoom(x, xPos, yPos) {
	if(freshZoom) {
		freshZoom=false;
		lastZoom=1;
	}
	control_zoom(x/lastZoom, xPos, yPos);
	lastZoom=x;
}
function control_click(x,y) {// x: x position, y: y position
	highlight(x,y);
	mouseLastPos={x:x, y:y};
}
function control_drag(x,y) {// x: x position, y: y position
	control_pan(x - mouseLastPos.x, y - mouseLastPos.y); 
	mouseLastPos={x:x, y:y};
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
		spriteoptions[i][0].custom=false;
		spriteoptions[i].push(spriteoptions[i][0].getContext("2d"));
		spriteoptions[i][1].imageSmoothingEnabled=false;
		spriteoptions[i][0].addEventListener("click",selectSprite);
		spritemenu.append(spriteoptions[i][0]);
	}
}
function selectSprite(event){
	trpnCtx.clearRect(0,0,64,64);
	if(!event.target.custom) {
		//trpnCtx.drawImage(sprites,(event.target.i%8)*16,parseInt(event.target.i/8)*16,16,16,0,0,64,64);
		drawSpriteFromMap(trpnCtx,0,0,sprites,event.target.i,16,64);
		trpSprite=event.target.i;
	}
	else {
		trpnCtx.drawImage(customsprites[event.target.i],0,0,64,64);
		trpSprite=customsprites[event.target.i].spriteid;
	}
	isSpriteCustom=event.target.custom;
	spritemenu.removeClass("show");
}
function canvasResize() {
	canvas=document.getElementById("mcmap");
	if(typeof mapCnv != "undefined") {
		width=mapCnv.width=boxCnv.width=pinCnv.width=trpCnv.width=uiCnv.width=canvas.width=window.innerWidth;
		height=mapCnv.height=boxCnv.height=pinCnv.height=trpCnv.height=uiCnv.height=canvas.height=window.innerHeight;
		mapCtx.imageSmoothingEnabled=false;
		boxCtx.imageSmoothingEnabled=false;
		pinCtx.imageSmoothingEnabled=false;
		trpCtx.imageSmoothingEnabled=false;
	}
	else {
		width=canvas.width=window.innerWidth;
		height=canvas.height=window.innerHeight;
	}
	ctx=canvas.getContext('2d');
	ctx.imageSmoothingEnabled=false;
	cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
}
function gotoPin(pinName) {
	if(pinsFound) {
		for(var i=0; i<markers.length; i++) {
			if(markers[i].name==pinName) {
				const tempPos=[(markers[i].x+0.5)/baseTileSize,(markers[i].z+0.5)/baseTileSize]
				pos[0]=Math.floor(tempPos[0]);
				pos[1]=Math.floor(tempPos[1]);
				offsetPos[0]=tempPos[0]-pos[0];
				offsetPos[1]=tempPos[1]-pos[1];
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
	if(jumpMenuActive && e.srcElement.parentElement.id=="jumpCoordForm"+mobilePinCheck && e.key=='Enter') {
		gotoPoint((parseFloat($("#jumpCoordX"+mobilePinCheck).val())/baseTileSize),(parseFloat($("#jumpCoordZ"+mobilePinCheck).val())/baseTileSize),(parseInt($("#jumpCoordD"+mobilePinCheck).val())));
		setTimeout(function(){jumpMenuActive=false; menuActive=false;},1);
		$("#jumpMenu"+mobilePinCheck).removeClass("shown");
	}
}
function draw() {
	mapCtx.clearRect(0,0,width,height);
	tileDelta=[pos[0]-cornerPos[0],pos[1]-cornerPos[1]];
	for(var i=-Math.ceil(tileDelta[0])-1; i<=Math.ceil(tileDelta[0])+1; i++) {
		for(var j=-Math.ceil(tileDelta[1])-1; j<=Math.ceil(tileDelta[1])+1; j++) {
			var x=(Math.floor(pos[0])+i);
			var y=(Math.floor(pos[1])+j);
			var key="d"+dimension+"x"+x+"y"+y;
			if(!idsRetrieved || tileIds[dimension+"_"+x+"_"+y]==undefined) {
				if(!showMissingTiles) {
					mapCtx.drawImage(defaultTile,offsetPix[0]+(Math.floor(i)+tileDelta[0]+0.5)*tileSize,offsetPix[1]+(Math.floor(j)+tileDelta[1]+0.5)*tileSize,tileSize,tileSize);
				}
			}
			else if(tiles[key]==undefined) {
				tiles[key]=document.createElement('img');
				tiles[key].onerror=function() {
					empty.push(key);
					if (this.src != 'img/default.png') {
						this.src = 'img/default.png'
					};
					preventDefault();
				};
				tiles[key].onload=function () {
					tileDelta=[pos[0]-cornerPos[0],pos[1]-cornerPos[1]];
					const regionData=this.src.split(".");
					const x=parseInt(regionData[regionData.length-3]);
					const y=parseInt(regionData[regionData.length-2]);
					const key="d"+regionData[regionData.length-4]+"x"+x+"y"+y;
					mapCtx.drawImage(defaultTile,offsetPix[0]+(x-pos[0]+tileDelta[0]+0.5)*tileSize,offsetPix[1]+(y-pos[1]+tileDelta[1]+0.5)*tileSize,tileSize,tileSize);
					mapCtx.drawImage(this,offsetPix[0]+(x-pos[0]+tileDelta[0]+0.5)*tileSize,offsetPix[1]+(y-pos[1]+tileDelta[1]+0.5)*tileSize,tileSize,tileSize);
					drawMain();
				};
				if(dimension==0 && true)
					tiles[key].src="img/tiles/r."+dimension+"."+x+"."+y+".png";
				else
					tiles[key].src="img/tile."+dimension+"."+x+"."+y+".png";
			}
			else {
				mapCtx.drawImage(defaultTile,offsetPix[0]+(i+tileDelta[0]+0.5)*tileSize,offsetPix[1]+(j+tileDelta[1]+0.5)*tileSize,tileSize,tileSize);
				mapCtx.drawImage(tiles[key],offsetPix[0]+(i+tileDelta[0]+0.5)*tileSize,offsetPix[1]+(j+tileDelta[1]+0.5)*tileSize,tileSize,tileSize);
			}
		}
	}
	drawPoints();
}
function drawMain() {
	ctx.clearRect(0,0,width,height);
	ctx.drawImage(mapCnv,0,0);// Copy map to view
	ctx.drawImage(boxCnv,0,0);// Copy highlight box to view
	ctx.drawImage(trpCnv,0,0);// Copy troops to view
	ctx.drawImage(pinCnv,0,0);// Copy pins to view
	ctx.drawImage(uiCnv,0,0);// Copy ui elements (rotary menus) to view
}
function move(e) {
	var allowNormalExecution=true;
	var time=(new Date()).getTime();
	if(time-lastButtonPress>100) {
		if(!menuActive) {
			if(e.key=='ArrowLeft' || e.key=='ArrowUp' || e.key=='ArrowRight' || e.key=='ArrowDown' || e.key=='w' || e.key=='a' || e.key=='s' || e.key=='d') {
				boxCtx.clearRect(0,0,width,height);
				resetStuff();
			}
			if(e.key=='ArrowLeft' || e.key=='a') {
				pos[0]-=Math.max(Math.floor(startingTileSize/tileSize),1);
				Object.keys(tileDest).forEach(function(key){tileDest[key][0]++});
			}
			else if(e.key=='ArrowUp' || e.key=='w') {
				pos[1]-=Math.max(Math.floor(startingTileSize/tileSize),1);
				Object.keys(tileDest).forEach(function(key){tileDest[key][1]++});
			}
			else if(e.key=='ArrowRight' || e.key=='d') {
				pos[0]+=Math.max(Math.floor(startingTileSize/tileSize),1);
				Object.keys(tileDest).forEach(function(key){tileDest[key][0]--});
			}
			else if(e.key=='ArrowDown' || e.key=='s') {
				pos[1]+=Math.max(Math.floor(startingTileSize/tileSize),1);
				Object.keys(tileDest).forEach(function(key){tileDest[key][1]--});
			}
			else if(e.key=='p') {
				if(pointsVis && selectedPoint!=0) {
					resetStuff();
				}
				pointsVis=!pointsVis;
			}
			else if(e.key=='j') {
				$("#jumpMenu").addClass("shown");
				jumpMenuActive=true;
				menuActive=true;
			}
			else if(e.key=='i') {
				$("#instr").removeClass("hide");
				instMenuActive=true;
				menuActive=true;
				allowNormalExecution=false;
			}
			else if(e.key=='q') {
				dimension--;
				while(dimension<-1) {
					dimension+=3
				}
				setHash();
				draw();
			}
			else if(e.key=='e') {
				dimension++;
				while(dimension>1) {
					dimension-=3
				}
				setHash();
				draw();
			}
			else if(e.key=='-') {
				boxCtx.clearRect(0,0,width,height);
				resetStuff();
				control_zoom(Math.SQRT1_2,width/2,height/2);
			}
			else if(e.key=='+' || e.key=='=') {
				boxCtx.clearRect(0,0,width,height);
				resetStuff();
				control_zoom(Math.SQRT2,width/2,height/2);
			}
			else {
				//console.log(e.key);
			}
			if(e.key=='ArrowLeft' || e.key=='ArrowUp' || e.key=='ArrowRight' || e.key=='ArrowDown' || e.key=='w' || e.key=='a' || e.key=='s' || e.key=='d') {
				cornerPos=[pos[0]-width/(2*tileSize)+0.5,pos[1]-height/(2*tileSize)+0.5];
				setHash();
				draw();
			}
			drawPoints();
		}
		else if(jumpMenuActive && e.key=='Escape') {
			$("#jumpMenu").removeClass("shown");
			jumpMenuActive=false;
			menuActive=false;
		}
		else if(instMenuActive && (e.key=='Escape' || e.key=='i')) {
			$("#instr").addClass("hide");
			instMenuActive=false;
			menuActive=false;
			//if(e.key=='Tab') {allowNormalExecution=false;}
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
function closeCommanderMenu() {
	$("#commanderMenu").removeClass("shown");
	commanderMenuActive=false;
	menuActive=false;
}
function closePinMenu() {
	$("#pinMenu").removeClass("shown");
	pinMenuActive=false;
	menuActive=false;
}
function closeGenericMenu() {
	$("#genericMenu").removeClass("shown");
	genericMenuActive=false;
	menuActive=false;
}
function highlight(inX,inY) {
	var rawX=inX-offsetPix[0];
	var rawY=inY-offsetPix[1];
	var xf = Math.floor((rawX-width/2)/tileSize)-cornerPos[0]+pos[0];
	var yf = Math.floor((rawY-height/2)/tileSize)-cornerPos[1]+pos[1];
	var x = Math.floor(xf);
	var y = Math.floor(yf);
	var xCor=Math.floor(xf+cornerPos[0]);
	var yCor=Math.floor(yf+cornerPos[1]);
	var xXct=rawX+(cornerPos[0]*tileSize);
	var yXct=rawY+(cornerPos[1]*tileSize);
	var newX=Math.floor(((rawX-width/2)/tileSize+pos[0])*baseTileSize);
	var newZ=Math.floor(((rawY-height/2)/tileSize+pos[1])*baseTileSize);
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
			var dist=Math.sqrt(Math.pow(xXct-(markers[i].x+0.5)/baseTileSize*tileSize-tileSize/2,2)+Math.pow(yXct-(markers[i].z+0.5)/baseTileSize*tileSize-tileSize/2,2));
			if(dist<markDist && (((dist<15 && selectedPoint==markers[i].id) || dist<10)) || (((dist<60 && selectedPoint==markers[i].id) || dist<40) && isMobile)) {
				clickedMark=true;
				whichMark=i;
				markDist=dist;
			}
		}
		for(var i=0; i<troops.length; i++) {
			var dist=Math.sqrt(Math.pow(xXct-(troops[i].x+0.5)/baseTileSize*tileSize-tileSize/2,2)+Math.pow(yXct-(troops[i].z+0.5)/baseTileSize*tileSize-tileSize/2,2));
			if(dist<trpDist && (((dist<26 && (selectedArmy==troops[i].id || targetArmy==troops[i].id)) || (dist<22 && hoverArmy==troops[i].id) || dist<18)) || (((dist<102 && (selectedArmy==troops[i].id || targetArmy==troops[i].id)) || (dist<85 && hoverArmy==troops[i].id) || dist<72) && isMobile)) {
				clickedTrp=true;
				whichTrp=i;
				trpDist=dist;
			}
		}
	}
	for(var i=0; i<activeRotaryBtns.length; i++) {
		//drawCircle(ctx,activeRotaryBtns[i].x,activeRotaryBtns[i].y,activeRotaryBtns[i].radius,"#ffff00");
		var dist=Math.sqrt(Math.pow(inX-activeRotaryBtns[i].x,2)+Math.pow(inY-activeRotaryBtns[i].y,2));
		if(dist<btnDist && dist<activeRotaryBtns[i].radius) {
			clickedBtn=true;
			whichBtn=i;
			btnDist=dist;
		}
	}
	//drawCircle(ctx,x,y,16,"#0000ff");
	if(clickedBtn) {
		activeRotaryBtns[whichBtn].action(activeRotaryBtns[whichBtn].data);
	}
	else if(clickedTrp && ((actionState!="move" && actionState!="move_story") || selectedArmy==whichTrp)) {
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
				if(troops[selectedArmy].state==6) {		
					updateBattleHealths.length=0;
					currentBattleHealths.length=0;
					var battle=battles[troops[selectedArmy].battle];
					for(let i=0; i<battle.length; i++) {
						updateBattleHealths.push('Please enter the new health for the army "'+troops[battle[i]].name+'".');
						currentBattleHealths.push(troops[battle[i]].health);
					}
				}
				$("#infoTxt")[0].innerHTML="<b>"+troops[whichTrp].name+"</b><br>Nation: "+troops[whichTrp].nation+", Size: "+NumberFormat.format(troops[whichTrp].size)+", Health: "+troops[whichTrp].health+"%<br><span onclick=\"viewTrp()\">Details</span>";
				$("#infoTxtBox").addClass("shown");
			}
		}
		else {
			if((actionState!="move" && actionState!="move_story")) {
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
	else if(actionState=="move" && Math.pow(newX-troops[selectedArmy].x,2)+Math.pow(newZ-troops[selectedArmy].z,2)<=4096) {
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
	else if(actionState=="move_story") {
		console.log("Passed",newX,newZ,",",troops[selectedArmy].x,troops[selectedArmy].z);
		sendAltTrpAction({id:troops[selectedArmy].id,x:newX,z:newZ,action:"move"});
		troops[selectedArmy].x=newX;
		troops[selectedArmy].z=newZ;
		selectedArmy=-1;
		actionState="default";
		drawUI();
		resetStuff();
	}
	else if(clickedMark) {
		if(selectedPoint!=markers[whichMark].id) {
			clickedPin=whichMark;
			selectedArmy=-1;
			actionState="default";
			lastTar=[Infinity,Infinity];
			selectedPoint=markers[whichMark].id;
			editTxt="";
			if(markers[whichMark].owned) $("#editpin").addClass("shown");
			else $("#editpin").removeClass("shown");
			$("#infoTxt")[0].innerHTML="<b>"+markers[whichMark].name+"</b>: "+markers[whichMark].desc;
			$("#infoTxtBox").addClass("shown");
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
		boxCtx.strokeRect(offsetPix[0]+(xf+0.5)*tileSize, offsetPix[1]+(yf+0.5)*tileSize, tileSize, tileSize);
		boxCtx.strokeStyle="#FFFFFF";
		boxCtx.lineWidth=4;
		boxCtx.strokeRect(offsetPix[0]+(xf+0.5)*tileSize, offsetPix[1]+(yf+0.5)*tileSize, tileSize, tileSize);
		$("#infoTxt")[0].innerHTML="Highlighted region ("+xCor+", "+yCor+"), coordinates ("+((xCor*baseTileSize))+", "+((yCor*baseTileSize))+") to ("+(((xCor+1)*baseTileSize)-1)+", "+(((yCor+1)*baseTileSize)-1)+").";
		$("#infoTxtBox").addClass("shown");
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
	if(pointsVis && actionState!="move" && actionState!="move_story") {
		for(var i=0; i<troops.length; i++) {
			var dist=Math.sqrt(Math.pow(xXct-(troops[i].x+0.5)/baseTileSize*tileSize-tileSize/2,2)+Math.pow(yXct-(troops[i].z+0.5)/baseTileSize*tileSize-tileSize/2,2));
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
		trpPos=[offsetPix[0]+(troops[selectedArmy].x+0.5)/baseTileSize*tileSize-cornerPos[0]*tileSize+tileSize/2,offsetPix[1]+(troops[selectedArmy].z+0.5)/baseTileSize*tileSize-cornerPos[1]*tileSize+tileSize/2];
	if(clickedTrp || clickedBtn || (actionState=="move" && Math.sqrt(Math.pow(trpPos[0]-e.x,2)+Math.pow(trpPos[1]-e.y,2))<=64*tileSize/baseTileSize) || actionState=="move_story")
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
		boxCtx.strokeRect(offsetPix[0]+(xf+0.5)*tileSize, offsetPix[1]+(yf+0.5)*tileSize, tileSize, tileSize);
		boxCtx.strokeStyle="#FFFFFF";
		boxCtx.lineWidth=4;
		boxCtx.strokeRect(offsetPix[0]+(xf+0.5)*tileSize, offsetPix[1]+(yf+0.5)*tileSize, tileSize, tileSize);
		$("#infoTxt")[0].innerHTML="Highlighted region ("+xCor+", "+yCor+"), coordinates ("+((xCor*baseTileSize))+", "+((yCor*baseTileSize))+") to ("+(((xCor+1)*baseTileSize)-1)+", "+(((yCor+1)*baseTileSize)-1)+").";
		$("#infoTxtBox").addClass("shown");
		drawMain();
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
	var posRel=[pos[0]*baseTileSize,pos[1]*baseTileSize];
	var delta=[(pos[0]-cornerPos[0])*baseTileSize,(pos[1]-cornerPos[1])*baseTileSize];
	for(var i=0; i<markers.length; i++) {
		if(markers[i].x>=cornerPos[0]-1 && markers[i].x<=cornerPos[0]+width/tileSize*baseTileSize+1 && markers[i].z>=cornerPos[1]-1 && markers[i].z<=cornerPos[1]+height/tileSize*baseTileSize+1) {
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
				var posAdj=[offsetPix[0]+(markers[i].x+0.5)/baseTileSize*tileSize-posRel[0]+tileSize/2,offsetPix[1]+(markers[i].z+0.5)/baseTileSize*tileSize-posRel[1]+tileSize/2];
				var sizeMod=1;
				if(markers[i].id==selectedPoint) {
					sizeMod=Math.sqrt(2);
				}
				if(isMobile) sizeMod*=2;
				if(markers[i].type=="default") {
					drawCircle(pinCtx,posAdj[0]+(2*sizeMod),posAdj[1]+(2*sizeMod),10*sizeMod,"#000000");
					drawCircle(pinCtx,posAdj[0],posAdj[1],10*sizeMod,"#"+markers[i].icondata);
					drawCircle(pinCtx,posAdj[0]-(3*sizeMod),posAdj[1]-(3*sizeMod),4*sizeMod,"#"+parseInt((256+parseInt(markers[i].icondata.substr(0,2),16))/2).toString(16)+parseInt((256+parseInt(markers[i].icondata.substr(2,2),16))/2).toString(16)+parseInt((256+parseInt(markers[i].icondata.substr(4,2),16))/2).toString(16));
				}
				else if(markers[i].type=="custom") {
					if(pinSpritesAcquired)
						pinCtx.drawImage(custompins[markers[i].icondata].img,posAdj[0]-15*sizeMod,posAdj[1]-15*sizeMod,30*sizeMod,30*sizeMod);
					else {
						drawCircle(pinCtx,posAdj[0]+(2*sizeMod),posAdj[1]+(2*sizeMod),10*sizeMod,"#000000");
						drawCircle(pinCtx,posAdj[0],posAdj[1],10*sizeMod,"#ff0000");
						drawCircle(pinCtx,posAdj[0]-(3*sizeMod),posAdj[1]-(3*sizeMod),4*sizeMod,"#ff8080");
				}
				}
			}
		}
	}
	drawTroops();
	drawMain();
}
function resetStuff() {
	$("#infoTxt")[0].innerHTML="";
	$("#infoTxtBox").removeClass("shown");
	$("#editpin").removeClass("shown")
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
	/*clearTimeout(setHashTimeout);
	setHashTimeout=setTimeout(function(){history.replaceState(undefined, undefined, "#x="+(pos[0]+offsetPos[0])+"&z="+(pos[1]+offsetPos[1])+"&zoom="+tileSize/baseTileSize+"&dimension="+dimension)},1000);*/
}
function drawTroops() {
	trpCtx.clearRect(0,0,width,height);
	if(pointsVis) {
		var posRel=[cornerPos[0]*tileSize,cornerPos[1]*tileSize];
		for(var i=0; i<troops.length; i++) {
			if(troops[i].dimension==dimension) {
				var posAdj=[offsetPix[0]+(troops[i].x+0.5)/baseTileSize*tileSize-posRel[0]+tileSize/2,offsetPix[1]+(troops[i].z+0.5)/baseTileSize*tileSize-posRel[1]+tileSize/2];
				var sizeMod=1;
				var mobMod=1;
				if(i==hoverArmy) {
					sizeMod=1.25;
				}
				if(i==selectedArmy || i==targetArmy) {
					sizeMod=1.5;
				}
				if(isMobile) {
					sizeMod*=2;
					mobMod=2;
				}
				drawCircle(trpCtx,posAdj[0],posAdj[1],18*sizeMod,"#"+troops[i].color);
				if(!troops[i].customsprite) {
					trpCtx.imageSmoothingEnabled=false;
					//trpCtx.drawImage(sprites,(troops[i].sprite%8)*16,parseInt(troops[i].sprite/8)*16,16,16,posAdj[0]-16*sizeMod,posAdj[1]-16*sizeMod,32*sizeMod,32*sizeMod);
					drawSpriteFromMap(trpCtx,posAdj[0]-16*sizeMod,posAdj[1]-16*sizeMod,sprites,troops[i].sprite,16,32*sizeMod);
				}
				else
					for(var j=0; j<customsprites.length; j++)
						if(troops[i].sprite==customsprites[j].spriteid) {
							if(customsprites[j].width*customsprites[j].height>4096*sizeMod*sizeMod)
								trpCtx.imageSmoothingEnabled=true;
							else
								trpCtx.imageSmoothingEnabled=false;
							trpCtx.drawImage(customsprites[j],posAdj[0]-16*sizeMod,posAdj[1]-16*sizeMod,32*sizeMod,32*sizeMod);
						}
				if(troops[i].state>0) {
					//trpCtx.drawImage(statusSprites,((troops[i].state-1)%4)*64,parseInt((troops[i].state-1)/4)*64,64,64,posAdj[0],posAdj[1],16*sizeMod,16*sizeMod);
					drawSpriteFromMap(trpCtx,posAdj[0],posAdj[1],statusSprites,troops[i].state-1,64,16*sizeMod);
				}
				if(i==selectedArmy) {
					if(troops[selectedArmy].state==6) {
						var trpBtns=RotaryButton.copyGroup(controlBattleBtns);
						if(!troops[i].owned) {
							for(var j=1; j<trpBtns.length; j++) {
								trpBtns[j].active=1;
								trpBtns[j].data.owned=false;
							}
						}
						scheduleRotary(posAdj[0],posAdj[1],mobMod*32,mobMod*96,trpBtns);
					}
					else {
						var trpBtns=RotaryButton.copyGroup(controlBtns);
						if(!troops[i].owned) {
							for(var j=1; j<trpBtns.length; j++) {
								trpBtns[j].active=1;
								trpBtns[j].data.owned=false;
							}
						}
						/*if(troops[selectedArmy].moveleft<1) {
							trpBtns[2].active=1;
							trpBtns[2].data.moveless=true;
						}
						if(troops[selectedArmy].moveleft<2) {
							trpBtns[0].active=1;
							trpBtns[0].data.moveless=true;
							trpBtns[1].active=1;
							trpBtns[1].data.moveless=true;
						}*/
						if(troops[selectedArmy].bonuses.indexOf("fortify")>-1) {
							trpBtns[0].buff++;
						}
						if(troops[selectedArmy].bonuses.indexOf("healing")>-1) {
							trpBtns[1].buff++;
						}
						scheduleRotary(posAdj[0],posAdj[1],mobMod*32,mobMod*96,trpBtns);
					}
				}
				if(i==targetArmy) {
					if(troops[selectedArmy].nation==troops[targetArmy].nation || (relations[troops[selectedArmy].nation][troops[targetArmy].nation]!=undefined && relations[troops[selectedArmy].nation][troops[targetArmy].nation]<4)) {
						var trpBtns=RotaryButton.copyGroup(helpBtns);
						if(!troops[selectedArmy].owned) {
							for(var j=1; j<trpBtns.length; j++) {
								trpBtns[j].active=1;
								trpBtns[j].data.owned=false;
							}
						}
						/*if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)>4096) {
							for(var j=0; j<3; j++) {
								trpBtns[j].active=1;
								trpBtns[j].data.toofar=true;
							}
						}*/
						/*if(troops[selectedArmy].moveleft<2) {
							trpBtns[0].active=1;
							trpBtns[0].data.moveless=true;
							trpBtns[1].active=1;
							trpBtns[1].data.moveless=true;
						}*/
						if(troops[selectedArmy].bonuses.indexOf("healing")>-1) {
							trpBtns[1].buff++;
						}
						scheduleRotary(posAdj[0],posAdj[1],mobMod*32,mobMod*96,trpBtns);
					}
					else {
						var trpBtns=RotaryButton.copyGroup(attackBtns);
						if(!troops[selectedArmy].owned) {
							for(var j=1; j<trpBtns.length; j++) {
								trpBtns[j].active=1;
								trpBtns[j].data.owned=false;
							}
						}
						/*if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)>4096) {
							for(var j=0; j<3; j++) {
								trpBtns[j].active=1;
								trpBtns[j].data.toofar=true;
							}
						}*/
						/*if(troops[selectedArmy].moveleft<2) {
							trpBtns[0].active=1;
							trpBtns[0].data.moveless=true;
							trpBtns[2].active=1;
							trpBtns[2].data.moveless=true;
						}
						if(troops[selectedArmy].moveleft<4) {
							trpBtns[1].active=1;
							trpBtns[1].data.moveless=true;
						}*/
						if(troops[selectedArmy].bonuses.indexOf("open")>-1) {
							trpBtns[0].buff+=2;
						}
						else if(troops[selectedArmy].bonuses.indexOf("combat")>-1) {
							trpBtns[0].buff++;
						}
						if(troops[selectedArmy].mobile) {
							trpBtns[1].buff+=2;
							trpBtns[1].data.ismobile=true;
						}
						else {
							trpBtns[1].data.ismobile=false;
						}
						if(troops[selectedArmy].bonuses.indexOf("mobility")>-1) {
							trpBtns[1].buff+=2;
						}
						else if(troops[selectedArmy].bonuses.indexOf("combat")>-1) {
							trpBtns[1].buff++;
						}
						if(troops[selectedArmy].ranged) {
							trpBtns[2].buff+=2;
							trpBtns[2].data.isranged=true;
						}
						else {
							trpBtns[2].data.isranged=false;
						}
						if(troops[selectedArmy].bonuses.indexOf("ranged")>-1) {
							trpBtns[2].buff+=2;
						}
						else if(troops[selectedArmy].bonuses.indexOf("combat")>-1) {
							trpBtns[2].buff++;
						}
						scheduleRotary(posAdj[0],posAdj[1],mobMod*32,mobMod*96,trpBtns);
					}
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
	while(rotaryQueue.length>0 && actionState!="move" && actionState!="move_story") {
		var stuff=rotaryQueue.splice(0,1)[0];
		drawRotary(stuff[0],stuff[1],stuff[2],stuff[3],stuff[4]);
	}
	if(tooltip.active>-1 && actionState!="move" && actionState!="move_story") {
		var fontSize=16;
		uiCtx.font=fontSize+"px pixel";
		uiCtx.fillStyle="#808080";
		uiCtx.strokeStyle="#404040";
		var lines=tooltip.desc.split("\n");
		lines.splice(0,0,tooltip.name);
		var qualities=["good","really good","great","amazing","exceptional"];
		if(tooltip.buff>=0) {
			lines.push("Your army is "+qualities[tooltip.buff]+" at this action.");
		}
		if(tooltip.data.isranged!=undefined && !tooltip.data.isranged) {
			lines.push("Unless focussed on ranged attacks the army will only deal 1/3 normal damage.");
		}
		if(tooltip.data.ismobile!=undefined && !tooltip.data.ismobile) {
			lines.push("Unless focussed on mobility the army will only deal 1/3 normal damage.");
		}
		if(tooltip.data.owned!=undefined && !tooltip.data.owned) {
			lines.push("You can not perform this action because you do not own this army.");
		}
		if(tooltip.data.toofar!=undefined && tooltip.data.toofar) {
			lines.push("Your army is out of range.");
		}
		if(tooltip.data.moveless!=undefined && tooltip.data.moveless) {
			lines.push("Your army does not have enough moves left.");
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
		var posAdj=[offsetPix[0]+(troops[selectedArmy].x+0.5)/baseTileSize*tileSize-posRel[0]+tileSize/2,offsetPix[1]+(troops[selectedArmy].z+0.5)/baseTileSize*tileSize-posRel[1]+tileSize/2];
		drawCircle(boxCtx,posAdj[0],posAdj[1],64*tileSize/baseTileSize,"rgba("+parseInt(troops[selectedArmy].color.slice(0,2),16)+","+parseInt(troops[selectedArmy].color.slice(2,4),16)+","+parseInt(troops[selectedArmy].color.slice(4,6),16)+",0.5)");
		boxCtx.beginPath();
		boxCtx.strokeStyle="#"+troops[selectedArmy].color;
		boxCtx.lineWidth=4;
		boxCtx.arc(posAdj[0], posAdj[1], 64*tileSize/baseTileSize, 0, 2*Math.PI, true);
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
		btns[i].x=x+radius*Math.cos(Math.TAU*i/btns.length);
		btns[i].y=y-radius*Math.sin(Math.TAU*i/btns.length);
		btns[i].radius=menuwidth/2;
		//uiCtx.drawImage(rotarySprites[btns[i].active],(btns[i].icon%4)*baseTileSize,parseInt(btns[i].icon/4)*baseTileSize,baseTileSize,baseTileSize,btns[i].x-btnSize/2,btns[i].y-btnSize/2,btnSize,btnSize);
		drawSpriteFromMap(uiCtx,btns[i].x-btnSize/2,btns[i].y-btnSize/2,rotarySprites[btns[i].active],btns[i].icon,baseTileSize,btnSize);
		if(btns[i].buff>=0) {
			//uiCtx.drawImage(buffSprites,(btns[i].buff%4)*64,parseInt(btns[i].buff/4)*64,64,64,btns[i].x,btns[i].y,btnSize/2,btnSize/2);
			drawSpriteFromMap(uiCtx,btns[i].x,btns[i].y,buffSprites,btns[i].buff,64,btnSize/2);
		}
		activeRotaryBtns.push(btns[i]);
	}
}
function trpAction(data) {
	console.log("trpAction",data);
	if(data.story) {
		switch(data.name) {
			case "Fortify":
				sendAltTrpAction({id:troops[selectedArmy].id,action:"fortify"});
				break;
			case "Rest":
				sendAltTrpAction({id:troops[selectedArmy].id,newHealth:data.health,action:"rest"});
				break;
			case "Move":
				actionState="move_story";//action:"move"
				drawUI();
				break;
			case "Update Health":
				sendAltTrpAction({id:troops[selectedArmy].id,health_values:data.healths,armies:data.armies,action:"update_battle"});
				break;
			case "Exit Battle":
				sendAltTrpAction({id:troops[selectedArmy].id,health_values:data.healths,armies:data.armies,action:"exit_battle"});
				break;
			case "Enter Battle":
				sendAltTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"enter_battle"});
				break;
			case "Attack":
				sendAltTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,newHealth0:data.attackerHealth,newHealth1:data.targetHealth,action:"attack"});
				break;
			case "Shoot":
				sendAltTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,newHealth:data.health,action:"shoot"});
				break;
			case "Aid":
				sendAltTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"aid"});
				break;
			case "Heal":
				sendAltTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,newHealth:data.health,action:"heal"});
				break;
			case "Merge":
				sendAltTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"merge"});
				break;
		}
	}
	else {
		switch(data.name) {
			case "Fortify":
				if(troops[selectedArmy].moveleft>=1)
					sendTrpAction({id:troops[selectedArmy].id,action:"fortify"});
				else
					addBanner("That unit does not have enough movement left to perform that action.");
				break;
			case "Rest":
				if(troops[selectedArmy].moveleft>=1)
					sendTrpAction({id:troops[selectedArmy].id,action:"rest"});
				else
					addBanner("That unit does not have enough movement left to perform that action.");
				break;
			case "Move":
				actionState="move";
				drawUI();
				break;
			case "Attack":
				if(troops[selectedArmy].moveleft>=1)
					if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)<=4096)
						sendTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"attack"});
					else
						addBanner("Too far away.");
				else
					addBanner("That unit does not have enough movement left to perform that action.");
				break;
			case "Hit & Run":
				if(troops[selectedArmy].moveleft>=2)
					if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)<=4096)
						sendTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"hitrun"});
					else
						addBanner("Too far away.");
				else
					addBanner("That unit does not have enough movement left to perform that action.");
				break;
			case "Shoot":
				if(troops[selectedArmy].moveleft>=1)
					if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)<=4096)
						sendTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"shoot"});
					else
						addBanner("Too far away.");
				else
					addBanner("That unit does not have enough movement left to perform that action.");
				break;
			case "Aid":
				if(troops[selectedArmy].moveleft>=1)
					if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)<=4096)
						sendTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"aid"});
					else
						addBanner("Too far away.");
				else
					addBanner("That unit does not have enough movement left to perform that action.");
				break;
			case "Heal":
				if(troops[selectedArmy].moveleft>=2)
					if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)<=4096)
						sendTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"heal"});
					else
						addBanner("Too far away.");
				else
					addBanner("That unit does not have enough movement left to perform that action.");
				break;
			case "Merge":
				if(troops[selectedArmy].moveleft>=1)
					if(Math.pow(troops[selectedArmy].x-troops[targetArmy].x,2)+Math.pow(troops[selectedArmy].z-troops[targetArmy].z,2)<=4096)
						sendTrpAction({id:troops[selectedArmy].id,target:troops[targetArmy].id,action:"merge"});
					else
						addBanner("Too far away.");
				else
					addBanner("That unit does not have enough movement left to perform that action.");
				break;
		}
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
function sendAltTrpAction(data) {
	console.log('sendAltTrpAction','trpActionStory.php',data);
	$.getJSON("trpActionStory.php",data,function (data) {
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
	})
	.done(function(data) {
		console.log('getJSON done');
	})
	.fail(function(data) {
		console.log('getJSON fail',data);
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
		$("#trpn-x").val(parseInt((offsetPos[0]+pos[0])*baseTileSize));
		$("#trpn-z").val(parseInt((offsetPos[1]+pos[1])*baseTileSize));
		trpnCtx.clearRect(0,0,64,64);
		//trpnCtx.drawImage(sprites,(0%8)*16,parseInt(0/8)*16,16,16,0,0,64,64);
		drawSpriteFromMap(trpnCtx,0,0,sprites,0,16,64);
		trpSprite=0;
		spritemenu.removeClass("show");
		setTroopCalcs();
	}
	else if(kind==1) {
		$("#trpe-name").val(troops[id].name);
		$("#trpe-owner").val(troops[id].nation);
		$("#trpe-size").val(troops[id].size);
		$(".trp-power").text(troops[id].power);
		$("#trpe-health").val(troops[id].health);
		$(".trp-lvl").text(parseInt(Math.sqrt(0.25+2*troops[id].xp)-0.5));
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
		$(".trp-position").text("X: "+NumberFormat.format(troops[id].x)+", Z: "+NumberFormat.format(troops[id].z));
		var spectxt="";
		for(var j=0; j<troops[id].bonuses.length; j++) {
			if(troops[id].bonuses[j]!="") {
				if(j>0) {
					spectxt+=", ";
				}
				spectxt+="<span title=\""+specials.desc[specials.name.indexOf(troops[id].bonuses[j])]+"\">"+specials.title[specials.name.indexOf(troops[id].bonuses[j])]+"</span>";
			}
		}
		$(".trp-bonuses")[0].innerHTML=spectxt;
		$(".trp-move").text(troops[id].move);
		$(".trp-moveleft").text(troops[id].moveleft);
		trpeCtx.clearRect(0,0,64,64);
		if(!troops[id].customsprite) {
			//trpeCtx.drawImage(sprites,(troops[id].sprite%8)*16,parseInt(troops[id].sprite/8)*16,16,16,0,0,64,64);
			drawSpriteFromMap(trpeCtx,0,0,sprites,troops[id].sprite,16,64);
			trpSprite=troops[id].sprite;
		}
		else {
			var spriteId=0;
			for(var i=0; i<customsprites.length; i++) {
				if(customsprites[j].spriteid==troops[id].sprite)
					spriteId=i;
			}
			trpeCtx.drawImage(customsprites[spriteId],0,0,64,64);
			trpSprite=troops[id].sprite;
		}
	}
	else if(kind==0) {
		$(".trp-name").text(troops[id].name);
		$(".trp-owner").text(troops[id].nation);
		$(".trp-size").text(NumberFormat.format(troops[id].size));
		$(".trp-power").text(troops[id].power);
		$(".trp-health").text(troops[id].health+"%");
		$(".trp-lvl").text(parseInt(Math.sqrt(0.25+2*troops[id].xp)-0.5));
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
		$(".trp-position").text("X: "+NumberFormat.format(troops[id].x)+", Z: "+NumberFormat.format(troops[id].z));
		var spectxt="";
		for(var j=0; j<troops[id].bonuses.length; j++) {
			if(troops[id].bonuses[j]!="") {
				if(j>0) {
					spectxt+=", ";
				}
				spectxt+="<span title=\""+specials.desc[specials.name.indexOf(troops[id].bonuses[j])]+"\">"+specials.title[specials.name.indexOf(troops[id].bonuses[j])]+"</span>";
			}
		}
		$(".trp-bonuses")[0].innerHTML=spectxt;
		$(".trp-move").text(troops[id].move);
		$(".trp-moveleft").text(troops[id].moveleft);
		trpvCtx.clearRect(0,0,64,64);
		if(!troops[id].customsprite) {
			//trpvCtx.drawImage(sprites,(troops[id].sprite%8)*16,parseInt(troops[id].sprite/8)*16,16,16,0,0,64,64);
			drawSpriteFromMap(trpvCtx,0,0,sprites,troops[id].sprite,16,64);
			trpSprite=troops[id].sprite;
		}
		else {
			var spriteId=0;
			for(var i=0; i<customsprites.length; i++) {
				if(customsprites[i].spriteid==troops[id].sprite)
					spriteId=i;
			}
			trpvCtx.drawImage(customsprites[spriteId],0,0,64,64);
			trpSprite=troops[id].sprite;
		}
		if(troops[id].owned)
			$("#editbtn").addClass("show");
		else
			$("#editbtn").removeClass("show");
	}
}
function setTroopCalcs() {
	var tempPower=parseInt((parseInt($("#trpn-cost").val())+parseInt($("#trpn-size").val()))/1000);
	var tempMove=Math.min(Math.pow(2,Math.log(1000*tempPower/parseInt($("#trpn-size").val()))),Math.max(100000/parseInt($("#trpn-size").val()),1));
	tempMove*=3;
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
	data.customsprite=isSpriteCustom;
	$.getJSON("createTrp.php",data,checkTrpResponse).fail(function(){console.log(data);});
}
function editTrp() {
	var data={id:activeTrpId};
	data.name=$("#trpe-name").val();
	data.owner=$("#trpe-owner").val();
	data.size=$("#trpe-size").val();
	data.health=$("#trpe-health").val();
	$.getJSON("editTrpStory.php",data,checkTrpResponse).fail(function(){console.log(data);});
}
function deleteTrp() {
	var data={id:activeTrpId};
	$.getJSON("deleteTrp.php",data,checkTrpResponse).fail(function(){console.log(data);});
	resetStuff();
}
function moveTrp(x,z) {
	var data={id:activeTrpId,x:x,z:z};
	$.getJSON("moveTrp.php",data,checkTrpResponse).fail(function(){console.log(data);});
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
		if(selectedArmy!==-1)
			$("#infoTxt")[0].innerHTML="<b>"+troops[selectedArmy].name+"</b><br>Nation: "+troops[selectedArmy].nation+", Size: "+NumberFormat.format(troops[selectedArmy].size)+", Health: "+troops[selectedArmy].health+"%<br><span onclick=\"viewTrp()\">Details</span>";
		battles={};
		for(let i=0; i<troops.length; i++) {
			if(troops[i].state!==6) 
				continue;
			if(battles[troops[i].battle]===undefined)
				battles[troops[i].battle]=[i];
			else
				battles[troops[i].battle].push(i);
		}
	});
}
commChecks=[];
function commCheckHandler(e) {
	if(e.target.checked) {
		commChecks.push(e.target.value);
		while(commChecks.length>3) {
			$(".addcommspec[value="+commChecks.splice(0,1)[0]+"]")[0].checked=false;
		}
	}
	else {
		commChecks.splice(commChecks.indexOf(e.target.value),1);
	}
}
function createComm() {
	$.getJSON("createComm.php",{
		name:$("#comm-name").val(),
		nation:$("#comm-ntn").val(),
		specials:commChecks,
	},function(data){
		$("#comm-name").val("");
		$("#comm-ntn").val(nation);
		while(commChecks.length>0) {
			$(".addcommspec[value="+commChecks.splice(0,1)[0]+"]")[0].checked=false;
		}
		console.log(data);
		if(data.response.status!=0) 
			addBanner(data.response.text);
		$("#tab-2.tab").removeClass("active");
		$("#tab-3.tab").addClass("active");
	});
}
function deleteComm(e) {
	$.getJSON("deleteComm.php",{
		id:$(e.target).attr("card")
	},function(data){
		console.log(data);
		/*if(data.response.status!=0) 
			addBanner(data.response.text);*/
	});
}
function setCommanderLists() {
	var commview=$("#comm-view");
	var commmanage=$("#comm-manage");
	var viewingmanage=$(".tab.active #comm-manage").length>0 && commanderMenuActive==true;
	var seenCommanders=[];
	for(var i=0; i<commanders.length; i++) {
		var spectxt="";
		for(var j=0; j<commanders[i].special.length; j++) {
			if(j>0) {
				spectxt+=", ";
			}
			spectxt+="<span title=\""+specials.desc[specials.name.indexOf(commanders[i].special[j])]+"\">"+specials.title[specials.name.indexOf(commanders[i].special[j])]+"</span>";
		}
		seenCommanders[commanders[i].id]=true;
		if(shownCommanders[commanders[i].id]) {
			$(".card[card="+commanders[i].id+"]").attr("nation",commanders[i].nation);
			$(".card[card="+commanders[i].id+"] .h").text(commanders[i].name);
			$(".card[card="+commanders[i].id+"] .topic").text(commanders[i].nation);
			$(".card[card="+commanders[i].id+"] .time").text(commanders[i].owner);
			$("#comm-view .card[card="+commanders[i].id+"] .stuffing")[0].innerHTML="Specials: "+spectxt+"<br>Army: "+commanders[i].armyname+"<br>Level: "+parseInt(Math.sqrt(0.25+2*commanders[i].xp)-0.5);
			if(!viewingmanage && commanders[i].owned) {
				var armyselecter="<select class=\"comm-army\" commid=\""+commanders[i].id+"\"><option></option>";
				for(var j=0; j<troops.length; j++) {
					if(commanders[i].nation==troops[j].nation || relations[commanders[i].nation]===undefined || relations[commanders[i].nation][troops[j].nation]<4) {
						var isselected="";
						if(commanders[i].armyid==troops[j].id)
							isselected=" selected";
						armyselecter+="<option"+isselected+">"+troops[j].name+"</option>";
					}
				}
				armyselecter+="</select>";
				$("#comm-manage .card[card="+commanders[i].id+"] .stuffing")[0].innerHTML="Specials: "+spectxt+"<br>Army: "+armyselecter+"<br>Level: "+parseInt(Math.sqrt(0.25+2*commanders[i].xp)-0.5);
			}
		}
		else {
			shownCommanders[commanders[i].id]=true;
			commview.append('<div class="card" nation="'+commanders[i].nation+'" card="'+commanders[i].id+'"><div class="postmeta"><div class="h">'+commanders[i].name+'</div> <div class="topic">'+commanders[i].nation+'</div> <div class="time">'+commanders[i].owner+'</div></div><div class="stuffing">Specials: '+spectxt+'<br>Army: '+commanders[i].armyname+"<br>Level: "+parseInt(Math.sqrt(0.25+2*commanders[i].xp)-0.5)+'</div></div>');
			if(commanders[i].owned) {
				var armyselecter="<select class=\"comm-army\" commid=\""+commanders[i].id+"\"><option></option>";
				for(var j=0; j<troops.length; j++) {
					if(commanders[i].nation==troops[j].nation || relations[commanders[i].nation]===undefined || relations[commanders[i].nation][troops[j].nation]<4) {
						var isselected="";
						if(commanders[i].armyid==troops[j].id)
							isselected=" selected";
						armyselecter+="<option"+isselected+">"+troops[j].name+"</option>";
					}
				}
				armyselecter+="</select>";
				commmanage.append('<div class="card" nation="'+commanders[i].nation+'" card="'+commanders[i].id+'"><div class="postmeta"><div class="h">'+commanders[i].name+'</div> <div class="topic">'+commanders[i].nation+'</div> <div class="time">'+commanders[i].owner+'</div></div><div class="stuffing">Specials: '+spectxt+'<br>Army: '+armyselecter+"<br>Level: "+parseInt(Math.sqrt(0.25+2*commanders[i].xp)-0.5)+'</div><div class="footer"><div class="delete" card="'+commanders[i].id+'">delete</div></div></div>');
			}
		}
	}
	var commCards=$("#comm-view .card");
	for(var i=0; i<commCards.length; i++) {
		if(seenCommanders[parseInt(commCards[i].attributes.card.value)]==undefined) {
			shownCommanders[parseInt(commCards[i].attributes.card.value)]=false;
			$(".card[card="+commCards[i].attributes.card.value+"]").remove();
		}
	}
	$(".comm-army").off("change",setCommTrp);
	$(".comm-army").on("change",setCommTrp);
	$("#comm-manage .card .delete").off("click",deleteComm);
	$("#comm-manage .card .delete").on("click",deleteComm);
}
function setNationStyles() {
	styleBox=document.getElementById("nationstyles");
	var styleStr=""
	for(var i=0; i<nationColorsIndex.length; i++) {
		styleStr+="\n.card[nation=\""+nationColorsIndex[i]+"\"] {\n	color: #"+nationColors[nationColorsIndex[i]].fore+";\n	background-color: #"+nationColors[nationColorsIndex[i]].back+";\n}";
	}
	styleStr+="\n";
	styleBox.innerHTML=styleStr;
}
function setCommTrp(e) {
	var theselector=$(e.currentTarget);
	$.getJSON("setcommtrp.php",{id:parseInt(theselector.attr("commid")),armyname:theselector.val()},function(data) {
		console.log(data);
		if(data.response.status>0) {
			addBanner(data.response.text);
		}
	});
}
function setPinMenu() {
	currentpinmenu="change";
	$("#pinMenu").addClass("change");
	$("#pinMenu").removeClass("create");
	$("#pin-name").val(markers[clickedPin].name);
	$("#pin-x").val(markers[clickedPin].x);
	$("#pin-z").val(markers[clickedPin].z);
	$("#pin-dimen").val(markers[clickedPin].dimension);
	$("#pin-desc").val(markers[clickedPin].desc);
	$(".pinicon").val(markers[clickedPin].type);
	$(".icondata").removeClass("shown");
	$(".icon-"+markers[clickedPin].type).addClass("shown");
	if(markers[clickedPin].type=="default") $("#pinColor").val("#"+markers[clickedPin].icondata);
	else $("#pinColor").val("#FF0000");
	if(markers[clickedPin].type=="custom") selectPinSprite({target:{spritename:markers[clickedPin].icondata}});
	else selectPinSprite({target:{spritename:pinoptions[0].cnv.spritename}});
}
function resetPinMenu() {
	currentpinmenu="create";
	$("#pinMenu").addClass("create");
	$("#pinMenu").removeClass("change");
	$("#pin-name").val("");
	$("#pin-x").val(parseInt((offsetPos[0]+pos[0])*baseTileSize));
	$("#pin-z").val(parseInt((offsetPos[1]+pos[1])*baseTileSize));
	$("#pin-dimen").val(dimension);
	$("#pin-desc").val("");
	$(".pinicon").val("default");
	$(".icondata").removeClass("shown");
	$(".icon-default").addClass("shown");
	$("#pinColor").val("#ff0000");
	selectPinSprite({target:{spritename:pinoptions[0].cnv.spritename}});
}
function selectPinSprite(e) {
	selectedPinSprite=e.target.spritename;
	pinctx.clearRect(0,0,48,48);
	pinctx.drawImage(custompins[e.target.spritename].img,0,0,48,48);
	pinspritemenu.removeClass("show");
}
function resetPinSprites() {
	custompins={};
	pinoptions=[];
	$.getJSON("getMarkers.php",function (data) {
		$(".sprite-pin").remove();
		customspritedata=data.sprites;
		var pinI=0;
		for(var i=0; i<customspritedata.length; i++) {
			if(customspritedata[i].type=="pin") {
				var name=customspritedata[i].name;
				custompins[name]={img:document.createElement('img'),i:pinI};
				custompins[name].img.src="img/uploads/"+name;
				custompins[name].img.width=customspritedata[i].width;
				custompins[name].img.height=customspritedata[i].height;
				pinoptions.push({cnv:document.createElement("canvas")});
				pinoptions[pinI].cnv.width=pinoptions[pinI].cnv.height=48;
				pinoptions[pinI].cnv.i=pinI;
				pinoptions[pinI].cnv.spritename=name;
				pinoptions[pinI].ctx=pinoptions[pinI].cnv.getContext("2d");
				pinoptions[pinI].ctx.imageSmoothingEnabled=false;
				pinoptions[pinI].cnv.addEventListener("click",selectPinSprite);
				pinoptions[pinI].cnv.className="sprite-custom sprite-pin";
				custompins[name].img.cnvId=pinI;
				pinspritemenu.append(pinoptions[pinI].cnv);
				custompins[name].img.addEventListener("load",function(e){
					pinoptions[e.target.cnvId].ctx.drawImage(e.target,0,0,48,48);
				});
				pinI++;
			}
		}
	});
}
function createPin() {
	if($("#pin-name").val()!="" && $("#pin-x").val()!="" && $("#pin-y").val()!="" && $("#pin-desc").val()!="") {
		var data={};
		data.mode="create";
		data.name=$("#pin-name").val();
		data.x=$("#pin-x").val();
		data.z=$("#pin-z").val();
		data.dimension=$("#pin-dimen").val();
		data.desc=$("#pin-desc").val();
		data.type=$("#pinicon").val();
		if(data.type=="default")
			data.icondata=$("#pinColor").val().substr(1);
		else if(data.type=="custom")
			data.icondata=selectedPinSprite;
		else
			data.icondata="";
		$.getJSON("controlPins.php",data,checkPinResponse);
	}
	else {
		addBanner("Please fill out all fields.");
	}
}
function deletePin() {
	var data={};
	data.mode="delete";
	data.id=selectedPoint;
	$.getJSON("controlPins.php",data,checkPinResponse);
}
function changePin() {
	if($("#pin-name").val()!="" && $("#pin-x").val()!="" && $("#pin-y").val()!="" && $("#pin-desc").val()!="") {
		var data={};
		data.mode="change";
		data.id=selectedPoint;
		data.name=$("#pin-name").val();
		data.x=$("#pin-x").val();
		data.z=$("#pin-z").val();
		data.dimension=$("#pin-dimen").val();
		data.desc=$("#pin-desc").val();
		data.type=$("#pinicon").val();
		if(data.type=="default")
			data.icondata=$("#pinColor").val().substr(1);
		else if(data.type=="custom")
			data.icondata=selectedPinSprite;
		else
			data.icondata="";
		$.getJSON("controlPins.php",data,checkPinResponse);
	}
	else {
		addBanner("Please fill out all fields.");
	}
}
function checkPinResponse(data) {
	console.log(data);
	if(data.output.status==0) {
		closePinMenu();
	}
	addBanner(data.output.text);
	$.getJSON("getMarkers.php",function(data){markers=data.pins;});
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

function drawSpriteFromMap(CTX,x,y,img,ind,sWidth,sHeightOpt,fWidthOpt,fHeightOpt) {
	const sHeight=(fWidthOpt===undefined?sWidth:sHeightOpt);
	const fWidth=(sHeightOpt===undefined?sWidth:(fWidthOpt===undefined?sHeightOpt:fWidthOpt));
	const fHeight=(sHeightOpt===undefined?sWidth:(fWidthOpt===undefined?sHeightOpt:(fHeightOpt===undefined?(fWidthOpt*sHeightOpt/sWidth):fHeightOpt)));
	const spriteRowLen=parseInt(img.width/sWidth);
	CTX.drawImage(img,(ind%spriteRowLen)*sWidth,parseInt(ind/spriteRowLen)*sHeight,sWidth,sHeight,x,y,fWidth,fHeight);
}

promptFunc=function(){};
promptArgs=[];
questionsCount=0;
variedCountPrompt=false;
function createPromptAbstract(questionTxts,buttonTxt,respFunc,extraArgs,defaultTxtsOpt) {
	promptFunc=respFunc;
	promptArgs=extraArgs;
	questionsCount=questionTxts.length;
	const questionsDiv=$("#genericQuestions");
	questionsDiv.empty();
	for(let i=0; i<questionsCount; i++) {
		const newLabel=document.createElement('label');
		newLabel.setAttribute('id','gnrc-question-'+i);
		newLabel.setAttribute('for','gnrc-answer-'+i);
		newLabel.innerHTML=questionTxts[i];
		questionsDiv.append(newLabel);
		const newInput=document.createElement('input');
		newInput.setAttribute('id','gnrc-answer-'+i);
		newInput.setAttribute('name','gnrc-answer-'+i);
		if(defaultTxtsOpt!==undefined)
			newInput.value=defaultTxtsOpt[i];
		questionsDiv.append(newInput);
	}
	document.getElementById('gnrc-submit').value=buttonTxt;
	$("#genericMenu").addClass("shown");
	genericMenuActive=true;
	menuActive=true;
}
function createPrompt(questionTxts,buttonTxt,respFunc,extraArgs,defaultTxtsOpt) {
	variedCountPrompt=false;
	createPromptAbstract(questionTxts,buttonTxt,respFunc,extraArgs,defaultTxtsOpt);
}
function createVariedCountPrompt(questionTxts,buttonTxt,respFunc,extraArgs,defaultTxtsOpt) {
	variedCountPrompt=true;
	createPromptAbstract(questionTxts,buttonTxt,respFunc,extraArgs,defaultTxtsOpt);
}
function completePrompt() {
	closeGenericMenu();
	const responseValues=[];
	for(let i=0; i<questionsCount; i++) {
		responseValues.push(document.getElementById('gnrc-answer-'+i).value);
	}
	if(variedCountPrompt)
		promptFunc(responseValues,...promptArgs);
	else
		promptFunc(...responseValues,...promptArgs);
}

function getWidth() {
	return Math.max(
		document.body.scrollWidth,
		document.documentElement.scrollWidth,
		document.body.offsetWidth,
		document.documentElement.offsetWidth,
		document.documentElement.clientWidth
	);
}
function getHeight() {
	return Math.max(
		document.body.scrollHeight,
		document.documentElement.scrollHeight,
		document.body.offsetHeight,
		document.documentElement.offsetHeight,
		document.documentElement.clientHeight
	);
}


