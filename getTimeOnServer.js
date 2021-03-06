startYear=1458;
srvSegments=[0];
irlSegments=[[1543622400000,1552258560000],[1564617600000,1573344000000],[1573344000000,1580644800000],[1580644800000,1580901600000]];
rates=[60,60,45,30];

for(var i=0; i<irlSegments.length-1; i++) {
    srvSegments.push(rates[i]*(irlSegments[i][1]-irlSegments[i][0])+srvSegments[i]);
}

const monthLen=[31,28,31,30,31,30,31,31,30,31,30,31,Infinity];
const monName=["January","February","March","April","May","June","July","August","September","October","November","December","Error"];
const wkdName=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

const tzolkinName=["Imix","Ik'","Ak'b'al","K'an","Chikchan","Kimi","Manik'","Lamat","Muluk","Ok","Chuwen","Eb'","B'en","Ix","Men","K'ib'","Kab'an","Etz'nab'","Kawak","Ajaw"];
const haabName=["Pop","Wo'","Sip","Sotz'","Sek","Xul","Yaxk'in","Mol","Ch'en","Yax","Sak","Keh","Mak","K'ank'in","Muwan","Pax","K'ayab","Kumk'u","Wayeb'"]
const mayaUnitNames=["k'in","winal","tun","k'atun","bakʼtun","piktun","kalabtun","kinchiltun","alawtun"];
const mayaUnitSizes=[1,20,18,20,20,20,20,20,20];

function getTimeOnServerLegacy(forTime) {
	var now=(new Date()).getTime();
	var activeSegment=irlSegments.length;
	if(typeof forTime!="undefined") now=forTime;
	for(var i=0; i<irlSegments.length; i++) {
		if(irlSegments[i][1]>now) {
			activeSegment=i;
			break;
		}
	}
	if((activeSegment>=irlSegments.length || irlSegments[activeSegment][0]>now) && activeSegment>0) {
		activeSegment--;
		now=irlSegments[activeSegment][1];
	}
	var time={mil:rates[activeSegment]*(now-irlSegments[activeSegment][0])+srvSegments[activeSegment]};
	time.sec=parseInt(time.mil/1000)
	time.min=parseInt(time.sec/60);
	time.sec%=60;
	time.hr=parseInt(time.min/60);
	time.min%=60;
	time.day=parseInt(time.hr/24);
	time.hr%=24;
	time.wkd=time.day%7+1;
	time.yr=parseInt(time.day/365.25+startYear);
	time.maya=getMayaDate(time.day);
	time.day-=parseInt((time.yr-startYear)*365.25-1);
	if(time.yr%4==0) { monthLen[1]=29; }
	time.mon=1;
	while(time.day>monthLen[time.mon-1]) {
		time.day-=monthLen[time.mon-1];
		time.mon++;
	}
	time.monStr=monName[time.mon-1];
	time.wkdStr=wkdName[time.wkd-1];
	return time;
}

function getTimeOnServer(arg) {
	const type=typeof arg;
	if(type == "number") return getTimeOnServerLegacy(arg);
	else if(type == "function") {
		$.getJSON("date.json?t="+Date.now(),function(data) {
			const time={};
			time.yr=data.yr;
			time.mon=data.mon;
			time.day=data.day;
			time.hr=18;
			time.min=0;
			time.sec=0;
			time.monStr=monName[time.mon-1];
			time.wkdStr=wkdName[time.wkd-1];
			let days=time.day+parseInt(365.25*(time.yr-1460)+470);
			let daysAlt=time.day+parseInt(365.25*time.yr);
			for(var i=0; i<time.mon-1; i++) {
				days+=monthLen[i];
				daysAlt+=monthLen[i];
			}
			time.mil=1000*(time.sec+60*(time.min+60*(time.hr+24*days)));
			time.maya=getMayaDate(daysAlt);

			time.toString=function() {
				return `${this.monStr} ${numSuffix(this.day)}, ${this.yr}`
			}

			arg(time);
		});
	}
}

function getMayaDate(euroDay) {
	const day=euroDay+1640703;
	const roundDay=day+2538;
	const maya={longCount:day,roundNum:parseInt(day/18980),roundDay:day%18980};
	maya.tzolk_in={number:(roundDay%13)+1,name:((roundDay+1)%20)+1,day:(roundDay%260)+1};
	maya.tzolk_in.nameStr=tzolkinName[maya.tzolk_in.name-1];
	maya.tzolk_in.toString=function() {
		return `${this.number} ${this.nameStr}`;
	}
	maya.haab_={day:(roundDay%365)};
	maya.haab_.number=maya.haab_.day%20;
	maya.haab_.month=parseInt(maya.haab_.day/20);
	maya.haab_.monthStr=haabName[maya.haab_.month];
	maya.haab_.toString=function() {
		return `${this.number} ${this.monthStr}`;
	}
	maya.unitCounts={};
	maya.units={};
	let unit=1;
	for(let i=0; i<mayaUnitNames.length; i++) {
		unit*=mayaUnitSizes[i];
		maya.unitCounts[mayaUnitNames[i]]=parseInt(day/unit);
		maya.units[mayaUnitNames[i]]=unit;
	}
	maya.toString=function() {
		return `${this.tzolk_in} ${this.haab_} of the ${numSuffix(this.roundNum)} Calender Round`;
	}
	return maya;
}

function numSuffix(num) {
	if(num>10 && num<20) {
		return num+"th";
	}
	let suffix;
	switch(num%10) {
		case 1:
			suffix="st";
			break;
		case 2:
			suffix="nd";
			break;
		case 3:
			suffix="rd";
			break;
		default:
			suffix="th";
	}
	return num+suffix;
}
