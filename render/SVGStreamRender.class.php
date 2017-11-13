<?php
/**
 * SVGStreamRender Class
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
class SVGStreamRender {
    private $minx;
    private $miny;
    private $maxx;
    private $maxy;
    private $width;
    private $height;
    private $enablestretchmap;
    private $enableSVGPixelCoordinate;
    private $finalsvg;
    private $useStream;

    /**
     *
     * @DESCRIPTION :Class Constructor.	**
     */
    public function SVGStreamRender($useStream)
    {
        $this->useStream = $useStream;
    }
    
    public function setSVGStreamRender($minx, $miny, $maxx, $maxy, $width, $height, $enablestretchmap, $enableSVGPixelCoordinate
		, $hangle=0,$vangle=0, $distance=0)
    {
        $this->minx = $minx;
        $this->miny = $miny;
        $this->maxx = $maxx;
        $this->maxy = $maxy;
        $this->width = $width;
        $this->height = $height;
        $this->enablestretchmap = $enablestretchmap;
        $this->enableSVGPixelCoordinate = $enableSVGPixelCoordinate;

        $this->hangle = $hangle;
		$this->vangle = $vangle;
		$this->distance = $distance;
    }

    public function setFinalSvg($svg)
    {
        //this will output stream svg directly
        if($this->useStream){
            echo $svg;
        }
        else{
            $this->finalsvg .= $svg;
        }
    }

    public function getFinalSvg()
    {
        return $this->finalsvg;
    }

    public function SvgDocument() {
        $this->setFinalSvg('<?xml version="1.0" encoding="utf-8"?>' . "\n");

        $this->setFinalSvg('<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
         "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n");
    }

/*
*
*$type 0: SVG
*      1: SVGT
*      3: SVGB
*/
    function SvgFragmentBegin()
    {
        if ($this->enableSVGPixelCoordinate) {
            $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $this->minx, $this->miny, $this->enablestretchmap);
            if(blnGetMap25D)
            	$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
			$minxr = $coord[0];
            $minyr = $coord[1];
            $coord1 = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $this->maxx, $this->maxy, $this->enablestretchmap);
            if(blnGetMap25D)
            	$coord1 = get25DXY($coord1,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
			$maxxr = $coord1[0];
            $maxyr = $coord1[1];
            $W = $maxxr - $minxr;
            $H = $maxyr - $minyr;
            $this->setFinalSvg("<svg width=\"$this->width"."px\" height=\"$this->height"."px\" ");
            $mMaxy_tem = -$maxyr;
            $this->setFinalSvg("viewBox=\"$minxr $mMaxy_tem $W $H\" ");
        } else {
            $W = $this->maxx - $this->minx;
            $H = $this->maxy - $this->miny;
            $this->setFinalSvg("<svg width=\"$this->width"."px\" height=\"$this->height"."px\" ");
            $mMaxy_tem = -$this->maxy;
            $this->setFinalSvg("viewBox=\"$this->minx $mMaxy_tem $W $H\" ");
        }

        $this->setFinalSvg('preserveAspectRatio="xMidYMid meet" ');
        $this->setFinalSvg('xml:space="preserve" ');
        $this->setFinalSvg('style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd" ');
        $this->setFinalSvg('xmlns:SUASsvg="http://suas.easywms.com" ');
        $this->setFinalSvg('xmlns="http://www.w3.org/2000/svg" ');
        $this->setFinalSvg('xmlns:xlink="http://www.w3.org/1999/xlink" ');
        $this->setFinalSvg(">\n");
    }

    function SvgFragmentEnd()
    {
        $this->setFinalSvg("</svg>\n");
    }

    function SvgDesc($desc)
    {
        $this->setFinalSvg("<desc>$desc</desc>\n");
    }

    function printId($id)
    {
        if ($id != "") {
            $this->setFinalSvg("id=\"$id\" ");
        }
    }

    function printStyle($style)
    {
        if ($style != "") {
            $this->setFinalSvg("style=\"$style\" ");
        }
    }

    function printTransform($transform)
    {
        if ($transform != "") {
            $this->setFinalSvg("transform=\"$transform\" ");
        }
    }

    function printClass($class)
    {
        if ($class != "") {
            $this->setFinalSvg("class=\"$class\" ");
        }
    }

    function SvgText($id = "", $x = 0, $y = 0, $text = "", $style = "", $transform = "")
    {
        if ($this->enableSVGPixelCoordinate) {
            $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $x, $y, $this->enablestretchmap);
            if(blnGetMap25D)
            	$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
			$x = $coord[0];
            $y = $coord[1];
        }
        $this->setFinalSvg("<text ");
        $this->printId($id);
        $this->setFinalSvg("x=\"$x\" y=\"".(-$y)."\" ");
        $this->printStyle($style);
        $this->printTransform($transform);
        $this->setFinalSvg(">\n");
        $this->setFinalSvg($text);
        $this->setFinalSvg("</text>\n");
    }

    function SvgTextPath($id = "", $cx = 0, $cy = 0, $pointNumber, $text = "", $style = "", $transform = "")
    {
        $data_x = $cx;
        $data_y = $cy;
        for($i = 0;$i < $pointNumber;$i++) {
            if ($this->enableSVGPixelCoordinate) {
                $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
                if(blnGetMap25D)
            		$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
				$data_x[$i] = $coord[0];
                $data_y[$i] = $coord[1];
            }
            if ($i < $pointNumber-1) {
                if ($i == 0) {
                    $pathD .= "M" . $data_x[$i] . " " . (- $data_y[$i]) . "L";
                } else {
                    $pathD .= $data_x[$i] . " " . (- $data_y[$i]) . " ";
                }
            }
            if ($i == $pointNumber-1) {
                if ($data_x[$i] == $data_x[0] AND $data_y[$i] == $data_y[0]) {
                    $pathD .= "z";
                    // use later
                    // $pathD .= $data_x[$i]." ".$data_y[$i]." ".$data_x[0]." ".$data_y[0];
                } else {
                    $pathD .= $data_x[$i] . " " . (- $data_y[$i]);
                }
            }
        }

        $this->setFinalSvg("<def><path id=\"$id"."_path"."\" d=\"$pathD\" /></def>");

        $this->setFinalSvg("<text ");
        $this->printId($id);
        $this->printStyle($style);
        $this->printTransform($transform);
        $this->setFinalSvg(">\n");
        $this->setFinalSvg("<textPath xlink:href=\"#".$id."_path\">");
        $this->setFinalSvg($text);
        $this->setFinalSvg("</textPath>");
        $this->setFinalSvg("</text>\n");

        $pathD = "";
    }

    function SvgGroupBegin($id = "", $style = "", $transform = "", $class = "")
    {
        $this->setFinalSvg("<g ");
        $this->printId($id);
        $this->printStyle($style);
        $this->printTransform($transform);
        $this->printClass($class);
        $this->setFinalSvg(">\n");
    }

    function SvgGroupEnd()
    {
        $this->setFinalSvg("</g>\n");
    }

    /*
     * @DESCRIPTION SvgPoint: used to create points
     */
    function SvgPoint($id = "", $cx = 0, $cy = 0, $r = 0, $style = "", $transform = "", $xmlWellKnownName = "SQUARE")
    {
        if ($this->enableSVGPixelCoordinate) {
            $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $cx, $cy, $this->enablestretchmap);
            if(blnGetMap25D)
            	$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
			$cx = $coord[0];
            $cy = $coord[1];
        }
        switch ($xmlWellKnownName) {
            case "SQUARE": {
                    $this->SvgRect($id, $cx - $r / 2, $cy - $r / 2, 2 * $r, 2 * $r, $style, $transform);
                }
                break;
            case "CIRCLE": {
                    $this->SvgCircle($id, $cx, $cy, $r, $style, $transform);
                }
                break;
            case "TRIANGLE": {
                    $tem = getShapeTriangle($cx, $cy, $r);
                    $points = "";
                    for($j = 0;$j < 3;$j++) {
                        $points .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }
                    $this->SvgPolygon($id, $points, $style, $transform);
                }
                break;
            case "STAR": {
                    $tem = getShapeFiveCornerStar($cx, $cy, $r);
                    $points = "";
                    for($j = 0;$j < 10;$j++) {
                        $points .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }
                    $this->SvgPolygon($id, $points, $style, $transform);
                }
                break;
            case "CROSS": {
                    $tem = getShapeCross($cx, $cy, $r);
                    $points = "";
                    for($j = 0;$j < 12;$j++) {
                        $points .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }
                    $this->SvgPolygon($id, $points, $style, $transform);
                }
                break;
            case "X": {
                    $tem = getShapeX($cx, $cy, $r);
                    $points = "";
                    for($j = 0;$j < 12;$j++) {
                        $points .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }
                    $this->SvgPolygon($id, $points, $style, $transform);
                }
                break;
            default: {
                    $this->SvgRect($id, $cx - $r / 2, $cy - $r / 2, 2 * $r, 2 * $r, $style, $transform);
                }
        }
    }
    /*
     * @DESCRIPTION SvgPoint: used to create multipoints
     */
    function SvgMultiPoint($id = "", $cx, $cy, $r = 0, $pointNumber, $style = "", $transform = "", $xmlWellKnownName = "SQUARE")
    {
        for($i = 0;$i < $pointNumber;$i++) {
            if ($this->enableSVGPixelCoordinate) {
                $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $cx[$i], $cy[$i], $this->enablestretchmap);
                if(blnGetMap25D)
            		$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
				$cx[$i] = $coord[0];
                $cy[$i] = $coord[1];
            }
            switch ($xmlWellKnownName) {
            case "SQUARE": {
                    $this->SvgRect($id, $cx - $r / 2, $cy - $r / 2, 2 * $r, 2 * $r, $style, $transform);
                }
                break;
            case "CIRCLE": {
                    $this->SvgCircle($id, $cx, $cy, $r, $style, $transform);
                }
                break;
            case "TRIANGLE": {
                    $tem = getShapeTriangle($cx, $cy, $r);
                    $points = "";
                    for($j = 0;$j < 3;$j++) {
                        $points .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }
                    $this->SvgPolygon($id, $points, $style, $transform);
                }
                break;
            case "STAR": {
                    $tem = getShapeFiveCornerStar($cx, $cy, $r);
                    $points = "";
                    for($j = 0;$j < 10;$j++) {
                        $points .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }
                    $this->SvgPolygon($id, $points, $style, $transform);
                }
                break;
            case "CROSS": {
                    $tem = getShapeCross($cx, $cy, $r);
                    $points = "";
                    for($j = 0;$j < 12;$j++) {
                        $points .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }
                    $this->SvgPolygon($id, $points, $style, $transform);
                }
                break;
            case "X": {
                    $tem = getShapeX($cx, $cy, $r);
                    $points = "";
                    for($j = 0;$j < 12;$j++) {
                        $points .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }
                    $this->SvgPolygon($id, $points, $style, $transform);
                }
                break;
            default: {
                    $this->SvgRect($id, $cx - $r / 2, $cy - $r / 2, 2 * $r, 2 * $r, $style, $transform);
                }
        }
        }
    }

    function SvgLineString($id = "", $cx, $cy, $pointNumber, $style = "", $transform = "")
    {
        $data_x = $cx;
        $data_y = $cy;
        for($i = 0;$i < $pointNumber;$i++) {
            if ($this->enableSVGPixelCoordinate) {
                $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
                if(blnGetMap25D)
            		$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
				$data_x[$i] = $coord[0];
                $data_y[$i] = $coord[1];
            }
            if ($i < $pointNumber-1) {
                if ($i == 0) {
                    $pathD .= "M" . $data_x[$i] . " " . (- $data_y[$i]) . "L";
                } else {
                    $pathD .= $data_x[$i] . " " . (- $data_y[$i]) . " ";
                }
            }
            if ($i == $pointNumber-1) {
                if ($data_x[$i] == $data_x[0] AND $data_y[$i] == $data_y[0]) {
                    $pathD .= "z";
                    // use later
                    // $pathD .= $data_x[$i]." ".$data_y[$i]." ".$data_x[0]." ".$data_y[0];
                } else {
                    $pathD .= $data_x[$i] . " " . (- $data_y[$i]);
                }
            }
        }

        $this->SvgPath($id, trim($pathD), $style, $transform);
        $pathD = "";
    }

    function SvgMultiLinstring($id = "", $cx, $cy, $lineNumber, $pointNumber, $style = "", $transform = "")
    {
        $data_x = $cx;
        $data_y = $cy;
        for($i = 0;$i < $lineNumber;$i++) {
            for($j = 0;$j < $pointNumber[$i];$j++) {
                if ($this->enableSVGPixelCoordinate) {
                    $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
                    if(blnGetMap25D)
            			$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
					$data_x[$i][$j] = $coord[0];
                    $data_y[$i][$j] = $coord[1];
                }
                if ($j < $pointNumber[$i]-1) {
                    if ($j == 0) {
                        $pathD .= "M" . $data_x[$i][$j] . " " . (- $data_y[$i][$j]) . "L";
                    } else {
                        $pathD .= $data_x[$i][$j] . " " . (- $data_y[$i][$j]) . " ";
                    }
                }
                if ($j == $pointNumber[$i]-1) {
                    if ($data_x[$i][$j] == $data_x[$i][0] AND $data_y[$i][$j] == $data_y[$i][0]) {
                        $pathD .= "z";
                    } else {
                        $pathD .= $data_x[$i][$j] . " " . (- $data_y[$i][$j]);
                    }
                }
            }
        }
        $this->SvgPath($id, trim($pathD), $style, $transform);
        // set the $pathD as null
        $pathD = "";
    }

    function SvgSinglePolygon($id = "", $cx, $cy, $lineNumber, $pointNumber, $style = "", $transform = "")
    {
        $data_x = $cx;
        $data_y = $cy;
        for($i = 0;$i < $lineNumber;$i++) {
            $polygonpoints = "";
            for($j = 0;$j < $pointNumber[$i];$j++) {
                if ($this->enableSVGPixelCoordinate) {
                    $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
                    if(blnGetMap25D)
            			$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
					$data_x[$i][$j] = $coord[0];
                    $data_y[$i][$j] = $coord[1];
                }
                $polygonpoints .= $data_x[$i][$j] . " " . (- $data_y[$i][$j]) . " ";
            }
            $this->SvgPolygon($id, trim($polygonpoints), $style, $transform);
        }
    }

    function SvgMultiPolygon($id = "", $cx, $cy, $lineNumber, $pointNumber, $style = "", $transform = "")
    {
        $data_x = $cx;
        $data_y = $cy;
        for($i = 0;$i < $lineNumber;$i++) {
            $polygonpoints = "";
            for($j = 0;$j < $pointNumber[$i];$j++) {
                if ($this->enableSVGPixelCoordinate) {
                    $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
                    if(blnGetMap25D)
            			$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
					$data_x[$i][$j] = $coord[0];
                    $data_y[$i][$j] = $coord[1];
                }
                $polygonpoints .= $data_x[$i][$j] . " " . (- $data_y[$i][$j]) . " ";
            }
            $this->SvgPolygon($id . "_" . $i, trim($polygonpoints), $style, $transform);
        }
    }

    function SvgSingleImage($id = "", $cx, $cy, $xlink = "", $style = "", $transform = "")
    {
        $data_x = $cx;
        $data_y = $cy;
        if ($this->enableSVGPixelCoordinate) {
            $coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[0], $data_y[0], $this->enablestretchmap);
            if(blnGetMap25D)
            	$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
			$data_x[0] = $coord[0];
            $data_y[0] = $coord[1];
            
            $coord1 = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[2], $data_y[2], $this->enablestretchmap);
            if(blnGetMap25D)
            	$coord1 = get25DXY($coord1,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
			$data_x[2] = $coord1[0];
            $data_y[2] = $coord1[1];
        }
        $data_w = $data_x[2] - $data_x[0];
        $data_h = $data_y[2] - $data_y[0];

        $this->SvgImage($id, $data_x[0], -$data_y[0], $data_w, $data_h, $xlink, $style, $transform);
    }
    // ==========================================SVG basic Element==================================
    // x,y here is the top left corner
    function SvgRect($id = "", $x = 0, $y = 0, $width = 0, $height = 0, $style = "", $transform = "")
    {
        $this->setFinalSvg("<rect ");
        $this->printId($id);
        $this->setFinalSvg("x=\"$x\" y=\"$y\" width=\"$width\" height=\"$height\"");
        $this->printStyle($style);
        $this->printTransform($transform);
        $this->setFinalSvg(">\n");
        $this->setFinalSvg("</rect>\n");
    }

    function SvgCircle($id = "", $cx = 0, $cy = 0, $r = 0, $style = "", $transform = "")
    {
        $this->setFinalSvg("<circle ");
        $this->printId($id);
        $this->setFinalSvg("cx=\"$cx\" cy=\"$cy\" r=\"$r\" ");
        $this->printStyle($style);
        $this->printTransform($transform);
        $this->setFinalSvg("/>\n");
        //$this->setFinalSvg(">\n");
        //$this->setFinalSvg("</circle>\n");
    }

    function SvgPath($id = "", $d = "", $style = "", $transform = "")
    {
        $this->setFinalSvg("<path ");
        $this->printId($id);
        $this->setFinalSvg("d=\"$d\" ");
        $this->printStyle($style);
        $this->printTransform($transform);
        $this->setFinalSvg("/>\n");
        //$this->setFinalSvg(">\n");
        //$this->setFinalSvg("</path>\n");
    }

    function SvgPolygon($id = "", $points = "", $style = "", $transform = "")
    {
        $this->setFinalSvg("<polygon ");
        $this->printId($id);
        $this->setFinalSvg("points=\"$points\" ");
        $this->printStyle($style);
        $this->printTransform($transform);
        $this->setFinalSvg("/>\n");
        //$this->setFinalSvg(">\n");
        //$this->setFinalSvg("</polygon>\n");
    }

    function SvgImage($id = "", $x = 0, $y = 0, $width = 0, $height = 0, $xlink = "", $style = "", $transform = "")
    {
        $this->setFinalSvg("<image ");
        $this->printId($id);
        $this->setFinalSvg("x=\"$x\" y=\"$y\" width=\"$width\" height=\"$height\" xlink:href=\"$xlink\" ");
        $this->printStyle($style);
        $this->printTransform($transform);
        $this->setFinalSvg("/>\n");
        //$this->setFinalSvg(">\n");
        //$this->setFinalSvg("</image>\n");
    }
    // ==========================================SVG basic Element==================================
}

?>