<?php
/*
 * Created on 07.06.2009
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
 * create_overview.php
 * @version $Id$
 * @copyright (C) 2006-2009 LI Hui
 * @Description : 
 * @contact webmaster@easywms.com
 * 
 */
include_once '../models/common.inc';
include_once '../models/perm.inc';
include_once '../config.php';
switchDatabase($dbtype);
session_start();
$perm = false;

$aid = $_REQUEST['aid'];
$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();
$atlas_info = $database->db_get_atlas($aid);


$perm = perm_atlas_oper($atlas_info, $database);
$custom_param_maxgeonum = $_REQUEST['custom_param_maxgeonum'];
//TODO load this number from config
$custom_param_maxgeonum = 1000/*($custom_param_maxgeonum=='' || $custom_param_maxgeonum==null)?"1000":$custom_param_maxgeonum*/;

if($perm && atlas_create_overview($database, $aid)){
	echo "1";
}else{
	echo  "0";
}

$database->databaseClose();

function atlas_create_overview($database, $aid){
	$database->dbEmptyErrorMessage();
	$srsnameslist = $database->getAllSrssNames($aid);
	if (!$database->databaseGetErrorMessage()) {
		
		$srscount = 0;
		//only use the first srs
		while ($srsnames = $database->getColumns($srsnameslist)) {
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
			$srscount++;
			if($srscount>0){
				break;
			}
		}
		if($srscount == 0 || empty($layers)){
			return true;
		}
		if(strrpos($layers, ",") == strlen($layers)-1){
			$layers = substr($layers, 0, strlen($layers)-1);
		}
		$querystring = "VERSION=1.1.1&SERVICE=WMS" .
			"&BBOX=$totalminx%2C$totalminy%2C$totalmaxx%2C$totalmaxy" .
			"&LAYERS=$layers&STYLES=default&REQUEST=GetMap" .
			"&SRS=$srsname&WIDTH=256&HEIGHT=256&TRANSPARENT=True" .
			"&FORMAT=image/gif&EXCEPTIONS=application/vnd.ogc.se_inimage&aid=$aid&custom_param_maxgeonum=$custom_param_maxgeonum";		
		//$url = get_wms_interface($aid)."?".$querystring;
		$url = get_base_server_host()."wms/getmapcap.php?".$querystring;
		//echo $url;
		$data = file_get_contents($url);
		if($data){
			if(strpos($data, "GIF")>=0){
				if ($fp = fopen("../files/atlas/$aid/overview.gif", "w")) {
					@fwrite($fp, $data);
				}
				return true;
			}
		}
		/*
		$handle = @fopen($url, 'r');
		if($handle){
			while (!feof($handle)){
				$data .= fgets($handle, 1024);				
			} 
			//echo $data;
			//if is not ServiceExceptionReport
			//if(strpos($data, "IHDR")){ //for png
			if(strpos($data, "GIF")>=0){
				if ($fp = fopen("../files/atlas/$aid/overview.gif", "w")) {
					@fwrite($fp, $data);
				}
				fclose($handle); 
				return true;
			}
			
		}
		*/
	}
	return false;
}
?>
