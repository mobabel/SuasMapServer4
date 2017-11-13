<?php
include_once '../models/setting.inc';
include_once '../models/menu.inc';
include_once '../models/common.inc';
include_once '../models/perm.inc';
require_once '../models/import.inc';
require_once '../models/page.class.php';
require_once '../models/Cache.class.php';
include_once '../config.php';
include_once '../atlas/atlas.inc';

$page = "atlas";
//TODO set the expired time in system admin, now is one day
$cache = new Cache(28800, Cache::$TYPE_HTML, "");
$cache->cacheCheck();
	
switchDatabase($dbtype);
session_start();
if ( isset ($_SESSION['user']) ){
	$user = $_SESSION['user'];
	global $user;
}
$aid = $_GET['aid'];
$mapname = $_GET['mapname'];
$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

		//TODO if 4326, call google map
		$srsnameslist = $database->getAllSrssNames($aid);
		//only use the first srs
		$srsnames = $database->getColumns($srsnameslist); 
		$srsname = $srsnames["srs"];
		//get boundary box
		$rs7 = $database->getRowsMinMaxXYBySrs($aid, $srsname);
		$line7 = $database->getColumns($rs7);
		$totalminx = $line7[0];
		$totalminy = $line7[1];
		$totalmaxx = $line7[2];
		$totalmaxy = $line7[3];
		
		$layersnameslist = null;
		$layersnameslist = $database->getAllLayersNamesInSrs($aid, $srsname);
		$layerinsrscount = 0;
		while ($row = $database->getColumns($layersnameslist)) {
			if ($row["layer"] != "") {
				$layers .= $row["layer"].",";
			}
		}	
		if(strrpos($layers, ",") == strlen($layers)-1){
			$layers = substr($layers, 0, strlen($layers)-1);
		}					
?>
<html>
<head>
<script src="../plugin/OpenLayers/lib/OpenLayers.js"></script>
</head>
<body>
<div style="width:370px; height:240px; border:1px solid #c5dbec" id="map"></div>
<script defer="defer" type="text/javascript">
var map;
var popup;
var lon = (<?=$totalminx+$totalmaxx?>)/2;
var lat = (<?=$totalminy+$totalmaxy?>)/2;
var zoom = 3;
var options = {maxExtent: new OpenLayers.Bounds(<?=$totalminx.",".$totalminy.",".$totalmaxx.",".$totalmaxy?>), maxResolution: "auto", projection:"<?=$srsname?>" };
map = new OpenLayers.Map( ('map'), options );
var suaswms = new OpenLayers.Layer.WMS( "<?=$mapname?>",
"../files/atlas/<?=$aid?>/wms.php",
{layers: '<?=$layers?>', request: 'getmap', transparent: "false", format: "image/png"} );
map.addLayer(suaswms);
map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);
</script>
</body>
</html>
<?
$cache->caching();
$database->databaseClose();
ob_end_flush();
?>