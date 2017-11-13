function show_deatil_block(id, count){
	if($("#block_detail_"+id).css('display') == 'block'){
		return;
	}
	for(i=0;i<count;i++){
		$("#block_detail_"+i).hide();
	}
	$("#block_detail_"+id).slideToggle("normal");
}

//***************************************************************************************
//Validate the input value, use for data upload in atlas.php
//***************************************************************************************
function chk_atlas_create_form(){
	pass = true;
	$("td:contains('*')").next().find("input").each(function(){
		if(this.value == '') {
			text = $(this).parent().prev().text();
			growlError("You must fill out the fields with star * ");
			this.focus();
			pass = false;
			return false;
		}
	});
	return pass; 
}


/**
 *
 * @access public
 * @return void
 **/
function reset_atlas_create_form(){
	try{
		$("form input:text").each(function(){
			this.value = '';
		});
	}catch(e){
		showDebugMessage(e + " in reset_atlas_create_form");
	}
}

/**
 *
 * @access public
 * @return void
 **/
function setLayerName(opt){
	var layername = opt.options[opt.selectedIndex].value;
	if(layername == "Use_File_Name"){
		$("#layernametem").attr("disabled","disabled");
	}
	else{
		$('#layernametem').removeAttr("disabled")
	}
	$('#layernametem').val(layername);
}
//***************************************************************************************
//Validate the input local file value, use for data upload
//***************************************************************************************
function validate_clear(id){
	$('#'+id).css({'background-color': "#fff"});
	//$('#'+id).removeClass('ui-state-error');
}

function validate_error(id){
	$('#'+id).css({'background-color': "#fcc"});
	//$('#'+id).addClass('ui-state-error');
	try{
		$('#'+id).focus();
	}catch(e){		
	}
}

/**
 *
 * @access public
 * @return void
 **/
function checkImportForm(flag){
	//for all form
	//check if has given layer name
	validate_clear('layernametem');

	if($('#layernametem').val().isEmpty()){
		if(flag == 'csv' || flag == 'csv_remote'){
			if($('#CSV_Use_Default_Name_'+flag).is(':checked') == false){
				growlError("Please give destination layer name or select on option.");
				validate_error('layernametem');
				return false;
			}
		}else{
			growlError("Please give destination layer name or select on option.");
			validate_error('layernametem');
			return false;
		}
	}

	//csv has not srs list
	validate_clear('srs_default');
	if(flag != 'csv' && flag != 'csv_remote'){
		if($('#srs_default').val().trim().isEmpty()){
			growlError("Please give the Spatial Reference System name! Default value: SRS_not_defined");
			validate_error('srs_default');
			return false;
		}
		else if($('#srs_default').val().trim().indexOf(" ")>0){
			growlError("SRS name can not contain space.");
			validate_error('srs_default');
			return false;
		}

		//csv use custom encode, can be empty
		validate_clear('data_encode');
		if($('#data_encode').val().trim().isEmpty()){
			growlError("Please give the Input Encode!");
			validate_error('data_encode');
			return false;
		}
		else if($('#data_encode').val().trim().indexOf(" ")>0){
			growlError("User defined Input Encode can not contain space.");
			validate_error('data_encode');
			return false;
		}
	}
	else{
		if($('#CSV_Use_Default_SRS_'+flag).is(':checked') == false){
			if($('#srs_default').val().trim().isEmpty()){
				growlError("Please give the Spatial Reference System name! Default value: SRS_not_defined");
				validate_error('srs_default');
				return false;
			}
			else if($('#srs_default').val().trim().indexOf(" ")>0){
				growlError("SRS name can not contain space.");
				validate_error('srs_default');
				return false;
			}
		}		
	}
	$('#srs_'+flag).val($('#srs_default').val());



	//does not work, why
	//var fileName = $(":file[@name='file_'+flag]");
	validate_clear('filecontainer_'+flag);
	var fileName = $('#file_'+flag);
	if(flag.lastIndexOf("_remote")>0){
		fileName = $('#file_'+flag);	
		//$('#selectList :selected').text()
	}
	var strFileName = fileName.val();

	var FileType = flag;
	if(flag.lastIndexOf("_remote")>0){
		FileType = flag.substring(0, flag.lastIndexOf('_'));
	}
	FileType = FileType.toUpperCase();
	//if remote select list has no element, strFileName is null
	if( strFileName==null || strFileName.isEmpty()){
		growlError("Please select "+FileType+" file!");
		validate_error('filecontainer_'+flag);
		return false;
	}
	else if(strFileName.toUpperCase().lastIndexOf("."+FileType) == -1){
		growlError("Only "+FileType+" file is allowed.");
		validate_error('filecontainer_'+flag);
		return false;
	}

	var strFileName_file = get_filename_from_path(strFileName);

	if(flag == 'csv_remote' || flag == 'csv'){
		//$('#Form_'+flag input, #myForm textarea');
		//alert( $("#Form_"+flag+" :input[@name='csv_terminated']").text() );
		//alert($("form[@name='Form_'+flag] : input[@name='csv_terminated']").val());
		document.getElementById("Form_"+flag).csv_terminated.style.backgroundColor = '#fff';
		if(document.getElementById("Form_"+flag).csv_terminated.value.isEmpty()){
			document.getElementById("Form_"+flag).csv_terminated.focus();
			growlError("Please input the terminated letter");
			document.getElementById("Form_"+flag).csv_terminated.style.backgroundColor = '#fcc';
			return false;
		}
	}
	if( flag == 'shp'){
		validate_clear('filecontainer_dbf');
		validate_clear('filecontainer_shx');
		if($('#usefile_dbf').is(':checked')){
			if($('#file_dbf').val().isEmpty()){
				growlError("Please select DBF file!");
				validate_error('filecontainer_dbf');
				return false;
			}
			if($('#file_dbf').val().toUpperCase().lastIndexOf(".DBF")== -1 ){
				growlError("Only DBF file is allowed.");
				validate_error('filecontainer_dbf');
				return false;
			}
			var strFileName_file_add = get_filename_from_path($('#file_dbf').val());
			if(strFileName_file != strFileName_file_add){
				growlError("You can only import DBF file with same name of SHP file!");
				validate_error('filecontainer_dbf');
				return false;
			}
		}
		if($('#usefile_shx').is(':checked')){
			if($('#file_shx').val().isEmpty()){
				growlError("Please select SHX file!");
				validate_error('filecontainer_shx');
				return false;
			}
			if($('#file_shx').val().toUpperCase().lastIndexOf(".SHX")== -1 ){
				growlError("Only SHX file is allowed.");
				validate_error('filecontainer_shx');
				return false;
			}
			var strFileName_file_add = get_filename_from_path($('#file_shx').val());
			if(strFileName_file != strFileName_file_add){
				growlError("You can only import SHX file with same name of SHP file!");
				validate_error('filecontainer_shx');
				return false;
			}
		}	
	}
	if( flag == 'mif'){
		validate_clear('filecontainer_mid');
		if($('#usefile_mid').is(':checked')){
			if($('#file_mid').val().isEmpty()){
				growlError("Please select MID file!");
				validate_error('filecontainer_mid');
				return false;
			}
			if($('#file_mid').val().toUpperCase().lastIndexOf(".MID")== -1 ){
				growlError("Only MID file is allowed.");
				validate_error('filecontainer_mid');
				return false;
			}
			var strFileName_file_add = get_filename_from_path($('#file_mid').val());
			if(strFileName_file != strFileName_file_add){
				growlError("You can only import MID file with same name of MIF file!");
				validate_error('filecontainer_mid');
				return false;
			}
		}
	}
	if(flag == 'shp_remote'){
		validate_clear('filecontainer_dbf_remote');
		validate_clear('filecontainer_shx_remote');
		if($('#usefile_dbf_remote').is(':checked')){
			if($('#file_dbf_remote').val().isEmpty()){
				growlError("Please select DBF file!");
				validate_error('filecontainer_dbf_remote');
				return false;
			}
			if($('#file_dbf_remote').val().toUpperCase().lastIndexOf(".DBF")== -1 ){
				growlError("Only DBF file is allowed.");
				validate_error('filecontainer_dbf_remote');
				return false;
			}
			var strFileName_file_add = get_filename_from_path($('#file_dbf_remote').val());
			if(strFileName_file != strFileName_file_add){
				growlError("You can only import DBF file with same name of SHP file!");
				validate_error('filecontainer_dbf_remote');
				return false;
			}
		}
		if($('#usefile_shx_remote').is(':checked')){
			if($('#file_shx_remote').val().isEmpty()){
				growlError("Please select SHX file!");
				validate_error('filecontainer_shx_remote');
				return false;
			}
			if($('#file_shx_remote').val().toUpperCase().lastIndexOf(".SHX")== -1 ){
				growlError("Only SHX file is allowed.");
				validate_error('filecontainer_shx_remote');
				return false;
			}
			var strFileName_file_add = get_filename_from_path($('#file_shx_remote').val());
			if(strFileName_file != strFileName_file_add){
				growlError("You can only import SHX file with same name of SHP file!");
				validate_error('filecontainer_shx_remote');
				return false;
			}
		}	
	}
	if( flag == 'mif_remote'){
		validate_clear('filecontainer_mid_remote');
		if($('#usefile_mid_remote').is(':checked')){
			if($('#file_mid_remote').val().isEmpty()){
				growlError("Please select MID file!");
				validate_error('filecontainer_mid_remote');
				return false;
			}
			if($('#file_mid_remote').val().toUpperCase().lastIndexOf(".MID")== -1 ){
				growlError("Only MID file is allowed.");
				validate_error('filecontainer_mid_remote');
				return false;
			}
			var strFileName_file_add = get_filename_from_path($('#file_mid_remote').val());
			if(strFileName_file != strFileName_file_add){
				growlError("You can only import MID file with same name of MIF file!");
				validate_error('filecontainer_mid_remote');
				return false;
			}
		}
	}

	//begin to upload file
	upload(flag);
}

/**
 * 
 * @param flag
 * @param filename, use lower case as default!!
 * @return
 */
function chk_upload_file(flag_add){
	var blnusefile = false;
	//var usefile_flag = $( "'#Form_'+flag >:checkbox[name='usefile_'+flag_add]");
	$('#filecontainer_'+flag_add).css({'background-color': "#fff"});
	var blnusefile = $('#usefile_'+flag_add);
	if(blnusefile.is(':checked')){
		document.getElementById('filecontainer_'+flag_add).style.display = 'block';
		//can not use show(), will destory the layout
		//$('#filecontainer_'+flag_add).show();
	}else{
		document.getElementById('filecontainer_'+flag_add).style.display = 'none';
		//$('#filecontainer_'+flag_add).hide();
	}

}

/**
 * get filename without postfix from file path
 */
function get_filename_from_path(strFileName){
	var strFileName_file = strFileName;
	//window system, use anti slash
	if(strFileName.lastIndexOf('\\')!=-1){
		strFileName_file  = strFileName.substring(strFileName.lastIndexOf('\\')+1,strFileName.lastIndexOf('.'));
	}
	//unix system
	else{
		strFileName_file  = strFileName.substring(strFileName.lastIndexOf('/')+1,strFileName.lastIndexOf('.'));
	}
	return strFileName_file;
}

function upload(flag) {
	$('#layername_'+flag).val($('#layernametem').val());
	$('#Form_'+flag).attr("target","uploadhideiframe");
	$('#Form_'+flag).attr("action","upload.php");
	$('#Form_'+flag).submit();

	var arrayPageSize = getPageSize();
	setBackgroudOverlay(arrayPageSize);
	createContainer();
}

function create_overview(aid){
	showLoading();
	$.ajax({
		type: "GET",
		url: "create_overview.php?aid="+aid,
		data: "",
		async: true,
		dataType: 'text',
		success: function(msg) {
		if(msg == '1'){
			growlInfo(' Overview image has been created.');
		}else{
			growlError(' Failed to create overview image.');
		}
		hideLoading();
	}
	}); 
}

function add_favorite(aid, uid){
	//show the dialog to add desc

	if($("#dialog").html() == null || $("#dialog").html() == ''){
		var $dialog = $('<div id="dialog" title="Add into Favorite" style="font-size:90%">'+
				'<form>'+
				'<fieldset>'+
				'<label for="name">Please add your description</label><br>'+
				'<textarea name="desc" id="desc" class="text ui-widget-content ui-corner-all" width="100%" size="256" wrap="VIRTUAL" ROWS="3" COLS="40"></textarea>'+
				'</fieldset>'+
				'</form>'+
		'</div>');
		$('body').append($dialog);
	}
	$(function() {
		var desc = $("#desc"),
		allFields = $([]).add(desc);

		$("#dialog").dialog({
			bgiframe: true,
			modal: true,
			resizable: false,
			buttons: {
			Add: function() {
			var bValid = true;
			allFields.removeClass('ui-state-error');
			var descstr = desc.val();
			if(desc.val().length == 0 || desc.val().length > 128){
				growlError("Length of Description must be between 1 to 128");
				desc.addClass('ui-state-error');
				bValid = false;
			}
			if(!bValid){
				return;
			}
			$(this).dialog('close');

			showLoading();
			$.ajax({
				type: "POST",
				url: "favorite.php",
				data: "aid="+aid+"&uid="+uid+"&op=1&desc="+descstr,
				async: true,
				dataType: 'text',
				success: function(msg) {
				var flag = msg.substring(0, 3);//flag:xxxxx
				var message = msg.substring(4, msg.length);
				if(flag == 'suc'){
					growlInfo(message);
					//update the favocucount
					var count = parseInt($('#text_atlas_favocucount').html()==''?'0':$('#text_atlas_favocucount').html());
					$('#text_atlas_favocucount').html((count+1)+'');
					$('#icon_atlas_addfavo').hide();
				}else{
					growlError(message);
				}
				hideLoading();
			}
			});

		},
		Cancel: function() {
			$(this).dialog('close');
		}
		},
		close: function() {
			allFields.val('').removeClass('ui-state-error');
		}

		});

		$('#dialog').dialog('open');
	});


}

function remove_favorite(aid, uid, removeListItem){
	if($("#dialog"+aid).html() == null || $("#dialog"+aid).html() == ''){
		var $dialog = $('<div id="dialog'+aid+'" title="Remove from Favorite" style="font-size:10px">'+
				'<form>'+
				'<fieldset>'+
				'<label for="name">Are you sure to remove this atlas from your favorite?</label><br>'+
				'</fieldset>'+
				'</form>'+
		'</div>');
		$('body').append($dialog);
	}
	
	$(function() {
		$("#dialog"+aid).dialog({
			bgiframe: true,
			modal: true,
			resizable: false,
			buttons: {
			OK: function() {
			$(this).dialog('close');

			showLoading();
			$.ajax({
				type: "POST",
				url: "favorite.php",
				data: "aid="+aid+"&uid="+uid+"&op=0",
				async: true,
				dataType: 'text',
				success: function(msg) {
				var flag = msg.substring(0, 3);
				var message = msg.substring(4, msg.length);
				if(flag == 'suc'){
					growlInfo(message);
					//update the favocucount
					if(!removeListItem){
						var count = parseInt($('#text_atlas_favocucount').html()==''?'0':$('#text_atlas_favocucount').html());
						$('#text_atlas_favocucount').html((count-1)<0?'0':((count-1)+''));
						$('#icon_atlas_removefavo').hide();
					}else{
						$('#favolistitemaid_'+aid).hide();
						$('#favolistitembid_'+aid).hide();
					}
				}else{
					growlError(message);
				}
				hideLoading();
			}
			}); 

		},
		Cancel: function() {
			$(this).dialog('close');
		}
		}

		});

		$('#dialog'+aid).dialog('open');
	});

}

/**
 *
 * @access public
 * @return void
 **/
function finish(){
	//if has error
	if($('photostatus').innerHTML.indexOf("ERROR")>0){
		$('idPhotoOperation').disabled = false;
	}else{
		deviceid = $('deviceid').value;
		vendorid = $('vendorid').value;
		$('idPhotoFile').value = "";
		$('idphotolink').value = vendorid+"_"+deviceid+".jpg";
		$('photoimage').src = "deviceimages/"+vendorid+"_"+deviceid+"_thumb.jpg";
	}
}


function openWindows(url){
	var tmp=window.open("about:blank","","fullscreen=1");
	tmp.moveTo(0,0);
	tmp.resizeTo(screen.width+20,screen.height);
	tmp.focus();
	tmp.location=url;
}