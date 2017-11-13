<?php
/*
 * Created on 21.06.2009
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
 * MapViewerGmapsWMS.php
 * @version $Id$
 * @copyright (C) 2006-2009 HUI LI
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

header("Content-type: text/html;charset=utf-8");
switchDatabase($dbtype);
$database = new Database();
$database->databaseConfig($dbserver, $dbusername ,$dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

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
		$width = urldecode($b[1]);
	}
	elseif ($text_upper == "HEIGHT") {
		$height = urldecode($b[1]);
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
	$width = urldecode($_POST["WIDTH"]);
	$height = urldecode($_POST["HEIGHT"]);
	$format = strtolower($_POST["FORMAT"]);
	$srs = urldecode($_POST["SRS"]);
	$bbox = urldecode($_POST["BBOX"]);
	$layers = urldecode($_POST["LAYERS"]);
	$transparent = strtolower(urldecode($_POST["TRANSPARENT"]));
	$exceptions = urldecode($_POST["EXCEPTIONS"]);
}
$bboxvalues = explode(",", $bbox);
$minx = $bboxvalues[0];
$miny = $bboxvalues[1];
$maxx = $bboxvalues[2];
$maxy = $bboxvalues[3];
$centerx = ($minx+ $maxx) /2;
$centery = ($miny+ $maxy) /2;

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

$serverwfs = get_wms_interface($aid);

/*if ($atlas_cfg['enableCache']) {
 $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_HTML, $aid);
 $cache->cacheCheck();
 }*/

?>
<html>
<head>
<!-- this gmaps key could be edited in atlas configuration-->
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?= $atlas_cfg['GoogleMapKey']?>"  type="text/javascript"></script>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<script src="../plugin/gmapswms/wms236.js" type="text/javascript"></script>
</head>
<body onunload="GUnload()">
<table class="tableNone">
<tr>
<td style="width: <?=$width?>px; height: <?=$height?>px; ">
	<div id="map" style="position:relative; width: <?=$width?>px; height: <?=$height?>px; "></div>
</td>
<td style="align:left;widht:200px">
</td>
</tr>
<table>

<script type="text/javascript">
 	
	var REMOTE_FILE = '<?
echo $serverwfs."?a=1";
?>';
	
	if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Opera') != -1) {
	_mSvgForced = true;
	_mSvgEnabled = true; 
 	}
    var map=new GMap2(document.getElementById("map"),{draggableCursor: 'crosshair', draggingCursor: 'move'});   
    
	var suas= new GTileLayer(new GCopyrightCollection("<?=$atlas['name']?>"),1,17);
	suas.myLayers='<?=$layers?>';
	suas.myFormat='<?=$format?>';
	suas.myStyles='<?=$style?>';
	suas.myBaseURL= REMOTE_FILE;
 	suas.getTileUrl= CustomGetTileUrl;
	suas.getOpacity = function() {return 0.9;}


	var layer = [G_NORMAL_MAP.getTileLayers()[0], suas]; 
	var custommap = new GMapType(layer, G_SATELLITE_MAP.getProjection(), "<?=$atlas['name']?>", G_SATELLITE_MAP);

    map.addMapType(custommap);
	
	map.addControl(new GLargeMapControl());
    map.addControl(new GMapTypeControl());
    map.setCenter(new GLatLng(<?=$centery.",".$centerx?>), 7);
    map.enableScrollWheelZoom();
    map.enableDoubleClickZoom();
    map.enableContinuousZoom();    

    function wheelblock(e) {
		if (!e){ e = window.event }
		if (e.preventDefault){ e.preventDefault() }
		e.returnValue = false;
		}
	GEvent.addDomListener(map.getContainer(), "DOMMouseScroll", wheelblock);
	map.getContainer().onmousewheel = wheelblock; 
	
</script>

</body>
</html>
<?
/*if ($atlas_cfg['enableCache']) {
 $cache->caching();
 }*/
$database->databaseClose();
?>
