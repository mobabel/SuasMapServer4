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
 * @copyright (C) 2006-2007  LI Hui
 * @Description : This show the copyright .
 * @version $1.0$ 2005
 * @Author Filmon Mehari and Professor Dr. Franz Josef Behr
 * @version $2.0$ 2006.05
 * @Author Chen Hang and LI Hui
 * @version $3.0$ 2006
 * @Author LI Hui
 * @version $4.0$ 2009.04
 * @Author LI Hui
 */

if ($atlas_cfg['enableCache']) {
    if (equalIgnoreCase($map_paras['format'], "image/png")) {
        $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_PNG, $aid);
        $cache->cacheCheck();
    } elseif (equalIgnoreCase($map_paras['format'], "image/gif")) {
        $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_GIF, $aid);
        $cache->cacheCheck();
    } elseif (equalIgnoreCase($map_paras['format'], "image/jpeg")) {
        $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_JPEG, $aid);
        $cache->cacheCheck();
    } elseif (equalIgnoreCase($map_paras['format'], "image/wbmp")) {
        $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_WBMP, $aid);
        $cache->cacheCheck();
    } elseif (equalIgnoreCase($map_paras['format'], "image/bmp")) {
        $cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_BMP, $aid);
        $cache->cacheCheck();
    }
}

setConnectionTime(SITE_MAX_TIMEOUT_LIMIT);

$rasterimagerender = new RasterImageRender();

/*$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();*/

if ($database->databaseGetErrorMessage() != "") {
    $errornumber = -1;
    $errorexceptionstring = $database->databaseGetErrorMessage();
}
if ($errornumber != 0) {
    $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
} else {
    /*if ( $map_paras['style']  != "default" )
	    {
				 	 $errornumber = 0;
					 $errorexceptionstring = "Invalid Style name " .$map_paras['style']." given. There is only one \"default\" style name " ;
            $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
	    }*/
    if ($map_paras['width'] == "" OR $map_paras['height'] == "" OR $map_paras['width'] == 0 OR $map_paras['height'] == 0) {
        $errornumber = 13;
        $errorexceptionstring = "The width OR the height cannot be empty or zero." ;
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
        // check BBox error, if coordinate is outside of the geomery collection
        if ($maxx < $totalminx OR $miny > $totalmaxy OR $minx > $totalmaxx OR $maxy < $totalminy) {
            //$errornumber = 1;
            //$errorexceptionstring = "Invalid bounding box coordinates for SRS =" . $map_paras['srs'] . ". Easting must be between " . $totalminx . " and " . $totalmaxx . " AND Northing  must be between " . $totalminy . " and " . $totalmaxy . ".";
        }
        // check layer and SRS error
        if ($line2 == null AND $map_paras['layers'] != "" AND $map_paras['srs'] != "") {
            $errornumber = 2;
            $errorexceptionstring = "LayerNotDefined. Layer " . $layersvalues[$i0] . " with SRS " . $map_paras['srs'] . " not found, Please check your Layer name and/or SRS.";
            // The layers supported by this SRS " . $map_paras['srs'] . " are  ". $data_Geom_Type .",";
        }
        // }
        else if ($line2 == null AND $map_paras['layers'] == "") {
            $errornumber = 4;
            $errorexceptionstring = "Layer " . $layersvalues[$i0] . " not specified. Please insert  Layer names!";
        } 
        else if ($line2 == null AND $map_paras['srs'] == "") {
            $errornumber = 5;
            $errorexceptionstring = "SRS " . $map_paras['srs'] . " not specified. Please insert  valid SRS!";
        }
    } 
}
if ($errornumber != 0) {
    $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
} else {
    /**
     * *Coordinate transform
     */
    $size = getStretchWidthHeight($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $enablestretchmap);
    $map_paras['width'] = $size[0];
    $map_paras['height'] = $size[1];

    if (blnGetMap25D) {
        /*
        * Use the new extended bbox to query the spatial data
        */
        $newbbox = getExtendBbox($minx, $miny, $maxx, $maxy, $map_paras['vangle']);
        $minx1 = $newbbox[0];
        $miny1 = $newbbox[1];
        $maxx1 = $newbbox[2];
        $maxy1 = $newbbox[3];
    }

    /**
     * *Begin to create Raster Image
     */
    $newImg =  imagecreatetruecolor($map_paras['width'], $map_paras['height']);
	imagealphablending( $newImg, false );
	
    // set transparent
    $bg = imagecolorallocate ($newImg, 0xFF, 0xFF, 0xFF); //blank but white
    imagefilledrectangle($newImg, 0, 0, $map_paras['width'], $map_paras['height'], $bg);
    if ($map_paras['transparent']) {
        ImageColorTransparent($newImg, $bg);
    }
    imagealphablending( $newImg, true );
    
    $fg = imagecolorallocate ($newImg, 0xFF, 0xFF, 0xFF);

    $color_copyrightinfo = imagecolorallocate ($newImg, 255, 0, 0);
    $color_yellow = ImageColorAllocate($newImg, 251, 252, 194);
    $color_land = imagecolorallocate ($newImg, 0xF7, 0xEF, 0xDE);
    $color_sea = imagecolorallocate ($newImg, 0xB5, 0xC7, 0xD6);
    $color_blue = imagecolorallocate ($newImg, 0, 0, 255);

    if($map_paras['skycolor']!=""){
    	$rastercolorskycolor = new RasterColor($map_paras['skycolor']);
    	$color_skycolor = imagecolorallocate ($newImg, $rastercolorskycolor->setRGB_R, $rastercolorskycolor->setRGB_G, $rastercolorskycolor->setRGB_B);
    }
    if($map_paras['bgcolor']!=""){
    	$rastercolorbgcolor = new RasterColor($map_paras['bgcolor']);
    	$color_bgcolor = imagecolorallocate ($newImg, $rastercolorbgcolor->setRGB_R, $rastercolorbgcolor->setRGB_G, $rastercolorbgcolor->setRGB_B);
    	imagefilledrectangle($newImg, 0, 0, $map_paras['width'], $map_paras['height'], $color_bgcolor);
    }
	
    $rasterimagerender->setRender($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $newImg, $enablestretchmap,$map_paras['hangle'],$map_paras['vangle'],$map_paras['distance'],$atlas_cfg['outputEncodeCountry']);
    if ($atlas_cfg['showCopyright']) {
        $rasterimagerender->createTextWithScreenCoordinate(15, $map_paras['height']-15, "Copyright(C)" . SUAS_NAME . SITE_VERSION . SITE_VERSION_EDITION, 5, $fontangle, $color_copyrightinfo);
    }

    $styleparser = new StyleParser($aid, $database);
    $aryXmlUserLayerNode = $styleparser->createStyleNode4layer(true, $stid);
	
	$geocount = 0;
    for ($j = 0; $j < $numberofvalueslayer; $j++) {
    	if(!empty($atlas_cfg['custom_param_maxgeonum'])){
        	if($geocount > $atlas_cfg['custom_param_maxgeonum']){
            	break;
           	}
		}
        $rs2_ = $database->getRowsBySrsLayer($aid, $map_paras['srs'], $layersvalues[$j]);
        $line2_ = $database->getColumns($rs2_);
        $data_Layer_Type = strtoupper($line2_["layertype"]);

        $xmlFillColor == "-1";
        $xmlMinScaleDenominator = "";
        $xmlMaxScaleDenominator = "";

        $styleparser->getLayerStyleFromStyleNode($layersvalues[$j], $data_Layer_Type, $aryXmlUserLayerNode);

		//print_r($styleparser);
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
        $xmlWellKnownName = strtolower($styleparser->xmlWellKnownName);
        // For linestring, point
        $xmlStrokeOpacity = ($styleparser->xmlStrokeOpacity) /*/ 100*/;
        // For polygon, point,image, unit from 0-100
        $xmlFillOpacity = ($styleparser->xmlFillOpacity) /*/ 100*/;
        // For text
        $xmlFontStyle = $styleparser->xmlFontStyle;
        $xmlFontWeight = $styleparser->xmlFontWeight;
        // For linestring
        $xmlLineJoin = $styleparser->xmlLineJoin;
        $xmlLineCap = $styleparser->xmlLineCap;
        // only use for linestring or polygon
        if ($xmlFillColor == "-1")
            $blnFillColor = 0;
        else $blnFillColor = 1;

        $rastercolorFillColor = new RasterColor($xmlFillColor);
        $color_FillColor = imagecolorallocate ($newImg, $rastercolorFillColor->setRGB_R, $rastercolorFillColor->setRGB_G, $rastercolorFillColor->setRGB_B);

        $rastercolorStrokeColor = new RasterColor($xmlStrokeColor);
        $color_StrokeColor = imagecolorallocate ($newImg, $rastercolorStrokeColor->setRGB_R, $rastercolorStrokeColor->setRGB_G, $rastercolorStrokeColor->setRGB_B);
		//print_r($rastercolorStrokeColor);echo "$layersvalues[$j]\n";
		//echo $color_StrokeColor;
		//$red = imagecolorallocate ($rasterimagerender->image, 255,255,0);
		//echo $red;
        /*
        * Control the display range/ scale
        */
        $showlayer = false;
        if ($xmlMinScaleDenominator != "" OR $xmlMaxScaleDenominator != "") {
            if ($xmlMinScaleDenominator == "") $xmlMinScaleDenominator = 1;
            if ($xmlMaxScaleDenominator == "") $xmlMaxScaleDenominator = 900000000;

            $currentScale = getCurrentScale($maxx - $minx, $map_paras['width'], $map_paras['srs']);//178706132
            if ($xmlMinScaleDenominator <= $currentScale AND $currentScale <= $xmlMaxScaleDenominator)
                $showlayer = true;
        } elseif ($xmlMinScaleDenominator == "" AND $xmlMaxScaleDenominator == "") {
            $showlayer = true;
        }

        if ($showlayer) {
            if (blnGetMap25D){
                $rs1_ = $database->getGeomAsTextInBboxBySrsLayer($aid, $minx, $miny, $maxx, $maxy, $map_paras['srs'], $layersvalues[$j]);
            }
            else {
                $rs1_ = $database->getGeomAsTextInBboxBySrsLayer($aid, $minx, $miny, $maxx, $maxy, $map_paras['srs'], $layersvalues[$j]);
            }
			
             while ($line1_ = $database->getColumns($rs1_)) {
             	if(!empty($atlas_cfg['custom_param_maxgeonum'])){
             		if($geocount > $atlas_cfg['custom_param_maxgeonum']){
            			break;
           			}
		        	$geocount++;
				}
                $data_Geom = $database->getGeometryTextFromRS($line1_); //geom
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
                            $rasterimagerender->createPoints($data_x, $data_y, $Number_Point, $xmlWellKnownName, $color_FillColor , $xmlSize);
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
                                        $rasterimagerender->createText($data_x[0], $data_y[0], $data_TextContent, $xmlFont, $xmlSize, $angle, $color_FillColor);
                                    }
                                    break;
                                case "LINESTRING": {
                                        $rasterimagerender->createText($data_x[0], $data_y[0], $data_TextContent, $xmlFont, $xmlSize, $angle, $color_FillColor);
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
                                        $rasterimagerender->createLinstring($data_x, $data_y, $Number_Point, $color_StrokeColor , $xmlSize, $color_FillColor, $blnFillColor);
                                    }
                                    break;
                                case "MULTILINESTRING": {
                                        $data_x = $wktparser->wktMPointX;
                                        $data_y = $wktparser->wktMPointY;
                                        $MNumber_Point = $wktparser->wktMPointNr;
                                        $MLine_Point = $wktparser->wktMLineNr;
                                        $rasterimagerender->createMultiLinstring($data_x, $data_y, $MLine_Point, $MNumber_Point, $color_StrokeColor, $xmlSize, $color_FillColor, $blnFillColor);
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
                                        $rasterimagerender->createMultiPolygon($data_x, $data_y, $MLine_Point, $MNumber_Point, $color_StrokeColor, $color_FillColor, $xmlSize, $blnFillColor);
                                    }
                                    break;
                                case "MULTIPOLYGON": {
                                        $data_x = $wktparser->wktMPointX;
                                        $data_y = $wktparser->wktMPointY;
                                        $MNumber_Point = $wktparser->wktMPointNr;
                                        $MLine_Point = $wktparser->wktMLineNr;
                                        
                                        $rasterimagerender->createMultiPolygon($data_x, $data_y, $MLine_Point, $MNumber_Point, $color_StrokeColor, $color_FillColor, $xmlSize, $blnFillColor);
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
                            $Number_Point = $wktparser->wktPointNr;//$Number_Point==5
                            
                            $rasterimagerender->createImage($data_x, $data_y, $Number_Point, $custom_image_path_absolute.$data_ImageLink, $xmlFillOpacity, $color_StrokeColor);
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
    } 

    //draw the sky background
     if (blnGetMap25D){
         if($map_paras['skycolor']!=""){
             $skyimage = new GDGradientFill($map_paras['width'], $map_paras['height'] * (cos($map_paras['vangle']))*1.2,'vertical',$map_paras['skycolor'],'#FFFFFF',0);
             imagecopymerge($rasterimagerender->image, $skyimage->image, 0, 0, 0, 0, $map_paras['width'], $map_paras['height'] * (cos($map_paras['vangle']))*1.2, 90);
             //imagefilledrectangle($rasterimagerender->image, 0, 0, $map_paras['width'], $map_paras['height'] * (cos($map_paras['vangle'])), $color_skycolor);
         }
	 }
	
	// create an interlaced image for better loading in the browser
	imageInterlace($rasterimagerender->image, 1);

    if (equalIgnoreCase($map_paras['format'], "image/png")) {
        header("Content-Type: image/png");
        ImagePNG($rasterimagerender->image);
    } elseif (equalIgnoreCase($map_paras['format'], "image/gif")) {
        header("Content-Type: image/gif");
        Imagegif($rasterimagerender->image);
    } elseif (equalIgnoreCase($map_paras['format'], "image/jpeg")) {
        header("Content-Type: image/jpeg");
        Imagejpeg($rasterimagerender->image);
    } elseif (equalIgnoreCase($map_paras['format'], "image/wbmp")) {
        header("Content-Type: image/wbmp");
        imagewbmp($rasterimagerender->image);
    } elseif (equalIgnoreCase($map_paras['format'], "image/bmp")) {
        header("Content-Type: image/bmp");
        imagebmp($rasterimagerender->image, '' , 8, 0);
        //imagebmp_($rasterimagerender->image, '' , 0);
    }
    
    $rasterimagerender->clearAllResource();
    if ($atlas_cfg['enableCache']) {
        $cache->caching();
    }
} 

$database->databaseClose();
exit();

?>
