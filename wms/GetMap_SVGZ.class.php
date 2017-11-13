<?php
/**
 * Getmap SVGZ Class
 * Copyright (C) 2006-2007  leelight
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
    $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_SVG, $aid);
    $cache->cacheCheck();
}

$gz = new gzip();

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

    if ($map_paras['width'] == "" OR $map_paras['height'] == "") {
        $errornumber = 13;
        $errorexceptionstring = "The width OR the height cannot be empty. " ;
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

    for ($i = 0; $i < $numberofvalueslayer; $i++) {
        $rs2 = $database->getRowsBySrsLayer($aid, $map_paras['srs'], $layersvalues[$i]);
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
            $errorexceptionstring = "LayerNotDefined. Layer " . $layersvalues[$i] . " with SRS " . $map_paras['srs'] . " not found, Please check your Layer name and/or SRS.";
            $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
        } else if ($line2 == null AND $map_paras['layers'] == "") {
            $errornumber = 4;
            $errorexceptionstring = "Layer " . $layersvalues[$i] . " not specified. Please insert  Layer names!";
            $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
        } else if ($line2 == null AND $map_paras['srs'] == "") {
            $errornumber = 5;
            $errorexceptionstring = "SRS " . $map_paras['srs'] . " not specified. Please insert  valid SRS!";
            $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
        }
        $data2 = $line2["style"];
        $data3 = $line2["srs"];
        $data4 = $line2["layer"];
    } // end  of for ($i=0; $i < $numberofvalueslayer; $i++)
}
if ($errornumber == 0) {
    /**
     * *Coordinate transform
     */
    $size = getStretchWidthHeight($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $enablestretchmap);
    $map_paras['width'] = $size[0];
    $map_paras['height'] = $size[1];

	header("Content-type: image/svg+xml");
    //header("Content-Type: ".$MAP_RENDER_FORMAT['svgz']);

    $svgz = new SVGStreamRender(0);
    $svgz->setSVGStreamRender($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $enablestretchmap, $atlas_cfg['enableSVGPixelCoordinate']);

    $svgz->SvgDocument();
    $svgz->SvgFragmentBegin();
    $svgz->SvgDesc("Generator: ".SUAS_NAME."\n" . "Copyright(C)" . SUAS_COPYRIGHT . SITE_VERSION . SITE_VERSION_EDITION);

    if ($atlas_cfg['showCopyright']) {
        $svgz->SvgText("", $minx, - $miny, "Copyright(C)" . SUAS_COPYRIGHT . SITE_VERSION . SITE_VERSION_EDITION,
            "fill:red;font-size:10px;text-anchor:begin;font-rendering:optimizeLegibility;pointer-events:none", "");
    }
    $styleparser = new StyleParser($aid, $database);
    $aryXmlUserLayerNode = $styleparser->createStyleNode4layer(true, $stid);

    for ($ii = 0; $ii < $numberofvalueslayer; $ii++) {
        $rs2_ = $database->getRowsBySrsLayer($aid, $map_paras['srs'], $layersvalues[$ii]);
        $line2_ = $database->getColumns($rs2_);
        $data_Layer_Type = strtoupper($line2_["layertype"]);

        $styleparser->getLayerStyleFromStyleNode($layersvalues[$ii], $data_Layer_Type, $aryXmlUserLayerNode);

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
        // For polygon, point, image,linestring
        $xmlFillOpacity = ($styleparser->xmlFillOpacity) / 100;
        // For text
        $xmlFontStyle = $styleparser->xmlFontStyle;
        $xmlFontWeight = $styleparser->xmlFontWeight;
        // For linestring
        $xmlLineJoin = $styleparser->xmlLineJoin;
        $xmlLineCap = $styleparser->xmlLineCap;

        $style4eachlayer = createStyle4SVG($data_Layer_Type, $xmlSize, $xmlFillColor, $xmlStrokeColor, $xmlFont,
            $xmlWellKnownName, $xmlStrokeOpacity, $xmlFillOpacity, $xmlFontStyle,
            $xmlFontWeight, $xmlLineJoin, $xmlLineCap);

        $data_style = $line2_["style"];
        $data_srs = $line2_["srs"];
        $data_geomtype = strtoupper($line2_["geomtype"]);
        $data_attributes = strtoupper($line2_["attributes"]);

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
            // justify the spatial relationship
            $rs1_ = $database->getGeomAsTextInBboxBySrsLayer($aid, $minx, $miny, $maxx, $maxy, $map_paras['srs'], $layersvalues[$ii]);

            $svgz->SvgGroupBegin($layersvalues[$ii], $style4eachlayer, "", "");

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
                            // <circle cx="3562592.6" cy="-5519511.68" r = "1046.83" />
                            // $xml=new SVGParser($data_Geom);
                            $wktparser = new WKTParser();
                            $wktparser->parse($data_Geom);
                            // readstyle now
                            // $sld->poinStyle = "CIRCLE";
                            $data_x = null;
                            $data_y = null;

                            $data_x = $wktparser->wktPointX;
                            $data_y = $wktparser->wktPointY;
                            $Number_Point = $wktparser->wktPointNr;
                            // $rasterimagerender->createLinstring($data_x, $data_y, $Number_Point, $color_blue , 2);
                            switch ($wktparser->wktGeomType) {
                                case "POINT": {
                                        $svgz->SvgPoint($data_Recid, $data_x[0], - $data_y[0], $xmlSize, "", "", $xmlWellKnownName);
                                    }
                                    break;
                                case "MULTIPOINT": {
                                        $svgz->SvgMultiPoint($data_Recid, $data_x, - $data_y, $xmlSize, $Number_Point, "", "", $xmlWellKnownName);
                                    }
                            }
                        }
                        break;
                    // ======================================================================================
                    // ======================================================================================
                    case 'TEXT': {
                            $wktparser = new WKTParser();
                            $wktparser->parse($data_Geom); //Point type, only one point!
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
                                        $svgz->SvgText($data_Recid, $data_x[0], $data_y[0], $data_TextContent, "", "");
                                    }
                                    break;
                                case "LINESTRING": {
                                        $svgz->SvgTextPath($data_Recid, $data_x, $data_y, $Number_Point, $data_TextContent, "", "");
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
                                        $data_x = $wktparser->wktPointX;
                                        $data_y = $wktparser->wktPointY;
                                        $Number_Point = $wktparser->wktPointNr;
                                        $svgz->SvgLineString($data_Recid, $data_x, $data_y, $Number_Point, "", "");
                                    }
                                    break;
                                case "MULTILINESTRING": {
                                        $data_x = $wktparser->wktMPointX;
                                        $data_y = $wktparser->wktMPointY;
                                        $MNumber_Point = $wktparser->wktMPointNr;
                                        $MLine_Point = $wktparser->wktMLineNr;
                                        // print_r($MLine_Point);
                                        $svgz->SvgMultiLinstring($data_Recid, $data_x, $data_y, $MLine_Point, $MNumber_Point, "", "");
                                    }
                            } //switch
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
                                        $MLine_Point = $wktparser->wktMLineNr; //is 1
                                        $svgz->SvgSinglePolygon($data_Recid, $data_x, $data_y, $MLine_Point, $MNumber_Point, "", "");
                                    }
                                    break;
                                case "MULTIPOLYGON": {
                                        $data_x = $wktparser->wktMPointX;
                                        $data_y = $wktparser->wktMPointY;
                                        $MNumber_Point = $wktparser->wktMPointNr;
                                        $MLine_Point = $wktparser->wktMLineNr;
                                        $svgz->SvgSinglePolygon($data_Recid, $data_x, $data_y, $MLine_Point, $MNumber_Point, "", "");
                                    }
                                    break;
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
                            $Number_Point = $wktparser->wktPointNr; //$Number_Point==5

                            $svgz->SvgSingleImage($data_Recid, $data_x, $data_y, $custom_image_path.$data_ImageLink, "", "");
                        }
                        break;
                    // ======================================================================================================
                    // ======================================================================================================
                    case 'UNKNOWN': {
                        }
                        // ======================================================================================================
                        // ======================================================================================================
                } //switchs
            } //while1
            $svgz->SvgGroupEnd();
        }
    }

    $svgz->SvgFragmentEnd();

    $gz->add($svgz->getFinalSvg(), "");
    $gz->print_file();
    //$gz->write_file("../cache/dd.xx");
}

if ($atlas_cfg['enableCache']) {
    $cache->caching();
}
$database->databaseClose();
exit();

?>
