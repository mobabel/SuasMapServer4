/*
* Usage: the js file MUST be putted at last, before end body element,
*        because it will search nav and li DIV element at first!
*   <script type="text/javascript" src="../cssjs/menu.js"></script>
*  </body>
*/

//=======menu function in local data input setting===========
//########################  START CONFIG  ##############################
var LastLeftID = "";

var LastLeftIDr = "";

function menuFix() {

    var objUl = document.createElement('ul');
    objUl.id = "nav";
    var objLi = document.createElement('li');

	if(document.getElementById("nav")){
		var obj = document.getElementById("nav").getElementsByTagName("li");
		for (var i=0; i<obj.length; i++) {
			obj[i].onmouseover=function() {
				this.className+=(this.className.length>0? " ": "") + "sfhover";
			}
			obj[i].onMouseDown=function() {
				this.className+=(this.className.length>0? " ": "") + "sfhover";
			}
			obj[i].onMouseUp=function() {
				this.className+=(this.className.length>0? " ": "") + "sfhover";
			}
			obj[i].onmouseout=function() {
				this.className=this.className.replace(new RegExp("( ?|^)sfhover\\b"), "");
			}
		}
	}
}

function DoMenu(emid)
{
	var obj = document.getElementById(emid);
	obj.className = (obj.className.toLowerCase() == "expanded"?"collapsed":"expanded");
	if((LastLeftID!="")&&(emid!=LastLeftID))	//Close the last Menu
	{
		document.getElementById(LastLeftID).className = "collapsed";
	}
	LastLeftID = emid;
}

function GetMenuID()
{

	var MenuID="";
	var _paramStr = new String(window.location.href);

	var _sharpPos = _paramStr.indexOf("#");

	if (_sharpPos >= 0 && _sharpPos < _paramStr.length - 1)
	{
		_paramStr = _paramStr.substring(_sharpPos + 1, _paramStr.length);
	}
	else
	{
		_paramStr = "";
	}

	if (_paramStr.length > 0)
	{
		var _paramArr = _paramStr.split("&");
		if (_paramArr.length>0)
		{
			var _paramKeyVal = _paramArr[0].split("=");
			if (_paramKeyVal.length>0)
			{
				MenuID = _paramKeyVal[1];
			}
		}
		/*
		if (_paramArr.length>0)
		{
			var _arr = new Array(_paramArr.length);
		}

		//Get all behind #
		//for (var i = 0; i < _paramArr.length; i++)
		{
			var _paramKeyVal = _paramArr[i].split('=');

			if (_paramKeyVal.length>0)
			{
				_arr[_paramKeyVal[0]] = _paramKeyVal[1];
			}
		}
		*/
	}

	if(MenuID!="")
	{
		DoMenu(MenuID)
	}
}

//GetMenuID();	//*The oder of 2 function must be like this, or it doesnt work in Firefox!
//menuFix();
doOnload(GetMenuID);
doOnload(menuFix);
//########################  END CONFIG  ##############################

//########################  START CONFIG  ##############################

function menuFixr() {
	if(document.getElementById("nav")){
		var obj = document.getElementById("nav").getElementsByTagName("li");
		for (var i=0; i<obj.length; i++) {
			obj[i].onmouseover=function() {
				this.className+=(this.className.length>0? " ": "") + "sfhover";
			}
			obj[i].onMouseDown=function() {
				this.className+=(this.className.length>0? " ": "") + "sfhover";
			}
			obj[i].onMouseUp=function() {
				this.className+=(this.className.length>0? " ": "") + "sfhover";
			}
			obj[i].onmouseout=function() {
				this.className=this.className.replace(new RegExp("( ?|^)sfhover\\b"), "");
			}
		}
	}
}

function DoMenur(emid)
{
	var obj = document.getElementById(emid);
	obj.className = (obj.className.toLowerCase() == "expanded"?"collapsed":"expanded");
	if((LastLeftIDr!="")&&(emid!=LastLeftIDr))	//Close the last Menu
	{
		document.getElementById(LastLeftIDr).className = "collapsed";
	}
	LastLeftIDr = emid;
}

function GetMenuIDr()
{

	var MenuID="";
	var _paramStr = new String(window.location.href);

	var _sharpPos = _paramStr.indexOf("#");

	if (_sharpPos >= 0 && _sharpPos < _paramStr.length - 1)
	{
		_paramStr = _paramStr.substring(_sharpPos + 1, _paramStr.length);
	}
	else
	{
		_paramStr = "";
	}

	if (_paramStr.length > 0)
	{
		var _paramArr = _paramStr.split("&");
		if (_paramArr.length>0)
		{
			var _paramKeyVal = _paramArr[0].split("=");
			if (_paramKeyVal.length>0)
			{
				MenuID = _paramKeyVal[1];
			}
		}
		/*
		if (_paramArr.length>0)
		{
			var _arr = new Array(_paramArr.length);
		}

		//Get all behind #
		//for (var i = 0; i < _paramArr.length; i++)
		{
			var _paramKeyVal = _paramArr[i].split('=');

			if (_paramKeyVal.length>0)
			{
				_arr[_paramKeyVal[0]] = _paramKeyVal[1];
			}
		}
		*/
	}

	if(MenuID!="")
	{
		DoMenur(MenuID)
	}
}

//GetMenuIDr();	//*The oder of 2 function must be like this, or it doesnt work in Firefox!
//menuFixr();
doOnload(GetMenuIDr);
doOnload(menuFixr);
//########################  END CONFIG  ##############################

