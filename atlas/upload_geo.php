<?php
require_once '../config.php';
require_once '../models/import.inc';
include_once '../models/common.inc';
include_once '../models/perm.inc';
require_once '../models/setting.inc';
session_start();

$perm = false;
$error = "";
setConnectionTime(SITE_MAX_TIMEOUT_LIMIT);
switchDatabase($dbtype);
$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

$kml = ($_POST['kmlstr']);
$kml = str_replace("\\\"", "\"", $kml); 
$aid = $_POST['aid'];
$data_encode = 'utf-8';
//delete the space inside
$SRSname = str_replace(" ", "_", trim($_POST['srs'])); 
$layername = trim($_POST['layername']);

$atlas_info = $database->db_get_atlas($aid);
$perm = perm_atlas_oper($atlas_info, $database);

if(!$perm){
	$error =  "You have no permission.";
	echo "err:".$error;
    return;
}

if (empty($SRSname)) {
    $error = "SRS can not be empty!";
}
else if (empty($layername)) {
    $error = "Layer name can not be empty!";
}
else if (empty($kml)) {
    $error = "data can not be empty!";
}

/*$outp = fopen("../files/atlas/$aid/cache/" . date("YmdHis"), "w");
fwrite($outp, $kml);
fclose($outp);*/

if(!empty($error)){
    echo "err:".$error;
    return;
}
else{
	require_once '../parser/KML2DB.class.php';

	if($_POST['KML_Use_Group_Name']){
		$layername = "";
	}
	//echo $kml;
    $parser = new KML2DB($database, $aid, $data_encode, $layername, $temp_name, $SRSname);
    $parser->set_appendix_parameters(array(
			'use_custom_layername'=>!empty($layername)
    		,'use_groupname_as_layername'=>false
    		,'use_kml_string'=>true
			,'kml_string'=>$kml
    		));
    $parser->begin();

    $error = $parser->error;
    $recordgood = empty($parser->recordgood)?"0":$parser->recordgood;
    $recordbad = empty($parser->recordbad)?"0":$parser->recordbad;
    $log = $parser->log;
    //only display 1024 length, to avoid that browser may freeze
    $logpart = substr($log, 0, strlen($log)>1024?1024:strlen($log));
    $logpart = trim(str_replace("\"", "\\\"", $logpart));

	//refresh_srs_list($aid, $database);
    $database->databaseClose();
}

if ($error == "" AND $recordgood > 0) {
	$msg = t('Data Imported Successfully.<br><FONT color=\"green\">%recordgood</FONT> records has been imported into database successfully. <br><FONT color=\"red\">DO NOT FORGET</FONT> to create style after new data has been imported!'
	, array('%recordgood' => $recordgood));
    echo "suc:".$msg;
}
else if ($error == "" AND $recordgood == 0) {
	$msg = t('No Data Has Been Imported.'
	.'<br><FONT color="green">%recordgood</FONT> records has been imported into database. <br>'
	.'Please check your data. '
	, array('%recordgood' => $recordgood));
    echo "err:".$msg;
}
else if ($error != "" AND $recordgood > 0) {
	$msg = t('Data Import Has Errors: '
	.'<br><FONT color=\"green\">%recordgood</FONT> records have been imported into database<br>'
	.'<FONT color=\"red\">%recordbad</FONT> records have mistakes and can not be imported into database<br>'
	.'Errors are listed below: %logpart', 
		array('%recordgood' => empty($recordgood)?"0":$recordgood, '%recordbad' => empty($recordbad)?"0":$recordbad, '%logpart' => $logpart));
	echo "err:".$msg;
}
else if ($error != "" AND ($recordgood == 0 OR $recordgood == null)) {
	$msg = t('Failed to import data: %error<br>'
  	.'<FONT class=\"error\">%recordbad</FONT> records have mistakes and can not be imported into database, part of errors are listed below: %logpart'
  		, array('%error' => $error, '%recordbad' => empty($recordbad)?"0":$recordbad, '%logpart' => empty($logpart)?$error:$logpart));
	echo "err:".$msg;
}
else{
	
}


function refresh_srs_list($aid, $database){
	$options = '<option value=\"' . LayerNotDefined . '\">' . LayerNotDefined . '</option>'.
			'<option value=\"Use_File_Name\" class=\"error\">Use File Name As Layer Name</option>';
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
?>