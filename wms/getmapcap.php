<?php
/**
 * Getmapcap Class
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
 * @version $Id$
 * @copyright (C) 2006-2009  LI Hui
 * @Description : This show the copyright .
 * @contact webmaster@easywms.com
 * @version $1.0$ 2005
 * @Author Filmon Mehari and Professor Dr. Franz Josef Behr
 * @version $2.0$ 2006.05
 * @Author Chen Hang and LI Hui
 * @version $3.0$ 2006
 * @Author LI Hui
 * @version $4.0$ 2009.04
 * @Author LI Hui
 */

include_once 'SendException.class.php';
include_once '../config.php';
include_once '../models/common.inc';
include_once '../models/CommonFormula.class.php';
require_once '../models/setting.inc';
require_once '../render/RasterImagerRender.class.php';
require_once '../render/BMPRender.class.php';
require_once '../render/GDGradientFill.php';
require_once '../models/Cache.class.php';
require_once '../parser/AttributeParser.class.php';
require_once '../atlas/atlas_common.inc';

$errornumber = 0;
$btn_user_interface = false;
$map_paras = array();

switchDatabase($dbtype);
$database = new Database();
$database->databaseConfig($dbserver, $dbusername ,$dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

/*   : = %3a   / = %2f   @ = %40
 *   + = %2b   ( = %28   ) = %29
 *   ? = %3f   = = %3d   & = %26
 */
$assalowarray = array("%3a", "%2f", "%40", "%2b", "%28", "%29", "%3f", "%3d", "%26");
$assaupperarray = array("%3A", "%2F", "%40", "%2B", "%28", "%29", "%3F", "%3D", "%26");
$chararray = array(":", "/", "@", "+", "(", ")", "?", "=", "&");

$QUERY_STRING = $_SERVER ['QUERY_STRING'];
$QUERY_STRING = str_replace($assalowarray, $chararray, $QUERY_STRING);
$QUERY_STRING = str_replace($assaupperarray, $chararray, $QUERY_STRING);
//this is for suas client, in Firefox the & will be turned to "&amp;" during the variable transfering
$QUERY_STRING = str_replace('&amp;', '&', $QUERY_STRING);

$a = explode('&', $QUERY_STRING);
$i = 0;
while ($i < count($a)) {
	$b = split('=', $a[$i]);
	// echo "Value for parameter " .$b[0]."is " .$b[1]. "\n";
	$text_upper = strtoupper($b[0]);
	
	if ($text_upper == "AID") {
		$aid = urldecode($b[1]);
	}
	elseif ($text_upper == "SERVICE") {
		$map_paras['service'] = urldecode($b[1]);
	}
	elseif ($text_upper == "REQUEST") {
		$map_paras['request'] = urldecode($b[1]);
		$map_paras['requests'][] = urldecode($b[1]);
	}
	elseif ($text_upper == "VERSION") { // the comma ',' in serversion is speicialchars, so must be preserved
		$map_paras['version'] = $b[1];
	}
	elseif ($text_upper == "STYLES") {
		$map_paras['style'] = urldecode($b[1]);
	}
	elseif ($text_upper == "WIDTH") {
		$map_paras['width'] = urldecode($b[1]);
		$map_paras['widths'][] = urldecode($b[1]);
	}
	elseif ($text_upper == "HEIGHT") {
		$map_paras['height'] = urldecode($b[1]);
		$map_paras['heights'][] = urldecode($b[1]);
	}
	elseif ($text_upper == "FORMAT") { // the plus '+' in format is speicialchars, so must be preserved
		$map_paras['format'] = $b[1];
	}
	elseif ($text_upper == "SRS") {
		$map_paras['srs'] = urldecode($b[1]);
	}
	elseif ($text_upper == "BBOX") {
		$map_paras['bbox'] = urldecode($b[1]);
		$map_paras['bboxs'][] = urldecode($b[1]);
	}
	elseif ($text_upper == "LAYERS") {
		$map_paras['layers'] = urldecode($b[1]);
	}
	elseif ($text_upper == "TRANSPARENT") {
		$map_paras['transparent'] = urldecode($b[1]);
	}
	elseif ($text_upper == "EXCEPTIONS") {
		$map_paras['exceptions'] = urldecode($b[1]);
	}
	/**
	 * * Set the parameters of GetFeatureInfo
	 */
	elseif ($text_upper == "INFO_FORMAT") {
		$map_paras['info_format'] = urldecode($b[1]);
	}
	elseif ($text_upper == "QUERY_LAYERS") {
		$map_paras['query_layers'] = urldecode($b[1]);
	}
	// Screen coordinate
	elseif ($text_upper == "X") {
		$map_paras['pixel_x'] = urldecode($b[1]);
	}
	elseif ($text_upper == "Y") {
		$map_paras['pixel_y'] = urldecode($b[1]);
	}
	elseif ($text_upper == "RADIUS") {
		$map_paras['radius'] = urldecode($b[1]);
	}
	/**
	 * * Set the parameters of GetMap25D
	 */
	elseif ($text_upper == "BGCOLOR") {
		$map_paras['bgcolor'] = urldecode($b[1]);
		if($map_paras['bgcolor']!="" && strpos($map_paras['bgcolor'], "#")!=0){
			$map_paras['bgcolor'] = "#".$map_paras['bgcolor'];
		}
	}
	elseif ($text_upper == "SKYCOLOR") {
		$map_paras['skycolor'] = urldecode($b[1]);
		if($map_paras['skycolor']!="" && strpos($map_paras['skycolor'], "#")!=0){
			$map_paras['skycolor'] = "#".$map_paras['skycolor'];
		}
	}
	elseif ($text_upper == "HANGLE") {
		$map_paras['hangle'] = urldecode($b[1]);
		$map_paras['hangle'] = deg2rad($map_paras['hangle']);
	}
	elseif ($text_upper == "VANGLE") {
		$map_paras['vangle'] = urldecode($b[1]);
		$map_paras['vangle'] = deg2rad($map_paras['vangle']);
	}
	elseif ($text_upper == "DISTANCE") {
		$map_paras['distance'] = urldecode($b[1]);
	}
	/**
	 * * Set the parameters of GetMap3D
	 */
	elseif ($text_upper == "ELEVATIONS") {
		$map_paras['elevations'] = urldecode($b[1]);
		$elevationsvalues = explode(",", $map_paras['elevations']);
	}
	elseif ($text_upper == "POI") {
		$map_paras['poi'] = urldecode($b[1]);
		$poivalues = explode(",", $map_paras['poi']);
		$map_paras['poix'] = $poivalues[0];
		$map_paras['poiy'] = $poivalues[1];
		$map_paras['poiz'] = $poivalues[2];
	}
	elseif ($text_upper == "PITCH") {
		$map_paras['pitch'] = urldecode($b[1]);
	}
	elseif ($text_upper == "YAW") {
		$map_paras['yaw'] = urldecode($b[1]);
	}
	elseif ($text_upper == "ROLL") {
		$map_paras['roll'] = urldecode($b[1]);
	}
	elseif ($text_upper == "AOV") {
		$map_paras['aov'] = urldecode($b[1]);
	}
	elseif ($text_upper == "ENVIRONMENT") {
		$map_paras['environment'] = urldecode($b[1]);
	}
	elseif ($text_upper == "BGIMAGE") {
		$map_paras['bgimage'] = urldecode($b[1]);
	}
	$i++;
}
//not data via Get
if($QUERY_STRING == ""){
	$_POST = array_change_key_case($_POST, CASE_UPPER);
	$aid = $_POST["AID"];
	$map_paras['service'] = urldecode($_POST["SERVICE"]);
	$map_paras['request'] = urldecode($_POST["REQUEST"]);
	// the comma ',' in serversion is speicialchars, so must be preserved
	$map_paras['version'] = $_POST["VERSION"];
	$map_paras['style'] = urldecode($_POST["STYLES"]);
	$map_paras['width'] = urldecode($_POST["WIDTH"]);
	$map_paras['height'] = urldecode($_POST["HEIGHT"]);
	// the plus '+' in format is speicialchars, so must be preserved
	$map_paras['format'] = $_POST["FORMAT"];
	$map_paras['srs'] = urldecode($_POST["SRS"]);
	$map_paras['bbox'] = urldecode($_POST["BBOX"]);
	$map_paras['layers'] = urldecode($_POST["LAYERS"]);
	$map_paras['transparent'] = urldecode($_POST["TRANSPARENT"]);
	$map_paras['exceptions'] = urldecode($_POST["EXCEPTIONS"]);
	/**
	 * * Set the parameters of GetFeatureInfo
	 */
	$map_paras['info_format'] = urldecode($_POST["INFO_FORMAT"]);
	$map_paras['query_layers'] = urldecode($_POST["QUERY_LAYERS"]);
	$map_paras['pixel_x'] = urldecode($_POST["X"]);
	$map_paras['pixel_y'] = urldecode($_POST["Y"]);
	$map_paras['radius'] = urldecode($_POST["RADIUS"]);
	/**
	 * * Set the parameters of GetMap25D
	 */
	$map_paras['bgcolor'] = urldecode($_POST["BGCOLOR"]);
	$map_paras['bgcolor'] = formatParamColor($map_paras['bgcolor']);	
	$map_paras['skycolor'] = urldecode($_POST["SKYCOLOR"]);
	$map_paras['skycolor'] = formatParamColor($map_paras['skycolor']);
	$map_paras['hangle'] = urldecode($_POST["HANGLE"]);
	$map_paras['hangle'] = deg2rad($map_paras['hangle']);
	$map_paras['vangle'] = urldecode($_POST["VANGLE"]);
	$map_paras['vangle'] = deg2rad($map_paras['vangle']);
	$map_paras['distance'] = urldecode($_POST["DISTANCE"]);
	/**
	 * * Set the parameters of GetMap3D
	 */
	$map_paras['elevations'] = urldecode($_POST["ELEVATIONS"]);
	$elevationsvalues = explode(",", $map_paras['elevations']);
	$map_paras['poi'] = urldecode($_POST["POI"]);
	$poivalues = explode(",", $map_paras['poi']);
	$map_paras['poix'] = $poivalues[0];
	$map_paras['poiy'] = $poivalues[1];
	$map_paras['poiz'] = $poivalues[2];
	$map_paras['pitch'] = urldecode($_POST["PITCH"]);
	$map_paras['yaw'] = urldecode($_POST["YAW"]);
	$map_paras['roll'] = urldecode($_POST["ROLL"]);
	$map_paras['aov'] = urldecode($_POST["AOV"]);
	$map_paras['environment'] = urldecode($_POST["ENVIRONMENT"]);
	$map_paras['bgimage'] = urldecode($_POST["BGIMAGE"]);
}

if($map_paras['exceptions'] == ""){
	$map_paras['exceptions'] = "application/vnd.ogc.se_xml";
}
if(equalIgnoreCase($map_paras['transparent'], "true")){
	$map_paras['transparent'] = true;
}

$bboxvalues = explode(",",$map_paras['bbox']);
$numberofvalues = count($bboxvalues);
$minx = $bboxvalues[0];
$miny = $bboxvalues[1];
$maxx = $bboxvalues[2];
$maxy = $bboxvalues[3];

$cfg_data = $database->db_get_atlas_cfg("", $aid);
$atlas_cfg = array();
if($cfg_data){
	$atlas_cfg = AttributeParser::extractAttribute($cfg_data['variable']);
	$atlas_cfg['enableCache'] = $atlas_cfg['cacheExpiredTime']=="0"?false:true;
	$atlas_cfg['enablestretchmap'] = $atlas_cfg['enablestretchmap']=="0"?false:true;
	$atlas_cfg['showCopyright'] = $atlas_cfg['showCopyright']=="0"?false:true;
	$atlas_cfg['enableSVGPixelCoordinate'] = $atlas_cfg['enableSVGPixelCoordinate']=="0"?false:true;
	$atlas_cfg['enableStreamSVG'] = $atlas_cfg['enableStreamSVG']=="0"?false:true;
}else{
	$atlas_cfg = atlas_get_default_cfg();
}
//for create overview image for atlas
$atlas_cfg['custom_param_maxgeonum'] = $_REQUEST['custom_param_maxgeonum'];

$custom_image_path = "rasterdata/";
$custom_image_path_absolute = "../files/atlas/$aid/rasterdata/";

//TODO this will fix GetFeatureInfo request from GetOpenLayerViewer.php, there are 2 'request' getFeatureInfo and GetMap in url
//this is wrong, reason caused by OpenLayers is unknown!
if(count($map_paras['requests'])>1){
	$counti = 0;
	foreach($map_paras['requests'] as $v){
		if(equalIgnoreCase($v, "GetFeatureInfo")){
			$map_paras['request'] = "GetFeatureInfo";
			$map_paras['width'] = $map_paras['widths'][$counti];
			$map_paras['height'] = $map_paras['heights'][$counti];
			$map_paras['bbox'] = $map_paras['bboxs'][$counti];
			break;
		}
		$counti++;
	}
}

/**
 * *THE SEND EXCEPTION FUNCTION
 */
$sendexceptionclass = new SendExceptionClass(get_base_server_host(), "wms", SUAS_CFG_WMS_VERSION ,$map_paras['exceptions'],$map_paras['format'],
	$minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $enablestretchmap,$params['GetImageDefaultWidth'],$params['GetImageDefaultHeight']);

if (!equalIgnoreCase($map_paras['request'],"GetFeatureInfo") AND !equalIgnoreCase($map_paras['request'],"GetMap") AND !equalIgnoreCase($map_paras['request'],"GetCapabilities") AND
		!equalIgnoreCase($map_paras['request'],"GetStyles") AND !equalIgnoreCase($map_paras['request'],"DescribeLayer") AND !equalIgnoreCase($map_paras['request'],"GetLegendGraphic") AND
		!equalIgnoreCase($map_paras['request'],"GetMap25D") AND !equalIgnoreCase($map_paras['request'],"GetMap3D") AND $map_paras['request'] != "") {
	$errornumber = 33;
	$errorexceptionstring = "The REQUEST " . $map_paras['request'] . " is not supported by the server. The supported Requests are GetMap, GetCapabilities, GetFeatureInfo, GetStyles, GetLegendGraphic, DescribeLayer, GetMap25D and GetMap3D. " ;
}
if ($map_paras['request'] == "") {
	$errornumber = 32;
	$errorexceptionstring = "The REQUEST has not been given. The supported Requests are GetMap, GetCapabilities, GetFeatureInfo, GetStyles, GetLegendGraphic and DescribeLayer. " ;
}
// check version error
if (!equalIgnoreCase($map_paras['version'],SUAS_CFG_WMS_VERSION) AND $map_paras['version'] != "") {
	$errornumber = 20;
	$errorexceptionstring = "Invalid version number " . $map_paras['version'] . " given. The supported version number is VERSION = " . SUAS_CFG_WMS_VERSION ;
}
if ($map_paras['version'] == "") {
	$errornumber = 21;
	$errorexceptionstring = "Version number has not been given. The supported version number is VERSION = " . SUAS_CFG_WMS_VERSION ;
}
if (!equalIgnoreCase($map_paras['service'],"WMS") AND $map_paras['service'] != "") {
	$errornumber = 1;
	$errorexceptionstring = " " . $map_paras['service'] . " is not supported by the server. The only  service available is WMS" ;
}
if ($map_paras['service'] == "") {
	$errornumber = 21;
	$errorexceptionstring = "SERVICE has not been given. The supported SERVICE is WMS" ;
}
if (!equalIgnoreCase($map_paras['exceptions'],"application/vnd.ogc.se_xml") AND !equalIgnoreCase($map_paras['exceptions'],"application/vnd.ogc.se_inimage")) {
	$errornumber = 3;
	$errorexceptionstring = " " . $map_paras['exceptions'] . " is not supported by the server. The only  service available is application/vnd.ogc.se_xml and application/vnd.ogc.se_inimage." ;
}
if ($errornumber) {
	$sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
	$database->databaseClose();
}
else{
	
	if (equalIgnoreCase($map_paras['request'],"GetMap") OR equalIgnoreCase($map_paras['request'],"GetMap25D") OR equalIgnoreCase($map_paras['request'],"GetMap3D")) {
		if(equalIgnoreCase($map_paras['request'],"GetMap25D")){
			$map_paras['distance'] = ($maxy - $miny)*1.2;
			define("blnGetMap25D", true);
		}
		else{
			define("blnGetMap25D", false);
		}
		
		include_once '../render/WKTParser.class.php';
		include_once '../parser/StyleReader.class.php';
		include_once '../models/RasterColor.class.php';
		include 'GetMap.class.php';
	}
	
	if (equalIgnoreCase($map_paras['request'],"GetCapabilities")) {
		if(equalIgnoreCase($map_paras['format'],"text/x-json")){
			$proxyurl = get_base_server_host()."wms/getjson.php?URL=".get_base_server_host()
			."wms/getmapcap.php?SERVICE=".$map_paras['service']."&VERSION=".$map_paras['version']."&REQUEST=".$map_paras['request']."";
			echo file_get_contents($proxyurl);
		}
		else
			include 'GetCapabilities.class.php';
	}
	
	if (equalIgnoreCase($map_paras['request'],"GetFeatureInfo")) {
		include 'GetFeatureInfo.class.php';
	}
	
	if (equalIgnoreCase($map_paras['request'],"DescribeLayer")) {
		include 'DescribeLayer.class.php';
	}
	
	if (equalIgnoreCase($map_paras['request'],"GetLegendGraphic")) {
		require_once '../parser/StyleReader.class.php';
		require_once '../models/RasterColor.class.php';		
		include 'GetLegendGraphic.class.php';
	}
	
	if (equalIgnoreCase($map_paras['request'],"GetStyles")) {
		require_once '../render/RasterImagerRender.class.php';
		require_once '../parser/StyleReader.class.php';
		require '../models/RasterColor.class.php';
		include 'GetStyles.class.php';
	}
	
}

?>