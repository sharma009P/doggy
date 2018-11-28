/**
* Timer
*/

var d = 00;
var h = 02;
var m = 00;
var s = 00;

(function( $ ) {
 
    $.fn.timer = function(params) {
        var html = "<div class='_time days'><h3>" +  "</h3><label>Days</label></div>";
        html += "<div class='_time hours'><h3>" + "</h3><label>Hours</label></div>";
        html += "<div class='_time minutes'><h3>" + "</h3><label>Mins</label></div>";
        html += "<div class='_time seconds'><h3>" + "</h3><label>Secs</label></div>";
        this.html(html);
      	var ths = this;
      	
      	rem_time = get_formated_ramainin_time(d, h, m, s);
      	var days = rem_time.days;
      	var hours = rem_time.hours;
      	var minutes = rem_time.minutes;
      	var seconds = rem_time.seconds;
      
      	setInterval(function(){
        	if(seconds <= 0 && (minutes > 0 || hours > 0 || days > 0) ) {
          		seconds = 59;
          		minutes = minutes - 1;
        	} else {
          		seconds--;
        	}

        	if(minutes <0 && (hours > 0 || days > 0)) {
          		minutes = 59;
          		hours = hours - 1;
            }

        	if(hours < 0 && days > 0) {
          		hours = 23;
          		days = days - 1;
        	}
        	ths.find('.days h3').text(twoDigit(days));
        	ths.find('.hours h3').text(twoDigit(hours));
        	ths.find('.minutes h3').text(twoDigit(minutes));
        	ths.find('.seconds h3').text(twoDigit(seconds));

      	}, 1000);
    };
 
}( jQuery ));

function twoDigit(number) {
  var twodigit = number >= 10 ? number : "0"+number.toString();
  return twodigit;
}

function get_formated_ramainin_time(d, h, m, s){
  	var current_date = calcTime(-8);//new Date();

	current_date.setHours(h);
    current_date.setMinutes(m);
    current_date.setSeconds(s);
    if((calcTime(-8)).getTime() > current_date) {
      current_date.setDate(current_date.getDate()+d);
    }
    //alert(current_date);
    var diff = (current_date - calcTime(-8));
    var secDiff = diff / 1000; //in s
    var minDiff = diff / 60 / 1000; //in minutes
    var hDiff = diff / 3600 / 1000; //in hours
    var daysDiff = Math.floor(hDiff / 24);

    var humanReadable = {};
    humanReadable.days = daysDiff;
    humanReadable.hours = Math.floor(hDiff) - daysDiff;
    humanReadable.minutes = Math.floor(minDiff - (60 * Math.floor(hDiff)));
  	humanReadable.seconds = 0;
  
  	return humanReadable;
}

function calcTime(offset) {

  // create Date object for current location
  d = new Date();
  // convert to msec
  // add local time zone offset
  // get UTC time in msec
  utc = d.getTime() + (d.getTimezoneOffset() * 60000);
  // create new Date object for different city
  // using supplied offset
  nd = new Date(utc + (3600000*offset));

  return nd;

}