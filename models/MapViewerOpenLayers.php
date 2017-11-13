<?php
/**
 * MapViewerOpenLayers
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
 * @version $4.0$ 2009.05
 * @Author LI Hui
 */
include_once '../config.php';
include_once '../models/common.inc';
require_once '../models/Cache.class.php';
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
	// echo "Value for parameter " .$b[0]."is " .$b[1]. "\n";
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
	// =============OpenLayers Parameters=============================
	elseif ($text_upper == "OPTEMPLATE") {
		$optemplate = urldecode($b[1]);
	}
	elseif ($text_upper == "OPZOOMLEVEL") {
		$opzoomlevel = urldecode($b[1]);
		if($opzoomlevel=="") {
			$opzoomlevel = 1;
		}
	}
	elseif ($text_upper == "OPLAYERSWITCHER") {
		$oplayerswitcher = urldecode($b[1]);
	}
	elseif ($text_upper == "OPMOUSEDEFAULTS") {
		$opmousedefaults = urldecode($b[1]);
	}
	elseif ($text_upper == "OPMOUSEPOSITION") {
		$opmouseposition = urldecode($b[1]);
	}
	elseif ($text_upper == "OPMOUSETOOLBAR") {
		$opmousetoolbar = urldecode($b[1]);
	}
	elseif ($text_upper == "OPOVERVIEWMAP") {
		$opoverviewmap = urldecode($b[1]);
	}
	elseif ($text_upper == "OPPANZOOM") {
		$oppanzoom = urldecode($b[1]);
	}
	elseif ($text_upper == "OPPANZOOMBAR") {
		$oppanzoombar = urldecode($b[1]);
	}
	elseif ($text_upper == "OPPERMALINK") {
		$oppermalink = urldecode($b[1]);
	}
	elseif ($text_upper == "OPSCALE") {
		$opscale = urldecode($b[1]);
	}
	elseif ($text_upper == "OPDRAWFEATURE") {
		$opdrawfeature = urldecode($b[1]);
	}
	elseif ($text_upper == "OPGETFEATUREINFO") {
		$opgetfeatureinfo = urldecode($b[1]);
	}
	elseif ($text_upper == "OPOPACITYCONTROL") {
		$opopacitycontrol = urldecode($b[1]);
	}
	elseif ($text_upper == "OPWFS") {
		$opwfs = urldecode($b[1]);
	}
	// =====================Map Servers==================
	elseif ($text_upper == "OPSUASLAYERASOVERLAY") {
		$opsuaslayerasoverlay = urldecode($b[1]);
	}
	elseif ($text_upper == "OPSUASONELAYERONELAYER"){
		$opsuasonelayeronelayer = urldecode($b[1]);
	}
	elseif ($text_upper == "OPOPENLAYERSWMS") {
		$opopenlayerswms = urldecode($b[1]);
	}
	elseif ($text_upper == "OPOPENPLANSWMS") {
		$opopenplanswms = urldecode($b[1]);
	}
	elseif ($text_upper == "OPMULTIMAP") {
		$opmultimap = urldecode($b[1]);
	}
	elseif ($text_upper == "OPNASAWORLDWIND") {
		$opnasaworldwind = urldecode($b[1]);
	}
	elseif ($text_upper == "OPGOOGLEMAP") {
		$opgooglemap = urldecode($b[1]);
	}
	elseif ($text_upper == "OPGOOGLESATELLITE") {
		$opgooglesatellite = urldecode($b[1]);
	}
	elseif ($text_upper == "OPGOOGLEHYBRID") {
		$opgooglehybrid = urldecode($b[1]);
	}
	elseif ($text_upper == "OPVIRTUALEARTH") {
		$opvirtualearth = urldecode($b[1]);
	}
	elseif ($text_upper == "OPYAHOOMAP") {
		$opyahoomap = urldecode($b[1]);
	}
	elseif ($text_upper == "OPOSMMAPMAPNIK"){
		$oposmmapmapnik = urldecode($b[1]);
	}
	elseif ($text_upper == "OPOSMMAPOSMARENDER"){
		$oposmmaposmarender = urldecode($b[1]);
	}
	elseif ($text_upper == "OPOSMMAPCYCLEMAP"){
		$oposmmapcyclemap = urldecode($b[1]);
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
	// =============OpenLayers Parameters=============================
	$optemplate = urldecode($_POST["OPTEMPLATE"]);
	$opzoomlevel = urldecode($_POST["OPZOOMLEVEL"]);
	if($opzoomlevel=="") {
		$opzoomlevel = 1;
	}
	$oplayerswitcher = urldecode($_POST["OPLAYERSWITCHER"]);
	$opmousedefaults = urldecode($_POST["OPMOUSEDEFAULTS"]);
	$opmouseposition = urldecode($_POST["OPMOUSEPOSITION"]);
	$opmousetoolbar = urldecode($_POST["OPMOUSETOOLBAR"]);
	$opoverviewmap = urldecode($_POST["OPOVERVIEWMAP"]);
	$oppanzoom = urldecode($_POST["OPPANZOOM"]);
	$oppanzoombar = urldecode($_POST["OPPANZOOMBAR"]);
	$oppermalink = urldecode($_POST["OPPERMALINK"]);
	$opscale = urldecode($_POST["OPSCALE"]);
	$opdrawfeature = urldecode($_POST["OPDRAWFEATURE"]);
	$opgetfeatureinfo = urldecode($_POST["OPGETFEATUREINFO"]);
	$opopacitycontrol = urldecode($_POST["OPOPACITYCONTROL"]);
	$opwfs = urldecode($_POST["OPWFS"]);
	// =====================Map Servers==================
	$opsuaslayerasoverlay = urldecode($_POST["OPSUASLAYERASOVERLAY"]);
	$opsuasonelayeronelayer = urldecode($_POST["OPSUASONELAYERONELAYER"]);
	$opopenlayerswms = urldecode($_POST["OPOPENLAYERSWMS"]);
	$opopenplanswms = urldecode($_POST["OPOPENPLANSWMS"]);
	$opmultimap = urldecode($_POST["OPMULTIMAP"]);
	$opnasaworldwind = urldecode($_POST["OPNASAWORLDWIND"]);
	$opgooglemap = urldecode($_POST["OPGOOGLEMAP"]);
	$opgooglesatellite = urldecode($_POST["OPGOOGLESATELLITE"]);
	$opgooglehybrid = urldecode($_POST["OPGOOGLEHYBRID"]);
	$opvirtualearth = urldecode($_POST["OPVIRTUALEARTH"]);
	$opyahoomap = urldecode($_POST["OPYAHOOMAP"]);
	$oposmmapmapnik = urldecode($_POST["OPOSMMAPMAPNIK"]);
	$oposmmaposmarender = urldecode($_POST["OPOSMMAPOSMARENDER"]);
	$oposmmapcyclemap = urldecode($_POST["OPOSMMAPCYCLEMAP"]);
}

if ($exceptions == "")
	$exceptions = "application/vnd.ogc.se_xml";
if ($transparent == "")
	$transparent = "False";
if ($optemplate == "")
	$optemplate = 0;

$bboxvalues = explode(",", $bbox);
$minx = $bboxvalues[0];
$miny = $bboxvalues[1];
$maxx = $bboxvalues[2];
$maxy = $bboxvalues[3];

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
$serverwms = get_wms_interface($aid);
$serverwfs = get_wfs_interface($aid);

/*if ($atlas_cfg['enableCache']) {
	$cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_HTML, $aid);
	$cache->cacheCheck();
}*/

$tmparray = array("##BBOX##", "##LAYERS##", "##SRS##", "##MINX##", "##MINY##", "##MAXX##", "##MAXY##", "##HEIGHT##", "##WIDTH##", "##SERVERGET##", "##REQUEST##");
$paramsarray = array($bbox, $layers, $srs, $minx, $miny, $maxx, $maxy, $height, $width, $serverwms, strtolower($request));

// use Template
if ($optemplate != 0) {
	$fileName = "OpenLayersTemplate/OpenlayersViewer" . $optemplate . ".tmpl";

	if (file_exists($fileName)) {
		$fp = fopen($fileName, "r");
		while (!feof($fp)) {
			$line .= fgets($fp, 1024);
		}
		$line = str_replace($tmparray, $paramsarray, $line);
		echo $line;

		fclose($fp);
	} else {
		echo "Template " . $fileName . " does not exist, please check it.";
	}
}

// use openlayers parameters
if ($optemplate == 0) {
	print '<html>' . "\n";
	print '<head>' . "\n";
	if ($opgooglemap OR $opgooglesatellite OR $opgooglehybrid) {
		print '    <!-- this gmaps key could be edited in atlas configuration-->' . "\n";
		print '    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $atlas_cfg['GoogleMapKey'] . '"></script>' . "\n";
	}
	print '    <!-- You can set the openlayers.js path as yours-->' . "\n";
	//TODO, why they support osm layers
	if($oposmmapmapnik || $oposmmaposmarender || $oposmmapcyclemap)
		print '    <script src="http://data.giub.uni-bonn.de/openrouteservice/lib/OpenLayers.js"></script>' . "\n";
	else
		print '    <script src="../plugin/OpenLayers/lib/OpenLayers.js"></script>' . "\n";
	
	print '<link rel="stylesheet" href="../plugin/OpenLayers/theme/default/style.css" type="text/css" />';
	if ($opmultimap) {
		print '    <script type="text/javascript" src="http://clients.multimap.com/API/maps/1.1/metacarta_04"></script>' . "\n";
	}
	if ($opyahoomap) {
		print '    <script src="http://api.maps.yahoo.com/ajaxymap?v=3.0&appid=euzuro-openlayers"></script>' . "\n";
	}
	if ($opvirtualearth) {
		print '    <script src="http:// dev.virtualearth.net/mapcontrol/v3/mapcontrol.js"></script>' . "\n";
	}
	print '</head>' . "\n";
	print '<body>' . "\n";

	if ($oppermalink) {
		print ' <a href="" id="permalink">Permalink</a>' . "\n";
	}
	if	($opopacitycontrol){
		print '<script type="text/javascript">
        var maxOpacity = 0.9;
        var minOpacity = 0.1;
        function changeOpacity(byOpacity) {
            var newOpacity = (parseFloat(OpenLayers.Util.getElement(\'opacity\').value) + byOpacity).toFixed(1);
            newOpacity = Math.min(maxOpacity,
                                  Math.max(minOpacity, newOpacity));
            OpenLayers.Util.getElement(\'opacity\').value = newOpacity;';
        if(!$opsuasonelayeronelayer){
        	print 'suaswms.setOpacity(newOpacity);';
        }
        else{
        	$layers_array = explode(",", $layers);
			for($i=0,$j=count($layers_array);$i<$j;$i++){
				print 'suaswms'.$i.'.setOpacity(newOpacity);';
			}
        }

        print '}
    	</script>
		';
		print '<div style="float:top;width:28%">' .
				'<p>Layer Opacity:
            <a title="decrease opacity" href="javascript: changeOpacity(-0.1);">&lt;&lt;</a>
            <input id="opacity" type="text" value="0.5" size="3" disabled="true" />
            <a title="increase opacity" href="javascript: changeOpacity(0.1);">&gt;&gt;</a>

        	</p>' .
        	'</div>';
	}
	if($opgetfeatureinfo){
		print ' <div style="float:right;width:28%;">
		<p style="font-size:.8em;">Click to get feature information.</p>
		<div id="nodeList">
		</div>
		</div>' . "\n";
	}
	print ' <div style="width:' . $width . '; height:' . $height . '" id="map"></div>' . "\n";
	print ' <script defer="defer" type="text/javascript">' . "\n";

	print 'var map;' . "\n";
	print 'var popup;'."\n";
	print 'var lon = (' . $minx . '+' . $maxx . ')/2;' . "\n";
	print 'var lat = (' . $miny . '+' . $maxy . ')/2;' . "\n";
	print 'var zoom = '.$opzoomlevel.';' . "\n";
	/*print 'var options = {projection: "EPSG:900913", units: "m", ' .
			'maxResolution: "auto", ' .
			'maxExtent: new OpenLayers.Bounds(' . $minx . ',' . $miny . ',' . $maxx . ',' . $maxy . ')
			};';
			*/
			
	/*print 'var options = {
		projection: new OpenLayers.Projection("EPSG:900913"),
		displayProjection: new OpenLayers.Projection("EPSG:4326"),
		units: "m",
		maxResolution: 156543.0339,
		maxExtent: new OpenLayers.Bounds(' . $minx . ',' . $miny . ',' . $maxx . ',' . $maxy . ')
		};
		';
	*/	 
	//+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0+k=1.0 +units=m +nadgrids=@null +no_defs

	print 'var options = {maxExtent: new OpenLayers.Bounds(' . $minx . ',' . $miny . ',' . $maxx . ',' . $maxy . '), ' .
			'maxResolution: "auto", ' .
			'projection:"' . $srs . '" };' . "\n";
	
			
	print 'map = new OpenLayers.Map( (\'map\'), options );' . "\n";

	if ($oppanzoombar)
		print 'map.addControl(new OpenLayers.Control.PanZoomBar());' . "\n";

	if ($opmousedefaults)
		print 'map.addControl(new OpenLayers.Control.MouseDefaults());' . "\n";

	if ($opmousetoolbar)
		print 'map.addControl(new OpenLayers.Control.MouseToolbar());' . "\n";

	if ($oplayerswitcher)
		print 'map.addControl(new OpenLayers.Control.LayerSwitcher());' . "\n";

	if ($opmouseposition)
		print 'map.addControl(new OpenLayers.Control.MousePosition());' . "\n";

	if ($opoverviewmap)
		print 'map.addControl(new OpenLayers.Control.OverviewMap());' . "\n";

	if ($oppermalink)
		print 'function updateLink() {
		pl = document.getElementById("permalink");
		center = this.getCenter();
		zoom = this.getZoom();
		lat = Math.round(center.lat*1000)/1000;
		lon = Math.round(center.lon*1000)/1000;
		pl.href = "?lat="+lat+"&lon="+lon+"&zoom="+zoom;
		}' . "\n";

	if ($opscale)
		print 'map.addControl(new OpenLayers.Control.Scale());' . "\n";

	/*
	 if ($opdrawfeature){
	 print 'var drawControls = {
	 point: new OpenLayers.Control.DrawFeature(pointLayer,
	 OpenLayers.Handler.Point),
	 line: new OpenLayers.Control.DrawFeature(lineLayer,
	 OpenLayers.Handler.Path, options),
	 polygon: new OpenLayers.Control.DrawFeature(polygonLayer,
	 OpenLayers.Handler.Polygon, options)
	 };' . "\n";
	 print 'map.addControl(drawControls);'. "\n";

	 print 'var vectorlayer = new OpenLayers.Layer.Vector( "Editable" );
	 map.addLayer(vectorlayer);
	 map.addControl(new OpenLayers.Control.EditingToolbar(vectorlayer));'. "\n";
	 }
	 */
	// var ls = new OpenLayers.Control.LayerSwitcher();
	// map.addControl(ls);
	// ls.maximizeControl();

	//if there is no others map server, dont use $opsuaslayerasoverlay
	if( !$opopenlayerswms
		&& !$opopenplanswms
		&& !$opopenplanswms
		&& !$opmultimap
		&& !$opnasaworldwind
		&& !$opgooglemap
		&& !$opgooglesatellite
		&& !$opgooglehybrid
		&& !$opvirtualearth
		&& !$opyahoomap 
		&& !$oposmmapmapnik 
		&& !$oposmmaposmarender 
		&& !$oposmmapcyclemap
		){
		$opsuaslayerasoverlay = false;
		$transparent = "false";
	}

	$wmscount = 0;
	if(!$opsuasonelayeronelayer){
		print 'var suaswms = new OpenLayers.Layer.WMS( "' . $atlas['name'] . '",' . "\n";
		print '"' . $serverwms . '",' . "\n";
		print '{layers: \'' . $layers . '\', request: \'getmap\', transparent: "';
		if($opsuaslayerasoverlay){
			print "true" ;
		}else{
			print $transparent ;
		}
		print '", format: "' . $format . '"} );' . "\n";
		print 'map.addLayer(suaswms);' . "\n";
		//print 'suaswms.setVisibility(false);' . "\n";
	}
	//each layer as one separate overlay
	else{
		$layers_array = explode(",", $layers);
		for($i=0,$j=count($layers_array);$i<$j;$i++){
			print 'var suaswms'.$i.' = new OpenLayers.Layer.WMS( "' . $layers_array[$i] . '",' . "\n";
			print '"' . $serverwms . '",' . "\n";
			print '{layers: \'' . $layers_array[$i] . '\', request: \'getmap\', transparent: "';
			if($opsuaslayerasoverlay){
				print "true" ;
			}else{
				print $transparent ;
			}
			print '", format: "' . $format . '"} );' . "\n";
			print 'map.addLayer(suaswms'.$i.');' . "\n";
		}
	}

	//http://openlayers.org/dev/examples/wfs-t.html
	if($opwfs){
		print '
		var suaswfslayer = new OpenLayers.Layer.WFS( "WFS Overlay",
                "' . $serverwfs . '?",
                {typename: "' . $layers . '", maxfeatures: 100},
                { featureClass: OpenLayers.Feature.WFS});

		';
		print 'map.addLayer(suaswfslayer);' . "\n"; 
		
/*		print 'var suaswfs = new OpenLayers.Layer.WFS( "' . SUAS_NAME . '",' . "\n";
		print '"' . $serverwfs . '",' . "\n";
		print '{typename: \'topp:' . $layers . '\' ,';
		print ' {
                    typename: "' . $layers . '",
                    featureNS: "' . $serverwfs . '",
                    extractAttributes: false,
                    commitReport: function(str) {
                        OpenLayers.Console.log(str);
                    }
                }});' . "\n";
        print 'map.addLayer(suaswfs);' . "\n"; */
              

	}

	if ($opopenlayerswms) {
		print 'var opwms' . $wmscount . ' = new OpenLayers.Layer.WMS( "OpenLayers WMS", "http://labs.metacarta.com/wms/vmap0", {layers: \'basic\'} );' . "\n";
		$wmscount++;
	}
	if ($opopenplanswms) {
		print 'var opwms' . $wmscount . ' = new OpenLayers.Layer.WMS( "OpenPlans WMS",
		"http://sigma.openplans.org:3128/geoserver/wms",
		{layers: "topp:poly_landmarks,topp:water_polygon,topp:water_shorelines,topp:roads,topp:major_roads,topp:states,topp:countries,topp:gnis_pop",
		transparent: "false", format: "image/png", styles: "freemap_open_space,freemap_water,water_line,freemap_roads,freemap_major_roads,states_ol_sat,world_countries,gnis_pop_ol"});' . "\n";
		$wmscount++;
	}
	if ($opmultimap) {
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.MultiMap( "MultiMap", {minZoomLevel: 1});' . "\n";
		$wmscount++;
	}
	if ($opnasaworldwind) {
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.KaMap( "World Wind (NASA)","/world/index.php", {g: "satellite", map: "world"});' . "\n";
		$wmscount++;
	}
	if ($opgooglemap) {
		//new OpenLayers.Layer.Google("Google", {"sphericalMercator": true});
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.Google("Google Map");' . "\n";
		$wmscount++;
	}
	if ($opgooglesatellite) {
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.Google("Google Satellite", { \'type\': G_SATELLITE_MAP });' . "\n";
		$wmscount++;
	}
	if ($opgooglehybrid) {
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.Google("Google Hybrid", { \'type\': G_HYBRID_MAP });' . "\n";
		$wmscount++;
	}
	if ($opvirtualearth) {
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.VirtualEarth("VirtualEarth", {\'minZoomLevel\': 0});' . "\n";
		$wmscount++;
	}
	if ($opyahoomap) {
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.Yahoo("Yahoo");' . "\n";
		$wmscount++;
	}
	if ($oposmmapmapnik) {
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.OSM.Mapnik("OpenStreetMap Mapnik");' . "\n";
		$wmscount++;
	}
	if ($oposmmaposmarender) {
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.OSM.Osmarender("OpenStreetMap Osmarender");' . "\n";
		$wmscount++;
	}
	if ($oposmmapcyclemap) {
		print 'var opwms' . $wmscount . '= new OpenLayers.Layer.OSM.CycleMap("OpenStreetMap CycleMap");' . "\n";
		$wmscount++;
	}
	//map24 map
	//http://trac.openlayers.org/wiki/Layer/Map24


	if($wmscount!=0){
		$alllayers = "";
		for($i=0;$i<$wmscount;$i++){
			if($i!=$wmscount-1)
				$alllayers .= "opwms".$i.",";
			else
				$alllayers .= "opwms".$i;
		}
		print 'map.addLayers(['.$alllayers.']);' . "\n";
	}
	//http://openlayers.org/dev/examples/osm-layer.html
	//@TODO how to load gml dynamically and depends on zoom level
	//map = new OpenLayers.Map('map', {'maxResolution': 360/512/16, 'numZoomLevels':15});	
	/*print 'var layer = new OpenLayers.Layer.WMS( "OSM", 
               [
                 "http://t1.hypercube.telascience.org/tiles?",
                 "http://t2.hypercube.telascience.org/tiles?",
                 "http://t3.hypercube.telascience.org/tiles?",
                 "http://t4.hypercube.telascience.org/tiles?"
                 ], 
                {layers: \'osm-4326\', format: \'image/png\' } );
            map.addLayer(layer);
	';
	*/

	print 'map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);' . "\n";

	if($opgetfeatureinfo){
?>		
		var mouseLoc;
		map.events.register('click', map, function (e) {
			OpenLayers.Util.getElement('nodeList').innerHTML = "Loading... please wait...";
			
			mouseLoc = suaswms.map.getLonLatFromPixel(e.xy);
			
			var url =  suaswms.getFullRequestString({
			REQUEST: "GetFeatureInfo",
			EXCEPTIONS: "application/vnd.ogc.se_xml",
			BBOX: suaswms.map.getExtent().toBBOX(),
			X: e.xy.x,
			Y: e.xy.y,
			INFO_FORMAT: 'text/html',
			QUERY_LAYERS: suaswms.params.LAYERS,
			REDIUS:1,
			WIDTH: suaswms.map.size.w,
			HEIGHT: suaswms.map.size.h}
			);
			//alert(url);
			OpenLayers.loadURL(url, '', this, setHTML);
			OpenLayers.Event.stop(e);
		});
		
		function setHTML(response) {
			OpenLayers.Util.getElement('nodeList').innerHTML = response.responseText;
			
			var popup_info = response.responseText;
			if (popup != null) {
	            popup.destroy();
	            popup = null;
        	}
        	popup = new OpenLayers.Popup.AnchoredBubble("SDVegetationInfo",
                                        mouseLoc,
                                        new OpenLayers.Size(250,120),
                                        popup_info,
                                        null,
                                        true);
        	popup.setBackgroundColor("#bcd2ee");
			//popup.setOpacity(.95);
        	map.addPopup(popup);
        	popup.events.register("click", map, popupDestroy);
	
		}
		
		/*
		 * Destroy popup and stop event
		 */
		function popupDestroy(e) {
		    popup.destroy();
		    popup = null;
		    OpenLayers.Util.safeStopPropagation(e);
		}
		
<?
	}

	print ' </script>' . "\n";
	print ' </body>' . "\n";
	print ' </html>' . "\n";
}

/*if ($atlas_cfg['enableCache']) {
	$cache->caching();
}*/
$database->databaseClose();
?>