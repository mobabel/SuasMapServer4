<?php
/**
 * X3DRender Class
 * Copyright (C) 2006-2007  LI Hui
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
 * @contact webmaster@easywms.com
 */
class X3DRender {
    private $minx;
    private $miny;
    private $maxx;
    private $maxy;
    private $width;
    private $height;
    private $enablestretchmap;
    private $enablePixelCoordinate;
    private $finaloutput;
    private $useStream;//set to false, then output gzip, ture means directly output map

    public $file_name;

    public $poix;
    public $poiy;
    public $poiz;
    public $pitch;
    public $yaw;
    public $roll;
    public $distance;
    public $aov;
    public $environment;
    public $skycolor;
    public $bgcolor;
    public $bgimage;

    public function setX3DRender($minx, $miny, $maxx, $maxy, $width, $height, $enablePixelCoordinate, $useStream,
        $poix, $poiy, $poiz, $pitch , $yaw, $roll, $distance, $aov, $environment, $skycolor, $bgcolor, $bgimage,
        $enablestretchmap)
    {
        $this->minx = $minx;
        $this->miny = $miny;
        $this->maxx = $maxx;
        $this->maxy = $maxy;
        $this->width = $width;
        $this->height = $height;
        $this->enablestretchmap = $enablestretchmap;
        $this->enablePixelCoordinate = $enablePixelCoordinate;
        $this->useStream = $useStream;

        $this->file_name = "../cache/test.x3d";

        $this->poix = $poix;
        $this->poiy = $poiy;
        $this->poiz = $poiz;
        $this->pitch = deg2rad($pitch);
        $this->yaw = deg2rad($yaw + 0); //+90
        $this->roll = deg2rad($roll);
        $this->distance = $distance;
        $this->aov = deg2rad($aov);
        $this->environment = strtoupper($environment);

        $this->skycolor = trim($skycolor);
        if ($this->skycolor == "") $this->skycolor = "0.0 0.0 1.0";

        $this->bgcolor = trim($bgcolor);
        if ($this->bgcolor == "") $this->bgcolor = "0.0 0.0 0.0";

        $this->bgimage = $bgimage;
        if ($this->bgimage != "")$this->bgimage = "../img/" . $this->bgimage;

        $this->CreateX3D();
    }

    /**
     *
     * @DESCRIPTION :Class Constructor.	**
     */
    public function X3DRender()
    {
    }

    function CreateX3D()
    {
        $this->X3DDocument();
        $this->X3DFragmentBegin();

        $this->SceneBegin();

        $viewx = $this->poix + $this->distance * round(sin($this->pitch), 4) * round(sin($this->yaw), 4);
        $viewy = $this->poiy - $this->distance * round(sin($this->pitch), 4) * round(cos($this->yaw), 4);
        $viewz = $this->poiz + $this->distance * round(cos($this->pitch), 4);
        //round($this->pitch/pi(), 4)." ".round($this->roll/pi(), 4).round(-$this->yaw/pi(), 4)

        if ($this->environment == "ON") {
            $this->Background("0.0 0.2 0.7,".$this->skycolor.", 1.0 1.0 1.0", "1.309, 1.571",
                "$this->bgcolor,$this->bgcolor,$this->bgcolor", "1.309, 1.571",
                $this->bgimage, $this->bgimage, $this->bgimage, $this->bgimage);
            $this->Viewpoint("$viewx $viewy $viewz", "0 0 1 ".round($this->roll/(2*pi()), 4) ,round($this->aov/(pi()/2), 4), "\"POI View\"");
        }

        //EXAMINE, WALK , FLY
        $this->NavigationInfo((($this->maxx-$this->minx)/100).", 2, 1", "TRUE", 3, "\"EXAMINE\" \"ANY\"");

        if ($this->environment == "ON") {
            $this->TransformBegin("1 1 1", "0 0 1 0", ($this->maxx/2+$this->minx/2)." ".($this->maxy/2+$this->miny/2)." ".($this->poiz), "1 0 0 ".round(-(pi()/2), 4), "0 -10 0", "0 0 0", "-1 -1 -1");
        }
        else{
	    $this->TransformBegin("1 1 1", "0 0 1 0", ($this->maxx/2+$this->minx/2)." ".($this->maxy/2+$this->miny/2)." ".($this->poiz), "1 0 0 0", "0 -10 0","0 0 0", "-1 -1 -1");
	}
        // create background
        $data_x = array(array($this->minx, $this->minx, $this->maxx, $this->maxx));
        $data_y = array(array($this->miny, $this->maxy, $this->maxy, $this->miny));
        $this->MultiPolygon($data_x, $data_y, 0, 1, array(4), $this->bgcolor, 0, 0.7);

    }

    public function setfinaloutput($out)
    {
        // this will output stream svg directly
        if ($this->useStream) {
            echo $out;
        }
        $this->finaloutput .= $out;

    }

    public function getfinaloutput()
    {
        return $this->finaloutput;
    }

    public function X3DDocument()
    {
        $this->setfinaloutput('<?xml version="1.0" encoding="utf-8"?>' . "\n");

        $this->setfinaloutput('<!DOCTYPE X3D PUBLIC "ISO//Web3D//DTD X3D 3.1//EN" "http://www.web3d.org/specifications/x3d-3.1.dtd">' . "\n");
    }

    /*
*
*$type 0: SVG
*      1: SVGT
*      3: SVGB
*/
    function X3DFragmentBegin()
    {
        if ($this->enablePixelCoordinate) {
            $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $this->minx, $this->miny, $this->enablestretchmap);
            $minxr = $coord[0];
            $minyr = $coord[1];
            $coord1 = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $this->maxx, $this->maxy, $this->enablestretchmap);
            $maxxr = $coord1[0];
            $maxyr = $coord1[1];
            $W = $maxxr - $minxr;
            $H = $maxyr - $minyr;
        } else {
            $W = $this->maxx - $this->minx;
            $H = $this->maxy - $this->miny;
        }
        $this->setfinaloutput('<X3D version="3.1" profile="Immersive" xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
		xsd:noNamespaceSchemaLocation="http://www.web3d.org/specifications/x3d-3.1.xsd">'."\n");

        $this->X3DHead();
    }

    function X3DFragmentEnd()
    {
        $this->setfinaloutput("</X3D>\n");
    }

    function X3DHead()
    {
        $this->setfinaloutput('<head> '."\n");
        $this->setfinaloutput('    <meta content="' . $this->file_name . '"    name="filename"/> '."\n");
        $this->setfinaloutput('    <meta content="SUAS MapServer outputted X3D map" name="description"/> '."\n");
        $this->setfinaloutput('    <meta content="'.date("Ymd").'" name="created"/> '."\n");
        $this->setfinaloutput('    <meta content="'.date("Ymd").'" name="revised"/> '."\n");
        $this->setfinaloutput('	   <meta content="LI Hui" name="author"/> '."\n");
        $this->setfinaloutput('    <meta content="http://suas.easywms.com" name="url"/> '."\n");
        $this->setfinaloutput('    <meta content="SUAS MapServer, http://suasdemo.easywms.com" name="generator"/> '."\n");
        // $this->setfinaloutput('    <meta name="license" content="license.html"/> ');
        $this->setfinaloutput('</head> '."\n");
    }

    function printId($id)
    {
        if ($id != "") {
            $this->setfinaloutput("id=\"$id\" ");
        }
    }

    function printStyle($style)
    {
        if ($style != "") {
            $this->setfinaloutput("style=\"$style\" ");
        }
    }

    function SceneBegin()
    {
        $this->setfinaloutput("<Scene>\n");
    }

    function SceneEnd()
    {
        $this->TransformEnd();
        $this->setfinaloutput("</Scene>\n");
    }

    function Viewpoint($p_position = "", $p_orientation = "0.0 0.0 1.0 0.0", $p_fieldOfView = "", $p_description = "POI View")
    {
        $this->setfinaloutput("<Viewpoint ");
        $this->setfinaloutput("position=\"$p_position\" ");
        $this->setfinaloutput("orientation=\"$p_orientation\" ");
        if ($p_fieldOfView != "")
            $this->setfinaloutput("fieldOfView=\"$p_fieldOfView\" ");

        $this->setfinaloutput("description=$p_description ");
        $this->setfinaloutput("/>\n");
    }

    function NavigationInfo($p_avatarSize = "0.25, 1.6, 0.75", $p_headlight = "TRUE", $p_speed = "1.0", $p_type = "\"EXAMINE\" \"ANY\"")
{
    $this->setfinaloutput("<NavigationInfo ");
    $this->setfinaloutput("avatarSize=\"$p_avatarSize\" ");
    $this->setfinaloutput("headlight=\"$p_headlight\" ");
    $this->setfinaloutput("speed=\"$p_speed\" ");
    $this->setfinaloutput("type='$p_type' ");
    $this->setfinaloutput("/>\n");
}

function TransformBegin($p_scale = "1 1 1", $p_scaleOrientation = "0 0 1 0", $p_center = "0 0 0", $p_rotation = "0 0 1 0", $p_translation = "0 0 0", $p_bboxCenter = "0 0 0", $p_bboxSize = "-1 -1 -1")
{
    $this->setfinaloutput("<Transform ");
    $this->setfinaloutput("scale=\"$p_scale\" ");
    $this->setfinaloutput("scaleOrientation=\"$p_scaleOrientation\" ");
    $this->setfinaloutput("center=\"$p_center\" ");
    $this->setfinaloutput("rotation=\"$p_rotation\" ");
    $this->setfinaloutput("translation=\"$p_translation\" ");
    $this->setfinaloutput("bboxCenter=\"$p_bboxCenter\" ");
    $this->setfinaloutput("bboxSize=\"$p_bboxSize\" ");
    $this->setfinaloutput(">\n");
}

function TransformEnd()
{
    $this->setfinaloutput("</Transform>\n");
}

function GroupBegin($id)
{
    $this->setfinaloutput("<Group DEF=\"$id\"");
    $this->setfinaloutput(">\n");
}

function GroupEnd()
{
    $this->setfinaloutput("</Group>\n");
}

function ShapeBegin()
{
    $this->setfinaloutput("<Shape>\n");
}

function ShapeEnd()
{
    $this->setfinaloutput("</Shape>\n");
}

/*
	function Appearance($p_material = "", $p_texture = "", $p_textureTransform = "")
    {
        $this->setfinaloutput("<Appearance>\n");

        if ($p_material != "")
            $this->Material($p_material);

        if ($p_texture != "") {
            $this->Texture($p_texture);
            $this->TextureTransform($p_textureTransform);
        }

        $this->setfinaloutput("</Appearance>\n");
    }
*/
function AppearanceBegin()
{
    $this->setfinaloutput("<Appearance>\n");
}
function AppearanceEnd()
{
    $this->setfinaloutput("</Appearance>\n");
}

function Material($p_diffuseColor = "0.8 0.8 0.8", $p_ambientIntensity = "0.2", $p_emissiveColor = "0.0 0.0 0.0", $p_specularColor = "0.0 0.0 0.0", $p_shininess = "0.2", $p_transparency = "0.0")
{
    $this->setfinaloutput("<Material ");
    $this->setfinaloutput("diffuseColor=\"$p_diffuseColor\" ");
    $this->setfinaloutput("ambientIntensity=\"$p_ambientIntensity\" ");
    $this->setfinaloutput("emissiveColor=\"$p_emissiveColor\" ");
    $this->setfinaloutput("specularColor=\"$p_specularColor\" ");
    $this->setfinaloutput("shininess=\"$p_shininess\" ");
    $this->setfinaloutput("transparency=\"$p_transparency\" ");
    $this->setfinaloutput("/>\n");
}
// <ImageTexture url='"earth-topo.png"'>
function Texture($p_url = "")
{
    $this->setfinaloutput("<ImageTexture ");
    $this->setfinaloutput("url=\'$p_url\' ");
    $this->setfinaloutput(">\n");
}
function TextureTransform()
{
}

function Background($p_skyColor = "", $p_skyAngle = "", $p_groundColor = "", $p_groundAngle = "",
        $p_frontUrl = "", $p_backUrl = "", $p_leftUrl = "", $p_rightUrl = "")
{
    $this->setfinaloutput("<Background ");
    $this->setfinaloutput("skyColor=\"$p_skyColor\" \n");
    $this->setfinaloutput("skyAngle=\"$p_skyAngle\" \n");
    $this->setfinaloutput("groundColor=\"$p_groundColor\" \n");
    $this->setfinaloutput("groundAngle=\"$p_groundAngle\" \n");
    $this->setfinaloutput("frontUrl=\"&quot;$p_frontUrl&quot;\" \n");
    $this->setfinaloutput("backUrl=\"&quot;$p_backUrl&quot;\" \n");
    $this->setfinaloutput("leftUrl=\"&quot;$p_leftUrl&quot;\" \n");
    $this->setfinaloutput("rightUrl=\"&quot;$p_rightUrl&quot;\" \n");
    $this->setfinaloutput("/>\n");
}
// ===================================================================================================
// Basic shape
// ===================================================================================================
function IndexedFaceSet($p_coordIndex = "", $p_coordinate = "",
    $p_creaseAngle = "0.0", $p_convex = "FALSE", $p_solid = "TRUE", $p_colorPerVertex = "FALSE", $p_ccw = "FALSE")
{
    $this->setfinaloutput("<IndexedFaceSet ");
    $this->setfinaloutput("coordIndex=\"$p_coordIndex\" ");
    //$this->setfinaloutput("creaseAngle=\"$p_creaseAngle\" ");
    //$this->setfinaloutput("convex=\"$p_convex\" ");
    //$this->setfinaloutput("solid=\"$p_solid\" ");
    //$this->setfinaloutput("colorPerVertex=\"$p_colorPerVertex\" ");
    //$this->setfinaloutput("ccw=\"$p_ccw\" ");
    $this->setfinaloutput(">\n");
    $this->Coordinate($p_coordinate);
    $this->setfinaloutput("</IndexedFaceSet>\n");
}

function Coordinate($p_point = "")
{
    $this->setfinaloutput("<Coordinate ");
    $this->setfinaloutput("point=\"$p_point\" ");
    $this->setfinaloutput("/>\n");
}

function Box($p_size = "1 1 1")
{
    $this->setfinaloutput("<Box ");
    $this->setfinaloutput("size=\"$p_size\" ");
    $this->setfinaloutput("/>\n");
}

function Sphere($p_radius = "1")
{
    $this->setfinaloutput("<Sphere ");
    $this->setfinaloutput("radius=\"$p_radius\" ");
    $this->setfinaloutput("/>\n");
}

function Cylinder($p_radius = "1.0", $p_height = "2.0", $p_side = "TRUE", $p_bottom = "TRUE", $p_top = "TRUE")
{
    $this->setfinaloutput("<Cylinder ");
    $this->setfinaloutput("radius=\"$p_radius\" ");
    $this->setfinaloutput("height=\"$p_height\" ");
    $this->setfinaloutput("side=\"$p_side\" ");
    $this->setfinaloutput("bottom=\"$p_bottom\" ");
    $this->setfinaloutput("top=\"$p_top\" ");
    $this->setfinaloutput("/>\n");
}

function Cone($p_bottomRadius = "1", $p_height = "2.0", $p_side = "TRUE", $p_bottom = "TRUE")
{
    $this->setfinaloutput("<Cone ");
    $this->setfinaloutput("bottomRadius=\"$p_bottomRadius\" ");
    $this->setfinaloutput("height=\"$p_height\" ");
    $this->setfinaloutput("side=\"$p_side\" ");
    $this->setfinaloutput("bottom=\"$p_bottom\" ");
    $this->setfinaloutput("/>\n");
}

function Text($p_string = "")
{
    $this->setfinaloutput("<Text ");
    $this->setfinaloutput("string=\"$p_string\" ");
    $this->setfinaloutput("/>\n");
}

function X3DText($x = 0, $y = 0, $elevation = 0, $text = "", $Font, $FontSize = 5, $angle = 0, $color)
{
    if($elevation=="")$elevation = 0;
    $this->TransformBegin("1 1 1", "0 0 1 0", "0 0 0", "0 0 1 " . round($angle / (2 * pi()), 4), "$x $y $elevation", "0 0 0", "-1 -1 -1");
    $this->ShapeBegin();
    $this->Text($text);
    $this->AppearanceBegin();
    $this->Material($color, "0.3", "0.0 0.0 0.0", "0.0 0.0 0.0", "0.3", "0");
    $this->AppearanceEnd();
    $this->ShapeEnd();
    $this->TransformEnd();
}

function MultiLinstring($x, $y, $elevation = 0, $lineNumber, $pointNumber, $stroke , $strokewidth, $fillColor, $blnFillLineString)
{
    $data_x = $x;
    $data_y = $y;
    if($elevation=="")$elevation = 0;
    // if not fill
    if ($blnFillLineString == 0) {
        for($i = 0;
            $i < $lineNumber;
            $i++) {
            // $coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
            $arraycl[$i] = getContourLine($data_x[$i], $data_y[$i], $pointNumber[$i], $strokewidth);
            $clx[$i] = $arraycl[$i][0];
            $cly[$i] = $arraycl[$i][1];
            $newpointNumber[$i] = $pointNumber[$i] * 2;
        }
        $this->MultiPolygon($clx, $cly, $elevation, $lineNumber, $newpointNumber, $fillColor, $strokewidth,0);
    } else {
        $this->MultiPolygon($x, $y, $elevation, $lineNumber, $pointNumber, $fillColor, $strokewidth, 0);
    }
}

function MultiPolygon($x, $y, $elevation = 0, $lineNumber, $pointNumber, $stroke , $strokewidth, $transparent=0)
{
    $data_x = $x;
    $data_y = $y;
    if($elevation=="")$elevation = 0;
    for($i = 0;$i < $lineNumber;$i++) {
        $coordindex[$i] = "";
        $pointpbt[$i] = "";
        $pointpb = "";
        $pointpt = "";
        $bottom = "";
        $top = "";
        for($j = 0;$j < $pointNumber[$i];$j++) {
            // $coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
            $coord[$j] = array($data_x[$i][$j], $data_y[$i][$j]);
            $Pixel_x[$j] = $coord[$j][0];
            $Pixel_y[$j] = $coord[$j][1];
            $valuep[$i][$j * 3] = $Pixel_x[$j];
            $valuep[$i][$j * 3 + 1] = $Pixel_y[$j];
            $valuepb[$i][$j * 3 + 2] = 0;
            $valuept[$i][$j * 3 + 2] = $elevation;
            if ($j != $pointNumber[$i]-1) {
                $pointpb .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1] . " " . $valuepb[$i][$j * 3 + 2] . ",";
                $pointpt .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1] . " " . $valuept[$i][$j * 3 + 2] . ",";
            } else {
                $pointpb .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1] . " " . $valuepb[$i][$j * 3 + 2];
                $pointpt .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1] . " " . $valuept[$i][$j * 3 + 2];
            }
        }
        // get $coordindex, from bottom to top, anti-clockwise
        for($j = 0, $k = $pointNumber[$i];
            $j < $pointNumber[$i], $k > 0;
            $j++, $k--) {
            if ($j != $pointNumber[$i]-1) {
                $coordindex[$i] .= $j . " " . ($j + 1) . " " . ($j + 1 + $pointNumber[$i]) . " " . ($j + $pointNumber[$i]) . " -1 ";
                // if($j!= 0){
                $bottom .= $j . " ";
                $top .= ($k + $pointNumber[$i]-1) . " ";
                // }
            } else {
                $coordindex[$i] .= $j . " " . "0" . " " . (0 + $pointNumber[$i]) . " " . ($j + $pointNumber[$i]) . " -1 ";
                $bottom .= $j . " -1 ";
                $top .= ($k + $pointNumber[$i]-1). " -1 ";
                $coordindex[$i] .= $bottom . $top;
            }
        }

        $pointpbt[$i] = $pointpb . ", " . $pointpt; //echo $pointpbt[$i];

        $this->ShapeBegin();
        $this->IndexedFaceSet($coordindex[$i], $pointpbt[$i], "0.0", "FALSE", "TRUE", "TRUE", "TRUE");
        $this->AppearanceBegin();
        $this->Material($stroke, "0.3", "0.0 0.0 0.0", "0.0 0.0 0.0", "0.3", "$transparent");
        $this->AppearanceEnd();
        $this->ShapeEnd();
    }
}

function Image($x, $y, $elevation = 0, $pointNumber, $imagelink, $alpha){
	    //it is 5, but vrml need 4, auto close it
        $pointNumber=4;
        
        $data_x = $x;
        $data_y = $y;
        if($elevation=="")$elevation = 0;
        for($i = 0;$i < $pointNumber;$i++) {
            // $coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
            $coord = array($data_x[$i], $data_y[$i]);
            $Pixel_x[$i] = $coord[0];
            $Pixel_y[$i] = $coord[1];
        }
        $dst_x = $Pixel_x[0];
        $dst_y = $Pixel_y[0];
        $dst_w = $Pixel_x[2] - $Pixel_x[0];
        $dst_h = $Pixel_y[2] - $Pixel_y[0];
        // if too small in big scale, doesnt display it
        if ($dst_w > 0 and $dst_h > 0)
            if ($imagelink != "") {
                $data_x = array(array($Pixel_x[0], $Pixel_x[1], $Pixel_x[2], $Pixel_x[3]));
                $data_y = array(array($Pixel_y[0], $Pixel_y[1], $Pixel_y[2], $Pixel_y[3]));
               
            	$point_text = "";
                for($j = 0;$j < $pointNumber;$j++) {
	                // $coord[$j] = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
	                $coord[$j] = array($data_x[0][$j], $data_y[0][$j]);
	
	                $Pixel_x[$j] = $coord[$j][0];
	                $Pixel_y[$j] = $coord[$j][1];
	                $valuep[$i][$j * 3] = $Pixel_x[$j];
	                $valuep[$i][$j * 3 + 1] = $Pixel_y[$j];
	
	                if ($j != $pointNumber[$i]-1) {
	                    $point_text .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1]. " ". $elevation. ", \n";
	                } else {
	                    $point_text .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1]. " ". $elevation. "\n";
	                }
            	}
            	
            	$coordindex = "0,1,2,3";               
                $point_texture = "[0 0,0 1,1 1,1 0]";
                
           	 	/*$texture = new Texture($imagelink);
            	$texturetransform = new TextureTransform();            	
            	$coordinate = new Coordinate($point_text);
            	$indexedfaceset = new IndexedFaceSet($coordindex, $coordinate, "0.0", "FALSE", "TRUE", "TRUE", "TRUE");
            	$textureCoordinate = new TextureCoordinate($point_texture);               
                $indexedfaceset->setTextureCoordinate($textureCoordinate) ;    
            	$a = new Appearance("NULL", $texture, $texturetransform);
            	$s = new Shape($a, $indexedfaceset);*/

		        $this->ShapeBegin();
		        $this->IndexedFaceSet($coordindex, $point_text, "0.0", "FALSE", "TRUE", "TRUE", "TRUE");
		        $this->AppearanceBegin();
		        //$this->Material($stroke, "0.3", "0.0 0.0 0.0", "0.0 0.0 0.0", "0.3", "$transparent");
		        $this->AppearanceEnd();
		        $this->ShapeEnd();
            }
	
}

function Point($x, $y, $elevation = 0, $pointNumber, $pointstyle, $color , $radius)
{
    $data_x = $x;
    $data_y = $y;
    if($elevation=="")$elevation = 0;
    $t1 = null;
    for($i = 0;
        $i < $pointNumber;
        $i++) {
        $box = null;
        $sph = null;
        $cone = null;
        $mt = null;
        $a1 = null;
        $s1 = null;
        // $coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
        $coord = array($data_x[$i], $data_y[$i]);
        $Pixel_x = $coord[0];
        $Pixel_y = $coord[1];
        switch ($pointstyle) {
            case 'CIRCLE': {
                    $this->TransformBegin("1 1 1", "0 0 1 0", "0 0 0", "0 0 1 0", "$Pixel_x $Pixel_y $radius", "0 0 0", "-1 -1 -1");
                    $this->ShapeBegin();
                    $this->Sphere($radius);
                    $this->AppearanceBegin();
                    $this->Material($color, "0.3", "0.0 0.0 0.0", "0.0 0.0 0.0", "0.3", "0");
                    $this->AppearanceEnd();
                    $this->ShapeEnd();
                    $this->TransformEnd();
                }
                break;
            case 'SQUARE': {
                    $this->TransformBegin("1 1 1", "0 0 1 0", "0 0 0", "0 0 1 0", "$Pixel_x $Pixel_y ".$radius/2, "0 0 0", "-1 -1 -1");
                    $this->ShapeBegin();
                    $this->Box("$radius $radius $radius");
                    $this->AppearanceBegin();
                    $this->Material($color, "0.3", "0.0 0.0 0.0", "0.0 0.0 0.0", "0.3", "0");
                    $this->AppearanceEnd();
                    $this->ShapeEnd();
                    $this->TransformEnd();
                }
                break;
            case 'TRIANGLE': {
                    $this->TransformBegin("1 1 1", "0 0 1 0", "0 0 0", "0 0 1 0", "$Pixel_x $Pixel_y $radius", "0 0 0", "-1 -1 -1");
                    $this->ShapeBegin();
                    $this->Cone($radius, $radius * 2, "TRUE", "TRUE");
                    $this->AppearanceBegin();
                    $this->Material($color, "0.3", "0.0 0.0 0.0", "0.0 0.0 0.0", "0.3", "0");
                    $this->AppearanceEnd();
                    $this->ShapeEnd();
                    $this->TransformEnd();
                }
                break;
            case 'STAR': {
                    $tem = getShapeFiveCornerStar($Pixel_x, $Pixel_y, $radius);
                    $this->MultiPolygon(array($tem[0]), array($tem[1]), $elevation, 1, array(10), $color, $radius);
                }
                break;
            case 'CROSS': {
                    $tem = getShapeCross($Pixel_x, $Pixel_y, $radius);print_r($tem[0]);
                    $this->MultiPolygon(array($tem[0]), array($tem[1]), $elevation, 1, array(12), $color, $radius);
                }
                break;
            case 'X': {
                    $tem = getShapeX($Pixel_x, $Pixel_y, $radius);
                    $this->MultiPolygon(array($tem[0]), array($tem[1]), $elevation, 1, array(12), $color, $radius);
                }
                break;
            default: {
                    $this->TransformBegin("1 1 1", "0 0 1 0", "0 0 0", "0 0 1 0", "$Pixel_x $Pixel_y $radius", "0 0 0", "-1 -1 -1");
                    $this->ShapeBegin();
                    $this->Sphere($radius);
                    $this->AppearanceBegin();
                    $this->Material($color, "0.3", "0.0 0.0 0.0", "0.0 0.0 0.0", "0.3", "0");
                    $this->AppearanceEnd();
                    $this->ShapeEnd();
                    $this->TransformEnd();
                }
        }
    }
}
}

?>