startYear=1458;
srvSegments=[0];
irlSegments=[[1543622400000,1552258560000],[1564617600000,1573344000000],[1573344000000,1580644800000][1580644800000,Infinity]];
rates=[60,60,45,30];

for(var i=0; i<irlSegments.length-1; i++) {
    srvSegments.push(rates[i]*(irlSegments[i][1]-irlSegments[i][0])+srvSegments[i]);
}

function getTimeOnServer(forTime) {
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
	var monthLen=[31,28,31,30,31,30,31,31,30,31,30,31,Infinity];
	var monName=["January","February","March","April","May","June","July","August","September","October","November","December","Error"];
	var wkdName=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
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
