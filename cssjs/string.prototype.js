/**
* This script include the common string functions
*/


/**
* usuage :
var myString = " hello my name is ";
alert("*"+myString.trim()+"*");
alert("*"+myString.ltrim()+"*");
alert("*"+myString.rtrim()+"*");

*/
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}

String.prototype.ltrim = function() {
	return this.replace(/^\s+/,"");
}

String.prototype.rtrim = function() {
	return this.replace(/\s+$/,"");
}

//or standalone function

function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
function ltrim(stringToTrim) {
	return stringToTrim.replace(/^\s+/,"");
}
function rtrim(stringToTrim) {
	return stringToTrim.replace(/\s+$/,"");
}


String.prototype.isEmpty = function() {
	if(this.trim()=='')
		return true;
}

/**
 *
 * @access public check if the string is number
 * @return void
 **/
String.prototype.isNumber=function() {
var re = /^[-]?\d+[.]?\d*$/;
return re.test(this);
}

function isNumber(string){
	var re = /^[-]?\d+[.]?\d*$/;
	return re.test(string);
}

/**
* check the string is chinese
*/
String.prototype.isChinese=function() {
if(name.length == 0)
return false;
for(i = 0; i < name.length; i++) {
if(name.charCodeAt(i) > 128)
return true;
}
return false;
}

/**
* check the string is english character
*/
String.prototype.isEnglish=function() {
var re = /^\w*$/;
return re.test(str);
}

/**
* check the string is date
*/
String.prototype.isDate=function() {
var r = this.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})$/);
if(r == null)
return false;
var d = new Date(r[1], r[3]-1, r[4]);
return (d.getFullYear() == r[1] && (d.getMonth() + 1) == r[3] && d.getDate() == r[4]);
}

/**
* check the string is date time
*/
String.prototype.isDateTime=function()
{
var r = this.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/);
if(r == null)
return false;
var d = new Date(r[1], r[3]-1,r[4],r[5],r[6],r[7]);
return (d.getFullYear() == r[1] && (d.getMonth() + 1) == r[3] && d.getDate() == r[4]
&& d.getHours() == r[5] && d.getMinutes() == r[6] && d.getSeconds() == r[7]);
}

/**
* check the string is positive integer number
*/
String.prototype.isNumber1=function() {
var re = /^\d*$/;
return re.test(this);
}

/**
* check the length of string, chinese counts 2, ascii character counts 1
*/
String.prototype.len=function() {
return this.replace(/[^\x00-\xff]/g,"aa").length;
}

/**
* check the string is IP address
*/
String.prototype.isIp=function() {
var re = /(\d{0,255})\.(\d{0,255})\.(\d{0,255}).(\d{0,255})/g;
return reg.test(this);
}
