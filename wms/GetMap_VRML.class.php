<?php
/**
 * Getmap RasterImage Class
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

if (equalIgnoreCase($map_paras['format'], $MAP_RENDER_FORMAT['vrmlz'])) {
        $gz = new gzip();
        $gzip = true;
    }
if ($atlas_cfg['enableCache']) {
    if (equalIgnoreCase($map_paras['format'], $MAP_RENDER_FORMAT['vrml'])) {
        $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_VRML, $aid);
        $cache->cacheCheck();
    }
    if (equalIgnoreCase($map_paras['format'], $MAP_RENDER_FORMAT['vrmlz'])) {
        $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_VRMLZ, $aid);
        $cache->cacheCheck();
    }
}

$vrml = new VRMLRender();

if ($database->databaseGetErrorMessage() != "") {
    $errornumber = -1;
    $errorexceptionstring = $database->databaseGetErrorMessage();
    $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
}
if ($errornumber == 0) {
    /*if ( $map_paras['style']  != "default" )
	    {
				 	 $errornumber = 0;
					 $errorexceptionstring = "Invalid Style name " .$map_paras['style']." given. There is only one \"default\" style name " ;
	    }*/
    if ($map_paras['width'] == "" OR $map_paras['height'] == "" OR $map_paras['width'] == 0 OR $map_paras['height'] == 0) {
        $errornumber = 13;
        $errorexceptionstring = "The width OR the height cannot be empty or zero." ;
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
    $elevationsvalues = sortLayer($elevationsvalues, $arrayPriority);

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
            $errorexceptionstring = "LayerNotDefined. Layer " . $layersvalues[$i0] . " with SRS " . $map_paras['srs'] . " not found, Please check your Layer name and/or SRS.";
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
    } // end  of for ($i0=0; $i0 < $numberofvalueslayer; $i0++)
}
if ($errornumber == 0)  {
    /**
     * *Coordinate transform
     */
    $size = getStretchWidthHeight($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $enablestretchmap);
    $map_paras['width'] = $size[0];
    $map_paras['height'] = $size[1];

    /**
     * *Begin to create Vrml Image
     */
    if ($map_paras['skycolor'] != "") {
        $rastercolorskycolor = new RasterColor($map_paras['skycolor']);
        $color_skycolor = ($rastercolorskycolor->setRGB_R / 255) . " " . ($rastercolorskycolor->setRGB_G / 255) . " " . ($rastercolorskycolor->setRGB_B / 255);
    }
    if ($map_paras['bgcolor'] != "") {
        $rastercolorbgcolor = new RasterColor($map_paras['bgcolor']);
        $color_bgcolor = ($rastercolorbgcolor->setRGB_R / 255) . " " . ($rastercolorbgcolor->setRGB_G / 255) . " " . ($rastercolorbgcolor->setRGB_B / 255);
    }

    $vrml->setRender($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'],
        $map_paras['poix'], $map_paras['poiy'], $map_paras['poiz'], $map_paras['pitch'] , $map_paras['yaw'], 
        	$map_paras['roll'], $map_paras['distance'], $map_paras['aov'], $map_paras['environment'], $color_skycolor, $color_bgcolor, $map_paras['bgimage'],
        		$enablestretchmap,$gzip, $atlas_cfg['outputEncodeCountry']);


    $styleparser = new StyleParser($aid, $database);
    $aryXmlUserLayerNode = $styleparser->createStyleNode4layer(true, $stid);

    for ($j = 0; $j < $numberofvalueslayer; $j++) {
        $layerelevation = $elevationsvalues[$j];
        $rs2_ = $database->getRowsBySrsLayer($aid, $map_paras['srs'], $layersvalues[$j]);
        $line2_ = $database->getColumns($rs2_);
        $data_Layer_Type = strtoupper($line2_["layertype"]);

        $xmlFillColor == "-1";
        $xmlMinScaleDenominator = "";
        $xmlMaxScaleDenominator = "";

        $styleparser->getLayerStyleFromStyleNode($layersvalues[$j], $data_Layer_Type, $aryXmlUserLayerNode);

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
        $color_FillColor = Round(($rastercolorFillColor->setRGB_R / 255),4) . " " . Round(($rastercolorFillColor->setRGB_G / 255),4) . " " . Round(($rastercolorFillColor->setRGB_B / 255),4);

        $rastercolorStrokeColor = new RasterColor($xmlStrokeColor);
        $color_StrokeColor = Round(($rastercolorStrokeColor->setRGB_R / 255),4) . " " . Round(($rastercolorStrokeColor->setRGB_G / 255),4) . " " . Round(($rastercolorStrokeColor->setRGB_B / 255),4);

        /**
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
            $rs1_ = $database->getGeomAsTextInBboxBySrsLayer($aid, $minx, $miny, $maxx, $maxy, $map_paras['srs'], $layersvalues[$j]);

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

                            $po = new Point();
                            $poiobj = $po->CreatePoint($data_x, $data_y,$layerelevation, $Number_Point, $xmlWellKnownName, $color_FillColor , $xmlSize);
                            for($i = 0;$i < count($poiobj);$i++) {
                                $vrml->addNode($poiobj[$i]);
                            }
                            $poiobj = null;
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
                            $text = AttributeParser::getTextAngel($database->getAttributesFromRS($line1_));
                            $data_TextContent = $text[0];
                            $angle = $text[1];

                            switch ($wktparser->wktGeomType) {
                                case "POINT": {
                                        // only one point!!!
                                        $ft = new FreeText();
                                        $cft = $ft->CreateFreeText($data_x[0], $data_y[0], $layerelevation, $data_TextContent, $xmlFont, $xmlSize, $angle, $color_FillColor,$atlas_cfg['outputEncodeCountry']);
                                        $vrml->addNode($cft);
                                        $cft = null;
                                    }
                                    break;
                                case "LINESTRING": {
                                        $ft = new FreeText();
                                        $cft = $ft->CreateFreeText($data_x[0], $data_y[0], $layerelevation, $data_TextContent, $xmlFont, $xmlSize, $angle, $color_FillColor,$atlas_cfg['outputEncodeCountry']);
                                        $vrml->addNode($cft);
                                        $cft = null;
                                    }
                            }
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
                                        $data_x = array(0 =>$wktparser->wktPointX);
                                        $data_y = array(0 =>$wktparser->wktPointY);
                                        $MNumber_Point = array(0 => $wktparser->wktPointNr);
                                        $MLine_Point = 1;

                                        $ml = new MultiLinstring();
                                        $ls = $ml->createMultiLinstring($data_x, $data_y, $MLine_Point, $MNumber_Point, $layerelevation, $color_StrokeColor, $xmlSize, $color_FillColor, $blnFillLineString);
                                        for($i = 0;$i < count($ls);$i++) {
                                            $vrml->addNode($ls[$i]);
                                        }
                                        $ls = null;
                                    }
                                    break;
                                case "MULTILINESTRING": {
                                        $data_x = $wktparser->wktMPointX;
                                        $data_y = $wktparser->wktMPointY;
                                        $MNumber_Point = $wktparser->wktMPointNr;
                                        $MLine_Point = $wktparser->wktMLineNr;

                                        $ml = new MultiLinstring();
                                        $mls = $ml->createMultiLinstring($data_x, $data_y, $MLine_Point, $MNumber_Point, $layerelevation, $color_StrokeColor, $xmlSize, $color_FillColor, $blnFillLineString);
                                        for($i = 0;$i < count($mls);$i++) {
                                            $vrml->addNode($mls[$i]);
                                        }
                                        $mls = null;
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
                                        $data_x = $wktparser->wktMPointX;
                                        $data_y = $wktparser->wktMPointY;
                                        $MNumber_Point = $wktparser->wktMPointNr;
                                        $MLine_Point = $wktparser->wktMLineNr;
                                        $mp = new MultiPolygon();
                                        $pl = $mp->createMultiPolygon($data_x, $data_y, $MLine_Point, $MNumber_Point, $layerelevation, $color_FillColor, $xmlSize);
                                        for($i = 0;$i < count($pl);$i++) {
                                            $vrml->addNode($pl[$i]);
                                        }
                                    }
                                    break;
                                case "MULTIPOLYGON": {
                                        $data_x = $wktparser->wktMPointX;
                                        $data_y = $wktparser->wktMPointY;
                                        $MNumber_Point = $wktparser->wktMPointNr;
                                        $MLine_Point = $wktparser->wktMLineNr;
                                        $mp = new MultiPolygon();
                                        $mpl = $mp->createMultiPolygon($data_x, $data_y, $MLine_Point, $MNumber_Point, $layerelevation, $color_FillColor, $xmlSize);
                                        for($i = 0;$i < count($mpl);$i++) {
                                            $vrml->addNode($mpl[$i]);
                                        }
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
                            //it is 5, but vrml need 4, auto close it
                            $im = new Image();                           
                            $newim = $im->createImage($data_x, $data_y, $layerelevation, $Number_Point, $custom_image_path.$data_ImageLink, $xmlFillOpacity, $color_StrokeColor);
                            $vrml->addNode($newim);
                        }
                        break;
                    // ======================================================================================================
                    // ======================================================================================================
                    case 'UNKNOWN': {
                    	
                    }
                    // ======================================================================================================
                    // ======================================================================================================
                } //switchs
            } //while
        }
    } // end of for ($j=0; $j < $numberofvalueslayer; $j++)

    if (equalIgnoreCase($map_paras['format'], $MAP_RENDER_FORMAT['vrml'])) {
        header("Content-Type: ".$MAP_RENDER_FORMAT['vrml']);
        $vrml->generate();
    }
    if (equalIgnoreCase($map_paras['format'], $MAP_RENDER_FORMAT['vrmlz'])) {
        header("Content-Type: ".$MAP_RENDER_FORMAT['vrml']);
        $vrml->generate();
        $gz->add($vrml->getfinaloutput(), "");
        $gz->print_file();
    }
    // }
    
    if ($atlas_cfg['enableCache']) {
        $cache->caching();
    }
}
$database->databaseClose();
exit();
?>
