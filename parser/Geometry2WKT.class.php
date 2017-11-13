<?php

/**
 * Geometry2WKT transform Class
 *
 * @description : Input the points coordinates and use according class to output the wkt string
 *                 Used in read the SVG, SHP, E00 or MOF format
 * @Task :Calculation for Points XY's Min&Max value
 * @Task :Output WKT format
 * @version $3.0$ 2007
 * @Author leelight
 * @Contact webmaster@easywms.com
 */

/**
 *
 * @description :This class is used to parse CIRCLE,ELLIPSE, and TEXT in SVG
 * This class is used to parse point in SHP
 */
class PointParser {
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    // utput the WKT Point value:POINT(1 1)
    public $strWkt;
    public $blnConverseY = false;

    public function PointParser($blnConverseY_)
    {
        $this->blnConverseY = $blnConverseY_;
    }
    /**
     *
     * @param  $pointsX ,$pointsY : input center points of cirlce,ELLIPSE, and TEXT
     * 1: XY max min calculate
     * 2: WKT output
     * @return $resultarray: xy min max and wkt
     */
    public function parser($pointsX, $pointsY)
    {
        if(strpos($pointsX, 'E'))$pointsX=0;
        if(strpos($pointsY, 'E'))$pointsY=0;
        $resultarray = array();
        if ($this->blnConverseY == false) {
            $this->Point_Xmin = $resultarray[0] = $pointsX;
            $this->Point_Ymin = $resultarray[1] = $pointsY;
            $this->Point_Xmax = $resultarray[2] = $pointsX;
            $this->Point_Ymax = $resultarray[3] = $pointsY;
        } else {
            $this->Point_Xmin = $resultarray[0] = $pointsX;
            $this->Point_Ymin = $resultarray[1] = - $pointsY;
            $this->Point_Xmax = $resultarray[2] = $pointsX;
            $this->Point_Ymax = $resultarray[3] = - $pointsY;
        }
        if ($this->blnConverseY == false)
            $this->strWkt = $resultarray[4] = "POINT(" . $pointsX . " " . $pointsY . ")" ;
        else
            $this->strWkt = $resultarray[4] = "POINT(" . $pointsX . " " . - $pointsY . ")" ;

        return $resultarray;
    }
}

/**
 *
 * @description :This class is used to parse MultiPoint in SHP
 */
class MultiPointParser {
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    // utput the WKT Point value:MULTIPOINT(0 0, 20 20, 60 60)
    public $strWkt;
    public $blnConverseY = false;

    public function MultiPointParser($blnConverseY_)
    {
        $this->blnConverseY = $blnConverseY_;
    }
    /**
     *
     * @param  $pointsString : input multipoints string
     * 1: XY max min calculate, for SHP it is not required to calculate MinMax
     * 2: WKT output
     * @return $resultarray: xy min max and wkt
     */
    public function parser($pointsString)
    {
        $resultarray = array();
        $Array_Point = explode(" ", trim($pointsString));
        $Number_Point_Po = count($Array_Point) / 2;
        for($i = 0;$i < $Number_Point_Po;$i++) {
            $Point_x[$i] = $Array_Point[$i * 2];
            $Point_y[$i] = $Array_Point[$i * 2 + 1];
            if ($this->blnConverseY == false) {
                if ($i != $Number_Point_Po-1)
                    $this->strWkt .= $Point_x[$i] . " " . $Point_y[$i] . ",";
                else
                    $this->strWkt .= $Point_x[$i] . " " . $Point_y[$i];
            } else {
                if ($i != $Number_Point_Po-1)
                    $this->strWkt .= $Point_x[$i] . " " . - $Point_y[$i] . ",";
                else
                    $this->strWkt .= $Point_x[$i] . " " . - $Point_y[$i];
            }
        }
        // $this->Point_Xmin = $resultarray[0] = $pointsX;
        // $this->Point_Ymin = $resultarray[1] = $pointsY;
        // $this->Point_Xmax = $resultarray[2] = $pointsX;
        // $this->Point_Ymax = $resultarray[3] = $pointsY;
        $this->strWkt = $resultarray[4] = "MULTIPOINT(" . $this->strWkt . ")" ;

        return $resultarray;
    }
}

/**
 * This class is used to parse Line in SVG
 */
class LineParser {
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    // utput the WKT LINESTRING value:LINESTRING(0 0,1 1,2 2)
    public $strWkt;
    public $blnConverseY = false;

    public function LineParser($blnConverseY_)
    {
        $this->blnConverseY = $blnConverseY_;
    }
    /**
     *
     * @param  $x1 , $y1, $x2, $y2 : input points of line
     * 1: XY max min calculate
     * 2: WKT output
     * @return $resultarray: xy min max and wkt
     */
    public function parser($x1, $y1, $x2, $y2)
    {
        if ($x1 >= $x2) {
            $line_xmin = $x2;
            $line_xmax = $x1;
        } else if ($x1 < $x2) {
            $line_xmin = $x1;
            $line_xmax = $x2;
        }
        if ($y1 >= $y2) {
            $line_ymin = $y2;
            $line_ymax = $y1;
        } else if ($y1 < $y2) {
            $line_ymin = $y1;
            $line_ymax = $y2;
        }
        $resultarray = array();
        if ($this->blnConverseY == false) {
            $this->Point_Xmin = $resultarray[0] = $line_xmin;
            $this->Point_Ymin = $resultarray[1] = $line_ymin;
            $this->Point_Xmax = $resultarray[2] = $line_xmax;
            $this->Point_Ymax = $resultarray[3] = $line_ymax;
        } else {
            $this->Point_Xmin = $resultarray[0] = $line_xmin;
            $this->Point_Ymin = $resultarray[1] = - $line_ymax;
            $this->Point_Xmax = $resultarray[2] = $line_xmax;
            $this->Point_Ymax = $resultarray[3] = - $line_ymin;
        }
        if ($this->blnConverseY == false)
            $this->strWkt = $resultarray[4] = "LINESTRING(" . $x1 . " " . $y1 . "," . $x2 . " " . $y2 . ")" ;
        else
            $this->strWkt = $resultarray[4] = "LINESTRING(" . $x1 . " " . - $y1 . "," . $x2 . " " . - $y2 . ")" ;
        // echo $this->strWkt;
        return $resultarray;
    }
}

/**
 *
 * @description :This class is used to parse Line
 */
class RectangeParser {
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    // utput the WKT LINESTRING value:LINESTRING(0 0,0 1,1 1,1 0,0 0)
    public $strWkt;
    public $blnConverseY = false;

    public function RectangeParser($blnConverseY_)
    {
        $this->blnConverseY = $blnConverseY_;
    }

    /**
     *
     * @param  $x , $y, $w, $h : input points of rectange
     * 1: XY max min calculate
     * 2: WKT output
     * @return $resultarray: xy min max and wkt
     */
    public function parser($x, $y, $w, $h)
    {
        $resultarray = array();
        if ($this->blnConverseY == false) {
            $this->Point_Xmin = $resultarray[0] = $x1 = $x;
            $this->Point_Ymin = $resultarray[1] = $y1 = $y;
            $this->Point_Xmax = $resultarray[2] = $x2 = $x + $w;
            $this->Point_Ymax = $resultarray[3] = $y2 = $y + $h;
        } else {
            $this->Point_Xmin = $resultarray[0] = $x1 = $x;
            $this->Point_Ymin = $resultarray[1] = - $y1 = - $y - $h;
            $this->Point_Xmax = $resultarray[2] = $x2 = $x + $w;
            $this->Point_Ymax = $resultarray[3] = - $y2 = - $y;
        }
        if ($this->blnConverseY == false)
            $this->strWkt = $resultarray[4] = "LINESTRING(" . $x1 . " " . $y1 . "," . $x1 . " " . $y2 . "," . $x2 . " " . $y2 . "," . $x2 . " " . $y1 . "," . $x1 . " " . $y1 . ")" ;
        else
            $this->strWkt = $resultarray[4] = "LINESTRING(" . $x1 . " " . - $y1 . "," . $x1 . " " . - $y2 . "," . $x2 . " " . - $y2 . "," . $x2 . " " . - $y1 . "," . $x1 . " " . - $y1 . ")" ;
        // $this->strWkt = $resultarray[4] = "LINESTRING(".$x1." ".$y1.",".$x1." ".$y2.",".$x2." ".$y2.",".$x2." ".$y1.")" ;
        return $resultarray;
    }
}

/**
 *
 * @description :This class is used for parse polyline in SHP
 */
class MultiLineStringParser {
    private $pointsString;
    private $Point_X;
    private $Point_Y;
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    public $Number_Point;
    // $Number_path_M_devided is number of array
    public $Number_path_M_devided;
    // $Number_Point_M is array storing the points number of each new path
    public $Number_Point_M;
    public $strWkt;
    // output the WKT Polygon value:MultiLineString((0 0,10 0,10 10,0 10,0 0),(15 15, 30 15))
    private $strWkt_M;
    public $blnConverseY = false;

    public function MultiLineStringParser($blnConverseY_)
    {
        $this->blnConverseY = $blnConverseY_;
    }

    /**
     *
     * @param  $pointsString : input points string of polyline(MultiPolyline)
     *           from SHPData["parts"][$partIndex]["pointString"]
     *
     * 1: XY max min calculate, for SHP it is not required to calculate MinMax
     * 2: WKT output
     * @return $resultarray: xy min max and wkt
     */
    public function parser($PointsStringArray)
    {
        $resultarray = array();
        $Number_path_M_devided = count($PointsStringArray);

        for($i = 0;$i < $Number_path_M_devided;$i++) {
            $Array_Point[$i] = explode(" ", trim($PointsStringArray[$i]["pointString"]));
            $Number_Point_M[$i] = count($Array_Point[$i]) / 2;
            for($j = 0;$j < $Number_Point_M[$i];$j++) {
                $Point_x[$j] = $Array_Point[$i][$j * 2];
                $Point_y[$j] = $Array_Point[$i][$j * 2 + 1];
                // output the WKT value:0 0,10 0,10 10,0 10,0 0 for each linestring
                if ($this->blnConverseY == false) {
                    if ($j != $Number_Point_M[$i]-1) {
                        $strWkt_M[$i] .= $Point_x[$j] . " " . $Point_y[$j] . ",";
                    } else
                        $strWkt_M[$i] .= $Point_x[$j] . " " . $Point_y[$j];
                } else {
                    if ($j != $Number_Point_M[$i]-1) {
                        $strWkt_M[$i] .= $Point_x[$j] . " " . - $Point_y[$j] . ",";
                    } else
                        $strWkt_M[$i] .= $Point_x[$j] . " " . - $Point_y[$j];
                }
            }

            $strWkt_M[$i] = "(" . $strWkt_M[$i] . ")";
            if ($i != $Number_path_M_devided-1) {
                $this->strWkt .= $strWkt_M[$i] . "," ;
            } else {
                $this->strWkt .= $strWkt_M[$i] ;
            }
        }
        // $this->Point_Xmin = $resultarray[0] = min($this->Point_X_Po);
        // $this->Point_Ymin = $resultarray[1] = min($this->Point_Y_Po) ;
        // $this->Point_Xmax = $resultarray[2] = max($this->Point_X_Po);
        // $this->Point_Ymax = $resultarray[3] = max($this->Point_Y_Po);
        // output the WKT Polygon value:MultiLineString((0 0,10 0,10 10,0 10,0 0),(15 15, 30 15))
        $this->strWkt = $resultarray[4] = "MULTILINESTRING(" . $this->strWkt . ")";
        // echo $this->strWkt;
        return $resultarray;
    }
}
// ====================================================================================
/**
 *
 * @description :This class is used for parse polyline in SVG and MIF
 */
class PolylineParser {
    private $pointsString;
    private $Point_X_Po;
    private $Point_Y_Po;
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    public $Number_Point_Po;
    // public $iSpace;
    // output the WKT Polygon value:LINESTRING(0 0,10 0,10 10,0 10,0 0)
    public $strWkt;
    public $blnConverseY = false;
    public $type;
     /*
     *          $type : 0 - svg; 1 - mif
     *                  decide this function used for SVG or MIF file, because WKT is different
     *                  for SVG, which the last point should be the first point.
     */
    public function PolylineParser($blnConverseY_, $type_)
    {
        $this->blnConverseY = $blnConverseY_;
        $this->type = $type_;
    }

    /**
     * This function can be also used for Polyline
     *
     * @param  $pointsString : input points string of polygon(polylone)
     * 1: XY max min calculate
     * 2: WKT output
     * @return $resultarray: xy min max and wkt
     */
    public function parser($pointsString)
    {
        $resultarray = array();
        // Replace '-' with ' -'
        $this->pointsString = str_replace('-', ' -', $pointsString);
        // These are some spaces in the path, but the number is unclear, they must be delete
        // $iSpace= 100;
        // $iSpace=strlen($pathString);
        $iSpace = 3;
        // echo $iSpace;
        for($iSpace;$iSpace > 0;$iSpace--) {
            $strChars = " ";
            for($iaddS = 0;$iaddS < $iSpace;$iaddS++) {
                $strChars = $strChars . " ";
            }
            $this->pointsString = str_replace($strChars, ' ', $this->pointsString);
        }
        // echo $this->pointsString;
        // Some SVG file uses ',' in polygon or polyline command or not, whatever delete it at first
        // Replace ',' with space
        $this->pointsString = strtr($this->pointsString, ',', ' ');
        // Before explode, all empty in front and at end must be sure that is deleted!!!!!
        $this->pointsString = trim($this->pointsString);

        $Array_Point = explode(" ", $this->pointsString);
        $this->Number_Point_Po = count($Array_Point) / 2;
        // echo $this->Number_Point_Po."\n";
        for ($j = 0;$j < ($this->Number_Point_Po) * 2;$j++) {
            // All space must be deleted
            $Point_xy[$j] = trim($Array_Point[$j]);
        }
        for($i = 0;$i < $this->Number_Point_Po;$i++) {
            // echo count($Point_xy);
            $Point_x[$i] = $Point_xy[$i * 2];
            $Point_y[$i] = $Point_xy[$i * 2 + 1];

            $this->Point_X_Po[$i] = $Point_x[$i];
            $this->Point_Y_Po[$i] = $Point_y[$i];
            // echo $this->Point_X_Po[$i]."|".$this->Point_Y_Po[$i]."\n";
            // output the WKT Polygon value:0 0,10 0,10 10,0 10,0 0
            if ($this->blnConverseY == false) {
                if ($i != $this->Number_Point_Po-1) {
                    $this->strWkt .= $this->Point_X_Po[$i] . " " . $this->Point_Y_Po[$i] . ",";
                } else {
                    if ($this->type == 0)//for svg
                        $this->strWkt .= $this->Point_X_Po[$i] . " " . $this->Point_Y_Po[$i] . "," . $this->Point_X_Po[0] . " " . $this->Point_Y_Po[0];
                    else//for mif
                        $this->strWkt .= $this->Point_X_Po[$i] . " " . $this->Point_Y_Po[$i];
                }
            } else {
                if ($i != $this->Number_Point_Po-1) {
                    $this->strWkt .= $this->Point_X_Po[$i] . " " . - $this->Point_Y_Po[$i] . ",";
                } else {
                    if ($this->type == 0)//for svg
                        $this->strWkt .= $this->Point_X_Po[$i] . " " . - $this->Point_Y_Po[$i] . "," . $this->Point_X_Po[0] . " " . - $this->Point_Y_Po[0];
                    else//for mif
                        $this->strWkt .= $this->Point_X_Po[$i] . " " . - $this->Point_Y_Po[$i];
                }
            }
        }
        if ($this->blnConverseY == false) {
            $this->Point_Xmin = $resultarray[0] = min($this->Point_X_Po);
            $this->Point_Ymin = $resultarray[1] = min($this->Point_Y_Po) ;
            $this->Point_Xmax = $resultarray[2] = max($this->Point_X_Po);
            $this->Point_Ymax = $resultarray[3] = max($this->Point_Y_Po);
        } else {
            $this->Point_Xmin = $resultarray[0] = min($this->Point_X_Po);
            $this->Point_Ymin = $resultarray[1] = - max($this->Point_Y_Po) ;
            $this->Point_Xmax = $resultarray[2] = max($this->Point_X_Po);
            $this->Point_Ymax = $resultarray[3] = - min($this->Point_Y_Po);
        }
        // echo $this->Point_Xmax."\n";
        // echo $this->Point_Ymax."\n";
        // echo $this->Point_Xmin."\n";
        // echo $this->Point_Ymin."\n";
        // output the WKT Polygon value:LINESTRING(0 0,10 0,10 10,0 10,0 0)
        $this->strWkt = $resultarray[4] = "LINESTRING(" . $this->strWkt . ")";
        // echo $this->strWkt;
        return $resultarray;
    }
}
// define('iSpace', 10);
// $pointsString='220,100 300,210 170  250     30,320';
// $PolylineParser=new PolylineParser($pointsString);
// ====================================================================================
/**
 *
 * @description :This class is used for parse polygon in SHP
 */
class MultiPolygonParser {
    private $pointsString;
    private $Point_X;
    private $Point_Y;
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    public $Number_Point;
    // $Number_path_M_devided is number of array
    public $Number_path_M_devided;
    // $Number_Point_M is array storing the points number of each new path
    public $Number_Point_M;
    public $strWkt;
    // output the WKT Polygon value:MULTIPOLYGON(((0 0,10 0,10 10,0 10,0 0)),((5 5,7 5,7 7,5 7, 5 5)))
    private $strWkt_M;
    public $blnConverseY = false;

    public function MultiPolygonParser($blnConverseY_)
    {
        $this->blnConverseY = $blnConverseY_;
    }

    /**
     *
     * @param  $pointsString : input points string of polygon(MultiPolygon)
     *           from SHPData["parts"][$partIndex]["pointString"]
     *
     * 1: XY max min calculate, for SHP it is not required to calculate MinMax
     * 2: WKT output
     * @return $resultarray: xy min max and wkt
     */
    public function parser($PointsStringArray)
    {
        $resultarray = array();
        $Number_path_M_devided = count($PointsStringArray);

        for($i = 0;$i < $Number_path_M_devided;$i++) {
            $Array_Point[$i] = explode(" ", trim($PointsStringArray[$i]["pointString"]));
            $Number_Point_M[$i] = count($Array_Point[$i]) / 2;
            for($j = 0;$j < $Number_Point_M[$i];$j++) {
                $Point_x[$j] = $Array_Point[$i][$j * 2];
                $Point_y[$j] = $Array_Point[$i][$j * 2 + 1];
                // output the WKT value:0 0,10 0,10 10,0 10,0 0 for each linestring
                if ($this->blnConverseY == false) {
                    if ($j != $Number_Point_M[$i]-1) {
                        $this->strWkt_M[$i] .= $Point_x[$j] . " " . $Point_y[$j] . ",";
                    } else
                        $this->strWkt_M[$i] .= $Point_x[$j] . " " . $Point_y[$j];
                } else {
                    if ($j != $Number_Point_M[$i]-1) {
                        $this->strWkt_M[$i] .= $Point_x[$j] . " " . - $Point_y[$j] . ",";
                    } else
                        $this->strWkt_M[$i] .= $Point_x[$j] . " " . - $Point_y[$j];
                }
            }
            // the internal ring is not be considered here, using POLYGON((xx xx xx xx)),not POLYGON(xx xx xx xx)
            $this->strWkt_M[$i] = "((" . $this->strWkt_M[$i] . "))";
            // echo $strWkt_M[$i];
            if ($i != $Number_path_M_devided-1) {
                $this->strWkt .= $this->strWkt_M[$i] . "," ;
            } else {
                $this->strWkt .= $this->strWkt_M[$i] ;
            }
        }
        if ($this->blnConverseY == false) {
            $this->Point_Xmin = $resultarray[0] = min($Point_x);
            $this->Point_Ymin = $resultarray[1] = min($Point_y) ;
            $this->Point_Xmax = $resultarray[2] = max($Point_x);
            $this->Point_Ymax = $resultarray[3] = max($Point_y);
        } else {
            $this->Point_Xmin = $resultarray[0] = min($Point_x);
            $this->Point_Ymin = $resultarray[1] = - max($Point_y) ;
            $this->Point_Xmax = $resultarray[2] = max($Point_x);
            $this->Point_Ymax = $resultarray[3] = - min($Point_y);
        }
        // output the WKT Polygon value:MULTIPOLYGON(((0 0,10 0,10 10,0 10,0 0)),((5 5,7 5,7 7,5 7, 5 5)))
        $this->strWkt = $resultarray[4] = "MULTIPOLYGON(" . $this->strWkt . ")";
        // echo $this->strWkt;
        return $resultarray;
    }
}
// ====================================================================================
/**
 *
 * @description :This class is used for parse polygon in SVG
 */
class PolygonParser {
    private $pointsString;
    private $Point_X_Po;
    private $Point_Y_Po;
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    public $Number_Point_Po;
    //auto add 1st point as the last point
    private $autoClose = true;
    // public $iSpace;
    // output the WKT Polygon value:POLYGON(0 0,10 0,10 10,0 10,0 0)
    public $strWkt;
    public $blnConverseY = false;

    public function PolygonParser($blnConverseY_, $autoClose = true)
    {
        $this->blnConverseY = $blnConverseY_;
        $this->autoClose = $autoClose;
    }

    /**
     * This function can be also used for Polyline
     *
     * @param  $pointsString : input points string of polygon(polylone)
     * 1: XY max min calculate
     * 2: WKT output
     * @return $resultarray: xy min max and wkt
     */
    public function parser($pointsString)
    {
        $resultarray = array();
        // Replace '-' with ' -'
        $this->pointsString = str_replace('-', ' -', $pointsString);
        // These are some spaces in the path, but the number is unclear, they must be delete
        // $iSpace= 100;
        // $iSpace=strlen($pathString);
        $iSpace = 3;
        // echo $iSpace;
        for($iSpace;$iSpace > 0;$iSpace--) {
            $strChars = " ";
            for($iaddS = 0;$iaddS < $iSpace;$iaddS++) {
                $strChars = $strChars . " ";
            }
            $this->pointsString = str_replace($strChars, ' ', $this->pointsString);
        }
        // echo $this->pointsString;
        // Some SVG file uses ',' in polygon or polyline command or not, whatever delete it at first
        // Replace ',' with space
        $this->pointsString = strtr($this->pointsString, ',', ' ');
        // Before explode, all empty in front and at end must be sure that is deleted!!!!!
        $this->pointsString = trim($this->pointsString);

        $Array_Point = explode(" ", $this->pointsString);
        $this->Number_Point_Po = count($Array_Point) / 2;
        // echo $this->Number_Point_Po."\n";
        for ($j = 0;$j < ($this->Number_Point_Po) * 2;$j++) {
            // All space must be deleted
            $Point_xy[$j] = trim($Array_Point[$j]);
        }
        for($i = 0;$i < $this->Number_Point_Po;$i++) {
            // echo count($Point_xy);
            $Point_x[$i] = $Point_xy[$i * 2];
            $Point_y[$i] = $Point_xy[$i * 2 + 1];

            $this->Point_X_Po[$i] = $Point_x[$i];
            $this->Point_Y_Po[$i] = $Point_y[$i];
            // echo $this->Point_X_Po[$i]."|".$this->Point_Y_Po[$i]."\n";
            // output the WKT Polygon value:0 0,10 0,10 10,0 10,0 0
            if ($this->blnConverseY == false) {
                if ($i != $this->Number_Point_Po-1) {
                    $this->strWkt .= $this->Point_X_Po[$i] . " " . $this->Point_Y_Po[$i] . ",";
                } else
                	if($this->autoClose)
                    	$this->strWkt .= $this->Point_X_Po[$i] . " " . $this->Point_Y_Po[$i] . "," . $this->Point_X_Po[0] . " " . $this->Point_Y_Po[0];
                    else
						$this->strWkt .= $this->Point_X_Po[$i] . " " . $this->Point_Y_Po[$i];
            } else {
                if ($i != $this->Number_Point_Po-1) {
                    $this->strWkt .= $this->Point_X_Po[$i] . " " . - $this->Point_Y_Po[$i] . ",";
                } else
                	if($this->autoClose)
                    	$this->strWkt .= $this->Point_X_Po[$i] . " " . - $this->Point_Y_Po[$i] . "," . $this->Point_X_Po[0] . " " . - $this->Point_Y_Po[0];
                    else
                    	$this->strWkt .= $this->Point_X_Po[$i] . " " . - $this->Point_Y_Po[$i];
            }
        }
        if ($this->blnConverseY == false) {
            $this->Point_Xmin = $resultarray[0] = min($this->Point_X_Po) ;
            $this->Point_Ymin = $resultarray[1] = min($this->Point_Y_Po) ;
            $this->Point_Xmax = $resultarray[2] = max($this->Point_X_Po) ;
            $this->Point_Ymax = $resultarray[3] = max($this->Point_Y_Po) ;
        } else {
            $this->Point_Xmin = $resultarray[0] = min($this->Point_X_Po) ;
            $this->Point_Ymin = $resultarray[1] = - max($this->Point_Y_Po) ;
            $this->Point_Xmax = $resultarray[2] = max($this->Point_X_Po) ;
            $this->Point_Ymax = $resultarray[3] = - min($this->Point_Y_Po) ;
        }
        // echo $this->Point_Xmax."\n";
        // echo $this->Point_Ymax."\n";
        // echo $this->Point_Xmin."\n";
        // echo $this->Point_Ymin."\n";
        // output the WKT Polygon value:POLYGON((0 0,10 0,10 10,0 10,0 0),(could be none))
        // the internal ring is not be considered here, using POLYGON((xx xx xx xx)),not POLYGON(xx xx xx xx)
        $this->strWkt = $resultarray[4] = "POLYGON((" . $this->strWkt . "))" ;
        // echo $this->strWkt;
        return $resultarray;
    }
}
/*
 define('iSpace', 10);
 $pointsString='220,100 300,210 170  250     30,320';
 $PolygonParser=new PolygonParser($pointsString);
 $PolygonParser->parser($pointsString);
*/
// ====================================================================================
/**
 *
 * @description : This class is used for path command in SVG,which has only commands in SVG
 * Z = closepath or not used(for NoneRegion_ML)
 * M = moveto
 * L(l) = lineto
 * The other commands are not considered here
 */
class Path_MLZParser {
    private $pathString;
    private $Point_X;
    private $Point_Y;
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    private $bPathClose = false;
    private $Number_Point;
    // $Number_path_M_devided is number of array
    public $Number_path_M_devided;
    // $Number_Point_M is array storing the points number of each new path from new start point M
    public $Number_Point_M;
    // output the WKT MultiLineString value:MultiLineString((0 0,10 0,10 10,0 10,0 0),(5 5,7 5,7 7,5 7,5 5))
    public $strWkt;
    // WKT LineString value for each LineString:(0 0,10 0,10 10,0 10,0 0)
    private $strWkt_M;
    public $blnConverseY = false;

    public function Path_MLZParser($blnConverseY_)
    {
        $this->blnConverseY = $blnConverseY_;
    }

    /**
     * This function is used for path command in SVG
     *
     * @param  $pathString : input points string of path
     * 1: XY max min calculate
     * 2: WKT output
     * @return $resultarray: xy min max and wkt
     */
    function parser($pathString)
    {
        $resultarray = array();
        // delete the 'e-016' in 2.224e-016
        $this->pathString = str_replace('e-016', ' ', $pathString);
        // Replace '-' with ' -'
        $this->pathString = str_replace('-', ' -', $this->pathString);
        // Replace ',' with space
        $this->pathString = str_replace(',', ' ', $this->pathString);
        // Delete 'M' and space in the front and at end
        // $pathString = trim(substr($pathStringTemp, strpos($pathStringTemp, 'M')+1));
        // ----------------------------------------------------------
        /**
         * If These more than one start point M
         */
        // better use substr_count method
        $path_M_devided = explode("M", trim($this->pathString));
        // Because even nothing before the first M, it is also devided from the first M
        $this->Number_path_M_devided = count($path_M_devided)-1;
        // echo $this->Number_path_M_devided."\n";
        // $i1 must be set from 1, because at 0 is nothing
        for($i1 = 1;$i1 <= $this->Number_path_M_devided;$i1++) {
            // Point to relative(l) method
            if (strpos($path_M_devided[$i1], 'l')) {
                // Replace 'l' with space
                // Here cant not use $this->pathString,why?
                $pathString = strtr($path_M_devided[$i1], 'l', ' ');
                if (strstr($pathString, 'Z') OR strstr($pathString, 'z')) {
                    $bPathClose = true;
                }
                // Replace 'Z' with space
                // $pathString = strtr($pathString, 'Z', ' ');
                // $pathString = strtr($pathString, 'z', ' ');
                $pathString = str_replace('Z', '', $pathString);
                $pathString = str_replace('z', '', $pathString);
                // echo $pathString."|"."\n";
                // These are some spaces in the path, but the number is unclear, they must be delete
                $iSpace = 3;
                // $iSpace=strlen($pathString);
                // $iSpace=iSpace;
                // echo $iSpace;
                for($iSpace;$iSpace > 0;$iSpace--) {
                    $strChars = " ";
                    for($iaddS = 0;$iaddS < $iSpace;$iaddS++) {
                        $strChars = $strChars . " ";
                    }
                    $pathString = str_replace($strChars, ' ', $pathString);
                }
                // echo $pathString."|"."\n";
                // Before explode, all empty in front and at end must be sure that is deleted!!!!!
                $Array_Point = explode(" ", trim($pathString));
                $this->Number_Point_M[$i1] = count($Array_Point) / 2;
                // echo $this->Number_Point_M[$i1]."\n";
                for ($j = 0;$j < ($this->Number_Point_M[$i1]) * 2;$j++) {
                    // All space must be deleted
                    $Point_DxDy[$j] = trim($Array_Point[$j]);
                }

                for($i = 0;$i < $this->Number_Point_M[$i1];$i++) {
                    $Point_Dx[$i] = $Point_DxDy[$i * 2];
                    $Point_Dy[$i] = $Point_DxDy[$i * 2 + 1];
                    if ($i == 0) {
                        $this->Point_X[0] = $Point_DxDy[0];
                        $this->Point_Y[0] = $Point_DxDy[1];
                    }
                    if ($i > 0) {
                        $this->Point_X[$i] = $this->Point_X[$i-1] + $Point_Dx[$i];
                        $this->Point_Y[$i] = $this->Point_Y[$i-1] + $Point_Dy[$i];
                    }
                    // echo $this->Point_Y[$i]."\n";
                    // echo $Point_DxDy[0]."\n".$Point_DxDy[1]."\n".$Point_DxDy[2]."\n";
                    // WKT output
                    // 250 150,300 200,350 150
                    if ($this->blnConverseY == false) {
                        if ($i != $this->Number_Point_M[$i1]-1) {
                            $strWkt_M[$i1] .= $this->Point_X[$i] . " " . $this->Point_Y[$i] . ",";
                        } else {
                            if ($bPathClose)// add the first point if is close path
                                $strWkt_M[$i1] .= $this->Point_X[$i] . " " . $this->Point_Y[$i] . "," . $this->Point_X[0] . " " . $this->Point_Y[0];
                            else
                                $strWkt_M[$i1] .= $this->Point_X[$i] . " " . $this->Point_Y[$i];
                        }
                    } else {
                        if ($i != $this->Number_Point_M[$i1]-1) {
                            $strWkt_M[$i1] .= $this->Point_X[$i] . " " . - $this->Point_Y[$i] . ",";
                        } else {
                            if ($bPathClose)// add the first point if is close path
                                $strWkt_M[$i1] .= $this->Point_X[$i] . " " . - $this->Point_Y[$i] . "," . $this->Point_X[0] . " " . - $this->Point_Y[0];
                            else
                                $strWkt_M[$i1] .= $this->Point_X[$i] . " " . - $this->Point_Y[$i];
                        }
                    }
                }
                // WKT value
                // (250 150,300 200,350 150)
                $strWkt_M[$i1] = "(" . $strWkt_M[$i1] . ")";
                // echo $strWkt_M[$i1]."\n";
                if ($this->blnConverseY == false) {
                    $this->Point_Xmax_M[$i1] = max($this->Point_X);
                    $this->Point_Ymax_M[$i1] = max($this->Point_Y);
                    $this->Point_Xmin_M[$i1] = min($this->Point_X);
                    $this->Point_Ymin_M[$i1] = min($this->Point_Y);
                } else {
                    $this->Point_Xmax_M[$i1] = max($this->Point_X);
                    $this->Point_Ymax_M[$i1] = - min($this->Point_Y);
                    $this->Point_Xmin_M[$i1] = min($this->Point_X);
                    $this->Point_Ymin_M[$i1] = - max($this->Point_Y);
                }
                // echo $this->Point_Xmax_M[$i1]."\n";
                // echo $this->Point_Ymax_M[$i1]."\n";
                // echo $this->Point_Xmin_M[$i1]."\n";
                // echo $this->Point_Ymin_M[$i1]."\n";
                $bPathClose = false;
            }
            // Point to absolute(L) method
            if (strchr($path_M_devided[$i1], 'L')) {
                // Replace 'L' with space
                // Here cant not use $this->pathString,why?
                $pathString = strtr($path_M_devided[$i1], 'L', ' ');
                if (strstr($pathString, 'Z') OR strstr($pathString, 'z')) {
                    $bPathClose = true;
                }
                // Replace 'Z' with space
                // $pathString = strtr($pathString, 'Z', ' ');
                // $$pathString = strtr($pathString, 'z', ' ');
                $pathString = str_replace('Z', '', $pathString);
                $pathString = str_replace('z', '', $pathString);
                // echo $pathString."|"."\n";
                // These are some spaces in the path, but the number is unclear, they must be delete
                $iSpace = 3;
                // $iSpace=strlen($pathString);
                // $iSpace=iSpace;
                // echo $iSpace;
                for($iSpace;$iSpace > 0;$iSpace--) {
                    $strChars = " ";
                    for($iaddS = 0;$iaddS < $iSpace;$iaddS++) {
                        $strChars = $strChars . " ";
                    }
                    $pathString = str_replace($strChars, ' ', $pathString);
                }
                // echo $pathString."|"."\n";
                // Before explode, all empty in front and at end must be sure that is deleted!!!!!
                $Array_Point = explode(" ", trim($pathString));
                $this->Number_Point_M[$i1] = count($Array_Point) / 2;

                for ($j = 0;$j < ($this->Number_Point_M[$i1]) * 2;$j++) {
                    // All space must be deleted
                    $PointXY[$j] = trim($Array_Point[$j]);
                }
                // echo $pathString;
                for($i = 0;$i < $this->Number_Point_M[$i1];$i++) {
                    $this->Point_X[$i] = $PointXY[$i * 2];
                    $this->Point_Y[$i] = $PointXY[$i * 2 + 1];
                    // WKT value
                    // 250 150,300 200,350 150
                    if ($this->blnConverseY == false) {
                        if ($i != $this->Number_Point_M[$i1]-1) {
                            $strWkt_M[$i1] .= $this->Point_X[$i] . " " . $this->Point_Y[$i] . ",";
                        } else {
                            if ($bPathClose)// add the first point if is close path
                                $strWkt_M[$i1] .= $this->Point_X[$i] . " " . $this->Point_Y[$i] . "," . $this->Point_X[0] . " " . $this->Point_Y[0];
                            else
                                $strWkt_M[$i1] .= $this->Point_X[$i] . " " . $this->Point_Y[$i];
                        }
                    } else {
                        if ($i != $this->Number_Point_M[$i1]-1) {
                            $strWkt_M[$i1] .= $this->Point_X[$i] . " " . - $this->Point_Y[$i] . ",";
                        } else {
                            if ($bPathClose)// add the first point if is close path
                                $strWkt_M[$i1] .= $this->Point_X[$i] . " " . - $this->Point_Y[$i] . "," . $this->Point_X[0] . " " . - $this->Point_Y[0];
                            else
                                $strWkt_M[$i1] .= $this->Point_X[$i] . " " . - $this->Point_Y[$i];
                        }
                    }
                }

                $strWkt_M[$i1] = "(" . $strWkt_M[$i1] . ")";
                // echo $strWkt_M[$i1]."\n";
                if ($this->blnConverseY == false) {
                    $this->Point_Xmax_M[$i1] = max($this->Point_X);
                    $this->Point_Ymax_M[$i1] = max($this->Point_Y);
                    $this->Point_Xmin_M[$i1] = min($this->Point_X);
                    $this->Point_Ymin_M[$i1] = min($this->Point_Y);
                } else {
                    $this->Point_Xmax_M[$i1] = max($this->Point_X);
                    $this->Point_Ymax_M[$i1] = - min($this->Point_Y);
                    $this->Point_Xmin_M[$i1] = min($this->Point_X);
                    $this->Point_Ymin_M[$i1] = - max($this->Point_Y);
                }
                // echo $this->Point_Xmax_M[$i1]."\n";
                // echo $this->Point_Ymax_M[$i1]."\n";
                // echo $this->Point_Xmin_M[$i1]."\n";
                // echo $this->Point_Ymin_M[$i1]."\n";
                $bPathClose = false;
            }
            // ##################################################################
            // calculate the last result
            // ##################################################################
            // output WKT polygon value
            // (250 150,300 200,350 150),(250 150,300 200,350 150)
            // Maybe these one $strWkt_M[$i1] has null value and it will cause that ',,' will appear, which cant not pass the mysql's test
            // if d="M-3324 -1200", the $strWkt_M[$i1] will be none value, to prevent this, dont let it be puted into array
            if ($strWkt_M[$i1] != "") {
                if ($i1 != $this->Number_path_M_devided) {
                    $this->strWkt .= $strWkt_M[$i1] . "," ;
                } else {
                    $this->strWkt .= $strWkt_M[$i1] ;
                }
            }
            // echo $strWkt . "\n";
        } //$i1 for
        $this->Point_Xmin = $resultarray[0] = min($this->Point_Xmin_M) ;
        $this->Point_Ymin = $resultarray[1] = min($this->Point_Ymin_M) ;
        $this->Point_Xmax = $resultarray[2] = max($this->Point_Xmax_M);
        $this->Point_Ymax = $resultarray[3] = max($this->Point_Ymax_M);
        // echo $this->Point_Xmax."\n";
        // echo $this->Point_Ymax."\n";
        // echo $this->Point_Xmin."\n";
        // echo $this->Point_Ymin."\n";
        // MultiLineString((-3142 3461,51 13,89 25,47 14,66 19,229 67,3667 -14,-3142 3461),(250 150,300 200,350 150))
        if ($this->Number_path_M_devided == 1) {
            $this->strWkt = $resultarray[4] = "LINESTRING" . $this->strWkt . "";
        } else if ($this->Number_path_M_devided > 1) {
            // Maybe these one $strWkt_M[$i1] has null value and it will cause that ',,' will appear, which cant not pass the mysql's test
            // if d="M-3324 -1200", the $strWkt_M[$i1] will be none, maybe two or more $strWkt_M[$i1] have none value backtoback(consequently)
            // It must be prevented before!
            // $strWkt = str_replace(',,', ',', $this->pathString);
            $this->strWkt = "MultiLineString(" . $this->strWkt . ")";
            // MultiLineString((1388.62 1388.22,1391.95 1377.56,1393.28 1367.56,1399.95 1365.56,1430.62 1375.56),) wrong format!!
            // replace ,) wiht ), the , must be followed with number or (
            $this->strWkt = $resultarray[4] = str_replace(',)', ')', $this->strWkt);
        }
        // echo $this->strWkt . "\n";
        return $resultarray;
    }
}
// ##################################################################
// Test
// ##################################################################
// define('iSpace', 10);
// $pathString='M250  ,            150 L150    350 L50 , 250 Z';
// $pathString='M25, 15   l15   35 l5 ,  25 Z  ';
// $pathString='M-3142,3461l51 13 89 25 47 14 66 19 229 67 3667,-14M250 150 l50          50 50        50';
// $pathString='M-3142,3461L51 13 89 25 47 14 66 19 229 67 3667,-14ZM250 150 l50 50 50 -50';
// 2.224e-016 means very small number,  2.224e+015 means very big number, so when we find 2.224e-016, turn it to 0;
// $pathString='M1325 19.4444 L1347.78 48.3333 L1341.67 55 L1351.67 60.5556 L1356.67 56.6667 L1373.33 21.1111 L1374.44 5.55556 L1367.22 -3.33067e-016 L1356.11 2.77778 L1348.33 12.7778 L1340.56 4.44444';
// $pathString='M1388.62 1388.22 L1391.95 1377.56 L1393.28 1367.56 L1399.95 1365.56 L1430.62 1375.56 M143000.62 137400.89';
// $Path_MLZParser=new Path_MLZParser();
// $Path_MLZParser->parser($pathString);
// ====================================================================================
/**
 *
 * @description :This class is used for path command in SVG,which has all commands in SVG
 * Z = closepath or not used(for NoneRegion_ML)
 * M = moveto
 * L(l) = lineto
 * H = horizontal lineto
 * V = vertical lineto
 * C = curveto
 * S = smooth curveto
 * Q = quadratic Belzier curve
 * T = smooth quadratic Belzier curveto
 * A = elliptical Arc
 */
class Path_RegionParser {
    private $pathString;
    private $Point_X;
    private $Point_Y;
    public $Point_Xmax;
    public $Point_Ymax;
    public $Point_Xmin;
    public $Point_Ymin;
    private $Number_Point;
    // $Number_path_M_devided is number of array
    public $Number_path_M_devided;
    // $Number_Point_M is array storing the points number of each new path from new start point M
    public $Number_Point_M;
    public $blnConverseY = false;

    public function parser($pathString)
    {
        $resultarray = array();
        // delete the 'e-016' in 2.224e-016
        $this->pathString = str_replace('e-016', ' ', $pathString);
        // Replace '-' with ' -'
        $this->pathString = str_replace('-', ' -', $this->pathString);
        // Replace ',' with space
        $this->pathString = str_replace(',', ' ', $this->pathString);
        // Replace 'Z' with space
        $pathStringTemp = strtr($this->pathString, 'Z', ' ');
        // ----------------------------------------------------------
        /**
         * If These more than one start point M
         */
        $path_M_devided = explode("M", trim($pathStringTemp));
        // Because even nothing before the first M, it is also devided from the first M
        $this->Number_path_M_devided = count($path_M_devided)-1;
        // $i1 must be set from 1, because at 0 is nothing
        for($i1 = 1;$i1 <= $this->Number_path_M_devided;$i1++) {
            // Point to relative(l) method
            if (strchr($path_M_devided[$i1], 'l')) {
                // //Replace 'l' with space
                // $pathString = strtr($path_M_devided[$i1],'l',' ');
                // echo $pathString."|"."\n";
                // $pathString = strrpos($pathString,'l');
                $pathString = trim($path_M_devided[$i1]);
                $iSpace = 3;
                // $iSpace=iSpace;
                for($iSpace;$iSpace > 0;$iSpace--) {
                    $strChars = " ";
                    for($iaddS = 0;$iaddS < $iSpace;$iaddS++) {
                        $strChars = $strChars . " ";
                    }
                    $pathString = str_replace($strChars, ' ', $pathString);
                }
                echo $pathString . "||\n";
                $number_l = substr_count($pathString, 'l');
                // echo $number_l."\n";
                $path_l_devided = explode("l", trim($pathString));
                // $number_l = count($path_l_devided)-1;
                // echo $number_l."\n";
                for($i2 = 0;$i2 <= $number_l;$i2++) {
                    $b_findChar = false;
                    // find the last coordinate of the first string, for next 'l'
                    if ($i2 == 0) {
                        // Delete all command and empty in front and at end
                        $path_l_devided_0 = strtr($path_l_devided[$i2], 'H', ' ');
                        $path_l_devided_0 = strtr($path_l_devided_0, 'V', ' ');
                        $path_l_devided_0 = strtr($path_l_devided_0, 'C', ' ');
                        $path_l_devided_0 = strtr($path_l_devided_0, 'S', ' ');
                        $path_l_devided_0 = strtr($path_l_devided_0, 'Q', ' ');
                        $path_l_devided_0 = strtr($path_l_devided_0, 'T', ' ');
                        $path_l_devided_0 = trim(strtr($path_l_devided_0, 'A', ' '));
                        // 3 spaces to 1 space
                        $path_l_devided_0 = str_replace('   ', ' ', $path_l_devided_0);

                        echo $path_l_devided_0 . "\n";
                        $Array_Point_0_path_l_devided_0 = explode(" ", trim($path_l_devided_0));
                        $Number_Point_M_path_l_devided_0[$i1] = count($Array_Point_0_path_l_devided_0) / 2;
                        for ($j = 0;$j < ($Number_Point_M_path_l_devided_0[$i1]) * 2;$j++) {
                            $PointXY_path_l_devided_0[$j] = trim($Array_Point_0_path_l_devided_0[$j]);
                        }
                        // echo $pathString;
                        for($ix = 0;$ix < $Number_Point_M_path_l_devided_0[$i1];$ix++) {
                            $Point_X_path_l_devided_0[$ix] = $PointXY_path_l_devided_0[$ix * 2];
                            $Point_Y_path_l_devided_0[$ix] = $PointXY_path_l_devided_0[$ix * 2 + 1];
                            $Point_X_path_l_devided_0last = $Point_X_path_l_devided_0[$Number_Point_M_path_l_devided_0[$i1]-1];
                            $Point_Y_path_l_devided_0last = $Point_Y_path_l_devided_0[$Number_Point_M_path_l_devided_0[$i1]-1];
                        }
                        $Point_Xmax_M_path_l_devided_0[$i1] = max($Point_X_path_l_devided_0);
                        $Point_Ymax_M_path_l_devided_0[$i1] = max($Point_Y_path_l_devided_0);
                        $Point_Xmin_M_path_l_devided_0[$i1] = min($Point_X_path_l_devided_0);
                        $Point_Ymin_M_path_l_devided_0[$i1] = min($Point_Y_path_l_devided_0);
                        // ===>To here we find the max etc in the first l devided string, and find the last XY for next l string
                        // echo $Point_X_path_l_devided_0last." ";
                        // echo $Point_Y_path_l_devided_0last."\n";
                    }
                    // find any command, and devided the string with this First found command.
                    if ($i2 > 0) {
                        if (strchr($path_l_devided[$i2], 'H')) {
                            $b_findChar = true;
                            $f_char = 'H';
                        } elseif (strchr($path_l_devided[$i2], 'V')) {
                            $b_findChar = true;
                            $f_char = 'V';
                        } elseif (strchr($path_l_devided[$i2], 'C')) {
                            $b_findChar = true;
                            $f_char = 'C';
                        } elseif (strchr($path_l_devided[$i2], 'S')) {
                            $b_findChar = true;
                            $f_char = 'S';
                        } elseif (strchr($path_l_devided[$i2], 'Q')) {
                            $b_findChar = true;
                            $f_char = 'Q';
                        } elseif (strchr($path_l_devided[$i2], 'T')) {
                            $b_findChar = true;
                            $f_char = 'T';
                        } elseif (strchr($path_l_devided[$i2], 'A')) {
                            $b_findChar = true;
                            $f_char = 'A';
                        } elseif (strchr($path_l_devided[$i2], 'Z')) {
                            $b_findChar = true;
                            $f_char = 'Z';
                        }
                        // Only l command in this string, add up all the dx and dy
                        if ($b_findChar == false) {
                            $path_l_devided[$i2] = trim($path_l_devided[$i2]);
                            echo $path_l_devided[$i2] . "\n";
                            $Array_Point_path_l_devided_i2f = explode(" ", $path_l_devided[$i2]);
                            $N_P_M_path_l_devided_i2f[$i1] = count($Array_Point_path_l_devided_i2f) / 2;

                            for ($jx2 = 0;$jx2 < $N_P_M_path_l_devided_i2f[$i1] * 2;$jx2++) {
                                // All space must be deleted
                                $Point_DxDy_path_l_devided_i2f[$jx2] = trim($Array_Point_path_l_devided_i2f[$jx2]);
                            }

                            for($ix2 = 0;$ix2 < $N_P_M_path_l_devided_i2f[$i1];$ix2++) {
                                $Point_Dx_path_l_devided_i2f[$ix2] = $Point_DxDy_path_l_devided_i2f[$ix2 * 2];
                                $Point_Dy_path_l_devided_i2f[$ix2] = $Point_DxDy_path_l_devided_i2f[$ix2 * 2 + 1];
                            }
                            for($ix2 = 0;$ix2 < $N_P_M_path_l_devided_i2f[$i1];$ix2++) {
                                $Point_Dx_path_l_devided_i2f_add = $Point_Dx_path_l_devided_i2f_add + $Point_Dx_path_l_devided_i2f[$ix2];
                                $Point_Dy_path_l_devided_i2f_add = $Point_Dy_path_l_devided_i2f_add + $Point_Dy_path_l_devided_i2f[$ix2];
                            }
                            // echo $Point_Dx_path_l_devided_i2f_add."!";
                            // echo $Point_Dy_path_l_devided_i2f_add."\n";
                        }

                        if ($b_findChar == true) {
                            // ---------------------------------------------------------------------
                            // Get the string between 'l' and the first command, those are  the dx and dy I need to add up
                            $path_fchar_devided_front[$i2] = trim(substr($path_l_devided[$i2], 1, strpos($path_l_devided[$i2], $f_char)-1));
                            echo $path_fchar_devided_front[$i2] . "\n";
                            $Array_Point_path_l_devided_i2t[$i2] = explode(" ", $path_fchar_devided_front[$i2]);
                            $N_P_M_path_l_devided_i2t[$i1][$i2] = count($Array_Point_path_l_devided_i2t[$i2]) / 2;

                            for ($jx2 = 0;$jx2 < $N_P_M_path_l_devided_i2t[$i1][$i2] * 2;$jx2++) {
                                // All space must be deleted
                                $Point_DxDy_path_l_devided_i2t[$i2][$jx2] = trim($Array_Point_path_l_devided_i2t[$i2][$jx2]);
                            }

                            for($ix2 = 0;$ix2 < $N_P_M_path_l_devided_i2t[$i1][$i2];$ix2++) {
                                $Point_Dx_path_l_devided_i2t[$i2][$ix2] = $Point_DxDy_path_l_devided_i2t[$i2][$ix2 * 2];
                                $Point_Dy_path_l_devided_i2t[$i2][$ix2] = $Point_DxDy_path_l_devided_i2t[$i2][$ix2 * 2 + 1];
                            }
                            // for($ix2=0;$ix2<$N_P_M_path_l_devided_i2t[$i1];$ix2++){
                            // $Point_Dx_path_l_devided_i2t_add =$Point_Dx_path_l_devided_i2t_add + $Point_Dx_path_l_devided_i2t[$ix2];
                            // $Point_Dy_path_l_devided_i2t_add =$Point_Dy_path_l_devided_i2t_add + $Point_Dy_path_l_devided_i2t[$ix2];
                            // }
                            // //echo $Point_Dx_path_l_devided_i2t_add."!";
                            // //echo $Point_Dy_path_l_devided_i2t_add."\n";
                            $Point_Dx_path_l_devided_i2t_add[$i2] = array_sum($Point_Dx_path_l_devided_i2t[$i2]);
                            $Point_Dy_path_l_devided_i2t_add[$i2] = array_sum($Point_Dy_path_l_devided_i2t[$i2]);
                            // echo $Point_Dx_path_l_devided_i2t_add[$i2]."!tf!";
                            // echo $Point_Dy_path_l_devided_i2t_add[$i2]."\n";
                            // ---------------------------------------------------------------------
                            // Get the string after the first command, those are  the normal command
                            $path_fchar_devided_behind[$i2] = trim(substr($path_l_devided[$i2], strpos($path_l_devided[$i2], $f_char) + 1, strlen($path_l_devided[$i2])));
                            // echo $path_fchar_devided_behind[$i2]."\n";
                            $Array_Point_0_path_l_devided_i2[$i2] = explode(" ", $path_fchar_devided_behind[$i2]);
                            $Number_Point_M_path_l_devided_i2[$i2] = count($Array_Point_0_path_l_devided_i2[$i2]) / 2;
                            for ($j = 0;$j < ($Number_Point_M_path_l_devided_i2[$i2]) * 2;$j++) {
                                $PointXY_path_l_devided_i2[$i2][$j] = trim($Array_Point_0_path_l_devided_i2[$i2][$j]);
                            }
                            // echo $pathString;
                            for($ix = 0;$ix < $Number_Point_M_path_l_devided_i2[$i2];$ix++) {
                                $Point_X_path_l_devided_i2[$i2][$ix] = $PointXY_path_l_devided_i2[$i2][$ix * 2];
                                $Point_Y_path_l_devided_i2[$i2][$ix] = $PointXY_path_l_devided_i2[$i2][$ix * 2 + 1];
                            }
                            $Point_Xmax_M_path_l_devided_i2[$i1][$i2] = max($Point_X_path_l_devided_i2[$i2]);
                            $Point_Ymax_M_path_l_devided_i2[$i1][$i2] = max($Point_Y_path_l_devided_i2[$i2]);
                            $Point_Xmin_M_path_l_devided_i2[$i1][$i2] = min($Point_X_path_l_devided_i2[$i2]);
                            $Point_Ymin_M_path_l_devided_i2[$i1][$i2] = min($Point_Y_path_l_devided_i2[$i2]);
                            // echo $Point_Xmin_M_path_l_devided_i2[$i1][$i2]."!tb!";
                            // echo $Point_Ymin_M_path_l_devided_i2[$i1][$i2]."\n";
                        } //if ($b_findChar==true)
                    }
                }
                // echo $Point_Xmax_M_path_l_devided_0[$i1]."!first!";
                // echo $Point_Ymax_M_path_l_devided_0[$i1]."|";
                // echo $Point_Xmin_M_path_l_devided_0[$i1]."|";
                // echo $Point_Ymin_M_path_l_devided_0[$i1]."\n";
                // echo array_sum($Point_Dx_path_l_devided_i2t_add)."!tfadd!";
                // echo array_sum($Point_Dy_path_l_devided_i2t_add)."\n";
                // echo array_sum($Point_Xmax_M_path_l_devided_i2[$i1])."!tbadd!";
                // echo array_sum($Point_Ymax_M_path_l_devided_i2[$i1])."|";
                // echo array_sum($Point_Xmin_M_path_l_devided_i2[$i1])."|";
                // echo array_sum($Point_Xmin_M_path_l_devided_i2[$i1])."\n";
                // $pathString = substr($pathString, 1, strlen($pathString)-1);
                // echo $pathString."||\n";
            }
            // Point to absolute(L) method
            if (strchr($path_M_devided[$i1], 'L')) {
                // Replace 'L' with space
                $pathString = strtr($path_M_devided[$i1], 'L', ' ');
                // Replace 'H' 'V' 'C' 'S' 'Q' 'T' 'A' with space
                $pathString = strtr($path_M_devided[$i1], 'H', ' ');
                $pathString = strtr($path_M_devided[$i1], 'V', ' ');
                $pathString = strtr($path_M_devided[$i1], 'C', ' ');
                $pathString = strtr($path_M_devided[$i1], 'S', ' ');
                $pathString = strtr($path_M_devided[$i1], 'Q', ' ');
                $pathString = strtr($path_M_devided[$i1], 'T', ' ');
                $pathString = strtr($path_M_devided[$i1], 'A', ' ');
                // echo $pathString."|"."\n";
                // These are some spaces in the path, but the number is unclear, they must be delete
                $iSpace = 3;
                // $iSpace=strlen($pathString);
                // $iSpace=iSpace;
                // echo $iSpace;
                for($iSpace;$iSpace > 0;$iSpace--) {
                    $strChars = " ";
                    for($iaddS = 0;$iaddS < $iSpace;$iaddS++) {
                        $strChars = $strChars . " ";
                    }
                    $pathString = str_replace($strChars, ' ', $pathString);
                }
                // echo $pathString."|"."\n";
                // Before explode, all empty in front and at end must be sure that is deleted!!!!!
                $Array_Point = explode(" ", trim($pathString));
                $this->Number_Point_M[$i1] = count($Array_Point) / 2;

                for ($j = 0;$j < ($this->Number_Point_M[$i1]) * 2;$j++) {
                    // All space must be deleted
                    $PointXY[$j] = trim($Array_Point[$j]);
                }
                // echo $pathString;
                for($i = 0;$i < $this->Number_Point_M[$i1];$i++) {
                    $this->Point_X[$i] = $PointXY[$i * 2];
                    $this->Point_Y[$i] = $PointXY[$i * 2 + 1];
                }

                $this->Point_Xmax_M[$i1] = max($this->Point_X);
                $this->Point_Ymax_M[$i1] = max($this->Point_Y);
                $this->Point_Xmin_M[$i1] = min($this->Point_X);
                $this->Point_Ymin_M[$i1] = min($this->Point_Y);
                // echo $this->Point_Xmax_M[$i1]."\n";
                // echo $this->Point_Ymax_M[$i1]."\n";
                // echo $this->Point_Xmin_M[$i1]."\n";
                // echo $this->Point_Ymin_M[$i1]."\n";
            }
        } //$i1 for
        $this->Point_Xmin = $resultarray[0] = min($this->Point_Xmin_M);
        $this->Point_Ymin = $resultarray[1] = min($this->Point_Ymin_M);
        $this->Point_Xmax = $resultarray[2] = max($this->Point_Xmax_M);
        $this->Point_Ymax = $resultarray[3] = max($this->Point_Ymax_M);
        // echo $this->Point_Xmax."\n";
        // echo $this->Point_Ymax."\n";
        // echo $this->Point_Xmin."\n";
        // echo $this->Point_Ymin."\n";
        return $resultarray;
    }
    // function xmaxadd2array(){
    // //$xmaxadd2array =
    // }
}
// define('iSpace', 5);
// $pathString='M250  ,            150 L150    350 L50 , 250 Z';
// $pathString='M25, 15  C 27 30 l15   35 0 4 l 5 ,  25 C 33 44l 4 4 3  3S 27 30 4 5Z  ';
// $pathString='M-3142,3461l51 13 89 25 47 14 66 19 229 67 3667,-14M250 150 l50          50 50        50';
// $pathString='M-3142 ,3461L51 13 89 25 47 14    66 19 229 67 3667 ,-14M250      150 L50 50 50 -50';
// $Path_RegionParser=new Path_RegionParser($pathString);
// ====================================================================================
?>