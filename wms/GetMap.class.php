<?php
/**
 * Getmapcap Class
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
 * @version $1.0$ 2005
 * @Author Filmon Mehari and Professor Dr. Franz Josef Behr
 * @version $2.0$ 2006.05
 * @Author Chen Hang and leelight
 * @version $3.0$ 2006
 * @Author LI Hui
 * @version $4.0$ 2009.04
 * @Author LI Hui
 */

$errornumber = 0;
$errorexceptionstring = "";

if ($map_paras['format']  == ""){
	$errornumber = 4;
	$errorexceptionstring = "Format has not been given.";
}
else if ( !equalIgnoreCase($map_paras['format'],"image/svg+xml") AND !equalIgnoreCase($map_paras['format'],"image/svgt+xml") AND !equalIgnoreCase($map_paras['format'],"image/svgb+xml")
		AND !equalIgnoreCase($map_paras['format'],"image/svgz+xml") AND !equalIgnoreCase($map_paras['format'],"image/svgtz+xml") AND !equalIgnoreCase($map_paras['format'],"image/svgbz+xml")
		AND !equalIgnoreCase($map_paras['format'],"image/png") AND !equalIgnoreCase($map_paras['format'],"image/jpeg")
		AND !equalIgnoreCase($map_paras['format'],"image/gif")AND !equalIgnoreCase($map_paras['format'],"application/pdf") AND !equalIgnoreCase($map_paras['format'],"application/ezpdf")
		AND !equalIgnoreCase($map_paras['format'],"image/wbmp") AND !equalIgnoreCase($map_paras['format'],"application/x-shockwave-flash")
		AND !equalIgnoreCase($map_paras['format'],"image/bmp") AND !equalIgnoreCase($map_paras['format'],"model/vrml")
		AND !equalIgnoreCase($map_paras['format'],"model/vrmlz") AND !equalIgnoreCase($map_paras['format'],"model/x3d+xml")
		AND !equalIgnoreCase($map_paras['format'],"application/vnd.google-earth.kml+xml") AND !equalIgnoreCase($map_paras['format'],"application/vnd.google-earth.kmz")
		AND !equalIgnoreCase($map_paras['format'],"model/x3dz")
		AND $map_paras['format'] !=""){
	$errornumber = 3;
	$errorexceptionstring = "Invalid Format '" .$map_paras['format'].
		"' is given.The \"image/svg+xml\" , \"image/svgt+xml\",\"image/svgb+xml\", \"image/svgz+xml\",\"image/svgtz+xml\",".
		"\"image/svgbz+xml\",\"model/vrml\",\"model/vrmlz\", \"model/x3d+xml\", \"model/x3dz\",".
		"\"application/vnd.google-earth.kml+xml\",\"application/vnd.google-earth.kmz\",".
		"\"image/png\", \"application/pdf\", \"application/ezpdf\", \"application/x-shockwave-flash\", \"image/jpeg\", \"image/gif\" ,".
		"\"image/bmp\" and \"image/wbmp\" are the formats supported" ;
}
if ($errornumber != 0){
	$sendexceptionclass->sendexception($errornumber,$errorexceptionstring);
}

/**Getmap SVG Class*/
if ( equalIgnoreCase($map_paras['format'],"image/svg+xml") ){
	include '../models/CreateStyle4SVG.php';
	require_once '../render/SVGRender.class.php';
	if ($atlas_cfg['enableStreamSVG']) {
    	require_once '../render/SVGStreamRender.class.php';
	}
	include 'GetMap_SVG.class.php';
}
/**Getmap SVGT Class*/
if ( equalIgnoreCase($map_paras['format'],"image/svgt+xml") ){
	include '../models/CreateStyle4SVGT.php';
	require_once '../render/SVGTRender.class.php';
	if ($atlas_cfg['enableStreamSVG']) {
    	require_once '../render/SVGStreamRender.class.php';
	}
	include 'GetMap_SVGT.class.php';
}
/**Getmap SVGB Class*/
if ( equalIgnoreCase($map_paras['format'],"image/svgb+xml") ){
	include '../models/CreateStyle4SVGT.php';
	require_once '../render/SVGTRender.class.php';
	if ($atlas_cfg['enableStreamSVG']) {
    	require_once '../render/SVGStreamRender.class.php';
	}
	include 'GetMap_SVGT.class.php';
}
/**Getmap SVGB Class*/
if ( equalIgnoreCase($map_paras['format'],"image/svgz+xml") ){
	include '../models/CreateStyle4SVG.php';
	include_once '../render/GZip.class.php';
	require_once '../render/SVGStreamRender.class.php';
	include 'GetMap_SVGZ.class.php';
}
/**Getmap SVGT Class*/
if ( equalIgnoreCase($map_paras['format'],"image/svgtz+xml") ){
	include '../models/CreateStyle4SVGT.php';
	include_once '../render/GZip.class.php';
	require_once '../render/SVGTStreamRender.class.php';
	include 'GetMap_SVGTZ.class.php';
}
/**Getmap SVGB Class*/
if ( equalIgnoreCase($map_paras['format'],"image/svgbz+xml") ){
	include '../models/CreateStyle4SVGT.php';
	include_once '../render/GZip.class.php';
	require_once '../render/SVGTStreamRender.class.php';
	include 'GetMap_SVGTZ.class.php';
}
/**Getmap Raster Image Class*/
if ( equalIgnoreCase($map_paras['format'],"image/png") OR equalIgnoreCase($map_paras['format'],"image/gif") OR equalIgnoreCase($map_paras['format'],"image/jpeg")
OR equalIgnoreCase($map_paras['format'],"image/wbmp") OR $map_paras['format'] == "image/bmp"){
	require '../render/Path2Point.class.php';
	include 'GetMap_RasterImage.class.php';
}
/**Getmap PDF Class*/
if ( equalIgnoreCase($map_paras['format'],"application/pdf")){
	require '../render/Path2Point.class.php';
	include_once '../render/PDFRender.class.php';
	include 'GetMap_PDF.class.php';
}
/**Getmap PDF free Class*/
if ( equalIgnoreCase($map_paras['format'],"application/ezpdf") ){
	include '../plugin/www.ros.co.nz/class.ezpdf.php';
	require '../render/Path2Point.class.php';
	include '../render/EzPDFRender.class.php';
	include 'GetMap_EzPDF.class.php';
}
/**Getmap SWF Class*/
if ( equalIgnoreCase($map_paras['format'],"application/x-shockwave-flash") ){
	require '../render/Path2Point.class.php';
	include '../render/SWFRender.class.php';
	include 'GetMap_SWF.class.php';
}
if ( equalIgnoreCase($map_paras['format'],"model/vrml") OR equalIgnoreCase($map_paras['format'],"model/vrmlz")){
	if(equalIgnoreCase($map_paras['request'],"GetMap3D")){
		require '../render/Path2Point.class.php';
		require '../render/VRMLRender.class.php';
		include_once '../render/GZip.class.php';
		include 'GetMap_VRML.class.php';
	}
	else{
		$errornumber = 56;
		$errorexceptionstring = "The REQUEST should be GetMap3D, but ".$map_paras['request']." is given.";
		$sendexceptionclass->sendexception($errornumber,$errorexceptionstring);
	}
	
}
if ( equalIgnoreCase($map_paras['format'],"model/x3d+xml") OR equalIgnoreCase($map_paras['format'],"model/x3dz")){
	if(equalIgnoreCase($map_paras['request'],"GetMap3D")){
		include_once '../render/GZip.class.php';
		require_once '../render/X3DRender.class.php';
		include 'GetMap_X3D.class.php';
	}
	else{
		$errornumber = 57;
		$errorexceptionstring = "The REQUEST should be GetMap3D, but ".$map_paras['request']." is given.";
		$sendexceptionclass->sendexception($errornumber,$errorexceptionstring);
	}
	
}
if ( equalIgnoreCase($map_paras['format'],"application/vnd.google-earth.kml+xml") OR equalIgnoreCase($map_paras['format'],"application/vnd.google-earth.kmz")){
	if(equalIgnoreCase($map_paras['request'],"GetMap3D") OR equalIgnoreCase($map_paras['request'],"GetMap")){
		include_once '../parser/StyleReader.class.php';
		include_once '../models/RasterColor.class.php';
		include_once '../render/GZip.class.php';
		require_once '../render/KMLRender.class.php';
		
		include 'GetMap_KML.class.php';
	}
	else{
		$errornumber = 58;
		$errorexceptionstring = "The REQUEST should be GetMap3D, but ".$map_paras['request']." is given.";
		$sendexceptionclass->sendexception($errornumber,$errorexceptionstring);
	}
	
}

?>