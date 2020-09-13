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
		$.getJSON("date.json",function(data) {
			const time={};
			time.yr=data.yr;
			time.mon=data.mon;
			time.day=data.day;
			time.hr=18;
			time.min=0;
			time.sec=0;
			time.monStr=monName[time.mon-1];
			time.wkdStr=wkdName[time.wkd-1];
			let days=time.days+parseInt(365.25*(time.yr-1460)+470);
			for(var i=0; i<time.mon-1; i++) {
				days+=monthLen[i];
			}
			time.mil=1000*(time.sec+60*(time.min+60*(time.hr+24*days)));

			arg(time);
		});
	}
}
