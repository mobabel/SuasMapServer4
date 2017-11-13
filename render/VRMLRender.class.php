<?php
/**
 * VRMLRender.class.php
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
 * @copyright (C) 2006-2007  leelight
 * @contact webmaster@easywms.com
 * Part of code is from Vrml class written by Charly Pache <3d@pache.ch>
 */

class VRMLRender {
    public $world_info;
    public $nodes;
    public $subTransformNodes;
    public $file_name;
    public $background;
    public $viewpoint;
    public $navigationinfo;

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
    public $gzip;
    public $finaloutput;

    public function setRender($minx, $miny, $maxx, $maxy, $width, $height,
        $poix, $poiy, $poiz, $pitch , $yaw, $roll, $distance, $aov, $environment, $skycolor, $bgcolor, $bgimage,
        $enablestretchmap, $gzip, $outputEncodeCountry)
    {
        $this->minx = $minx;
        $this->miny = $miny;
        $this->maxx = $maxx;
        $this->maxy = $maxy;
        $this->width = $width;
        $this->height = $height;
        $this->enablestretchmap = $enablestretchmap;
        $this->gzip = $gzip;

        $this->fontpath = "../files/fonts/" . $outputEncodeCountry . "/";
        $this->fonttype = "arial";
        $this->fontext = ".ttf";
        $this->font = $this->fontpath . $this->fonttype . $this->fontext;

        $this->poix = $poix;
        $this->poiy = $poiy;
        $this->poiz = $poiz;
        $this->pitch = deg2rad($pitch);
        $this->yaw = deg2rad($yaw+0);//+90
        $this->roll = deg2rad($roll);
        $this->distance = $distance;
        $this->aov = deg2rad($aov);
        $this->environment = strtoupper($environment);

        $this->skycolor = trim($skycolor);
        if ($this->skycolor == "") $this->skycolor = "0.0 0.0 1.0";

        $this->bgcolor = trim($bgcolor);
        if ($this->bgcolor == "") $this->bgcolor = "0.0 0.0 0.0";

        $this->bgimage = $bgimage;
        if($this->bgimage!="")$this->bgimage="\"../img/".$this->bgimage."\"";


        $this->CreateVRML();
    }

    function VRMLRender()
    {
    }

    function CreateVRML()
    {
        $p_title = "\"".SUAS_NAME."\"";
        $p_info = array("\"Link: ".SUAS_COPYRIGHT."\"", "\"Author: \"");
        $p_file_name = "../cache/test.wrl";

        $this->world_info = new WorldInfo($p_title, $p_info);
        $this->nodes = array();
        $this->subTransformNodes = array();

        $this->addFirstNode($this->world_info);

        if ($this->environment == "ON") {
            $this->background = new Background(array(0 => "0.0 0.2 0.7", $this->skycolor, "1.0 1.0 1.0"), array(0 => 1.309, 1.571),
                array(0 => "$this->bgcolor", "$this->bgcolor", "$this->bgcolor"), array(0 => 1.309, 1.571),
                $this->bgimage, $this->bgimage, $this->bgimage, $this->bgimage);
            $this->addFirstNode($this->background);
        }

        $viewx = $this->poix + $this->distance * round(sin($this->pitch), 4) * round(sin($this->yaw), 4);
        $viewy = $this->poiy - $this->distance * round(sin($this->pitch), 4) * round(cos($this->yaw), 4);
        $viewz = $this->poiz + $this->distance * round(cos($this->pitch), 4);
        //round($this->pitch/pi(), 4)." ".round($this->roll/pi(), 4).round(-$this->yaw/pi(), 4)
        $this->viewpoint = new Viewpoint("$viewx $viewy $viewz", "0 0 1 ".round($this->roll/(2*pi()), 4) ,round($this->aov/(pi()/2), 4), "\"POI View\"");
        if ($this->environment == "ON") {
            $this->addFirstNode($this->viewpoint);
        }

        //EXAMINE, WALK , FLY
        $this->navigationinfo = new NavigationInfo(array(0 => ($this->maxx-$this->minx)/20, 2, 1), "TRUE", 3, array(0 => "\"EXAMINE\"", "\"ANY\""));
        $this->addFirstNode($this->navigationinfo);


        // create background
        $data_x = array(array($this->minx, $this->minx, $this->maxx, $this->maxx));
        $data_y = array(array($this->miny, $this->maxy, $this->maxy, $this->miny));
        $mp = new MultiPolygon();
        $backgroud = $mp->createMultiPolygon($data_x, $data_y, 1, array(4), 0, $this->bgcolor, 0);
        $backgroud[0]->attributes["appearance"]->attributes["material"]->setTransparency("0.7");

/*
        $box = new Box(($this->maxx-$this->minx)." 0.0000000000001 ".($this->maxy-$this->miny));
        $mt = new Material($this->bgcolor, "0.2", "0.0 0.0 0.0 ", "0.0 0.0 0.0 ", "0.3", "0.8");
        $a1 = new Appearance($mt);
        $s1 = new Shape($a1, $box);
        $t1 = new Transform(array($s1, $a1));
*/

        $this->addNode($backgroud[0]);

        $this->file_name = $p_file_name;
    }

    function addFirstNode($p_node)
    {
        if (is_array($p_node)) {
            foreach($p_node as $node)
            array_push($this->nodes, $node);
        } else {
            array_push($this->nodes, $p_node);
        }
    }
    function addNode($p_node)
    {
        if (is_array($p_node)) {
            foreach($p_node as $node)
            array_push($this->subTransformNodes, $node);
        } else {
            array_push($this->subTransformNodes, $p_node);
        }
    }
    function generate()
    {
        $page = "#VRML V2.0 utf8";
        $lasttransform = new Transform($this->subTransformNodes);
        # Shift world down to avoid colliding with floor
        $lasttransform->setTranslation("0 -10 0");
        //$lasttransform->setCenter(($viewx)." ".($viewy)." 0");
        #$lasttransform->setCenter(($this->poix)." ".($this->poiy)." ".($this->poiz));
        $lasttransform->setCenter(($this->maxx/2+$this->minx/2)." ".($this->maxy/2+$this->miny/2)." ".($this->poiz));
        if ($this->environment == "ON") {
        $lasttransform->setRotation("1 0 0 ".round(-(pi()/2), 4));
        }

        $this->addFirstNode($lasttransform);

        foreach($this->nodes as $node) {
            $page .= $node->getNode();
        }
/*

        $fp = fopen ("./" . $this->file_name, "w");
        fwrite($fp, $page);
        fclose($fp);


*/
        if($this->gzip){
            $this->finaloutput = $page;
		}else{
			echo $page;
		}

    }

    public function getfinaloutput()
    {
        return $this->finaloutput;
    }
}
class Node {
    public $attributes = array();
    public $node_name = "";
    public $sub_nodes = array();

    function Node()
    {
        $this->sub_nodes = array();
    }
    function addNode($p_node)
    {
        array_push($this->sub_nodes, $p_node);
    }
    function getNode()
    {
        $page = "\n" . $this->node_name . "{";
        foreach ($this->attributes as $name => $value) {
            if (is_array($value)) {
                $page .= "\n" . $name . " [";
                foreach ($value as $single_value) {
                    if (is_object($single_value)) {
                        $page .= "\n" . " " . $single_value->getNode();
                    } else {
                        $page .= "\n" . "" . $single_value;
                    }
                }
                $page .= "\n" . "]";
            } else if (is_object($value)) {
                $page .= "\n" . $name . " " . $value->getNode();
            } else {
                $page .= "\n" . $name . " " . $value;
            }
        }
        $page .= "\n" . "}";
        return $page;
    }
}
class WorldInfo extends Node {
    function WorldInfo($p_title = "", $p_info = array())
    {
        $this->attributes = array("title" => $p_title, "info" => $p_info);
        $this->node_name = "WorldInfo";
    }
}
class Viewpoint extends Node {
    function Viewpoint($p_position = "", $p_orientation = "0.0 0.0 1.0 0.0", $p_fieldOfView = "", $p_description = "")
    {
        $this->attributes = array("position" => $p_position, "orientation" => $p_orientation, "fieldOfView" => $p_fieldOfView, "description" => $p_description);
        $this->node_name = "Viewpoint";
    }
}
class Background extends Node {
    function Background($p_skyColor = array(), $p_skyAngle = array(), $p_groundColor = array(), $p_groundAngle = array(),
        $p_frontUrl = "", $p_backUrl = "", $p_leftUrl = "", $p_rightUrl = "")
    {
        $this->attributes = array("skyColor" => $p_skyColor, "skyAngle" => $p_skyAngle, "groundColor" => $p_groundColor, "groundAngle" => $p_groundAngle,
            "frontUrl" => $p_frontUrl, "backUrl" => $p_backUrl, "leftUrl" => $p_leftUrl, "rightUrl" => $p_rightUrl);
        $this->node_name = "Background";
    }
    function addEnvironment($p_frontUrl)
    {
        $this->attributes["frontUrl"] = $p_frontUrl;
    }
}
class NavigationInfo extends Node {
    function NavigationInfo($p_avatarSize = array(0 => 0.25, 1.6, 0.75), $p_headlight = "TRUE", $p_speed = "1.0", $p_type = array(0 => "\"WALK\"", "\"ANY\""))
    {
        $this->attributes = array("avatarSize" => $p_avatarSize, "headlight" => $p_headlight, "speed" => $p_speed, "type" => $p_type);
        $this->node_name = "NavigationInfo";
    }
}

class Shape extends Node {
    function Shape($p_appearance = "NULL", $p_geometry = "NULL")
    {
        $this->attributes = array("appearance" => $p_appearance, "geometry" => $p_geometry);
        $this->node_name = "Shape";
    }
}
class Script extends Node {
    /*
		Script {
		url           []
		directOutput  FALSE
		mustEvaluate  FALSE
		# And any number of:
		eventIn      eventType eventName
		field        fieldType fieldName initialValue
		eventOut     eventType eventName
		}
	*/
}
class Appearance extends Node {
    function Appearance($p_material = "NULL", $p_texture = "NULL", $p_textureTransform = "NULL")
    {
        $this->attributes = array("material" => $p_material, "texture" => $p_texture, "textureTransform" => $p_textureTransform);
        $this->node_name = "Appearance";
    }
}
class Material extends Node {
    function Material($p_diffuseColor = "0.8 0.8 0.8", $p_ambientIntensity = "0.2", $p_emissiveColor = "0.0 0.0 0.0", $p_specularColor = "0.0 0.0 0.0", $p_shininess = "0.2", $p_transparency = "0.0")
    {
        $this->attributes = array("diffuseColor" => $p_diffuseColor, "ambientIntensity" => $p_ambientIntensity, "emissiveColor" => $p_emissiveColor, "specularColor" => $p_specularColor, "shininess" => $p_shininess, "transparency" => $p_transparency);
        $this->node_name = "Material";
    }
    function setTransparency($p_transparency)
    {
        $this->attributes["transparency"] = $p_transparency;
    }
}
class Texture extends Node {
    function Texture($p_url = "NULL")
    {
        $this->attributes = array("url" => $p_url);
        $this->node_name = "ImageTexture";
    }
    function setTexture($p_url)
    {
        $this->attributes["url"] = $p_url;
    }    
    function setRepeat($repeatS = "FALSE", $repeatT = "FALSE")
    {
        $this->attributes["repeatS"] = $repeatS;
        $this->attributes["repeatT"] = $repeatT;
    }
}
class TextureTransform extends Node {
	function TextureTransform($scale = "1.0 1.0", $rotation = "0.0", $center = "0.0 0.0", $translation = "0.0 0.0")
    {
        $this->attributes = array("scale" => $scale, "rotation" => $rotation, "center" => $center, "translation" => $translation);
        $this->node_name = "TextureTransform";
    }
    function setScale($scale)
    {
        $this->attributes["scale"] = $scale;
    }
	function setTranslation($translation)
    {
        $this->attributes["translation"] = $translation;
    }
    function setRotation($rotation)
    {
        $this->attributes["rotation"] = $rotation;
    }
    function setCenter($center)
    {
        $this->attributes["center"] = $center;
    }
}

class TextureCoordinate extends Node {
    function TextureCoordinate($p_point = "")
    {
        $this->attributes = array("point" => $p_point);
        $this->node_name = "TextureCoordinate";
    }
}

class Transform extends Node {
    function Transform($p_children = "NULL", $p_scale = "1 1 1", $p_scaleOrientation = "0 0 1 0", $p_center = "0 0 0", $p_rotation = "0 0 1 0", $p_translation = "0 0 0", $p_bboxCenter = "0 0 0", $p_bboxSize = "-1 -1 -1")
    {
        $this->attributes = array("scale" => $p_scale, "scaleOrientation" => $p_scaleOrientation, "center" => $p_center, "rotation" => $p_rotation, "translation" => $p_translation, "bboxCenter" => $p_bboxCenter, "bboxSize" => $p_bboxSize, "children" => $p_children);
        $this->node_name = "Transform";
    }
    function setTranslation($p_translation)
    {
        $this->attributes["translation"] = $p_translation;
    }
    function setRotation($p_rotation)
    {
        $this->attributes["rotation"] = $p_rotation;
    }
    function setCenter($p_center)
    {
        $this->attributes["center"] = $p_center;
    }
}
// +------------------------------------------------------+
// |   geometry
// +------------------------------------------------------+
class Box extends Node {
    function Box($p_size = "1 1 1")
    {
        $this->attributes = array("size" => $p_size);
        $this->node_name = "Box";
    }
}
class Sphere extends Node {
    function Sphere($p_radius = "1")
    {
        $this->attributes = array("radius" => $p_radius);
        $this->node_name = "Sphere";
    }
}
class Cylinder extends Node {
    function Cylinder($p_radius = "1.0", $p_height = "2.0", $p_side = "TRUE", $p_bottom = "TRUE", $p_top = "TRUE")
    {
        $this->attributes = array("radius" => $p_radius, "height" => $p_height, "side" => $p_side, "bottom" => $p_bottom, "top" => $p_top);
        $this->node_name = "Cylinder";
    }
}
class Cone extends Node {
    function Cone($p_bottomRadius = "1", $p_height = "2.0", $p_side = "TRUE", $p_bottom = "TRUE")
    {
        $this->attributes = array("bottomRadius" => $p_bottomRadius, "height" => $p_height, "side" => $p_side, "bottom" => $p_bottom);
        $this->node_name = "Cone";
    }
}
class Text extends Node {
    function Text($p_string = "", $p_fontStyle = "NULL")
    {
        $this->attributes = array("string" => "\"" . $p_string . "\"", "fontStyle" => $p_fontStyle);
        $this->node_name = "Text";
    }
}
/**
 * FontStyle is used in Text node
 * family: SERIF, SANS, TYPEWRITER
 * style: PLAIN, BOLD, ITALIC, BOLDITALIC
 * justify: FIRST", BEGIN, MIDDLE, END
 * language: en, en_US, zh
 */
class FontStyle extends Node {
    function FontStyle($p_family = "\"SANS\"", $p_style = "\"PLAIN\"", $p_justify = "\"MIDDLE\"", $p_language = "\"en\"", $p_size = "5.0", $p_horizontal = "TRUE")
    {
        $this->attributes = array("family" => $p_family, "style" => $p_style, "justify" => $p_justify, "language" => $p_language, "size" => $p_size, "horizontal" => $p_horizontal);
        $this->node_name = "FontStyle";
    }
}

class IndexedFaceSet extends Node {
    function IndexedFaceSet($p_coordIndex = "NULL", $p_Coordinate = "NULL",
        $p_creaseAngle = "0.0", $p_convex = "FALSE", $p_solid = "TRUE", $p_colorPerVertex = "FALSE", $p_ccw = "FALSE")
    {
        $this->attributes = array("coordIndex" => $p_coordIndex, "coord" => $p_Coordinate,
            "creaseAngle" => $p_creaseAngle, "convex" => $p_convex, "solid" => $p_solid, "colorPerVertex" => $p_colorPerVertex, "ccw" => $p_ccw);
        $this->node_name = "IndexedFaceSet";
    }
    function setTextureCoordinate($p_TextureCoordinate = "NULL"){
    	$this->attributes["texCoord"] = $p_TextureCoordinate;
    }
}
/**
 * Coordinate is used in IndexedFaceSet with coordIndex
 */
class Coordinate extends Node {
    function Coordinate($p_point = "")
    {
        $this->attributes = array("point" => $p_point);
        $this->node_name = "Coordinate";
    }
}

class MultiLinstring {
	function MultiLinstring(){
		
	}
	
    function createMultiLinstring($x, $y, $lineNumber, $pointNumber, $elevation, $stroke , $strokewidth, $fillColor, $blnFillLineString)
    {
        $data_x = $x;
        $data_y = $y;
        if($elevation=="")$elevation = 0;
        // if not fill
        if ($blnFillLineString == 0) {
            for($i = 0;$i < $lineNumber;$i++) {
                $arraycl[$i] = getContourLine($data_x[$i], $data_y[$i], $pointNumber[$i], $strokewidth);
                $clx[$i] = $arraycl[$i][0];
                $cly[$i] = $arraycl[$i][1];
                $newpointNumber[$i] = $pointNumber[$i] * 2;
            }
            $mp = new MultiPolygon();
            return $xx = $mp->createMultiPolygon($clx, $cly, $lineNumber, $newpointNumber, $elevation, $fillColor, $strokewidth);
        } else {
            $mp = new MultiPolygon();
            return $xx = $mp->createMultiPolygon($x, $y, $lineNumber, $pointNumber, $elevation, $fillColor, $strokewidth);
        }
    }
}

class MultiPolygon {
	function MultiPolygon(){
	}
	
    function createMultiPolygon($x, $y, $lineNumber, $pointNumber, $elevation, $stroke , $strokewidth)
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
                // $coord[$j] = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
                $coord[$j] = array($data_x[$i][$j], $data_y[$i][$j]);

                $Pixel_x[$j] = $coord[$j][0];
                $Pixel_y[$j] = $coord[$j][1];
                $valuep[$i][$j * 3] = $Pixel_x[$j];
                $valuep[$i][$j * 3 + 1] = $Pixel_y[$j];
                $valuepb[$i][$j * 3 + 2] = 0;
                $valuept[$i][$j * 3 + 2] = $elevation;

                if ($j != $pointNumber[$i]-1) {
                    $pointpb .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1] . " " . $valuepb[$i][$j * 3 + 2] . ",\n";
                    $pointpt .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1] . " " . $valuept[$i][$j * 3 + 2] . ",\n";
                } else {
                    $pointpb .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1] . " " . $valuepb[$i][$j * 3 + 2] . "\n";
                    $pointpt .= $valuep[$i][$j * 3] . " " . $valuep[$i][$j * 3 + 1] . " " . $valuept[$i][$j * 3 + 2] . "\n";
                }
            }
            // get $coordindex, from bottom to top, anti-clockwise
            for($j = 0, $k = $pointNumber[$i];$j < $pointNumber[$i], $k > 0;$j++, $k--) {
                if ($j != $pointNumber[$i]-1) {
                    $coordindex[$i] .= $j . "," . ($j + 1) . "," . ($j + 1 + $pointNumber[$i]) . "," . ($j + $pointNumber[$i]) . ", -1, \n";
                    $bottom .= $j . ", ";
                    $top .= ($k + $pointNumber[$i]-1) . ",";
                } else {
                    $coordindex[$i] .= $j . "," . "0" . "," . (0 + $pointNumber[$i]) . "," . ($j + $pointNumber[$i]) . ", -1, \n";
                    $bottom .= $j . ", -1 , \n";
                    $top .= ($k + $pointNumber[$i]-1) . ", -1 \n";
                    $coordindex[$i] .= $bottom . $top;
                }
            }
            $coordindex[$i] = "\n[ " . $coordindex[$i] . " ]\n";
            $pointpbt[$i] = "\n[ " . $pointpb . "," . $pointpt . " ]\n"; //echo $pointpbt[$i];

			$point_text = /*"[0 0,1 0,1 1,0 1]";*/"\n[ " . $point_text . " ]\n";
            //for texture
            //$t[$i] = new Texture("");
            //$tt[$i] = new TextureTransform();
            //$p_TextureCoordinate[$i] = new TextureCoordinate($point_text);
            
            $coordinate[$i] = new Coordinate($pointpbt[$i]);
            $indexedfaceset[$i] = new IndexedFaceSet($coordindex[$i], $coordinate[$i], "0.0", "FALSE", "TRUE", "TRUE", "TRUE");
            
            //$indexedfaceset[$i]->setTextureCoordinate($p_TextureCoordinate[$i]) ;
                       
            $m[$i] = new Material($stroke, "0.3", "0.0 0.0 0.0", "0.0 0.0 0.0", "0.3", "0");           
            $a[$i] = new Appearance($m[$i]/*, $t[$i], $tt[$i]*/);
            $s[$i] = new Shape($a[$i], $indexedfaceset[$i]);


            //$t[$i] = new Transform(array($s[$i], $a[$i]));
			//$t[$i]->setRotation("1 0 0 ".round((pi()/2), 4));
			//$t[$i]->setCenter("471934.833616 243510.309574 0");
        }
        // print_r($s);
        return $s;
    }
}

class FreeText {

    function CreateFreeText($x, $y, $elevation = 0, $Content, $Font, $FontSize = 5, $angle = 0, $color, $outputEncodeCountry)
    {
        $fs = null;
        $tt = null;
        $m1 = null;
        $a1 = null;
        $s1 = null;
        $t1 = null;
        if($elevation=="")$elevation = 0;
        // $coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $x, $y, $this->enablestretchmap);
        $fs = new FontStyle("\"SANS\"", "\"PLAIN\"", "\"MIDDLE\"", $outputEncodeCountry, $FontSize, "TRUE");
        $tt = new Text($Content, $fs);

        $m1 = new Material($color, "0.3", "0.0 0.0 0.0 ", "0.0 0.0 0.0 ", "0.3", "0");
        $a1 = new Appearance($m1);
        $s1 = new Shape($a1, $tt);

        $t1 = new Transform(array($s1, $a1));
        $t1->setTranslation("$x $y $elevation");
        $t1->setRotation("0 0 1 " . round($angle / (2 * pi()), 4));

        return $t1;
    }
}

class Point {
    function CreatePoint($x, $y, $elevation = 0, $pointNumber, $pointstyle, $color , $radius)
    {
        $data_x = $x;
        $data_y = $y;
        if($elevation=="")$elevation = 0;
        $t1 = null;
        for($i = 0;$i < $pointNumber;$i++) {
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
                        $sph = new Sphere("$radius");
                        $mt = new Material($color, "0.2", "0.0 0.0 0.0 ", "0.0 0.0 0.0 ", "0.3", "0");
                        $a1 = new Appearance($mt);
                        $s1 = new Shape($a1, $sph);

                        $t1[$i] = new Transform(array($s1, $a1));
                        $t1[$i]->setTranslation("$Pixel_x $Pixel_y ".$radius);
                    }
                    break;
                case 'SQUARE': {
                        $box = new Box("$radius $radius $radius");
                        $mt = new Material($color, "0.2", "0.0 0.0 0.0 ", "0.0 0.0 0.0 ", "0.3", "0");
                        $a1 = new Appearance($mt);
                        $s1 = new Shape($a1, $box);

                        $t1[$i] = new Transform(array($s1, $a1));
                        $t1[$i]->setTranslation("$Pixel_x $Pixel_y ".$radius/2);
                        //$t1[$i]->setCenter("471934.833616 243510.309574 0");
                        //$t1[$i]->setRotation("1 0 0 ".round((pi()/2), 4));
                    }
                    break;
                case 'TRIANGLE': {
                        $cone = new Cone($radius, $radius * 2, "TRUE", "TRUE");
                        $mt = new Material($color, "0.2", "0.0 0.0 0.0 ", "0.0 0.0 0.0 ", "0.3", "0");
                        $a1 = new Appearance($mt);
                        $s1 = new Shape($a1, $cone);

                        $t1[$i] = new Transform(array($s1, $a1));
                        $t1[$i]->setTranslation("$Pixel_x $Pixel_y $radius");
						$t1[$i]->setRotation("1 0 0 ".round((pi()/2), 4));
                    }
                    break;
                case 'STAR': {
                        $tem = getShapeFiveCornerStar($Pixel_x, $Pixel_y, $radius);

                        $mp = new MultiPolygon();
                        $pl = $mp->createMultiPolygon(array($tem[0]), array($tem[1]), 1, array(10), $elevation, $color, $radius);
                        $t1[$i] = $pl[0];
                    }
                    break;
                case 'CROSS': {
                        $tem = getShapeCross($Pixel_x, $Pixel_y, $radius);print_r($tem[0]);

                        $mp = new MultiPolygon();
                        $pl = $mp->createMultiPolygon(array($tem[0]), array($tem[1]), 1, array(12), $elevation, $color, $radius);
                        $t1[$i] = $pl[0];
                    }
                    break;
                case 'X': {
                        $tem = getShapeX($Pixel_x, $Pixel_y, $radius);

                        $mp = new MultiPolygon();
                        $pl = $mp->createMultiPolygon(array($tem[0]), array($tem[1]), 1, array(12), $elevation, $color, $radius);
                        $t1[$i] = $pl[0];
                    }
                    break;
                default: {
                        $box = new Box("$radius $radius $radius");
                        $mt = new Material($color, "0.2", "0.0 0.0 0.0 ", "0.0 0.0 0.0 ", "0.3", "0");
                        $a1 = new Appearance($mt);
                        $s1 = new Shape($a1, $box);

                        $t1[$i] = new Transform(array($s1, $a1));
                        $t1[$i]->setTranslation("$Pixel_x $Pixel_y 0");
                    }
            }
        }
        return $t1;
    }
}
class Image {
    public function createImage($x, $y, $elevation = 0, $pointNumber, $imagelink, $alpha, $color) // $alpha 0-100, 0 does nothing
    {
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
               
                //textrue
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
            	
            	$coordindex = "\n[0,1,2,3]\n";
            	$point_text = "\n[ " . $point_text . " ]\n"; 
                
                $point_texture = "[0 0,0 1,1 1,1 0]";
           	 	$texture = new Texture($imagelink);
            	$texturetransform = new TextureTransform();
            	
            	$coordinate = new Coordinate($point_text);
            	$indexedfaceset = new IndexedFaceSet($coordindex, $coordinate, "0.0", "FALSE", "TRUE", "TRUE", "TRUE");
            	$textureCoordinate = new TextureCoordinate($point_texture);
                
                $indexedfaceset->setTextureCoordinate($textureCoordinate) ;    
            	$a = new Appearance("NULL", $texture, $texturetransform);
            	$s = new Shape($a, $indexedfaceset);
            	//$t = new Transform(array($s, $a));
                
                //$mp = new MultiPolygon();
                //$img = $mp->createMultiPolygon($data_x, $data_y, 1, array(4), $elevation, /*$this->bgcolor*/"1.0 0.0 0.0", 0);
                //$img[0]->attributes["appearance"]->attributes["material"]->setTransparency($alpha);
                //$img[0]->attributes["appearance"]->attributes["texture"]->setTexture($imagelink);
                //$img[0]->attributes["appearance"]->attributes["texture"]->setRepeat("FALSE", "FALSE");
            }
            //return $img[0];
            return $s;
     }
}

?>
<?php
/*

include '../Models/CommonFormula.class.php';
$v = new VRMLRender();

$v->setRender(0, 0, 100, 100, 500, 500,
        $poix, $poiy, $poiz, $pitch , $yaw, $roll, $distance, $aov, $environment, $color_skycolor, $color_bgcolor, $bgimage,
        $enablestretchmap);

$b1 = new Box("3 5 2");
// $v->addNode($b1);
$m1 = new Material("0.3 0.5 0.8", "0.2", "0.0 0.0 0.0 ", "0.0 0.0 0.0 ", "0.2", "0");
$a1 = new Appearance($m1);
$s1 = new Shape($a1, $b1);
// $v->addNode($s1);
// add multipolygon
$x = array(array(1, 2, 2, 1), array(4, 5, 5));
$y = array(array(1, 1, 2, 2), array(2, 2, 3));
$mp = new MultiPolygon();
$xx = $mp->createMultiPolygon($x, $y, 2, array(4, 3), 2, "", 1);
for($i = 0;$i < count($xx);$i++) {
    //$v->addNode($xx[$i]);
}


//$x = array(array(10, 20, 20, 30), array(50, 60));
//$y = array(array(10, 10, 20, 30), array(50, 60));
$x = array(array(10, 20, 20, 30, 40));
$y = array(array(10, 10, 20, 30, 40));
$x = array(array(1942.10192083, 1931.5790706, 1921.16636902, 1912.38599067, 1898.36446424,1889.08882878));
$y = array(array(-423.00009788, -434.525124323, -447.989824635, -458.836174363, -463.689779664,-458.779149125));
$ml = new MultiLinstring();
$xxx = $ml->createMultiLinstring($x, $y, 1, array(6), 2, "0.3 0.5 0.8", 8, "0.3 0.5 0.8", 0);
for($i = 0;$i < count($xxx);$i++) {
    $v->addNode($xxx[$i]);
}
*/

    /*
$m2 = new Material("0.7 0.2 0.2","0.2","0.0 0.0 0.0 ","0.0 0.0 0.0 ","0.2","0.4");
$a2 = new Appearance($m2);
$b2 = new Box("4 4 4");
$s2 = new Shape($a2,$b2);

$sph1 = new Sphere("3");
$s3 = new Shape($a2,$sph1);

$m2->setTransparency("0");
$a3 = new Appearance($m2);
$cyl1 = new Cylinder("0.1","7");
$s4 = new Shape($a3,$cyl1);

$t1 = new Transform(array($s2,$s3));
$t1->setTranslation("-6 0 0");

$t2 = new Transform(array($s1));
$t2->setTranslation("6 0 0");

$t3 = new Transform(array($s4));
$t3->setTranslation("6 -3 0");

$v->addNode(array($t1,$t2,$t3));
*/
    // $v->generate();

?>