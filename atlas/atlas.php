<?php
/**
 * atlas.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @version $Id: atlas.php,v 1.2 2008/04/17 12:28 leelight Exp $
 * @copyright (C) 2006-2009  LI Hui(leelight)
 * @Description: input username and password, begin to setting
 */

include_once '../models/setting.inc';
include_once '../models/menu.inc';
include_once '../models/common.inc';
include_once '../models/perm.inc';
require_once '../models/Installation.class.php';
require_once '../parser/StyleReader.class.php';
require_once '../models/import.inc';
require_once '../models/page.class.php';
include_once '../config.php';
include_once 'atlas.inc';
include_once 'atlas_common.inc';

header('Content-Type: text/html; charset=utf-8'); 
//initialize all variable
$page = 'atlas';
$permview = false;
$permoper = false;
$listmax = 5;
$bln_create_overview = false;
$atlashascreated = false;
switchDatabase($dbtype);
//enable go back
session_cache_limiter('private, must-revalidate'); 
session_start();
global $menu;
if (isset ($_SESSION['user']) ){
	$user = $_SESSION['user'];
	global $user;
}

$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

$tab = "";
$QUERY_STRING = $_SERVER ['QUERY_STRING'];
$a = explode('&', $QUERY_STRING);
$i=0;

while ($i < count($a)) {
	$b = split('=', $a[$i]);
	$text_upper = strtoupper($b[0]);
	if ($text_upper == "TAB") {
		$tab = urldecode($b[1]);
	}
	if ($text_upper == "AID") {
		$aid = urldecode($b[1]);
	}
	//==========here is for WMS paramters===========
	if ($text_upper == "SERVICE") {
		$map_parameters['service'] = urldecode($b[1]);
	}
	if ($text_upper == "REQUEST") {
		$map_parameters['request'] = urldecode($b[1]);
	}
	if ($text_upper == "VERSION") {
		// the comma ',' in serversion is speicialchars, so must be preserved
		$map_parameters['version'] = $b[1];
	}
	//==========here is for WMS GetMap paramters===========
	if ($text_upper == "SRS") {
		$map_parameters['srs'] = urldecode($b[1]);
	}
	if ($text_upper == "FORMAT") {
		// the plus '+' in format is speicialchars, so must be preserved
		$map_parameters['format'] = $b[1];
	}
	if ($text_upper == "STYLES") {
		$map_parameters['style'] = urldecode($b[1]);
	}
	if ($text_upper == "WIDTH") {
		$map_parameters['width'] = urldecode($b[1]);
	}
	if ($text_upper == "HEIGHT") {
		$map_parameters['height'] = urldecode($b[1]);
	}
	if ($text_upper == "BBOX") {
		$map_parameters['bbox'] = urldecode($b[1]);
	}
	if ($text_upper == "LAYERS") {
		$map_parameters['layers'] = urldecode($b[1]);
	}
	if ($text_upper == "TRANSPARENT") {
		$map_parameters['transparent'] = urldecode($b[1]);
	}
	if ($text_upper == "EXCEPTIONS") {
		$map_parameters['exceptions'] = urldecode($b[1]);
	}
	$i++;
}

$op = strtoupper($_REQUEST['op']);
$isBackStep = strtoupper($_REQUEST['isBackStep']);
$sort = $_GET['sort'];
$order = $_GET['order'];
$pg = isset($_GET['pg'])?intval($_GET['pg']):1; 
$total = isset($_GET['total'])?intval($_GET['total']):0;
if(empty($aid))$aid = $_POST['atlas_aid'];

//if input aid is not for atlas in cache, then load the new atlas!!!!
if(isset ($_SESSION['atlas'])){
	if($_SESSION['atlas']['aid'] == $aid){
		$atlas = $_SESSION['atlas'];
	}else{
		$atlas = $database->db_get_atlas($aid, $uid);
		$_SESSION['atlas'] = $atlas;
	}
}else{
	$atlas = $database->db_get_atlas($aid, $uid);
	$_SESSION['atlas'] = $atlas;
	global $atlas;
}

$permoper = perm_atlas_oper( $atlas, $database );
$permview = perm_atlas_view( $atlas );
//echo "permoper:".$permoper."   /permview:".$permview;
if ($user){
	//create new atlas
	if($op == "ATLAS_CREATE_STEP0"){
		if($aid = atlas_create($user, $database, $_POST)){
			//has got the aid
			$atlashascreated = true;
			$op = "ATLAS_CREATE_STEP1_IMPORT";
			
			//store this new aid into session
			$atlas = $database->db_get_atlas($aid, $uid);
			$_SESSION['atlas'] = $atlas;
			global $atlas;
			$permoper = perm_atlas_oper( $atlas, $database );
			$permview = perm_atlas_view( $atlas );
		}else{
			$atlashascreated = false;
			$op = "ATLAS_CREATE_STEP0";
		}
	}
	//save the atlas
	else if($op == "ATLAS_SAVE" && $permoper){
		$aid = $_POST['atlas_aid'];
		
		if(atlas_update($user, $aid, $database, $_POST)){
			//$op = "";
			$tab = "meta";
		}else{
			$op = "";
		}
	}
	else if($op == "ATLAS_CFG_SAVE" && $permoper){
		$aid = $_POST['atlas_aid'];
		if(atlas_cfg_update($user, $aid, $database, $_POST)){
			$tab = "cfg";
		}else{
			$op = "";
		}
		
	}
	else if($op == "ATLAS_SLD_SAVE" && $permoper){
		$aid = $_POST['atlas_aid'];
		$sld_style_name = $_POST['sld_style_name'];
		$stid = $_POST['sld_style_id'];
		if(empty($sld_style_name)){
			setSessionMessage(t('Please give name for style, style has not been saved.'), SITE_MESSAGE_ERROR);
		}else{
			//save existed style name
			if($sld_style_name == "use_exist_style_name"){
				atlas_save_sld($aid, $database, $sld_style_name, $stid);
			}
			else{
				atlas_save_sld($aid, $database, $sld_style_name);
			}
		}
		$tab = "sld";
	}
	else if($op == "ATLAS_SLD_CHANGENAME"  && $permoper){
		$aid = $_POST['atlas_aid'];
		$sld_style_name = $_POST['sld_style_name'];
		$stid = $_POST['sld_style_id'];
		if(empty($sld_style_name)){
			setSessionMessage(t('Please give name for style, style name has not been changed.'), SITE_MESSAGE_ERROR);
		}else{
			atalas_change_style_name($database, $aid, $stid, $sld_style_name);
		}
		$tab = "sld";
	}
	else if(equalIgnoreCase($op, "ATLAS_SLD_SAVEAS")  && $permoper){
		$aid = $_POST['atlas_aid'];
		$sld_style_name = $_POST['sld_style_name'];
		if(empty($sld_style_name)){
			setSessionMessage(t('Please give name for style, style has not been saved.'), SITE_MESSAGE_ERROR);
		}else{
			atlas_save_sld($aid, $database, $sld_style_name, 0, true);
		}
		$tab = "sld";
	}
	else if(equalIgnoreCase($op, "ATLAS_SLD_SETDEFAULT")  && $permoper){
		$aid = $_POST['atlas_aid'];
		$stid = $_POST['sld_style_id'];
		if(empty($stid)){
			setSessionMessage(t('Style id is empty!'), SITE_MESSAGE_ERROR);
		}else{
			atlas_set_default_sld($aid, $stid, $database);
		}
		$tab = "sld";
	}
	else if(equalIgnoreCase($op, "ATLAS_SLD_DELETE")  && $permoper){
		$aid = $_POST['atlas_aid'];
		$stid = $_POST['sld_style_id'];
		if(empty($stid)){
			setSessionMessage(t('Style id is empty!'), SITE_MESSAGE_ERROR);
		}else{
			atlas_delete_sld($aid, $stid, $database);
		}
		$tab = "sld";
	}
	else if($op == "ATLAS_LAYERINFO_SAVE"  && $permoper){
		$aid = $_POST['atlas_aid'];
		if(atlas_save_layerinfo($aid, $database, $_POST)){
			$bln_create_overview = true;
		}
		$tab = "layerinfo";
	}
	else if($op == "ATLAS_DELETE_LAYERS"  && $permoper){
		$aid = $_POST['atlas_aid'];
		$current_deleted_layers = $_POST['CURRENT_DELETE_LAYERS'];
		$current_deleted_srs = $_POST['CURRENT_DELETE_SRS'];
		atlas_delete_layers($aid, $database, $current_deleted_layers, $current_deleted_srs);
		$tab = "layerinfo";
	}
	else if($op == "CANCEL"){
		$op = "MYATLAS";
	}
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=SUAS_NAME?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="Bookmark" href="../favicon.ico">
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="../cssjs/lib/jquery/css/redmond/jquery-ui-1.7.1.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../cssjs/lib/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="../cssjs/string.prototype.js"></script>
<script type="text/javascript" src="../cssjs/common.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/jquerycopyplugin/jquery.copy.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/jquery.blockUI.js"></script>
<script type="text/javascript" src="../cssjs/menu.js"></script>
<script type="text/javascript" src="../cssjs/atlas.js"></script>
<script type="text/javascript" src="../cssjs/loading.js"></script>
<?
if($tab == "geoedit"){
	$cfg_data = $database->db_get_atlas_cfg("", $aid);
	if($cfg_data){
		include_once '../parser/AttributeParser.class.php';
		$atlas_cfg = AttributeParser::extractAttribute($cfg_data['variable']);
	}else{
		$atlas_cfg = atlas_get_default_cfg();
	}
	print '    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $atlas_cfg['GoogleMapKey'] . '"></script>' . "\n";
	?>
<style type="text/css">
    v\:* {
      behavior:url(#default#VML);
    }
</style>
<link href="../cssjs/geoedit.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../cssjs/geoedit.js"></script>
<!--script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/jquery.bgiframe.min.js"></script-->
<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/clickmenu/jquery.clickmenu.js"></script>
<link type="text/css" href="../cssjs/lib/jquery/js/plugin/clickmenu/clickmenu.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="../cssjs/lib/jquery/js/plugin/jstree/tree_component.css" />
<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/jstree/css.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/jstree/tree_component.min.js"></script>
<script>
$(document).ready(function(){load();});
</script>
<?	
}		
?>


</head>
<body <?if($tab == "geoedit"){print'onunload="GUnload"';}?>>

<table id="main">
<tr id="logo"><td colspan="2"></td></tr>
<tr id="top">
	<td id="left" class="ui-widget-header">Menu</td>
	<td id="right">
	<div id="progressbar"><div id="process" style="width:<?
if($op == "ATLAS_CREATE"){
	echo "20";
}
else if($op == "ATLAS_CREATE_STEP0"){
	echo "30";
}
else if($op == "ATLAS_CREATE_STEP1_IMPORT"){
	echo "40";
}
else if($op == "ATLAS_CREATE_STEP2_SLD"){
	echo "60";
}
else if($op == "ATLAS_CREATE_STEP3_LAYERINFO"){
	echo "75";
}
else if($op == "ATLAS_CREATE_STEP4_CFG"){
	echo "90";
}
else if($op == "ATLAS_CREATE_STEP5_FINISH"){
	echo "100";
}
else{
	echo "0";
}
?>%;"></div></div>
	</td>
</tr>

<tr>
	<td id="progress">
        <ul>
<?
	menu::get_navigation_block($page, $op, $database);
if ($user){	
	//menu::get_navigation_block($page, $op, $database);
}
else{
	menu::get_login_block("", true);
}
		
if( (equalIgnoreCase(substr($op, 0, 9), "atlasview")  && $permview)
			|| ( ($op == "ATLAS_SELECT" || $op == "ATLAS_DELETE" 
				|| $op == "ATLAS_DEMO" || equalIgnoreCase(substr($op, 0,5), "demo_")
					|| $op == "ATLAS_CONFIGURATION" || !empty($tab)) && $permoper)){
		//refresh the atlas cache now
		if($op == "ATLAS_SELECT" || $op == "ATLAS_CONFIGURATION"){
			if(empty($aid))$aid = $_POST['atlas_aid'];
			if( $aid !=0 && !empty($aid)){
				$atlas = $database->db_get_atlas($aid, $uid);
				$_SESSION['atlas'] = $atlas;
				global $atlas;
				
			}
		}else if(equalIgnoreCase(substr($op, 0, 9), "atlasview") ){
			if( $aid > 0){
				$atlas = $database->db_get_atlas($aid, $uid);
				$_SESSION['atlas'] = $atlas;
				global $atlas;				
			}
		}

		menu::get_atlas_block($database);
	}		
?>
		</ul>
	</td>
	<td id="content">
	<div id="<?=SITE_MESSAGE_ERROR?>" class="messages error"></div>
	<div id="<?=SITE_MESSAGE_INFO?>" class="messages info"></div>
	<div id="<?=SITE_MESSAGE_WARN?>" class="messages warn"></div>
<?
if ( !$user ){
	setSessionMessage(t("You have not logged in. Go back to <a href='".get_base_server_host()."index.php'>Home</a>"), SITE_MESSAGE_INFO);
}

if($user){
	if($error = $database->databaseGetErrorMessage()){
		setSessionMessage(t($error), SITE_MESSAGE_ERROR);
	}
	
	if($op == "" ){
		get_atlas_list($database, $listmax, "modified", "desc", $pg, $total , false);
	}
	else if($op == "ATLASLIST"){
		get_atlas_list($database, $listmax, $order, $sort, $pg, $total, false);
	}
	else if(equalIgnoreCase(substr($op, 0, 9), "atlasview") && $permview){
		get_atlas_view($database, $user, $aid, $op, $map_parameters);		
	}
	else if($op == "FAVORITE" ){
		get_atlas_favo_list($database, $user, 10, $order, $sort, $pg, $total);
	}
	else if($op == "MYATLAS" && empty($tab)){
		get_myatlas_list($user,$database, $listmax, $order, $sort, $pg, $total);
	}
	else if($op == "ATLAS_SELECT" && $permoper){
		$aid = $_POST['atlas_aid'];
		if( $aid==0 || empty($aid) ){
			setSessionMessage("aid has not been given!", SITE_MESSAGE_ERROR);
			$tab = "";
		}
		$tab = "meta";
		$atlas = $database->db_get_atlas($aid, $uid);
		$_SESSION['atlas'] = $atlas;
		global $atlas;
		
	}
	//delete atlas and display the list
	else if($op == "ATLAS_DELETE" && $permoper){
		$aid = $_POST['atlas_aid'];
		if(atlas_delete($user, $aid, $database, "")){
			get_myatlas_list($user,$database, $listmax, $order, $sort, $pg, $total);
		}else{
			get_myatlas_list($user,$database, $listmax, $order, $sort, $pg, $total);
		}
	}
	//can not check the perm when create new atlas, because  there is no atlas id
	else if($op == "ATLAS_CREATE" /*&& $permoper*/){
		$_SESSION['atlas'] = array();
		get_atlas_create_form($user, $database, null, 0 );
	}
	else if($op == "ATLAS_DEMO"){
		if(empty($aid)){
			setSessionMessage("Please select one atlas.", SITE_MESSAGE_INFO);			
			get_myatlas_list($user,$database, $listmax, $order, $sort, $pg, $total);
		}else{
			if(!$permoper){
				setSessionMessage("You are not authorized to access this atlas.", SITE_MESSAGE_INFO);			
				get_myatlas_list($user,$database, $listmax, $order, $sort, $pg, $total);	
			}else{	
				atlas_get_demo_list("",$aid);
			}
		}		
	}
	else if(equalIgnoreCase(substr($op, 0,5), "demo_") && $permoper){
		atlas_get_demo_block($user, $aid, $op, $map_parameters, $database);
	}
	//if create, but failed, refilled the form, can not check the perm here
	else if(!$atlashascreated && $op == "ATLAS_CREATE_STEP0" /*&& $permoper*/){
		get_atlas_create_form($user,$database, $_POST, 0);
	}
	else if($atlashascreated && $op == "ATLAS_CREATE_STEP1_IMPORT" && $permoper){
		get_atlas_import_form($user, $aid, $database, true);
	}
	else if($isBackStep=="TRUE" && $op == "ATLAS_CREATE_STEP1_IMPORT" && $permoper){
		//use the aid from url
		//$aid = $_POST['atlas_aid'];
		get_atlas_import_form($user, $aid, $database, true);
	}
	else if(empty($isBackStep) && $op == "ATLAS_CREATE_STEP2_SLD" && $permoper){
		$aid = $_POST['atlas_aid'];
		get_atlas_sld_form($aid, $database, true);
	}
	else if($isBackStep=="TRUE" && $op == "ATLAS_CREATE_STEP2_SLD" && $permoper){
		get_atlas_sld_form($aid, $database, true);
	}
	else if(empty($isBackStep) && $op == "ATLAS_CREATE_STEP3_LAYERINFO" && $permoper){
		//save the default style
		$aid = $_POST['atlas_aid'];
		$sld_style_name = $_POST['sld_style_name'];
		$stid = $_POST['sld_style_id'];
		if(empty($sld_style_name)){
			setSessionMessage(t('Please give name for style, style has not been saved.'), SITE_MESSAGE_ERROR);
		}else{
			//save existed style name
			if($sld_style_name == "use_exist_style_name"){
				atlas_save_sld($aid, $database, $sld_style_name, $stid);
			}
			else{
				atlas_save_sld($aid, $database, $sld_style_name);
			}
		}
		
		get_atlas_layerinfo_form($aid, $database, $_POST, true);
	}
	else if($isBackStep == "TRUE" && $op == "ATLAS_CREATE_STEP3_LAYERINFO" && $permoper){
		get_atlas_layerinfo_form($aid, $database, $_POST, true);
	}
	else if(empty($isBackStep) && $op == "ATLAS_CREATE_STEP4_CFG" && $permoper){
		$aid = $_POST['atlas_aid'];
		if(atlas_save_layerinfo($aid, $database, $_POST)){
			$bln_create_overview = true;
		}
		
		get_atlas_cfg_form($user, $aid, $database, true);
	}
	else if($op == "ATLAS_CREATE_STEP5_FINISH" && $permoper){
		//finished
		$aid = $_POST['atlas_aid'];
		if(atlas_cfg_update($user, $aid, $database, $_POST)){
			
		}else{
			get_atlas_cfg_form($user, $aid, $database, true);
			$tab = "";
		}
		
		get_myatlas_list($user,$database, $listmax, $order, $sort, $pg, $total);
		setSessionMessage(t('Atlas installation finished.'), SITE_MESSAGE_INFO);
	}	
	else if($op == "ATLAS_CONFIGURATION" ){
		//go to print atlas tab configuration
		if(empty($aid)){
			setSessionMessage("Please select one atlas.", SITE_MESSAGE_INFO);			
			get_myatlas_list($user,$database, $listmax, $order, $sort, $pg, $total);	
			$tab = "";
		}else{
			if(!$permoper){
				setSessionMessage("You are not authorized to access this atlas.", SITE_MESSAGE_INFO);			
				get_myatlas_list($user,$database, $listmax, $order, $sort, $pg, $total);	
				$tab = "";
			}
		}
	}
	else if(!$permview || !$permoper){
		//perview has higher level
		if($user && !$permview){
			setSessionMessage(t("You have no permission to access this private atlas. <br>You can send request message to <b>%author</b> to get permission. <a href='#' onclick='javascript:GoBack();'>Go back</a>", 
				array('%author'=>$atlas['username'])), SITE_MESSAGE_INFO);
			//get_atlas_view($database, $user, $aid);
			
			get_atlas_detail($database, $permview, true);
		}
		else if($user && !$permoper){
			setSessionMessage("You are not authorized to access this atlas. <a href='#' onclick='javascript:GoBack();'>Go back</a>", SITE_MESSAGE_INFO);
		}			
		else{
			setSessionMessage("You have no permission to access this atlas. Please login.<a href='#' onclick='javascript:GoBack();'>Go back</a>", SITE_MESSAGE_INFO);
		}
		//get_atlas_list($database, $listmax, $order, $sort, $pg, $total, false);
		$tab = "";
	}
	
	print_atlas_tab($tab, $aid, $database, $user, $_POST);
	//if here has any message, print out
	displayMessage();
	if($bln_create_overview){
?>
	<script>
	create_overview(<?=$aid?>);
	</script>
<?		
	}
}else{
	if($op == "" ){
		get_atlas_list($database, $listmax, "modified", "desc", $pg, $total , false);
	}
	else if($op == "ATLASLIST"){
		get_atlas_list($database, $listmax, $order, $sort, $pg, $total, false);
	}
	else if(equalIgnoreCase(substr($op, 0, 9), "atlasview") && $permview){
		get_atlas_view($database, $user, $aid, $op, $map_parameters);
	}
	else if(!$permview || !$permoper){
		setSessionMessage("Guest has no permission to access this atlas. Please login or register. <a href='#' onclick='javascript:GoBack();'>Go back</a>", SITE_MESSAGE_INFO);			
	}
	
	displayMessage();
}
?>

	</td>
</tr>
<tr id="footer">
<td colspan="2">
<?menu::getFooter();?>
</td></tr>
</table>
<script>
<?
if($user){
	if($tab == "geoedit" && empty($atlas_cfg['GoogleMapKey'])){
		print 'growlError("Invalid Google Map Key, please apply for the key and save it in Atlas Configuration.");';
	}
}
?>
$(function() {jbutton();});
</script>
</body>
</html>
	
<?
ob_end_flush();
?>