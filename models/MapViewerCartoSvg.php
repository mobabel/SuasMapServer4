<?php
/*
 * Created on 2007
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
 * MapViewerCartoSVG
 * @version $Id$
 * @copyright (C) 2006-2009 LI Hui
 * @Description :
 * @contact webmaster@easywms.com
 *
 */
include_once '../config.php';
include_once '../models/common.inc';
require_once '../models/Cache.class.php';
require_once '../models/CommonFormula.class.php';
include_once '../parser/AttributeParser.class.php';
require_once '../atlas/atlas.inc';
require_once '../atlas/atlas_common.inc';

header("Content-Type: image/svg+xml;charset=utf-8");

switchDatabase($dbtype);

$database = new Database();
$database->databaseConfig($dbserver, $dbusername ,$dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

$assalowarray = array("%3a", "%2f", "%40", "%2b", "%28", "%29", "%3f", "%3d", "%26");
$assaupperarray = array("%3A", "%2F", "%40", "%2B", "%28", "%29", "%3F", "%3D", "%26");
$chararray = array(":", "/", "@", "+", "(", ")", "?", "=", "&");

$QUERY_STRING = $_SERVER ['QUERY_STRING'];
/*
 $QUERY_STRING = str_replace('%2A', ':', $QUERY_STRING);
 $QUERY_STRING = str_replace('%2C', ',', $QUERY_STRING);
 $QUERY_STRING = str_replace('%2F', '/', $QUERY_STRING);
 $QUERY_STRING = str_replace('%2B', '+', $QUERY_STRING);
 */
$QUERY_STRING = str_replace($assalowarray, $chararray, $QUERY_STRING);
$QUERY_STRING = str_replace($assaupperarray, $chararray, $QUERY_STRING);

$a = explode('&', $QUERY_STRING);
$i = 0;
while ($i < count($a)) {
	$b = split('=', $a[$i]);
	$text_upper = strtoupper($b[0]);
	
	if ($text_upper == "AID") {
		$aid = urldecode($b[1]);
	}
	elseif ($text_upper == "SERVICE") {
		$serservice = urldecode($b[1]);
	}
	elseif ($text_upper == "REQUEST") {
		$request = urldecode($b[1]);
	}
	elseif ($text_upper == "VERSION") { // the comma ',' in serversion is speicialchars, so must be preserved
		$serversion = $b[1];
	}
	elseif ($text_upper == "STYLES") {
		$style = urldecode($b[1]);
	}
	elseif ($text_upper == "WIDTH") {
		//$width = urldecode($b[1]);
		//fit for Carto SVG Viewer
		$width = 580;
	}
	elseif ($text_upper == "HEIGHT") {
		//$height = htmlspecialchars(urldecode($b[1]));
		//fit for Carto SVG Viewer
		$height = 700;
	}
	elseif ($text_upper == "FORMAT") { // the plus '+' in format is speicialchars, so must be preserved
		$format = strtolower($b[1]);
	}
	elseif ($text_upper == "SRS") {
		$srs = urldecode($b[1]);
	}
	elseif ($text_upper == "BBOX") {
		$bbox = urldecode($b[1]);
	}
	elseif ($text_upper == "LAYERS") {
		$layers = urldecode($b[1]);
	}
	elseif ($text_upper == "TRANSPARENT") {
		$transparent = strtolower(urldecode($b[1]));
	}
	elseif ($text_upper == "EXCEPTIONS") {
		$exceptions = urldecode($b[1]);
	}
	$i++;
}
//not data via Get
if($QUERY_STRING == ""){
	$_POST = array_change_key_case($_POST, CASE_UPPER);
	$aid = urldecode($_POST["AID"]);
	$serservice = urldecode($_POST["SERVICE"]);
	$request = urldecode($_POST["REQUEST"]);
	$serversion = $_POST["VERSION"];
	$style = urldecode($_POST["STYLES"]);
	//$width = urldecode($_POST["WIDTH"]);
	//fit for Carto SVG Viewer
	$width = 580;
	//$height = urldecode($_POST["HEIGHT"]);
	//fit for Carto SVG Viewer
	$height = 700;
	$format = strtolower($_POST["FORMAT"]);
	$srs = urldecode($_POST["SRS"]);
	$bbox = urldecode($_POST["BBOX"]);
	$layers = urldecode($_POST["LAYERS"]);
	$transparent = strtolower(urldecode($_POST["TRANSPARENT"]));
	$exceptions = urldecode($_POST["EXCEPTIONS"]);
}


$cfg_data = $database->db_get_atlas_cfg("", $aid);
if($cfg_data){
	$atlas_cfg = AttributeParser::extractAttribute($cfg_data['variable']);
	$atlas_cfg['enableCache'] = $atlas_cfg['cacheExpiredTime']=="0"?0:1;
}else{
	$atlas_cfg = atlas_get_default_cfg();
}

if ($exceptions == "")
	$exceptions = "application/vnd.ogc.se_inimage";
if ($transparent == "")
	$transparent = "true";

$bboxvalues = explode(",", $bbox);
$minx = $bboxvalues[0];
$miny = $bboxvalues[1];
$maxx = $bboxvalues[2];
$maxy = $bboxvalues[3];

$newwidthhieght = getStretchWidthHeight($minx, $miny, $maxx, $maxy, $width, $height, $enablestretchmap);
$width = $newwidthhieght[0];
$height = $newwidthhieght[1];

$viewbox = $minx." ".(-$maxy)." ".($maxx-$minx)." ".($maxy-$miny);

$serverget = get_wms_interface($aid). "?VERSION=$serversion&SERVICE=$serservice&REQUEST=".$request;

if ($atlas_cfg['enableCache']) {
	$cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_SVG, $aid);
	$cache->cacheCheck();
}

$tmparray = array("parent.parent.parasBbox","parent.parent.parasViewBox", "parent.parent.parasLayers", "parent.parent.parasEpsg",
	"parent.parent.parasHeight", "parent.parent.parasWidth",
	"parent.parent.parasFormat", "parent.parent.urlGetmap","js/");
$paramsarray = array("\"".$bbox."\"","\"".$viewbox."\"", "\"".$layers."\"", "\"".$srs."\"", $height, $width, "\"".$format."\"", "\"".$serverget."\"","../Plugin/suasclient/js/");

$fileName = "../plugin/suasclient/navigation.svg";

if (file_exists($fileName)) {
	$count = 0;
	$fp = fopen($fileName, "r");
	while (!feof($fp)) {
		$line .= fgets($fp, 1024);
		$count++;
	}
	//the line 15-30 maybe changed!
	//if($count>15 && $count<30){
	$line = str_replace($tmparray, $paramsarray, $line);
	//}
	echo $line;
	
	fclose($fp);
} else {
	echo "Template " . $fileName . " does not exist, please check it.";
}


if ($atlas_cfg['enableCache']) {
	$cache->caching();
}
$database->databaseClose();
?>