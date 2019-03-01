startYear=1458;
startTime=1543622400000;
rate=60;

function getTimeOnServer(forTime) {
	var now=(new Date()).getTime();
	if(typeof forTime!="undefined") now=forTime;
	var monthLen=[31,28,31,30,31,30,31,31,30,31,30,31,Infinity];
	var monName=["January","February","March","April","May","June","July","August","September","October","November","December","Error"];
	var wkdName=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
	var time={mil:rate*(now-startTime)};
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