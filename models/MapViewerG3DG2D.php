<?php
/*
 * Created on 29.06.2009
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
 * MapViewerG3DG2D
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
include_once '../render/WKTParser.class.php';

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
	elseif ($text_upper == "NAV_TYPE") {
		$nav_type = urldecode($b[1]);
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
	$nav_type = urldecode($_POST["NAV_TYPE"]);
}
if(empty($nav_type)){
	$nav_type = "car";
}

$bboxvalues = explode(",", $bbox);
$minx = $bboxvalues[0];
$miny = $bboxvalues[1];
$maxx = $bboxvalues[2];
$maxy = $bboxvalues[3];
$centerx = ($minx+ $maxx) /2;
$centery = ($miny+ $maxy) /2;

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

$serverwfs = get_wfs_interface($aid);

/*if ($atlas_cfg['enableCache']) {
 $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_HTML, $aid);
 $cache->cacheCheck();
 }*/
$maxfeatures  = 100;
$layersvalues = explode(",", $layers);
$numberofvalueslayer = count($layersvalues);
for ($i = 0; $i < $numberofvalueslayer; $i++) {
	$rs5 = $database->getRows4MetaGroupBy($aid, "srs");
	while ($line5 = $database->getColumns($rs5)) {
		$currentsrs = $line5["srs"];
		$rs6 = $database->getRowsMinMaxXYBySrs($aid, $currentsrs);
		$line6 = $database->getColumns($rs6);
		$totalminx = $line6[0];
		$totalminy = $line6[1];
		$totalmaxx = $line6[2];
		$totalmaxy = $line6[3];
		
		$rs1 = $database->getGeomAsTextBySrsLayer($aid, $currentsrs, $layersvalues[$i], "id");
		
		while ($line1 = $database->getColumns($rs1)) {
			if ($featurenumber < $maxfeatures ) {
				$data_id = $line1["id"];
				$data_Geom = $line1[8];
				$data_Geom_Type = strtoupper($line1["geomtype"]);
				$data_xmin = $line1["xmin"];
				$data_ymin = $line1["ymin"];
				$data_xmax = $line1["xmax"];
				$data_ymax = $line1["ymax"];
				$data_attributes = $line1["attributes"];
				
				
				switch ($data_Geom_Type) {
					case 'POINT': {
						$wktparser = new WKTParser();
						$wktparser->parse($data_Geom);
						$data_x = null;
						$data_y = null;
						switch ($wktparser->wktGeomType) {
							case "POINT": {
								$data_x = $wktparser->wktPointX;
								$data_y = $wktparser->wktPointY;
								$Number_Point = $wktparser->wktPointNr;
								//print($gmlrender->createPoints($data_x[0], $data_y[0], $Number_Point));
							}
							break;
							case "MULTIPOINT": {
								$data_x = $wktparser->wktPointX;
								$data_y = $wktparser->wktPointY;
								$Number_Point = $wktparser->wktPointNr;
								//print($gmlrender->createMultiPoint($data_x, $data_y, $Number_Point));
							}
						}
					}
					break;
					// ======================================================================================
					// ======================================================================================
					case 'TEXT': {
						$wktparser = new WKTParser();
						$wktparser->parse($data_Geom); //Point type, only one point!
						// readstyle now
						// $sld->poinStyle = "CIRCLE";
						$data_x = null;
						$data_y = null;
						
						$data_x = $wktparser->wktPointX;
						$data_y = $wktparser->wktPointY;
						$Number_Point = $wktparser->wktPointNr;
						
						//print($gmlrender->createPoints($data_x[0], $data_y[0], $Number_Point));
					}
					break;
					// ======================================================================================================
					// ======================================================================================================
					case 'LINESTRING': {
						$wktparser = new WKTParser();
						$wktparser->parse($data_Geom);
						// very important!!!!!!
						$data_x = null;
						$data_y = null;
						
						switch ($wktparser->wktGeomType) {
							case "LINESTRING": {
								$data_x = $wktparser->wktPointX;
								$data_y = $wktparser->wktPointY;
								$Number_Point = $wktparser->wktPointNr;
								$pathD = "";
								for($i = 0;$i < $Number_Point;$i++) {
									if($i != $Number_Point-1)
										$pathD .= "[".$data_x[$i] . "," . $data_y[$i] . "],";
									else
										$pathD .= "[".$data_x[$i] . "," . $data_y[$i] . "]";
								}
								$path_last .= "[".trim($pathD). "]\n,";
								
							}
							break;
							case "MULTILINESTRING": {
								$data_x = $wktparser->wktMPointX;
								$data_y = $wktparser->wktMPointY;
								$MNumber_Point = $wktparser->wktMPointNr;
								$MLine_Point = $wktparser->wktMLineNr;
								//print($gmlrender->createMultiLinstring($data_x, $data_y, $MLine_Point, $MNumber_Point));
							}
						}
					}
					break;
					// ======================================================================================================
					// ======================================================================================================
					case 'POLYGON': {
						$wktparser = new WKTParser();
						$wktparser->parse($data_Geom);
						// very important!!!!!!
						$data_x = null;
						$data_y = null;
						
						switch ($wktparser->wktGeomType) {
							case "POLYGON": {
								// $data_x = $wktparser->wktPointX;
								// $data_y = $wktparser->wktPointY;
								// $Number_Point = $wktparser->wktPointNr;
								$data_x = $wktparser->wktMPointX;
								$data_y = $wktparser->wktMPointY;
								$MNumber_Point = $wktparser->wktMPointNr;
								$MLine_Point = $wktparser->wktMLineNr;
								$pathD = "";
								for($j=0;$j<$MLine_Point;$j++){
									for($i = 0;$i < $MNumber_Point[$j];$i++) {
										if($i != $MNumber_Point[$j]-1)
											$pathD .= "[".$data_x[$j][$i] . "," . $data_y[$j][$i] . "],";
										else
											$pathD .= "[".$data_x[$j][$i] . "," . $data_y[$j][$i] . "]";
									}
									$path_last .= "[".trim($pathD). "]\n,";
								}
								
							}
							break;
							case "MULTIPOLYGON": {
								$data_x = $wktparser->wktMPointX;
								$data_y = $wktparser->wktMPointY;
								$MNumber_Point = $wktparser->wktMPointNr;
								$MLine_Point = $wktparser->wktMLineNr;
								//print($gmlrender->createMultiPolygon($data_x, $data_y, $MLine_Point, $MNumber_Point));
							}
						}
					}
					break;
					// ======================================================================================================
					// ======================================================================================================
				} //switchs
				$featurenumber++;
			}
		}
	}
}
//delete the last ',' , if last char is ','
if(strrpos ($path_last, ",") == strlen($path_last)-1 ){
	$path_last = substr($path_last, 0, strlen($path_last)-1);
}
if(empty($path_last))$path_last='[]';
$path_last = "[$path_last]";

?>
<html>
<head>
<!--script type="text/javascript" src="../cssjs/lib/jquery/js/jquery-1.3.2.min.js"></script-->
<!-- this gmaps key could be edited in atlas configuration-->
<script src="http://www.google.com/jsapi?hl=en&key=<?= $atlas_cfg['GoogleMapKey']?>"  type="text/javascript" ></script>
<script src="../plugin/g3dg2d/lib/greversegeocoderv107.js" type="text/javascript"></script>

<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<link href="../plugin/g3dg2d/index.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
// <![CDATA[
var baseUrl = "<?=get_base_server_host()?>";
var nav_type = "<?=$nav_type?>";
// ]]>
</script>
<script type="text/javascript" src="../plugin/g3dg2d/lib/geplugin-helpers.js"></script>
<script type="text/javascript" src="../plugin/g3dg2d/lib/math3d.js"></script>
<script type="text/javascript" src="../plugin/g3dg2d/simulator.js"></script>
<script type="text/javascript" src="../plugin/g3dg2d/index.js"></script>
<script type="text/javascript">
// <![CDATA[

var REMOTE_FILE = '<?
echo $serverwfs."?"."typename=".$layers."&maxfeatures=100&SERVICE=WFS&VERSION=1.1.1&REQUEST=GetFeature&OUTPUTFORMAT=text/xml";
?>';

var route_points_array = <?=$path_last?>;
var route_points = route_points_array[0];
if(route_points == ''){
	alert('Point is not supported or there are no geometries ');
}
var rp_length = route_points_array.length;

var DS_ge;
var DS_geHelpers;
var DS_map;
var reversegeocoder;
var currentLocAddress = "";

google.load("jquery", "1");
google.load("maps", "2.x");
google.load("earth", "1");

function DS_init() {
  $('#directions-form input').attr('disabled', 'disabled');
  $('#simulator-form input').attr('disabled', 'disabled');
  
  google.earth.createInstance(
    'earth',
    function(ge) {
      DS_ge = ge;
      DS_ge.getWindow().setVisibility(true);
      DS_ge.getLayerRoot().enableLayerById(DS_ge.LAYER_BUILDINGS, true);
      DS_ge.getLayerRoot().enableLayerById(DS_ge.LAYER_BORDERS, true);
      DS_geHelpers = new GEHelpers(DS_ge);
      
	//TODO load type from db
	
      DS_ge.getNavigationControl().setVisibility(ge.VISIBILITY_AUTO);
	//fly to start point	
	DS_flyToLatLng(new google.maps.LatLng(route_points[0][1],route_points[0][0]));
      
      DS_map = new GMap2($('#map-container').get(0));
      DS_map.setCenter(new GLatLng(<?=$centery.",".$centerx?>), 10);
      DS_map.addControl(new GSmallMapControl());
      DS_map.enableContinuousZoom();
      
      reversegeocoder = new GReverseGeocoder(DS_map);
	google.maps.Event.addListener(reversegeocoder,"load", geoCoderGood);
	google.maps.Event.addListener(reversegeocoder,"error", geoCoderBad);

   function geoCoderGood(placemark){
     	currentLocAddress =  placemark.address ;
   }

   function geoCoderBad(placemark){
     	currentLocAddress =  "No reverse geocoded address" ;
   }

	//reversegeocoder.reverseGeocode(new GLatLng(-122.083739, 37.423021));


      $('#go').removeAttr('disabled');
    },
    function() {
    });

  function onresize() {
    var clientHeight = document.documentElement.clientHeight;

    $('#route-details, #earth-container, #map-container').each(function() {
      $(this).css({
        height: (clientHeight - $(this).position().top - 100).toString() + 'px' });      
    });
  }
  
  $(window).resize(onresize);
  onresize();
}

google.setOnLoadCallback(DS_init);

// ]]>
</script>
</head>

<body onunload="GUnload();">
<table style="width: 1000px; height: 500px;">
<tr>
  <td style="width: 160px" valign="top">
    <table style="width: 200px">
      <tr>
        <td style="width: 50%" valign="top">
          <form id="directions-form" onsubmit="return false;" action="get">
            <fieldset>
              <input type="submit" onclick="DS_goDirections();" id="go" value="Load" disabled/>
            </fieldset>
          </form>
        </td>
        <td style="width: 50%" valign="top">
          <form id="simulator-form" onsubmit="return false;" action="get">
            <fieldset>
              <legend>Simulator</legend>
              <input type="button" onclick="DS_controlSimulator('reset');" value="Reset"/><br/>
              <input type="button" onclick="DS_controlSimulator('start');" value="Start"/><br/>
              <input type="button" onclick="DS_controlSimulator('pause');" value="Pause"/><br/>
              Speed: <strong><span id="speed-indicator">1x</span></strong><br/>

              <input type="button" onclick="DS_controlSimulator('slower');" value="-"/>
              <input type="button" onclick="DS_controlSimulator('faster');" value="+"/>
            </fieldset>
          </form>
        </td>
      </tr>
    </table>
    <div id="route-details"></div>
  </td>

  <td style="width: 50%" valign="top">
    <div id="earth-container" style="border: 1px solid #000; height: 500px;">
      <div id="earth" style="height: 100%;"></div>
    </div>
    <div id="status"></div>
  </td>
  <td style="width: 50%" valign="top">
    <div id="map-container" style="border: 1px solid #000; height: 500px;">
    </div>
  </td>
</tr>
</table>

</body>
</html>
<?
/*if ($atlas_cfg['enableCache']) {
 $cache->caching();
 }*/
$database->databaseClose();
?>