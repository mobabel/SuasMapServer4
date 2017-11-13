<?php

/**
 *
 *@description:
 *For Point:$xmlSize,$xmlFillColor,$xmlWellKnownName,$xmlStrokeOpacity,$xmlFillOpacity
 *For Linestring:$xmlSize,$xmlStrokeColor,$xmlStrokeOpacity,$xmlLineJoin,$xmlLineCap,$xmlFillColor,$xmlFillOpacity
 *For Polygon:$xmlSize,$xmlFillColor,$xmlStrokeColor,$xmlFillOpacity
 *For Text:$xmlSize,$xmlFillColor,$xmlFont,$xmlFontStyle,$xmlFontWeight
 *For image:$xmlFillOpacity
 * @param: see description
 * @return $style for each layertype
 */
function createStyle4SVGT($layertype,$xmlSize,$xmlFillColor,$xmlStrokeColor,$xmlFont,
                          $xmlWellKnownName,$xmlStrokeOpacity,$xmlFillOpacity,$xmlFontStyle,
						  $xmlFontWeight,$xmlLineJoin,$xmlLineCap){

	if($xmlSize =="") $xmlSize =10;
	if($xmlStrokeColor =="") $xmlStrokeColor ="black";
	if($xmlFillColor == -1 OR $xmlFillColor == "") $xmlFillColor = "none";
	if($xmlFont =="") $xmlFont ="Arial";
	if($xmlWellKnownName =="") $xmlWellKnownName ="square";
	if($xmlStrokeOpacity =="") $xmlStrokeOpacity ="Arial";
	if($xmlFillOpacity =="") $xmlFillOpacity =1;
	if($xmlFontStyle =="") $xmlFontStyle ="normal";
	if($xmlFontWeight =="") $xmlFontWeight ="normal";
	if($xmlLineJoin =="") $xmlLineJoin ="miter";
	if($xmlLineCap =="") $xmlLineCap ="butt";

    switch($layertype){
	    case 'POINT':{
	        $style4eachlayer = "fill=\"$xmlFillColor\" stroke-opacity=\"$xmlStrokeOpacity\" ".
			"fill-opacity=\"$xmlFillOpacity\"";
			return $style4eachlayer;

		}break;
		case 'LINESTRING':{
		    if($xmlFillColor =="none"){
            $style4eachlayer = "fill=\"none\" stroke-width=\"$xmlSize\" stroke=\"$xmlStrokeColor\" ".
			"stroke-linejoin=\"$xmlLineJoin\" stroke-linecap=\"$xmlLineCap\"";
			}
			else
			$style4eachlayer = "fill=\"$xmlFillColor\" fill-opacity=\"$xmlFillOpacity\" stroke-width=\"$xmlSize\" stroke=\"$xmlStrokeColor\" ".
			"stroke-linejoin=\"$xmlLineJoin\" stroke-linecap=\"$xmlLineCap\"";
			return $style4eachlayer;
		}break;
		case 'POLYGON':{
		    $style4eachlayer = "fill=\"$xmlFillColor\" stroke-width=\"$xmlSize\"  stroke=\"$xmlStrokeColor\" ".
			"fill-opacity=\"$xmlFillOpacity\"";
			return $style4eachlayer;

		}break;
		case 'TEXT':{
		    $style4eachlayer = "fill=\"$xmlFillColor\" font-size=\"$xmlSize"."pt\" font-family=\"$xmlFont\" ".
			"font-style=\"$xmlFontStyle\" font-weight=\"$xmlFontWeight\"";
			return $style4eachlayer;

		}break;
		case 'IMAGE':{
            $style4eachlayer = "opacity=\"$xmlFillOpacity\"";
            return $style4eachlayer;
		}break;
		case 'UNKNOWN':{
		    $style4eachlayer = "fill=\"none\" stroke-width=\"1\" stroke=\"#000000\" ".
			"fill-opacity=\"1\"";
			return $style4eachlayer;

		}break;
		default:{
		    $style4eachlayer = "fill=\"none\" stroke-width=\"1\" stroke=\"#000000\" ".
			"fill-opacity=\"1\"";
			return $style4eachlayer;

		}

	}

}

?>