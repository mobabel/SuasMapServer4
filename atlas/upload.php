<?php
require_once '../config.php';
require_once '../models/import.inc';
include_once '../models/common.inc';
include_once '../models/perm.inc';
require_once '../models/setting.inc';
session_start();
?>
<script type="text/javascript" src="../cssjs/lib/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/jquerycopyplugin/jquery.copy.js"></script>
<script type="text/javascript" src="../cssjs/string.prototype.js"></script>
<script type="text/javascript" src="../cssjs/common.js"></script>
<?

$perm = false;
$error = "";
setConnectionTime(SITE_MAX_TIMEOUT_LIMIT);
switchDatabase($dbtype);
$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

$uid = $_POST['uid'];
$aid = $_POST['aid'];
$uploadMethod = $_POST['uploadMethod'];
$flag = $_POST['flag'];
$data_encode = trim($_POST['data_encode']);
$cvs_use_default_srs = false;
//delete the space inside
$SRSname = str_replace(" ", "_", trim($_POST['srs_'.$flag])); 
$layername = trim($_POST['layername_'.$flag]);

$atlas_info = $database->db_get_atlas($aid);
$perm = perm_atlas_oper($atlas_info, $database);

if(!$perm){
	$error =  "You have no permission. Please log in.";
	setSessionMessage(t($error), SITE_MESSAGE_ERROR);
    displayMessage(true);
    hideLoadingWindows();
    return;
}

if ($uploadMethod == 1) {
    $temp_name = $_FILES['file_'.$flag]['tmp_name'];
    $file_name = $_FILES['file_'.$flag]['name'];

    $file_size = round(filesize($temp_name)/1024);
    //the user upload fize more than 2mb and it was stopped by browser.
    if(($file_name AND !$temp_name) OR $file_size >=SITE_MAX_UPLOAD_SIZE){
	    $error="You are allowed to upload files with size under ".SITE_MAX_UPLOAD_SIZE." Mbytes only. if you have big files, please upload the files to data folder in suas/files/user/".$user['uid']."/data/ and then use Remote Files Import.";
	}
}
else if ($uploadMethod == 0) {
    $temp_name = $_POST['file_'.$flag];
    $file_name = $_POST['file_'.$flag];
}

// $uploaddir = "./upload/";
// $uploadfile = $uploaddir . basename($_FILES['file_'.$flag]['name']);
if (!$file_name) {
    $error = "No file has been selected!";
    $bupload = false;
}
if (empty($SRSname)) {
    $error = "SRS can not be empty!";
    $bupload = false;
}
if (empty($layername)) {
    $error = "Layer name can not be empty!";
    $bupload = false;
}

if(!empty($error)){
    setSessionMessage(t($error), SITE_MESSAGE_ERROR);
    displayMessage(true);
    hideLoadingWindows();
}
else{
    clearAllMessage(true);
	
    if(equalIgnoreCase($layername, 'Use_File_Name')){
    	//delete the postfix
		$layername = substr($file_name, 0, strrpos($file_name,"."));
		//delete the path
		$layername = substr($layername, strrpos($layername, "/"), strlen($layername));
		$layername = str_replace("/", "", $layername);
		$layername = trim(str_replace(" ", "_", $layername));
	}
	//delete space in the given name
	else{
		$layername = trim(str_replace(" ", "_", $layername));
	}

	if($flag == "svg" || $flag == "svg_remote"){
		require_once '../parser/SVG2DB.class.php';

		if($_POST['SVG_Use_Group_Name']){
			$layername = "";
		}
    	$parser = new SVGToDB($database, $aid, $data_encode, $layername, $temp_name, $SRSname);
    }
    else if($flag == "csv" || $flag == "csv_remote"){
    	require_once "../parser/CSV2DB.class.php";

        $use_csv_header = isset($_POST['use_csv_header']);
    	$text_csv_terminated = $_POST['csv_terminated'][0];
    	$text_csv_enclosed = $_POST['csv_enclosed'][0];
    	$text_csv_escaped = $_POST['csv_escaped'][0];

		if($_POST['CSV_Use_Default_Name_'.$flag]){
			$layername = "";
		}
		if($_POST['CSV_Use_Default_SRS_'.$flag]){
			$cvs_use_default_srs = true;
		}
		$table_exists = true;
 
    	$parser = new CSV2DB($database, $aid, $data_encode, $layername, $temp_name, $SRSname, $use_csv_header,
        	$text_csv_terminated, $text_csv_enclosed, $text_csv_escaped, $table_exists);
        $parser->set_appendix_parameters(array('cvs_use_default_srs'=>$cvs_use_default_srs));
		$parser->import();
	}
	else if($flag == "shp"  || $flag == "shp_remote"){
		require_once "../parser/SHP2DB.class.php";
	
		if($flag == "shp" ){
    		$blnUseDbfFile = isset($_POST['usefile_dbf']);
    		$blnUseShxFile = isset($_POST['usefile_shx']);
		}else{
			$blnUseDbfFile = isset($_POST['usefile_dbf_remote']);
    		$blnUseShxFile = isset($_POST['usefile_shx_remote']);
		}
    	if ($uploadMethod == 1) {
		    $tempNameDbf = $_FILES['file_dbf']['tmp_name'];
		    $fileNameDbf = $_FILES['file_dbf']['name'];
		    $tempNameShx = $_FILES['file_shx']['tmp_name'];
		    $fileNameShx = $_FILES['file_shx']['name'];

		    $file_sizeDbf = round(filesize($tempNameDbf)/1024);
    		$file_sizeShx = round(filesize($tempNameShx)/1024);
    		if(($fileNameDbf AND !$tempNameDbf) OR ($fileNameShx AND !$tempNameShx)
				OR $file_sizeDbf >=SITE_MAX_UPLOAD_SIZE OR $file_sizeShx >=SITE_MAX_UPLOAD_SIZE){
					$error="You are allowed to upload files with size under ".SITE_MAX_UPLOAD_SIZE." Mbytes only. if you have big files, please upload the files to data folder in ".SITE_UPLOAD_DATA_DIRECTORY." and then run Remote Files Input.";
					setSessionMessage(t($error), SITE_MESSAGE_ERROR);
    				displayMessage(true);
    				return;
			}
    	}
    	else if ($uploadMethod == 0) {
		    $tempNameDbf = $_POST['file_dbf_remote'];
		    $fileNameDbf = $_POST['file_dbf_remote'];
		    $tempNameShx = $_POST['file_shx_remote'];
		    $fileNameShx = $_POST['file_shx_remote'];
    	}
    	
    	if (!$fileNameDbf /*&& $blnUseDbfFile*/) {
		   /*$error = "No DBF file has been selected!";
		    setSessionMessage(t($error), SITE_MESSAGE_ERROR);
    		displayMessage(true);
    		return;*/
    		$blnUseDbfFile = false;
		}
		if (!$fileNameShx /*&& $blnUseShxFile*/) {
		    /*$error = "No SHX file has been selected!";
		    setSessionMessage(t($error), SITE_MESSAGE_ERROR);
    		displayMessage(true);
    		return;*/
    		$blnUseShxFile = false;
		}
		if(empty($layername)){
			$layername = LayerNotDefined;
		}
		$parser = new SHP2DB($database, $aid, $data_encode, $layername, $SRSname,
        	$blnUseDbfFile, $blnUseShxFile, $temp_name, $tempNameDbf, $tempNameShx);
	}
	else if($flag == "mif"  || $flag == "mif_remote"){
		require_once "../parser/MIF2DB.class.php";
		if($flag == "mif" ){
			$blnUseMidFile = isset($_POST['usefile_mid']);
		}else{
			$blnUseMidFile = isset($_POST['usefile_mid_remote']);
		}
		if ($uploadMethod == 1) {
		    $tempNameMid = $_FILES['file_mid']['tmp_name'];
		    $fileNameMid = $_FILES['file_mid']['name'];

		    $file_sizeMid = round(filesize($tempNameMid)/1024);
    		if(($fileNameMid AND !$tempNameMid) OR $file_sizeMid >=SITE_MAX_UPLOAD_SIZE ){
					$error="You are allowed to upload files with size under ".SITE_MAX_UPLOAD_SIZE." Mbytes only. if you have big files, please upload the files to data folder in ".SITE_UPLOAD_DATA_DIRECTORY." and then run Remote Files Input.";
					setSessionMessage(t($error), SITE_MESSAGE_ERROR);
    				displayMessage(true);
    				return;
			}
    	}
    	else if ($uploadMethod == 0) {
		    $tempNameMid = $_POST['file_mid_remote'];
		    $fileNameMid = $_POST['file_mid_remote'];
    	}
    	
    	if (!$fileNameMid /*&& $blnUseMidFile*/) {
		    /*$error = "No MID file has been selected!";
		    setSessionMessage(t($error), SITE_MESSAGE_ERROR);
    		displayMessage(true);
    		return;*/
    		$blnUseMidFile = false;
		}
		if(empty($layername)){
			$layername = LayerNotDefined;
		}
		$parser = new MIF2DB($database, $aid, $data_encode,$layername, $SRSname, $temp_name, $blnUseMidFile, $tempNameMid);
	}
	else if($flag == "e00"  || $flag == "e00_remote"){
		require_once "../parser/E002DB.class.php";

		if(empty($layername)){
			$layername = LayerNotDefined;
		}
		$parser = new E002DB($database, $aid, $data_encode,$layername, $SRSname, $temp_name);
	}
	else if($flag == "osm"  || $flag == "osm_remote"){
		if(empty($layername)){
			$layername = LayerNotDefined;
		}
		
		$parser = new OSM2DB($database, $aid, $data_encode,$layername, $SRSname, $temp_name);
	}
	else if($flag == "kml" || $flag == "kml_remote"){
		require_once '../parser/KML2DB.class.php';

		if($_POST['KML_Use_Group_Name']){
			$layername = "";
		}
    	$parser = new KML2DB($database, $aid, $data_encode, $layername, $temp_name, $SRSname);
    	$parser->set_appendix_parameters(array('use_custom_layername'=>!empty($layername)
    		,'use_groupname_as_layername'=>$_POST['KML_Use_Group_Name']));
    	$parser->begin();
    }
    else if($flag == "gpx" || $flag == "gpx_remote"){
		require_once '../parser/GPX2DB.class.php';

		if($_POST['GPX_Use_Group_Name']){
			$layername = "";
		}

    	$parser = new GPX2DB($database, $aid, $data_encode, $layername, $temp_name, $SRSname);
    	$parser->set_appendix_parameters(array('use_custom_layername'=>!empty($layername)
    		,'use_groupname_as_layername'=>$_POST['GPX_Use_Group_Name']));
    	$parser->begin();
    }

    $error = $parser->error;
    $recordgood = empty($parser->recordgood)?"0":$parser->recordgood;
    $recordbad = empty($parser->recordbad)?"0":$parser->recordbad;
    $log = $parser->log;
    //only display 1024 length, to avoid that browser may freeze
    $logpart = substr($log, 0, strlen($log)>1024?1024:strlen($log));
    $logpart = trim(str_replace("\"", "\\\"", $logpart));
    $logpart = trim(str_replace("\n", "<br/>", $logpart));

	refresh_srs_list($aid, $database);
    $database->databaseClose();
}

if ($error == "" AND $recordgood > 0) {
	setSessionMessage("", SITE_MESSAGE_ERROR);
    setSessionMessage(t('Data Imported Successfully.<br><FONT color=\"green\">%recordgood</FONT> records has been imported into database successfully. <br><FONT color=\"red\">DO NOT FORGET</FONT> to create style after new data has been imported!'
	, array('%recordgood' => $recordgood)), SITE_MESSAGE_INFO);
    displayMessage(true);
}
if ($error == "" AND $recordgood == 0) {
	setSessionMessage("", SITE_MESSAGE_ERROR);
    setSessionMessage(t('No Data Has Been Imported.'
	.'<br><FONT color=\"green\">%recordgood</FONT> records has been imported into database. <br>'
	.'Please check your data. '
	, array('%recordgood' => $recordgood)), SITE_MESSAGE_INFO);
    displayMessage(true);
}
if ($error != "" AND $recordgood > 0) {
	setSessionMessage(t('Data Import Has Errors: '
	.'<br><FONT color=\"green\">%recordgood</FONT> records have been imported into database<br>'
	.'<FONT color=\"red\">%recordbad</FONT> records have mistakes and can not be imported into database<br>'
	.'Errors are listed below, please copy the error and go to'
	.'<a href=\"http://www.easywms.com/easywms/?q=en/node/158\" target=\"_blank\" class=\"error\">Issue Tracker</a>'
	.'to submit the error, to help us fix the bugs!<br/><form name=\"formLog\">'
	.'<table class=\"tableError\"><tr>'
     .'<td align=\"right\"><INPUT class=\"button\" name=\"Button\" onclick=\"HighlightAll(\'formLog.textareaLog\')\" type=\"button\" value=\"Copy to Clip\"></td>'
  .'</tr>'
    .'<tr>'
      .'<td height=\"30\" align=\"middle\">'
       .'<TEXTAREA class=\"editbox1\" name=\"textareaLog\" id=\"textareaLog\" wrap=\"VIRTUAL\">%logpart'
	   .'</TEXTAREA>'
  .'</td>'
  .'</tr>'
  .'</table>'
  .'</form>', array('%recordgood' => empty($recordgood)?"0":$recordgood, '%recordbad' => empty($recordbad)?"0":$recordbad, '%logpart' => $logpart)),
			 SITE_MESSAGE_ERROR);
    setSessionMessage("", SITE_MESSAGE_INFO);
    displayMessage(true);
}
if ($error != "" AND ($recordgood == 0 OR $recordgood == null)) {
	setSessionMessage(t('Failed to import data: %error<br>'
	/*.'<form name=\"formError\">'
  		.'<table class=\"tableError\">'
 		 .'<tr>'
     	.'<td align=\"right\"><INPUT class=\"button\" name=\"Button\" onclick=\"HighlightAll(\'formLog.textareaError\')\" type=\"button\" value=\"Copy Error to Clip\"></td>'
  		.'</tr>'
    	.'<tr>'
      	.'<td height=\"30\" align=\"middle\">'
       .'<TEXTAREA class=\"editbox2\" name=\"textareaError\" id=\"textareaError\" wrap=\"VIRTUAL\">%error'
	   .'</TEXTAREA>'
 		 .'</td>'
  	.'</tr>'
  	.'</table>'
  	.'</form>'*/
  	.'<FONT class=\"error\">%recordbad</FONT> records have mistakes and can not be imported into database, part of errors are listed below. Please go to'
			.'<a href=\"http://www.easywms.com/easywms/?q=en/node/158\" target=\"_blank\" class=\"error\">Issue Tracker</a>'
			.'to submit the error, to help us fix the bugs!<br/>'
  .'<form name=\"formLog\">'
  .'<table class=\"tableError\">'
  .'<tr>'
     .'<td align=right><INPUT class=\"button\" name=\"Button\" onclick=\"HighlightAll(\'formLog.textareaLog\')\" type=\"button\" value=\"Copy Log to Clip\"></td>'
  .'</tr>'
    .'<tr>'
      .'<td height=\"30\" align=\"middle\">'
       .'<TEXTAREA class=\"editbox1\" name=\"textareaLog\" id=\"textareaLog\" wrap=\"VIRTUAL\">%logpart'
	   .'</TEXTAREA>'
  .'</td>'
  .'</tr>'
  .'</table>'
  .'</form>', array('%error' => $error, '%recordbad' => empty($recordbad)?"0":$recordbad, '%logpart' => empty($logpart)?$error:$logpart)),
			 SITE_MESSAGE_ERROR);
    setSessionMessage("", SITE_MESSAGE_INFO);
    displayMessage(true);

}
else{
	//displayMessage(true);
}

hideLoadingWindows();

function refresh_srs_list($aid, $database){
	$options = '<option value=\"' . LayerNotDefined . '\">' . LayerNotDefined . '</option>'.
			'<option value=\"Use_File_Name\" class=\"error\">Use File Name As Layer Name</option>';
	/*$layernames = $database->getAllLayersNames($aid);
	while ($rec = $database->getColumns($layernames)) {
		if(!equalIgnoreCase($rec['layer'], "LayerNotDefined")){
			$options .= '<option value=\"'.$rec['layer'].'\">'.$rec['layer'].'</option>';
		}
	}*/
	$layersrs = $database->getAllLayersSrss($aid);
	$srs_temp = "";
	while ($rec = $database->getColumns($layersrs)) {
		if($rec['srs'] != $srs_temp){
			$options .= '<optgroup label=\"'.$rec['srs'].'\" >';
		}
		if($rec['typecount'] > 1){
			$options .= '<option value=\"' . $rec['layer'] . '\">' . $rec['layer'] . ' ('. GeometryTypeCompond .')'.  '</option>'; 
		}else{
			$options .= '<option value=\"' . $rec['layer'] . '\">' . $rec['layer'] . ' ('. $rec['geomtype'] .')'.   '</option>'; 
		}
		if($rec['srs'] != $srs_temp){
			$options .= "</optgroup>";
		}
		$srs_temp = $rec['srs'];
	}
    	$options .='<option value=\"\"></option>';

	echo "
	<script type=\"text/javascript\" >
		var layertem = parent.parent.$('#layertem');
		parent.parent.$('#layernametem').val('');
		layertem.html('$options');
	</script>";
}

function hideLoadingWindows(){
	echo "
	<script type=\"text/javascript\" >
		var targelem = parent.parent.$('#loader_container');
		targelem.hide();
		var overlay = parent.parent.$('#overlay');
		if(overlay){
			overlay.hide();
		}
	</script>";
}
?>