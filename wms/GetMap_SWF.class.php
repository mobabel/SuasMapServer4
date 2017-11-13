<?php
/**
 * Getmapcap GetMap_SWF Class
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
 * @Description : This show the copyright .
 * @version $3.0$ 2006
 * @Author Hui LI
 * @version $4.0$ 2009.04
 * @Author Hui LI
 */
setConnectionTime(SITE_MAX_TIMEOUT_LIMIT);

if ($atlas_cfg['enableCache']) {
    $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_SWF, $aid);
    $cache->cacheCheck();
}

$swfrender = new SWFRender();

if ($database->databaseGetErrorMessage() != "") {
    $errornumber = -1;
    $errorexceptionstring = $database->databaseGetErrorMessage();
    $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
}
// if (checkExtensionSwfInPHP()==false) {// does not work, why??????????
if (!extension_loaded('ming')) {
    $errornumber = 118;
    $errorexceptionstring = "The Host does not support Ming extension, please ask your host to open it";
    $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
}
if ($errornumber == 0) {
    /*if ( $map_paras['style']  != "default" )
	    {
				 	 $errornumber = 0;
					 $errorexceptionstring = "Invalid Style name " .$map_paras['style']." given. There is only one \"default\" style name " ;
	    }*/

    if ($map_paras['width'] == "" OR $map_paras['height'] == "") {
        $errornumber = 13;
        $errorexceptionstring = "The width OR the height cannot be empty. The default is HEIGHT 700 and WIDTH 700 " ;
        $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
    }
    if ($maxx <= $minx OR $maxy <= $miny) {
        $errornumber = 15;
        $errorexceptionstring = "The BBox parameters are not valid." ;
        $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
    }

    $rs5 = $database->getRowsBySrsGroupBy($aid, $map_paras['srs'], "layer");
    $num = $database->getRowsNumber($rs5);

    $layersvalues = explode(",", $map_paras['layers']);
    $numberofvalueslayer = count($layersvalues);
    // sort the layers according its priority
    $arrayPriority = $database->getPriorityArray($aid, $map_paras['srs'], $layersvalues);
    $layersvalues = sortLayer($layersvalues, $arrayPriority);

    for ($i0 = 0; $i0 < $numberofvalueslayer; $i0++) {
        $rs2 = $database->getRowsBySrsLayer($aid, $map_paras['srs'], $layersvalues[$i0]);
        $line2 = $database->getColumns($rs2);

        $rs7 = $database->getRowsMinMaxXYBySrs($aid, $map_paras['srs']);
        $line7 = $database->getColumns($rs7);
        $totalminx = $line7[0];
        $totalminy = $line7[1];
        $totalmaxx = $line7[2];
        $totalmaxy = $line7[3];
        // check BBox error
        if ($maxx < $totalminx OR $miny > $totalmaxy OR $minx > $totalmaxx OR $maxy < $totalminy) {
            $errornumber = 1;
            $errorexceptionstring = "Invalid bounding box coordinates for SRS =" . $map_paras['srs'] . ". Easting must be between " . $totalminx . " and " . $totalmaxx . " AND Northing  must be between " . $totalminy . " and " . $totalmaxy . ".";
            $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
        }
        // check layer and SRS error
        if ($line2 == null AND $map_paras['layers'] != "" AND $map_paras['srs'] != "") {
            $errornumber = 2;
            $errorexceptionstring = "LayerNotDefined.Layer " . $layersvalues[$i0] . " with SRS " . $map_paras['srs'] . " not found, Please check your Layer name and/or SRS."; // The layers supported by this SRS " . $map_paras['srs'] . " are  ". $data_Geom_Type .",";
            $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
        }
        // }
        else if ($line2 == null AND $map_paras['layers'] == "") {
            $errornumber = 4;
            $errorexceptionstring = "Layer " . $layersvalues[$i0] . " not specified. Please insert  Layer names!";
            $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
        } else if ($line2 == null AND $map_paras['srs'] == "") {
            $errornumber = 5;
            $errorexceptionstring = "SRS " . $map_paras['srs'] . " not specified. Please insert  valid SRS!";
            $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
        }
    } // end  of for ($i=0; $i < $numberofvalueslayer; $i++)
}
if ($errornumber == 0) {
    /**
     * *Coordinate transform
     */
    $size = getStretchWidthHeight($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $enablestretchmap);
    $map_paras['width'] = $size[0];
    $map_paras['height'] = $size[1];

    /**
     * *Begin to create SWF
     */
    $swf = new SWFMovie();
    $swf->setDimension($map_paras['width'], $map_paras['height']);
    $swf->setBackground(255, 255, 255);
	
	if($btn_user_interface){
    	$swffont = new SWFFont("../../../wms/VeraSerif.fdb");
	}else{
		$swffont = new SWFFont("VeraSerif.fdb");
	}

    $swfrender->setRender($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $swf, $swffont, $enablestretchmap);

    $color_copyrightinfo = new RasterColor("#FF0000");
    if ($atlas_cfg['showCopyright']) {
        $swfrender->createTextWithScreenCoordinate(15, $map_paras['height']-15, "Copyright(C)" . SUAS_NAME . SITE_VERSION . SITE_VERSION_EDITION, 10, $color_copyrightinfo);
    }

    $styleparser = new StyleParser($aid, $database);
    $aryXmlUserLayerNode = $styleparser->createStyleNode4layer(true, $stid);

    for ($j = 0; $j < $numberofvalueslayer; $j++) {
        $rs2_ = $database->getRowsBySrsLayer($aid, $map_paras['srs'], $layersvalues[$j]);
        $line2_ = $database->getColumns($rs2_);
        $data_Layer_Type = strtoupper($line2_["layertype"]);

        $styleparser->getLayerStyleFromStyleNode($layersvalues[$j], $data_Layer_Type, $aryXmlUserLayerNode);

        $xmlMinScaleDenominator = "";
        $xmlMaxScaleDenominator = "";
        $xmlMinScaleDenominator = formatScale($styleparser->xmlMinScaleDenominator);
        $xmlMaxScaleDenominator = formatScale($styleparser->xmlMaxScaleDenominator);
        // For point,linestring,polygon,,text
        $xmlSize = $styleparser->xmlSize;
        // For point,polygon,text,linestring
        $xmlFillColor = $styleparser->xmlFillColor;
        // For linestring,polygon
        $xmlStrokeColor = $styleparser->xmlStrokeColor;
        // For text
        $xmlFont = $styleparser->xmlFont;
        // For point
        $xmlWellKnownName = strtoupper($styleparser->xmlWellKnownName);
        // For linestring, point
        $xmlStrokeOpacity = ($styleparser->xmlStrokeOpacity) / 100;
        // For polygon, point, image
        $xmlFillOpacity = ($styleparser->xmlFillOpacity) / 100;
        // For text
        $xmlFontStyle = $styleparser->xmlFontStyle;
        $xmlFontWeight = $styleparser->xmlFontWeight;
        // For linestring
        $xmlLineJoin = $styleparser->xmlLineJoin;
        $xmlLineCap = $styleparser->xmlLineCap;
        // only use for linestring
        if ($xmlFillColor == "-1")
            $blnFillLineString = 0;
        else $blnFillLineString = 1;

        $rastercolorFillColor = new RasterColor($xmlFillColor);
        $rastercolorStrokeColor = new RasterColor($xmlStrokeColor);

        /*
        * Control the display range/ scale
        */
        $showlayer = false;
        if ($xmlMinScaleDenominator != "" OR $xmlMaxScaleDenominator != "") {
            if ($xmlMinScaleDenominator == "") $xmlMinScaleDenominator = 1;
            if ($xmlMaxScaleDenominator == "") $xmlMaxScaleDenominator = 900000000;

            $currentScale = getCurrentScale($maxx - $minx, $map_paras['width']);
            if ($xmlMinScaleDenominator <= $currentScale AND $currentScale <= $xmlMaxScaleDenominator)
                $showlayer = true;
        } elseif ($xmlMinScaleDenominator == "" AND $xmlMaxScaleDenominator == "") {
            $showlayer = true;
        }

        if ($showlayer) {
            $rs1_ = $database->getGeomAsTextInBboxBySrsLayer($tbname, $minx, $miny, $maxx, $maxy, $map_paras['srs'], $layersvalues[$j]);
            while ($line1_ = $database->getColumns($rs1_)) {
                $data_id = $database->getIdFromRS($line1_);
                $data_Recid = $database->getRecidFromRS($line1_);;
                if ($data_Recid == "") $data_Recid = $data_id;

                $data_Geom = $database->getGeometryTextFromRS($line1_);
                // echo $data_Geom."ha\n";
                //$data_Style = $database->getStyleFromRS($line1_);
                $data_Geom_Type = $database->getGeomtypeFromRS($line1_);
                // only for image, the svgxlink has the image source path!
                $data_ImageLink = $database->getXlinkFromRS($line1_);
                // ======================================================================================================
                // ======================================================================================================
                switch ($data_Geom_Type) {
                    case 'POINT': {
                            $wktparser = new WKTParser();
                            $wktparser->parse($data_Geom);
                            // readstyle now
                            // $sld->poinStyle = "CIRCLE";
                            $data_x = null;
                            $data_y = null;

                            $data_x = $wktparser->wktPointX;
                            $data_y = $wktparser->wktPointY;
                            $Number_Point = $wktparser->wktPointNr;
                            $swfrender->createPoints($data_x, $data_y, $Number_Point, $xmlWellKnownName, $rastercolorFillColor , $xmlSize);
                        }
                        break;
                    // ======================================================================================================
                    // ======================================================================================================
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
                            // $Number_Point = $wktparser->wktPointNr;
                            // only one point!!!
                            $text = AttributeParser::getTextAngel($database->getAttributesFromRS($line1_));
                            $data_TextContent = $text[0];
                            $angle = $text[1];
                            $swfrender->createText($data_x[0], $data_y[0], $data_TextContent, $xmlSize, $rastercolorFillColor);
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
                                        $swfrender->createLinstring($data_x, $data_y, $Number_Point, $rastercolorStrokeColor , $xmlSize, $rastercolorFillColor, $xmlLineJoin, $xmlLineCap, $blnFillLineString);
                                    }
                                    break;
                                case "MULTILINESTRING": {
                                        $data_x = $wktparser->wktMPointX;
                                        $data_y = $wktparser->wktMPointY;
                                        $MNumber_Point = $wktparser->wktMPointNr;
                                        $MLine_Point = $wktparser->wktMLineNr;
                                        $swfrender->createMultiLinstring($data_x, $data_y, $MLine_Point, $MNumber_Point, $rastercolorStrokeColor , $xmlSize, $rastercolorFillColor, $xmlLineJoin, $xmlLineCap, $blnFillLineString);
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
                                        // $rasterimagerender->createPolygon($data_x, $data_y, $Number_Point, $color_blue , $xmlSize);
                                        $swfrender->createMultiPolygon($data_x, $data_y, $MLine_Point, $MNumber_Point, $rastercolorFillColor , $xmlSize, $xmlLineJoin, $xmlLineCap);
                                    }
                                    break;
                                case "MULTIPOLYGON": {
                                        $data_x = $wktparser->wktMPointX;
                                        $data_y = $wktparser->wktMPointY;
                                        $MNumber_Point = $wktparser->wktMPointNr;
                                        $MLine_Point = $wktparser->wktMLineNr;
                                        $swfrender->createMultiPolygon($data_x, $data_y, $MLine_Point, $MNumber_Point, $rastercolorFillColor , $xmlSize, $xmlLineJoin, $xmlLineCap);
                                    }
                            }
                        }
                        break;
                    // ======================================================================================================
                    // ======================================================================================================
                    case 'IMAGE': {
                            $wktparser = new WKTParser();
                            $wktparser->parse($data_Geom);
                            // very important!!!!!!
                            $data_x = null;
                            $data_y = null;

                            $data_x = $wktparser->wktPointX;
                            $data_y = $wktparser->wktPointY;
                            $Number_Point = $wktparser->wktPointNr;
                            //$custom_image_path_absolute
                            $swfrender->createImage($data_x, $data_y, $Number_Point, $custom_image_path_absolute.$data_ImageLink, $xmlFillOpacity, $rastercolorStrokeColor);
                        }
                        break;
                    // ======================================================================================================
                    // ======================================================================================================
                    case 'UNKNOWN': {
                        }
                        // ======================================================================================================
                        // ======================================================================================================
                } //switch
            } //while
        }
    } // end of for ($j=0; $j < $numberofvalueslayer; $j++)
    // } // end of for ($j=0; $j < $numberofvalueslayer; $j++)

    //header('Content-type: application/x-shockwave-flash');
    header("Content-Type: ".$MAP_RENDER_FORMAT['swf']);
    $swf->output();
    if(debugMode){	    
	    if(!$btn_user_interface){
	    	$swf->save('../files/atlas/'.$aid.'/cache/map.swf');
    	}else{
    		$swf->save('cache/map.swf');
    	}
    }    
    $swf->clearAllResource();
}
/*
<html>
<body>
<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0" ID=objects WIDTH=<?=$map_paras['width']?> HEIGHT=<?=$map_paras['height']?>>
<PARAM NAME=movie VALUE="getswf.swf">
<EMBED src="../cache/getswf.swf" WIDTH=<?=$map_paras['width']?> HEIGHT=<?=$map_paras['height']?> TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash">
</OBJECT>
</body>
</html>
*/
if ($atlas_cfg['enableCache']) {
    $cache->caching();
}
$database->databaseClose();
exit();

?>
