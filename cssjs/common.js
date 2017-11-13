/*
function inc(filename)
{
var body = document.getElementsByTagName('body').item(0);
script = document.createElement('script');
script.src = filename;
script.type = 'text/javascript';
body.appendChild(script)
}

inc("string.prototype.js");
 */

//######################################################################

//The code of Tips is using FSTOOLTIPS.JS V1.1 from FUSIONSCRIPTZ   2006
//Thx!
//######################################################################
var offsetx = 15;
var offsety = 10;
var mouseX = 0;
var mouseY = 0;
var pageX = 0;
var pageY = 0;
var ie5 = (document.getElementById && document.all);
var ns6 = (document.getElementById && !document.all);
var ua = navigator.userAgent.toLowerCase();
var isapple = (ua.indexOf('applewebkit') != -1 ? 1 : 0);

function CreateNewElement(newid){
	if(document.createElement){
		var el = document.createElement('div');
		el.id = newid;
		with(el.style)
		{
			position = 'absolute';
		}
		el.innerHTML = '&nbsp;';
		window.document.body.appendChild(el);
	}
}

function createNewHiddenElement(newid){

	if(document.createElement){
		var el = document.createElement('div');
		el.id = newid;
		with(el.style)
		{
			display = 'none';
			position = 'absolute';
		}
		el.innerHTML = '&nbsp;';
		document.body.appendChild(el);
	}
}


function getmouseposition(e){
	if(document.getElementById){
		$().mousemove( function(e) {
			mouseX = e.pageX;
			mouseY = e.pageY;
			//alert(e.pageX + " " + e.pageY + " | " +e.clientX + " " + e.clientY);
		});


		var fstooltip = document.getElementById('tooltip');
		var fswarningtip = document.getElementById('warningtip');

		if(fstooltip){
			if ((mouseX+offsetx+fstooltip.clientWidth+5) > $(document).width() ) {
				fstooltip.style.left = ((document.body.scrollLeft+document.body.clientWidth) - (fstooltip.clientWidth*2))+'px';
			}
			else {
				fstooltip.style.left = (mouseX+pageX+offsetx)+'px';
			}
			if ((mouseY+offsety+fstooltip.clientHeight+5) > document.body.clientHeight) {
				fstooltip.style.top = ((document.body.scrollTop+document.body.clientHeight) - (fstooltip.clientHeight*2))+'px';
			}
			else {
				fstooltip.style.top = (mouseY+pageY+offsety)+'px';
			}
		}
		//for waring tips
		if(fswarningtip){
			if ((mouseX+offsetx+fswarningtip.clientWidth+5) > document.body.clientWidth) {
				fswarningtip.style.left = ((document.body.scrollLeft+document.body.clientWidth) - (fswarningtip.clientWidth*2))+'px';
			}
			else { fswarningtip.style.left = (mouseX+pageX+offsetx)+'px'; }
			if ((mouseY+offsety+fswarningtip.clientHeight+5) > document.body.clientHeight) {
				fswarningtip.style.top = ((document.body.scrollTop+document.body.clientHeight) - (fswarningtip.clientHeight*2))+'px';
			}
			else { fswarningtip.style.top = (mouseY+pageY+offsety)+'px'; }
		}
	}
}


function tooltip(tiptitle,tipbold,tipnormal){
	if(!document.getElementById('tooltip')) createNewHiddenElement('tooltip');
	var fstooltip = document.getElementById('tooltip');
	fstooltip.innerHTML = '<table class="fstooltips" cellpadding="2" cellspacing="0"><tr><td class="tipheader"><img src="../img/helptip.png" height="14" width="14" align="right">'
		+ tiptitle + '</td></tr><tr><td class="tipcontent"><b>' + tipbold + '</b><br>' + tipnormal + '</td></tr></table>'
		+ '<iframe src="javascript:false" class="divoverlapiframe" ></iframe>';
	fstooltip.style.display = 'block';
	fstooltip.style.position = 'absolute';
	fstooltip.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(Opacity=70) progid:DXImageTransform.Microsoft.dropshadow(OffX=5, OffY=5, Color=gray, Positive=true)';
	fstooltip.style.zIndex = '1000';
	document.onmousemove = getmouseposition;
}

function warningtip(tiptitle,tipbold,tipnormal){
	if(!document.getElementById('warningtip')) createNewHiddenElement('warningtip');
	var fswarningtip = document.getElementById('warningtip');
	fswarningtip.innerHTML = '<table class="warningtips" cellpadding="2" cellspacing="0"><tr><td class="warningheader"><img src="../img/warning.png" height="14" width="14" align="right">'
		+ tiptitle + '</td></tr><tr><td class="warningcontent"><b>' + tipbold + '</b><br>' + tipnormal + '</td></tr></table>'
		+ '<iframe src="javascript:false" class="divoverlapiframe" ></iframe>';

	fswarningtip.style.display = 'block';
	fswarningtip.style.position = 'absolute';
	fswarningtip.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(Opacity=70) progid:DXImageTransform.Microsoft.dropshadow(OffX=5, OffY=5, Color=gray, Positive=true)';
	fswarningtip.style.zIndex = '1000';
	document.onmousemove = getmouseposition;
}
function exitwarning(){
	$('#warningtip').hide();
}


function exit(){
	$('#tooltip').hide();
}
//################### FSTOOLTIPS END #######################################

//Show or hide the div by the ID
function ShowHide(id,dis){
	var bdisplay = (dis==null)?((document.getElementById(id).style.display=="")?"none":""):dis
			document.getElementById(id).style.display = bdisplay;
}

/*****************************************************************************************
 * @Show java script debug error message in the page
 * @access public
 * @return void
 *****************************************************************************************/
function showDebugMessage(error){
	$("#ERRORMESSAGE").html("<table width=\"100%\" id=\"idErrorTable\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" bgcolor=\"#FEF4CC\"><tr><td>"+
			error+"</td></tr></table>");
	window.setInterval("flashit()", 500);
}

function growlError(message){
	$.growlUI(false, message, 'error', 9000); 
}

function growlInfo(message){
	$.growlUI(false, message, 'info', 9000); 
}

function growlWarn(message){
	$.growlUI(false, message, 'warn', 9000); 
}

function updateMessageError(message){
	updateMessage(message, "ERRORMESSAGE", false);
}

function updateMessageInfo(message){
	updateMessage(message, "INFOMESSAGE", false);
}

function updateMessageWarn(message){
	updateMessage(message, "WARNMESSAGE", false);
}

function updateMessageError(message, isparent){
	updateMessage(message, "ERRORMESSAGE", isparent);
}

function updateMessageInfo(message, isparent){
	updateMessage(message, "INFOMESSAGE", isparent);
}

function updateMessageWarn(message, isparent){
	updateMessage(message, "WARNMESSAGE", isparent);
}

function updateMessage(message, type, isparent){
	try{
		if(!isparent){
			if(type == "ERRORMESSAGE"){
				if(message!=""){
					$("#ERRORMESSAGE").html(message);
					$("#ERRORMESSAGE").show();
				}else{
					$("#ERRORMESSAGE").hide();
				}
			}
			else if(type == "INFOMESSAGE"){
				if(message!=""){
					$("#INFOMESSAGE").html(message);
					$("#INFOMESSAGE").show();
				}else{
					$("#INFOMESSAGE").hide();
				}
			}
			else if(type == "WARNMESSAGE"){
				if(message!=""){
					$("#WARNMESSAGE").html(message);
					$("#WARNMESSAGE").show();
				}else{
					$("#WARNMESSAGE").hide();
				}
			}
		}
		else if(isparent){
			if(type == "ERRORMESSAGE"){
				if(message!=""){
					parent.parent.$("#ERRORMESSAGE").html(message);
					parent.parent.$("#ERRORMESSAGE").show();
				}else{
					parent.parent.$("#ERRORMESSAGE").hide();
				}
			}
			else if(type == "INFOMESSAGE"){
				if(message!=""){
					parent.parent.$("#INFOMESSAGE").html(message);
					parent.parent.$("#INFOMESSAGE").show();
				}else{
					parent.parent.$("#INFOMESSAGE").hide();
				}
			}
			else if(type == "WARNMESSAGE"){
				if(message!=""){
					parent.parent.$("#WARNMESSAGE").html(message);
					parent.parent.$("#WARNMESSAGE").show();
				}else{
					parent.parent.$("#WARNMESSAGE").hide();
				}
			}
		}
	}catch(e){
		//alert(e);
		//showDebugMessage(e);
	}
}


function flashit(){
	if ($("#idErrorTable").css('borderColor')=="green")
		$("#idErrorTable").css('borderColor', "#F709F7");
	else
		$("#idErrorTable").css('borderColor', "green");
}


/*****************************************************************************
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
 ******************************************************************************/
function getPageSize(){

	var xScroll, yScroll;

	if (window.innerHeight && window.scrollMaxY) {
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else {
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}


	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight)
	return arrayPageSize;
}
//***************************************************************************************
//show the error message
//***************************************************************************************
function showErrorMessage(error){
	var arrayPageSize = getPageSize();

	setBackgroudOverlay(arrayPageSize);

	if(!document.getElementById('errormessagefloat'))
		createNewHiddenElement('errormessagefloat');

	var errormessage = $("#errormessagefloat");

	errormessage.html("");
	errormessage.html('<table class="errortips" cellpadding="2" cellspacing="0"><tr><td class="errorheader"><img src="../img/close.png" height="14" width="14" align="right" onclick="closeMessage();" style="CURSOR: pointer">'
			+ 'Error:' + '</td></tr><tr><td class="errorcontent"><b>' + ''  + '</b><br>' + error + '<br></td></tr></table>'
			+ '<iframe src="javascript:false" class="divoverlapiframe" ></iframe>');

	//-moz-opacity:0.90;
	//set the errormessage on the top of backgroud overlay
	errormessage.css({'top' : (arrayPageSize[3]/2-60+'px'), 
		'left' : (arrayPageSize[2]/2-120+'px'), 
		'filter':'progid:DXImageTransform.Microsoft.Alpha(Opacity=70) progid:DXImageTransform.Microsoft.dropshadow(OffX=5,OffY=5, Color=gray, Positive=true)',
		'zIndex' : '1000'
	});

	errormessage.show();
}

function closeMessage(){
	$('#errormessagefloat').hide();
	clearBackgroudOverlay();
}

/**
 * @description Set the overlay on background to deactive the action on background when error message occors
 * @access public
 * @return void
 **/
function  setBackgroudOverlay(arrayPageSize){
	if(!document.getElementById('overlay')){
		createNewHiddenElement('overlay');
	}
	var objOverlay = $("#overlay");

	objOverlay.onclick = function tem() {return false;}

	// set height of Overlay to take up whole page and show
	$('#overlay').css({
		'top' : '0', 
		'left' : '0', 
		'zIndex' : '999', 
		'width' : '100%', 
		'width' : (arrayPageSize[2] + 'px'), 
		'height' : (arrayPageSize[1] + 'px')});
	$('#overlay').show();
}

/**
 *
 * @access public
 * @return void
 **/
function clearBackgroudOverlay(){
	if(document.getElementById('overlay')){
		$('#overlay').hide();
	}
}

//addLoadEvent()
//Adds event to window.onload without overwriting currently assigned onload functions.
//Function found at Simon Willison's weblog - http://simon.incutio.com/

function addLoadEvent(func)
{
	var oldonload = window.onload;
	if (typeof window.onload != 'function'){
		window.onload = func;
	} else {
		window.onload = function(){
			if (oldonload) {
				oldonload();
			}

			func();
		}
	}

}

/*
 * quit install
 */
function quitInstallation(){
	if(window.confirm('Install is not complete. If you exit now, the program will not be installed.\n'+
	'You may run Install again at another time to complete the installation.\n Exit Install?')){
		window.close();
	}else{
		;
	}
}

function quitConfigure(){
	if(window.confirm('Configure is not complete. If you exit now, the Configuration will not be saved.\n'+
	'You may run Configure again at another time to complete the Configuration.\n Exit Configure?')){
		window.close();
	}else{
		;
	}
}

/**************************************************************************/
/**
 *
 * @access public
 * @return void
 **/
function continueInstall(message){
	if(confirm(message)){
	}else{return false;}
}

/**
*<div>-<label/>(with *)-<input/>-</div>
*/
function chk_form_required_field(id){
	pass = true;
	try{
	$("#"+ id+ " label:contains('*')").next().each(function(){
		if(this.value == '') {
			$(this).addClass('ui-state-error');
			//text = $(this).parent().prev().text();
			growlError("You must fill out the fields with star * ");
			this.focus();
			pass = false;
			return false;
		}else{
			$(this).removeClass('ui-state-error');
		}
	});
	}catch(e){
		//alert(e);
		return false;
	}
	return pass; 
}

/**
 * @Description Validate the database login input value, use for data upload in 2.php
 * @access public
 * @return void
 **/
function chkLoginformInput(){
	if(document.formdb.ServerHost.value.isEmpty()){
		showErrorMessage("Server Host can not be none!");
		return false;
	}
	if(document.formdb.ServerHost.value.lastIndexOf('/') !=document.formdb.ServerHost.value.length-1){
		showErrorMessage("Please end Server Host with \"/\"");
		return false;
	}
	if(document.formdb.dbserver.value.isEmpty() || document.formdb.username.value.isEmpty() || document.formdb.dbpassword.value.isEmpty()){
		showErrorMessage("Please input server name, user name and password");
		return false;
	}
}

//***************************************************************************************
//Validate the input value, use for data upload in 2a.php
//***************************************************************************************
//Must input one database name
function chkDabaseCreateInput(){
	if(document.databasenamecreate.databasei.value.isEmpty()){
		document.databasenamecreate.databasei.value = "";
		//alert("Please input the database name!");
		showErrorMessage("Please input the database name!");
		return false;
	}
	//cant not input only number
	/*var meme = parseInt(document.databasenamecreate.databasei.value);
    if(!isNaN(meme)){
	    document.databasenamecreate.databasei.value = "";
	    showErrorMessage("Could not input only number!");
	    return false;
	}*/
	if(document.databasenamecreate.databasei.value.trim().isNumber()){
		document.databasenamecreate.databasei.value = "";
		showErrorMessage("Could not input only number!");
		return false;
	}

}


function submitDeleteSRS(){
	if(confirm("Are you sure to delete the layers?"))
	{
		//document.nameFormDelete.blndelete = "true";
		document.nameFormDelete.action ="datadelete.php";
		document.nameFormDelete.submit();

	}
	else{return false;}

}

/*
 * Select or deselect the select option in Form, used in getmap demo
 */
function checkall(form, tableid, prefix, checkall) {
	var checkall = checkall ? checkall : 'chkall';
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.name != checkall && (!prefix || (prefix && e.name.match(prefix)))) {
			e.checked = form.elements[checkall].checked;
		}
	}
	var flag = form.elements[checkall].checked;
	$('#'+tableid+' tr, th').each(
		function() {	
			 var classname = $(this).attr('class'); 	
			 if(flag){
				 if( classname == 'even' || classname == 'odd') {
					 $(this).addClass('marked');
				 }
			 }else{
				 if( classname == 'even marked' || classname == 'odd marked') {
					 $(this).removeClass('marked');
				 }
			 }
	});
}

function checkall_s(node)
{
	if(node.length){
		for (i = 0; i < node.length; i++){
			node[i].checked = true ;
		}
	}
	else if(!node.length){
		node.checked = true ;
	}
}

function uncheckAll_s(node)
{
	if(node.length){
		for (i = 0; i < node.length; i++)
			node[i].checked = false ;
	}
	else if(!node.length){
		node.checked = false ;
	}
}

/**
 *
 * @access public
 * @return void
 **/
function chkCacheformInput(){
	strDatefrom = document.namecache.txtdatefrom.value;
	strDatefrom = strDatefrom.replace(/\//g,"-");
			strDateto = document.namecache.txtdateto.value;
	strDateto = strDateto.replace(/\//g,"-");
			//if not select all
			if(!document.namecache.ckbSelectAll.checked){
				if(strDatefrom!=""){
					//12/31/2007
					var reg = /^(\d{1,2})(-|\/)(\d{1,2})\2(\d{1,4})$/;
					var r = strDatefrom.match(reg);
					var D= new Date(r[4], r[1]-1,r[3]);
					var B = D.getFullYear()==r[4]&&(D.getMonth()+1)==r[1]&&D.getDate()==r[3];
					if (!B) {
						showErrorMessage("The date format is not right, it should be mm/dd/yyyy");
						return false;
					}
				}
				if(strDateto!=""){
					//12/31/2007
					var reg = /^(\d{1,2})(-|\/)(\d{1,2})\2(\d{1,4})$/;
					var r = strDateto.match(reg);
					var D= new Date(r[4], r[1]-1,r[3]);
					var B = D.getFullYear()==r[4]&&(D.getMonth()+1)==r[1]&&D.getDate()==r[3];
					if (!B) {
						showErrorMessage("The date format is not right, it should be mm/dd/yyyy");
						return false;
					}
				}
			}
}

function GoBack(){
	history.go(-1);
}

/**
 *
 * @access public
 * @desc run the function when page on load
 * @return void
 **/
function doOnload(functionname){
	if (window.addEventListener)
		window.addEventListener("load", functionname, false);
	else if (window.attachEvent)
		window.attachEvent("onload", functionname);
	else if (document.getElementById)
		window.onload=functionname;
}

/**
 * works with all the input textfilesd
 * it will work for all the pages, it document.onmouseover=mouseOverEventHandle has been set
 */
function mouseOverEventHandle(e){
	try{
		if(event.srcElement.tagName.toLowerCase()=='input'){
			if(event.srcElement.type.toLowerCase()=='text'||event.srcElement.type.toLowerCase()=='password'){
				cc=event.srcElement;
				cc.focus();
				cc.select();
			}
		}
	}catch(e){
	}
	try{
		if(event.srcElement.tagName.toLowerCase()=='textarea'){
			cc=event.srcElement;
			cc.focus();
			cc.select();
		}
	}catch(e){
	}
}
/*document.onmouseover=mouseOverEventHandle;*/

/**
 *
 * @access public
 * @return void
 **/
function txtfieldSelectAll(e){
	try{
		e.focus();
		e.select();
	}catch(e){
		Debug(e);
	}
}


/**
 *
 * @access public
 * @return void
 **/
function Debug(error){
	if(javaScriptDebugMode=='true')
		showDebugMessage(error);
}

/**
*
* @access public
* @return void
**/
function showLoading(){
	$('#id_div_process_loading').html('<img src=\"../img/wait.gif\" />');
	$('#id_div_process_loading').show();
}
/**
*
* @access public
* @return void
**/
function hideLoading(){
	$('#id_div_process_loading').hide();
}

function jbutton(){
	$(':button').hover(
			function(){ 
				$(this).addClass("ui-state-hover"); 
			},
			function(){ 
				$(this).removeClass("ui-state-hover"); 
			}
			).mousedown(function(){
				$(this).addClass("ui-state-active"); 
			})
			.mouseup(function(){
				$(this).removeClass("ui-state-active");
			});
	$(':submit').hover(
			function(){ 
				$(this).addClass("ui-state-hover"); 
			},
			function(){ 
				$(this).removeClass("ui-state-hover"); 
			}
			).mousedown(function(){
				$(this).addClass("ui-state-active"); 
			})
			.mouseup(function(){
				$(this).removeClass("ui-state-active");
			});
}
