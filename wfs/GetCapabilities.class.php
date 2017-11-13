<?php
/**
 * Getmapcap GetCapabilities Class
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
 * 
 */

if($atlas_cfg['enableCache']){
	$cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_XML, $aid);
	$cache->cacheCheck();
}

$errornumber = 0;
$errorexceptionstring = "";

if ($database->databaseGetErrorMessage() !=""){
	$errornumber = -1;
	$errorexceptionstring = $database->databaseGetErrorMessage();
}

if ($errornumber != 0){
	$sendexceptionclass->sendexception($errornumber,$errorexceptionstring);
}
else{
	header("Content-type: text/xml;charset=utf-8");
	print('<?xml version="1.0" encoding="UTF-8"?>');
	
	?>
<WFS_Capabilities
    updateSequence="0"
	version="<?=$wfsversion?>"
	xmlns:wfs="http://www.opengis.net/wfs"
	xmlns:ogc="http://www.opengis.net/ogc"
	xmlns="http://www.opengis.net/wfs"
	xmlns:gml="http://www.opengis.net/gml"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.opengis.net/wfs http://schemas.opengeospatial.net/wfs/1.0.0/WFS-capabilities.xsd">
	<Service>
		<Name><?=$softName?>WFS</Name>
		<Title><?=$softName?>WFS</Title>
		<OnlineResource>www.easywms.com</OnlineResource>
	</Service>
    <Capability>
		<Request>
			<GetCapabilities>
				<DCPType>
					<HTTP>
						<Get onlineResource="<?=get_wfs_interface($aid)?>"/>
						<Post onlineResource="<?=get_wfs_interface($aid)?>"/>
					</HTTP>
				</DCPType>
			</GetCapabilities>
			<DescribeFeatureType>
				<SchemaDescriptionLanguage>
					<XMLSCHEMA/>
				</SchemaDescriptionLanguage>
				<DCPType>
					<HTTP>
						<Get onlineResource="<?=get_wfs_interface($aid)?>"/>
						<Post onlineResource="<?=get_wfs_interface($aid)?>"/>
					</HTTP>
				</DCPType>
			</DescribeFeatureType>
			<GetFeature>
				<ResultFormat>
					<GML2/>
				</ResultFormat>
				<DCPType>
					<HTTP>
						<Get onlineResource="<?=get_wfs_interface($aid)?>"/>
						<Post onlineResource="<?=get_wfs_interface($aid)?>"/>
					</HTTP>
				</DCPType>
			</GetFeature>
			<Transaction>
				<DCPType>
					<HTTP>
						<Post onlineResource="<?=get_wfs_interface($aid)?>"/>
					</HTTP>
				</DCPType>
			</Transaction>
		</Request>
	</Capability>

    <FeatureTypeList>
        <Operations>
            <Query />
        </Operations>
<?
$rs3 = $database->getRows4MetaGroupBy($aid,"layer");
while ($line3 = $database->getColumns($rs3)){
	$currentlayername = $line3["layer"];
	$currentlayertitle = $line3["description"];
	?>
    <FeatureType>
      <Name><?=$currentlayername?> </Name>
      <Title><?=$currentlayertitle?></Title>
<?
$rs4 = $database->getRowsByLayerGroupBy($aid,$currentlayername,"layer,srs");
while ($line4 = $database->getColumns($rs4)){
	$currentsrs = $line4["srs"];
	echo "    <SRS>".$currentsrs."</SRS>";
}
$rs5 = $database->getRowsByLayerGroupBy($aid,$currentlayername,"layer,srs");
while ($line5 = $database->getColumns($rs5)){
	$currentsrs = $line5["srs"];
	$rs6 = $database->getRowsMinMaxXYBySrsLayer($aid,$currentsrs,$currentlayername, false);
	$line6 = $database->getColumns($rs6);
	$totalminx = $line6[0];
	$totalminy = $line6[1];
	$totalmaxx = $line6[2];
	$totalmaxy = $line6[3];
	echo "    <LatLongBoundingBox minx=\"".$totalminx."\" miny=\"".$totalminy."\" maxx=\"".$totalmaxx."\" maxy=\"".$totalmaxy."\"/>\n";
}
?>
      </FeatureType>
<?php
} 
?>
    </FeatureTypeList>
	<!-- ADDITIONAL CAPABILITIES -->
	<ogc:Filter_Capabilities>
		<ogc:Spatial_Capabilities>
			<ogc:Spatial_Operators>
				<ogc:BBOX/>
				<ogc:Intersect/>
			</ogc:Spatial_Operators>
		</ogc:Spatial_Capabilities>
		<ogc:Scalar_Capabilities>
			<ogc:Logical_Operators/>
			<ogc:Comparison_Operators>
				<ogc:Simple_Comparisons/>
				<ogc:Like/>
			</ogc:Comparison_Operators>
		</ogc:Scalar_Capabilities>
	</ogc:Filter_Capabilities>
</WFS_Capabilities>
<?
}

if($atlas_cfg['enableCache']){
	$cache->caching();
}
$database->databaseClose();
exit();
?>