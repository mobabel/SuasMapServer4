<?php

/**
 * GetLegendGraphic Class
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
 * @copyright (C) 2006-2009  leelight
 * @Description : This show the copyright .
 * @version $3.0$ 2006
 * @Author leelight
 * @version $4.0$ 2009.04
 * @Author LI Hui
 * 
 */

$errornumber = 0;
$errorexceptionstring = "";

$rasterimagerender = new RasterImageRender();

if ($database->databaseGetErrorMessage() != "") {
	$errornumber = -1;
	$errorexceptionstring = $database->databaseGetErrorMessage();
	$sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
}
if ($errornumber == 0) {
	if (strcasecmp($map_paras['format'], "image/svg+xml") != 0 AND strcasecmp($map_paras['format'], "image/svgt+xml") != 0 AND strcasecmp($map_paras['format'], "image/svgb+xml") != 0 AND strcasecmp($map_paras['format'], "image/png") != 0 AND strcasecmp($map_paras['format'], "image/jpeg") != 0 AND strcasecmp($map_paras['format'], "image/gif") != 0 AND strcasecmp($map_paras['format'], "image/wbmp") != 0 AND strcasecmp($map_paras['format'], "image/bmp") != 0 AND $map_paras['format'] != "") {
		$errornumber = 3;
		$errorexceptionstring = "Invalid Format '" . $map_paras['format'] . "' is given.The \"image/svg+xml\" , \"image/svgt+xml\",\"image/svgb+xml\", \"image/png\",\"image/jepg\", \"image/gif\" , \"image/bmp\" and \"image/wbmp\" are the only formats supported." . "The default format is image/png.";
	}
	elseif ($map_paras['format'] == "") {
		$map_paras['format'] = "image/png";
	}
	elseif (($map_paras['width'] == "" AND $map_paras['height'] != "") OR ($map_paras['width'] != "" AND $map_paras['height'] == "")) {
		$errornumber = 13;
		$errorexceptionstring = "The width OR height is empty. The default width x height is " . $params['GetLegendGraphicWidth'] . "x" . $params['GetLegendGraphicHeight'] . " (pixels)";
	}
	elseif ($map_paras['width'] == "" AND $map_paras['height'] == "") {
		$map_paras['width'] = $params['GetLegendGraphicWidth'];
		$map_paras['height'] = $params['GetLegendGraphicHeight'];
	}

	$layersvalues = explode(",", $map_paras['layers']);
	$numberofvalueslayer = count($layersvalues);

	for ($i = 0; $i < $numberofvalueslayer; $i++) {
		$rs1 = $database->getRowsByQueryableLayer($aid, $layersvalues[$i]);
		$line1 = $database->getColumns($rs1);

		$rs2 = $database->getRowsByLayer($aid, $layersvalues[$i]);
		$line2 = $database->getColumns($rs2);
		// the layer exists, but not queryable, $line1=0, $line2!=0
		if ($line2 == "" OR $map_paras['layers'] == "") {
			$errornumber = 5;
			$errorexceptionstring = "Layer " . $layersvalues[$i] . " not specified. Please use other Layer names!";
		}
	}
}
if ($errornumber != 0) {
	$sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
} else {
	$styleparser = new StyleParser($aid, $database);
	$aryXmlUserLayerNode = $styleparser->createStyleNode4layer(true, $stid);

	for ($i = 0; $i < $numberofvalueslayer; $i++) {
		$rs1 = $database->getRowsByLayer($aid, $layersvalues[$i]);
		$line1 = $database->getColumns($rs1);

		$data1 = $line1["layer"];
		$data2 = $line1["description"];
		$data_Layer_Type = strtoupper($line1["layertype"]);

		$styleparser->getLayerStyleFromStyleNode($layersvalues[$i], $data_Layer_Type, $aryXmlUserLayerNode);
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
		else
			$blnFillLineString = 1;

		if (strcasecmp($map_paras['format'], $MAP_RENDER_FORMAT['png']) == 0 OR strcasecmp($map_paras['format'], $MAP_RENDER_FORMAT['jpeg']) == 0 OR strcasecmp($map_paras['format'], $MAP_RENDER_FORMAT['gif']) == 0 OR strcasecmp($map_paras['format'], $MAP_RENDER_FORMAT['wbmp']) == 0 OR strcasecmp($map_paras['format'], $MAP_RENDER_FORMAT['bmp']) == 0) {
			$newImg = imagecreate($map_paras['width'], $map_paras['height']);
			// $bg = imagecolorallocate ($newImg, 251, 252, 194); //light yellow
			// set transparent
			$bg = imagecolorallocate($newImg, 0xFF, 0xFF, 0xFF); //blank but white
			// ImageColorTransparent($newImg, $bg);
			$fg = imagecolorallocate($newImg, 0xFF, 0xFF, 0xFF);

			$rasterimagerender->setRender(0, 0, 0, 0, $map_paras['width'], $map_paras['height'], $newImg, 0, 0, 0, 0);
			$rastercolorFillColor = new RasterColor($xmlFillColor);
			$color_FillColor = imagecolorallocate($newImg, $rastercolorFillColor->setRGB_R, $rastercolorFillColor->setRGB_G, $rastercolorFillColor->setRGB_B);

			$rastercolorStrokeColor = new RasterColor($xmlStrokeColor);
			$color_StrokeColor = imagecolorallocate($newImg, $rastercolorStrokeColor->setRGB_R, $rastercolorStrokeColor->setRGB_G, $rastercolorStrokeColor->setRGB_B);
		}
		if (strcasecmp($map_paras['format'], "image/svg+xml") == 0 OR strcasecmp($map_paras['format'], "image/svgt+xml") == 0 OR strcasecmp($map_paras['format'], "image/svgb+xml") == 0) {

		}

		switch ($data_Layer_Type) {
			case 'POINT' :
				{
					$rasterimagerender->createLegendGraphicPoint($map_paras['width'], $map_paras['height'], $xmlWellKnownName, $color_FillColor, $xmlSize);
				}
				break;
			case 'TEXT' :
				{
					$rasterimagerender->createLegendGraphicText($map_paras['width'], $map_paras['height'], "T", $xmlSize, $color_FillColor);
				}
				break;
			case 'LINESTRING' :
				{
					$rasterimagerender->createLegendGraphicLineString($map_paras['width'], $map_paras['height'], $color_StrokeColor, $xmlSize, $color_FillColor, $blnFillLineString);
				}
				break;
			case 'POLYGON' :
				{
					$rasterimagerender->createLegendGraphicPolygon($map_paras['width'], $map_paras['height'], $color_StrokeColor, $xmlSize, $color_FillColor, $blnFillLineString);
				}
				break;
			case 'IMAGE' :
				{
					$rasterimagerender->createLegendGraphicImage($map_paras['width'], $map_paras['height']);
				}
				break;
			case 'UNKNOWN' :
				{
					$rasterimagerender->createLegendGraphicUnknown($map_paras['width'], $map_paras['height'], $color_StrokeColor);
				}
		}
	} // end of for ($i=0; $i < $numberofvalueslayer; $i++)
	if (strcasecmp($map_paras['format'], "image/png") == 0) {
		header("Content-Type:image/png");
		ImagePNG($newImg);
		ImageDestroy($newImg);
	}
	elseif (strcasecmp($map_paras['format'], "image/gif") == 0) {
		header("Content-Type:image/gif");
		Imagegif($newImg);
		ImageDestroy($newImg);
	}
	elseif (strcasecmp($map_paras['format'], "image/jpeg") == 0) {
		header("Content-Type:image/jpeg");
		Imagejpeg($newImg);
		ImageDestroy($newImg);
	}
	elseif (strcasecmp($map_paras['format'], "image/wbmp") == 0) {
		header("Content-Type:image/wbmp");
		imagewbmp($newImg);
		ImageDestroy($newImg);
	}
}
$database->databaseClose();
exit ();
?>