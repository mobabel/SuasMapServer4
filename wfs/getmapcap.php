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
 * @version $3.0$ 2006
 * @Author LI Hui
 * @version $4.0$ 2009.04
 * @Author LI Hui
 */

include '../config.php';
include_once 'SendException.class.php';
include_once '../models/common.inc';
require_once '../models/Cache.class.php';
require_once '../models/setting.inc';
require_once '../atlas/atlas.inc';
require_once '../parser/AttributeParser.class.php';

$errornumber = 0;
$btn_user_interface = false;

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
	}
	elseif ($text_upper == "VERSION") { // the comma ',' in serversion is speicialchars, so must be preserved
		$map_paras['version'] = $b[1];
	}
	elseif ($text_upper == "STYLES") {
		$map_paras['style'] = urldecode($b[1]);
	}
	elseif ($text_upper == "WIDTH") {
		$map_paras['width'] = urldecode($b[1]);
	}
	elseif ($text_upper == "HEIGHT") {
		$map_paras['height'] = urldecode($b[1]);
	}
	elseif ($text_upper == "FORMAT") { // the plus '+' in format is speicialchars, so must be preserved
		$map_paras['format'] = $b[1];
	}
	elseif ($text_upper == "SRS") {
		$map_paras['srs'] = urldecode($b[1]);
	}
	elseif ($text_upper == "BBOX") {
		$map_paras['bbox'] = urldecode($b[1]);
	}
	elseif ($text_upper == "LAYERS") {
		$map_paras['layers'] = urldecode($b[1]);
	}
	elseif ($text_upper == "OUTPUTFORMAT") {
		$map_paras['outputformat'] = urldecode($b[1]);
	}
	elseif ($text_upper == "TYPENAME") {
		$map_paras['typename'] = urldecode($b[1]);
	}
	elseif ($text_upper == "MAXFEATURES") {
		$map_paras['maxfeatures'] = urldecode($b[1]);
	}
	$i++;
}
//not data via Get
if($QUERY_STRING == ""){
	$_POST = array_change_key_case($_POST, CASE_UPPER);
	$aid = urldecode($_POST["AID"]);
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
	$map_paras['outputformat'] = urldecode($_POST["OUTPUTFORMAT"]);
	$map_paras['typename'] = urldecode($_POST["TYPENAME"]);
	$map_paras['maxfeatures'] = urldecode($_POST["MAXFEATURES"]);
}

$cfg_data = $database->db_get_atlas_cfg("", $aid);
if($cfg_data){
	$atlas_cfg = AttributeParser::extractAttribute($cfg_data['variable']);
	$atlas_cfg['enableCache'] = $atlas_cfg['cacheExpiredTime']=="0"?0:1;
}else{
	$atlas_cfg = atlas_get_default_cfg();
}

$sendexceptionclass = new SendExceptionClass(get_base_server_host(), "wfs", SUAS_CFG_WFS_VERSION);

/**
 * *THE SEND EXCEPTION FUNCTION
 */
// include 'SendException.php';
// THE DIFFERENT REQUEST
if (!equalIgnoreCase($map_paras['request'],"GetCapabilities") AND !equalIgnoreCase($map_paras['request'],"DescribeFeatureType") AND !equalIgnoreCase($map_paras['request'],"GetFeature")
		AND !equalIgnoreCase($map_paras['request'],"GetGmlObject") AND !equalIgnoreCase($map_paras['request'],"Transaction") AND $map_paras['request'] != "") {
	$errornumber = 33;
	$errorexceptionstring = "The REQUEST " . $map_paras['request'] . " is not supported by the server. The only supported Requests are GetCapabilities, DescribeFeatureType, GetFeature, GetGmlObject and Transaction. " ;
}
if ($map_paras['request'] == "") {
	$errornumber = 32;
	$errorexceptionstring = "The REQUEST has not been given. The only supported Requests are GetCapabilities, DescribeFeatureType, GetFeature, GetGmlObject and Transaction. " ;
}
// check version error
if (!equalIgnoreCase($map_paras['version'],SUAS_CFG_WFS_VERSION)  AND $map_paras['version'] != "") {
	$errornumber = 20;
	$errorexceptionstring = "Invalid version number " . $map_paras['version'] . " given. The only supported version number is VERSION = " . SUAS_CFG_WFS_VERSION ;
}
if ($map_paras['version'] == "") {
	$errornumber = 21;
	$errorexceptionstring = "Version number has not been given. The supported version number is VERSION = " . SUAS_CFG_WFS_VERSION ;
}
if (!equalIgnoreCase($map_paras['service'],"WFS") AND $map_paras['service'] != "") {
	$errornumber = 1;
	$errorexceptionstring = " " . $map_paras['service'] . " is not supported by the server. The only  service available is " . "WFS" ;
}
if ($map_paras['service'] == "") {
	$errornumber = 21;
	$errorexceptionstring = "SERVICE has not been given. The supported SERVICE is " . "WFS" ;
}
if ($errornumber) {
	$sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
	$database->databaseClose();
}
else{
	/**
	 * *THE GET CAPABILITY REQUEST
	 */
	if (equalIgnoreCase($map_paras['request'],"GetCapabilities")) {
		include 'GetCapabilities.class.php';
	}
	
	/**
	 * *THE DescribeFeatureType REQUEST
	 */
	if (equalIgnoreCase($map_paras['request'],"DescribeFeatureType")) {
		include 'DescribeFeatureType.class.php';
	}
	
	/**
	 * *THE GetFeature REQUEST
	 */
	if (equalIgnoreCase($map_paras['request'],"GetFeature")) {
		require_once '../render/WKTParser.class.php';
		require_once '../render/GMLRender.class.php';
		include 'GetFeature.class.php';
	}
}

?>