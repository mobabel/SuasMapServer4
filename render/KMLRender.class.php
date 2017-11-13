<?php
/**
 * KML KMZ 3D Class
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
class KMLRender {
    private $minx;
    private $miny;
    private $maxx;
    private $maxy;
    private $width;
    private $height;
    private $enablestretchmap;
    private $finaloutput;
    private $useStream; //set to false, then output gzip, ture means directly output map

    public $file_name;

    public $poix;
    public $poiy;
    public $poiz;
    public $pitch;
    public $yaw;
    public $roll;
    public $distance;
    public $aov;
    public $use3d;

    public function setRender($minx, $miny, $maxx, $maxy, $width, $height, $useStream,
        $poix, $poiy, $poiz, $pitch , $yaw, $roll, $distance, $aov, $enablestretchmap,$use3d)
    {
        $this->minx = $minx;
        $this->miny = $miny;
        $this->maxx = $maxx;
        $this->maxy = $maxy;
        $this->width = $width;
        $this->height = $height;
        $this->enablestretchmap = $enablestretchmap;
        $this->useStream = $useStream;

        $this->file_name = "../cache/test.kml";

        $this->poix = $poix;
        $this->poiy = $poiy;
        $this->poiz = $poiz;
        $this->pitch = $pitch;
        $this->yaw = $yaw; //+90
        $this->roll = $roll;
        $this->distance = $distance;
        $this->aov = $aov;
        $this->use3d = $use3d;

        $this->CreateKML();
    }

    /**
     *
     * @DESCRIPTION :Class Constructor.	**
     */
    public function KMLRender()
    {
    }

    function CreateKML()
    {
        $this->KMLDocument();
        $this->KMLFragmentBegin();

        $this->Name(SUAS_NAME." KML Map");
        $this->Description("Generator: ".SUAS_NAME.", ".SUAS_COPYRIGHT. "\n" . "Created:" . date("Ymd") . "\n");
        $this->Region();

        $viewx = $this->poix + $this->distance * round(sin($this->pitch), 16) * round(sin($this->yaw), 16);
        $viewy = $this->poiy - $this->distance * round(sin($this->pitch), 16) * round(cos($this->yaw), 16);
        $viewz = $this->poiz + $this->distance * round(cos($this->pitch), 16);
        // round($this->pitch/pi(), 4)." ".round($this->roll/pi(), 4).round(-$this->yaw/pi(), 4)

        //$this->Viewpoint($viewx, $viewy, $viewz, 127.2393107680517, $this->pitch, ($this->yaw-90));

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

    public function KMLDocument()
    {
        $this->setfinaloutput('<?xml version="1.0" encoding="utf-8"?>' . "\n");
    }


    function KMLFragmentBegin()
    {
        $this->setfinaloutput('<kml xmlns="http://earth.google.com/kml/2.1">' . "\n");
        $this->setfinaloutput('<Document>' . "\n");
    }

    function KMLFragmentEnd()
    {
        $this->setfinaloutput("</Document>\n");
        $this->setfinaloutput("</kml>\n");
    }

    function Region()
    {
        $W = $this->maxx - $this->minx;
        $H = $this->maxy - $this->miny;

        $this->setfinaloutput('<Region> ' . "\n");
        $this->setfinaloutput('<LatLonAltBox>' . "\n");
        $this->setfinaloutput('<north>' . $this->maxy . '</north>' . "\n");
        $this->setfinaloutput('<south>' . $this->miny . '</south>' . "\n");
        $this->setfinaloutput('<east>' . $this->maxx . '</east>' . "\n");
        $this->setfinaloutput('<west>' . $this->minx . '</west>' . "\n");
        $this->setfinaloutput('</LatLonAltBox>' . "\n");
        $this->setfinaloutput('<Lod>' . "\n");
        $this->setfinaloutput('<minLodPixels>128</minLodPixels>' . "\n");
        $this->setfinaloutput('<maxLodPixels>65536</maxLodPixels>' . "\n");
        $this->setfinaloutput('</Lod>' . "\n");
        $this->setfinaloutput('</Region> ' . "\n");
    }

    function Name($name)
    {
        $this->setfinaloutput('<name>' . $name . '</name>' . "\n");
    }

    function Description($description)
    {
        $this->setfinaloutput('<description><![CDATA[' . $description . ']]></description>' . "\n");
    }

    function PlacemarkBegin($name)
    {
        $this->setfinaloutput('<Placemark>' . "\n");
        $this->Name($name);
    }

    function PlacemarkEnd()
    {
        $this->setfinaloutput('</Placemark>' . "\n");
    }

    function Extrude($n)
    {
        $this->setfinaloutput('<extrude>' . $n . '</extrude>' . "\n");
    }
    function Tessellate($n)
    {
        $this->setfinaloutput('<tessellate>' . $n . '</tessellate>' . "\n");
    }
    function AltitudeMode($n = "relativeToGround")
    {
        $this->setfinaloutput('<altitudeMode>' . $n . '</altitudeMode>' . "\n");
    }

    function Viewpoint($lon = "", $lat = "",$alt = 0, $range = 127.2393107680517, $tilt = 0, $heading = 0)
    {
        $this->setfinaloutput("<LookAt>\n");
        $this->setfinaloutput("<longitude>$lon</longitude>\n");
        $this->setfinaloutput("<latitude>$lat</latitude>\n");
        $this->setfinaloutput("<altitude>$alt</altitude>\n");
        $this->setfinaloutput("<range>127.2393107680517</range>\n");
        $this->setfinaloutput("<tilt>$tilt</tilt>\n");
        $this->setfinaloutput("<heading>$heading</heading>\n");
        $this->setfinaloutput("</LookAt>\n");
    }

    //for import model
    function TransformBegin($lon = "", $lat = "",$alt = 0,$heading = 0, $tilt = 0, $roll = 0, $x = 1, $y = 1, $z = 1)
    {
	    $this->setfinaloutput("<Location>\n");
	    $this->setfinaloutput("<longitude>$lon</longitude>\n");
	    $this->setfinaloutput("<latitude>$lat</latitude>\n");
	    $this->setfinaloutput("<altitude>$alt</altitude>\n");
	    $this->setfinaloutput("</Location>\n");
        $this->setfinaloutput("<Orientation>\n");
        $this->setfinaloutput("<heading>$heading</heading>\n");
        $this->setfinaloutput("<tilt>$tilt</tilt>\n");
      	$this->setfinaloutput("<roll>$roll</roll>\n");
    	$this->setfinaloutput("</Orientation>\n");
    	$this->setfinaloutput("<Scale>\n");
      	$this->setfinaloutput("<x>$x</x>\n");
      	$this->setfinaloutput("<y>$y</y>\n");
      	$this->setfinaloutput("<z>$z</z>\n");
    	$this->setfinaloutput("</Scale>\n");
    }

    function GroupBegin($name)
    {
        $this->setfinaloutput("<Folder>\n");
        $this->Name($name);
    }

    function GroupEnd()
    {
        $this->setfinaloutput("</Folder>\n");
    }

    function ModelBegin($id)
    {
        $this->setfinaloutput("<Model id=\"$id\">\n");
    }

    function ModelEnd()
    {
        $this->setfinaloutput("</Model>\n");
    }

    function Coordinate($point = "")
    {
        $this->setfinaloutput("<coordinates>\n");
        $this->setfinaloutput($point . "\n");
        $this->setfinaloutput("</coordinates>\n");
    }

    function Style($stroke, $fillColor)
    {
        $this->setfinaloutput("<Style>\n");
        $this->setfinaloutput("<LineStyle>\n");
        $this->setfinaloutput("<color>" . $stroke . "</color>\n");
        $this->setfinaloutput("</LineStyle>\n");
        $this->setfinaloutput("<PolyStyle>\n");
        $this->setfinaloutput("<color>" . $fillColor . "</color>\n");
        $this->setfinaloutput("</PolyStyle>\n");
        $this->setfinaloutput("</Style>\n");
    }

    function Text($p_string = "")
    {
        $this->setfinaloutput("<Text ");
        $this->setfinaloutput("string=\"$p_string\" ");
        $this->setfinaloutput("/>\n");
    }

    function KMLText($x = 0, $y = 0, $elevation = 0, $text = "", $Font, $FontSize = 5, $angle = 0, $color)
    {
        if ($elevation == "")$elevation = 0;
        $this->TransformBegin("1 1 1", "0 0 1 0", "0 0 0", "0 0 1 " . round($angle / (2 * pi()), 4), "$x $y $elevation", "0 0 0", "-1 -1 -1");
        $this->ShapeBegin();
        $this->Text($text);
        $this->AppearanceBegin();
        $this->Material($color, "0.3", "0.0 0.0 0.0", "0.0 0.0 0.0", "0.3", "0");
        $this->AppearanceEnd();
        $this->ShapeEnd();
        $this->TransformEnd();
    }
    // Point, Polygon, LinearRing, LineString, Model, MultiGeometry.
    function MultiLinstring($x, $y, $elevation = 0, $lineNumber, $pointNumber, $stroke , $strokewidth, $fillColor, $blnFillLineString, $id)
    {
        $data_x = $x;
        $data_y = $y;
        if ($elevation == "")$elevation = 0;
        if(!$this->use3d)$elevation = 0;

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
            $x = $clx;
            $y = $cly;
            $pointNumber = $newpointNumber;
        }
        $multi = true;
        if($lineNumber==1) $multi = false;
        for($i = 0;$i < $lineNumber;$i++) {
            $coordindex[$i] = "";
            $pointpbt[$i] = "";
            $pointpb = "";
            $pointpt = "";
            $bottom = "";
            $top = "";
            for($j = 0;$j < $pointNumber[$i];$j++) {
                $coord[$j] = array($x[$i][$j], $y[$i][$j]);
                $Pixel_x[$j] = $coord[$j][0];
                $Pixel_y[$j] = $coord[$j][1];
                $valuep[$i][$j * 3 + 0] = $Pixel_x[$j];
                $valuep[$i][$j * 3 + 1] = $Pixel_y[$j];
                $valuept[$i][$j * 3 + 2] = $elevation;

                $pointpt .= $valuep[$i][$j * 3] . "," . $valuep[$i][$j * 3 + 1] . "," . $valuept[$i][$j * 3 + 2] . " ";
            }
            $pointpbt[$i] = $pointpt;

            if(!$multi)
            $this->PlacemarkBegin($id);
            else
            $this->PlacemarkBegin($id . "_" . $i);

            $this->Style($stroke, $fillColor);
            $this->setfinaloutput("<LineString>\n");
            $this->Extrude(1);
            $this->Tessellate(1);
            $this->AltitudeMode("relativeToGround");
            $this->Coordinate($pointpbt[$i]);
            $this->setfinaloutput("</LineString>\n");
            $this->PlacemarkEnd();
        }
    }

    function MultiPolygon($x, $y, $elevation = 0, $lineNumber, $pointNumber, $stroke , $strokewidth, $fillcolor, $id)
    {
        $data_x = $x;
        $data_y = $y;
        if ($elevation == "")$elevation = 0;
        if(!$this->use3d)$elevation = 0;
        $multi = true;
        if($lineNumber==1) $multi = false;
        for($i = 0;$i < $lineNumber;$i++) {
            $coordindex[$i] = "";
            $pointpbt[$i] = "";
            $pointpb = "";
            $pointpt = "";
            $bottom = "";
            $top = "";
            for($j = 0;$j < $pointNumber[$i];$j++) {
                $coord[$j] = array($data_x[$i][$j], $data_y[$i][$j]);
                $Pixel_x[$j] = $coord[$j][0];
                $Pixel_y[$j] = $coord[$j][1];
                $valuep[$i][$j * 3] = $Pixel_x[$j];
                $valuep[$i][$j * 3 + 1] = $Pixel_y[$j];
                $valuept[$i][$j * 3 + 2] = $elevation;

                $pointpt .= $valuep[$i][$j * 3] . "," . $valuep[$i][$j * 3 + 1] . "," . $valuept[$i][$j * 3 + 2] . " ";
            }

            $pointpbt[$i] = $pointpt;

            if(!$multi)
            $this->PlacemarkBegin($id);
            else
            $this->PlacemarkBegin($id . "_" . $i);

            $this->Style($stroke, $fillcolor);
            $this->setfinaloutput("<Polygon>\n");
            $this->Extrude(1);
            $this->Tessellate(1);
            $this->AltitudeMode("relativeToGround");
            $this->setfinaloutput("<outerBoundaryIs>\n");
            $this->setfinaloutput("<LinearRing>\n");
            $this->Coordinate($pointpbt[$i]);
            $this->setfinaloutput("</LinearRing>\n");
            $this->setfinaloutput("</outerBoundaryIs>\n");
            $this->setfinaloutput("</Polygon>\n");
            $this->PlacemarkEnd();
        }
    }


    function Point($x, $y, $elevation = 0, $pointNumber, $pointstyle, $color , $radius, $id)
    {
        $data_x = $x;
        $data_y = $y;
        if ($elevation == "")$elevation = 0;
        if(!$this->use3d)$elevation = 0;

        for($i = 0;
            $i < $pointNumber;
            $i++) {

            $this->PlacemarkBegin($id . "_" . $i);
            $this->Description("");
            $this->Style($color, $color);
            $this->setfinaloutput("<Point>\n");
            $this->AltitudeMode("relativeToGround");
            $this->Coordinate($x[$i].",".$y[$i].",".$elevation);
            $this->setfinaloutput("</Point>\n");
            $this->PlacemarkEnd();
        }
    }


    function Icon($imagelink){
		$this->setfinaloutput("<Icon>\n");
        $this->setfinaloutput("<href>$imagelink</href>\n");
      	$this->setfinaloutput("</Icon>\n");
	}

	function LatLonBox($n, $s, $e, $w){
		$this->setfinaloutput("<LatLonBox>\n");
        $this->setfinaloutput("<north>$n</north>\n");
        $this->setfinaloutput("<south>$s</south>\n");
        $this->setfinaloutput("<east>$e</east>\n");
        $this->setfinaloutput("<west>$w</west>\n");
      	$this->setfinaloutput("</LatLonBox>\n");
	}

    function Image($x, $y, $elevation = 0, $imagelink, $id)
    {
        $this->setfinaloutput("<GroundOverlay>\n");
        $this->Name($id);
        $this->Icon($imagelink);
        $this->LatLonBox($n, $s, $e, $w);
        $this->setfinaloutput("<GroundOverlay>\n");
    }
}

?>