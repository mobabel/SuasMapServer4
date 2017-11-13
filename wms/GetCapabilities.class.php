<?php
/**
 * GetCapabilities Class
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
 * @copyright (C) 2006-2009  Hui LI
 * @Description: This show the copyright .
 * @version $1.0$ 2005
 * @Author  Filmon Mehari and Professor Dr. Franz Josef Behr
 * @version $2.0$ 2006.05
 * @Author  Chen Hang and Hui LI
 * @version $3.0$ 2006
 * @Author  leelight
 * @version $4.0$ 2009.04
 * @Author  Hui LI
 */

if($atlas_cfg['enableCache']){
	//$cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_XML, $aid);
	//$cache->cacheCheck();
}

$errornumber = 0;
$errorexceptionstring = "";

//TODO define the default WMS information
//$data = $database->db_get_atlas($aid, "");

if ($database->databaseGetErrorMessage() !=""){
    $errornumber = -1;
    $errorexceptionstring = $database->databaseGetErrorMessage();
    $sendexceptionclass->sendexception($errornumber,$errorexceptionstring);
}
if ($errornumber == 0){
	$wmsmetadata = $database->db_get_atlas($aid, $uid);
	
	
   header("Content-type: text/xml;charset=utf-8");
   print('<?xml version="1.0" encoding="UTF-8"?>');
?>
 <!DOCTYPE WMT_MS_Capabilities SYSTEM
 "<?=get_base_server_host()?>wms/capabilities_1_1_1.dtd"
 [
 <!ELEMENT VendorSpecificCapabilities EMPTY>
 ]
 >

<WMT_MS_Capabilities version="<?=SUAS_CFG_WMS_VERSION?>" updateSequence="<?=date("Y-m-d")?>">

<Service>
  <Name>OGC:WMS</Name>
  <!-- Human-readable title for pick lists-->
  <Title><?=$wmsmetadata['title']?></Title>
  <!-- Narrative description providing additional information-->
  <Abstract><?=$wmsmetadata['abstract']?></Abstract>
  <KeywordList>
    <Keyword>WMS <?=$version?></Keyword>
    <Keyword><?=$wmsmetadata['keyword1']?></Keyword>
    <Keyword><?=$wmsmetadata['keyword2']?></Keyword>
  </KeywordList>
  <!-- Top-level address of service or service provider-->
  <OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink" xlink:type="simple"
   xlink:href="<?=get_wms_interface($aid)?>" />
  <!-- Contact information -->
  <ContactInformation>
    <ContactPersonPrimary>
      <ContactPerson><?=$wmsmetadata['person']?></ContactPerson>
      <ContactOrganization><?=$wmsmetadata['organization']?></ContactOrganization>
    </ContactPersonPrimary>
    <ContactPosition><?=$wmsmetadata['person']?></ContactPosition>
    <ContactAddress>
      <AddressType><?=$wmsmetadata['addresstype']?></AddressType>
      <Address><?=$wmsmetadata['contactaddress']?></Address>
      <City><?=$wmsmetadata['city']?></City>
      <StateOrProvince><?=$wmsmetadata['stateorprovince']?></StateOrProvince>
      <PostCode><?=$wmsmetadata['postcode']?></PostCode>
      <Country><?global $cty;echo get_country_name($cty, $wmsmetadata['country']);?></Country>
    </ContactAddress>
    <ContactVoiceTelephone><?=$wmsmetadata["phone"]?></ContactVoiceTelephone>
    <ContactFacsimileTelephone><?=$wmsmetadata["phone"]?></ContactFacsimileTelephone>
    <ContactElectronicMailAddress><?=$wmsmetadata["mail"]?></ContactElectronicMailAddress>
  </ContactInformation>
  <!-- Fees or access constraints imposed. -->
  <Fees>none</Fees>
  <AccessConstraints>none</AccessConstraints>
</Service>
<Capability>
  <Request>
    <GetCapabilities>
      <Format>application/vnd.ogc.wms_xml</Format>
      <DCPType>
        <HTTP>
          <Get>
             <OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
             xlink:type="simple"
             xlink:href="<?=get_wms_interface($aid)?>" />
          </Get>
          <Post>
             <OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
             xlink:type="simple"
             xlink:href="<?=get_wms_interface($aid)?>" />
          </Post>
        </HTTP>
      </DCPType>
    </GetCapabilities>
    <GetMap>
<?
foreach($MAP_RENDER_FORMAT as $k => $v){
	echo "<Format>image/".$k."</Format>";
}
?>
        <DCPType>
        <HTTP>
          <Get>
             <OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
             xlink:type="simple"
             xlink:href="<?=get_wms_interface($aid)?>"/>
          </Get>
        </HTTP>
      </DCPType>
    </GetMap>
    <GetFeatureInfo>
      <Format>text/xml</Format>
        <DCPType>
        <HTTP>
          <Get>
             <OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
		     xlink:href="<?=get_wms_interface($aid)?>" xlink:type="simple" />
          </Get>
        </HTTP>
        </DCPType>
    </GetFeatureInfo>
    <DescribeLayer>
      <Format>text/xml</Format>
        <DCPType>
        <HTTP>
          <Get>
             <OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
		     xlink:href="<?=get_wms_interface($aid)?>" xlink:type="simple" />
          </Get>
        </HTTP>
        </DCPType>
    </DescribeLayer>
    <GetLegendGraphic>
      <Format>image/png</Format>
      <Format>image/jpeg</Format>
      <Format>image/svg+xml</Format>
        <DCPType>
        <HTTP>
          <Get>
            <OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
		    xlink:href="<?=get_wms_interface($aid)?>" xlink:type="simple" />
          </Get>
        </HTTP>
        </DCPType>
    </GetLegendGraphic>
  </Request>
  <Exception>
    <Format>application/vnd.ogc.se_xml</Format>
    <Format>application/vnd.ogc.se_inimage</Format>
  </Exception>
  <UserDefinedSymbolization SupportSLD="1" UserLayer="0" UserStyle="1" RemoteWFS="0" />
  <Layer queryable="0" opaque="0" noSubsets="0">
    <Title><?=$wmsmetadata["LayerTitle"]?></Title>
<?
   $rs0 = $database->getRows4MetaGroupBy($aid, "srs");
   $rs1 = $database->getRows4MetaGroupBy($aid, "srs");

   while ($line0 = $database->getColumns($rs0)){
       $currentsrs = $line0["srs"];
       echo "    <SRS>".$currentsrs."</SRS>\n";
   }

    $rsLatLonBoundingBox = $database->getRowsMinMaxXY($aid);
    $lineLatLonBoundingBox = $database->getColumns($rsLatLonBoundingBox);
    $totalminxLatLonBoundingBox = $lineLatLonBoundingBox[0];
    $totalminyLatLonBoundingBox = $lineLatLonBoundingBox[1];
    $totalmaxxLatLonBoundingBox = $lineLatLonBoundingBox[2];
    $totalmaxyLatLonBoundingBox = $lineLatLonBoundingBox[3];
    echo "    <LatLonBoundingBox minx=\"".$totalminxLatLonBoundingBox."\" miny=\"".$totalminyLatLonBoundingBox."\" maxx=\"".$totalmaxxLatLonBoundingBox."\" maxy=\"".$totalmaxyLatLonBoundingBox."\"/>\n";
    echo "    <!-- BoundingBox is inheritable, we define at root, inherited by all--> \n";

    while ($line1 = $database->getColumns($rs1)){
        $currentsrs = $line1["srs"];
        $rs2 = $database->getRowsMinMaxXYBySrs($aid, $currentsrs);
		$line2 = $database->getColumns($rs2);
        $totalminx = $line2[0];
        $totalminy = $line2[1];
        $totalmaxx = $line2[2];
        $totalmaxy = $line2[3];
        echo "    <BoundingBox SRS=\"".$currentsrs."\" minx=\"".$totalminx."\" miny=\"".$totalminy."\" maxx=\"".$totalmaxx."\" maxy=\"".$totalmaxy."\"/>\n";
	}
?>
    <!-- all layers are available in at least this CRS -->
<?
  $rs3 = $database->getRows4MetaGroupBy($aid, "layer");
  while ($line3 = $database->getColumns($rs3)){
      $currentlayername = $line3["layer"];
      $currentlayertitle = $line3["description"];
      $rs4 = $database->getRowsByLayerGroupBy($aid, $currentlayername,"layer,srs");
      $line4 = $database->getColumns($rs4);
      $queryable = $line4["queryable"];
  ?>
    <Layer queryable="<?=$queryable?>" cascaded="0" opaque="1" noSubsets="0" fixedWidth="0" fixedHeight="0" >
      <Name><?=$currentlayername?> </Name>
      <Title><?=$currentlayertitle?></Title>
      <!--<Abstract>note</Abstract>-->
  <?
  //while ($line4 = $database->getColumns($rs4)){
      $currentsrs = $line4["srs"];
      echo "    <SRS>".$currentsrs."</SRS>";
  //}
  $rs5 = $database->getRowsByLayerGroupBy($aid, $currentlayername,"layer,srs");
  while ($line5 = $database->getColumns($rs5)){
      $currentsrs = $line5["srs"];
      $currentstyle = $line5["style"];
      $rs6 = $database->getRowsMinMaxXYBySrsLayer($aid,$currentsrs,$currentlayername, false);
	  $line6 = $database->getColumns($rs6);
      $totalminx = $line6[0];
      $totalminy = $line6[1];
      $totalmaxx = $line6[2];
      $totalmaxy = $line6[3];
      echo "    <BoundingBox SRS=\"".$currentsrs."\" minx=\"".$totalminx."\" miny=\"".$totalminy."\" maxx=\"".$totalmaxx."\" maxy=\"".$totalmaxy."\"/>\n";
  }
 ?>
      <Style>
        <Name><?=$currentstyle?></Name>
        <Title><?=$currentstyle?></Title>
        <LegendURL width="20" height="15">
          <Format>image/png</Format>
          <OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
		  xlink:href="<?
		  //echo get_wms_path($aid)."sld/legends/".$currentlayername."_Default.png"
		  echo get_wms_interface($aid)."?VERSION=1.1.1&amp;SERVICE=WMS&amp;REQUEST=GetLegendGraphic&amp;LAYERS=".$currentlayername."&amp;FORMAT=image/png"
		  ?>" xlink:type="simple" />
        </LegendURL>
      </Style>
	</Layer>
 <?php
  } // end for while ($line1 = mysql_fetch_array($rs1))
 ?>
  </Layer>
</Capability>
</WMT_MS_Capabilities>
<?
}
$database->databaseClose();

if($atlas_cfg['enableCache']){
	//$cache->caching();
}
exit();
?>