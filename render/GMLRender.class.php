<?php
/**
 * GMLRender.class.php
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
 * @copyright (C) 2006-2009  LI Hui
 * @contact webmaster@easywms.com
 */

class GMLRender {
    private $minx;
    private $miny;
    private $maxx;
    private $maxy;
    private $width;
    private $height;
    private $enablestretchmap;

    public function setRender(/*$minx, $miny, $maxx, $maxy, $width, $height, $enablestretchmap*/$srsname)
    {
        /*$this->minx = $minx;
        $this->miny = $miny;
        $this->maxx = $maxx;
        $this->maxy = $maxy;
        $this->width = $width;
        $this->height = $height;
        $this->enablestretchmap = $enablestretchmap;*/
        $this->srsname = $srsname;
    }

    /**
     *
     * @DESCRIPTION :Class Constructor.														
     */
    public function GMLRender()
    {
    }

    /*
   * @DESCRIPTION createPoints: used to create points and multipoints
   */
    public function createPoints($x, $y, $pointNumber)
    {
        $gml = "<gml:Point srsName=\"$this->srsname\">
       <gml:coordinates>$x,$y</gml:coordinates>
       </gml:Point>";
        return $gml;
    }

    public function createMultiPoint($x, $y, $pointNumber)
    {
        $gml = "<gml:MultiPoint srsName=\"$this->srsname\">";
        for($i = 0;$i < $pointNumber;$i++) {
            $pathD= "";
            $pathD .= $data_x[$i] . "," . $data_y[$i] . " ";
            $pathD = trim($pathD);
            $gml .="<gml:pointMember>
 	            <gml:Point>
 	              <gml:coordinates>$pathD</gml:coordinates>
 	            </gml:Point>
 	          </gml:pointMember>";
        }
        $gml .= "</gml:MultiPoint>";
        return $gml;
    }

/*          <gml:MultiPoint srsName="EPSG:4326">
 	          <gml:pointMember>
 	            <gml:Point>
 	              <gml:coordinates>2.079641,45.001795</gml:coordinates>
 	            </gml:Point>
 	          </gml:pointMember>
 	          <gml:pointMember>
 	            <gml:Point>
 	              <gml:coordinates>2.718330,45.541131</gml:coordinates>
 	            </gml:Point>
 	          </gml:pointMember>
 	        </gml:MultiPoint>*/


    public function createLinstring($x, $y, $pointNumber)
    {
        $data_x = $x;
        $data_y = $y;
        for($i = 0;$i < $pointNumber;$i++) {
            $pathD .= $data_x[$i] . "," . $data_y[$i] . " ";
        }
        $pathD = trim($pathD);
        $gml = "<gml:LineString srsName=\"$this->srsname\">
		<gml:coordinates>$pathD
		</gml:coordinates>
		</gml:LineString>";

        return $gml;
    }

    function createMultiLinstring($x, $y, $lineNumber, $pointNumber)
    {
        $data_x = $x;
        $data_y = $y;
        $gml = "<gml:MultiLineString srsName=\"$this->srsname\">";
        for($i = 0;$i < $lineNumber;$i++) {
            $polygonpoints= "";
            $gml .="<gml:lineStringMember>
                    <gml:LineString>
                    <gml:coordinates>";
            for($j = 0;$j < $pointNumber[$i];$j++) {
                $polygonpoints .= $data_x[$i][$j] . "," . $data_y[$i][$j] . " ";
            }
            $polygonpoints = trim($polygonpoints);
            $gml .=$polygonpoints."</gml:coordinates>
    </gml:LineString>
  </gml:lineStringMember>";
        }
        $gml .= "</gml:MultiLineString>";

/*<gml:MultiLineString srsName="">
  <gml:lineStringMember>
    <gml:LineString>
      <gml:coordinates>0.0,0.0 10.0,10.0 0.0,20.0</gml:coordinates>
    </gml:LineString>
  </gml:lineStringMember>
  <gml:lineStringMember>
    <gml:LineString>
      <gml:coordinates>0.0,30.0 40.0,10.0</gml:coordinates>
    </gml:LineString>
  </gml:lineStringMember>
</gml:MultiLineString>*/
        return $gml;
    }


    // This function not use now, has been replaced by createMultiPolygon!
    public function createPolygon($x, $y, $lineNumber, $pointNumber)
    {
        $data_x = $x;
        $data_y = $y;
        for($i = 0;$i < $lineNumber;$i++) {
            $polygonpoints = "";
            for($j = 0;$j < $pointNumber[$i];$j++) {
                $polygonpoints .= $data_x[$i][$j] . "," . $data_y[$i][$j] . " ";
            }
        }
        $polygonpoints = trim($polygonpoints);
        $gml = "<gml:Polygon srsName=\"$this->srsname\">
          <gml:outerBoundaryIs>
            <gml:LinearRing>
              <gml:coordinates>$polygonpoints</gml:LinearRing>
          </gml:outerBoundaryIs>
        </gml:Polygon>";
        return $gml;
    }

    function createMultiPolygon($x, $y, $lineNumber, $pointNumber)
    {
        $data_x = $x;
        $data_y = $y;
        $gml = "<gml:MultiPolygon srsName=\"$this->srsname\">";
        for($i = 0;$i < $lineNumber;$i++) {
            $polygonpoints= "";
            $gml .="<gml:polygonMember>
                    <gml:Polygon>
                    <gml:outerBoundaryIs>
                    <gml:LinearRing>
					<gml:coordinates>";
            for($j = 0;$j < $pointNumber[$i];$j++) {
                $polygonpoints .= $data_x[$i][$j] . "," . $data_y[$i][$j] . " ";
            }
            $polygonpoints = trim($polygonpoints);
            $gml .=$polygonpoints."</gml:coordinates>
			      </gml:LinearRing>
 	            </gml:outerBoundaryIs>
 	          </gml:Polygon>
 	        </gml:polygonMember>";
        }
        $gml .= "</gml:MultiPolygon>";
	return $gml;
    }


/* 	        <gml:MultiPolygon srsName="EPSG:4326">
 	        <gml:polygonMember>
 	          <gml:Polygon>
 	            <gml:outerBoundaryIs>
 	              <gml:LinearRing>
 	                <gml:coordinates>1.313216,46.690770 1.000968,46.861087 0.887424,47.059790 1.142899,47.244300 1.355795,47.244300 1.554498,47.017211 1.710622,47.059790 1.767394,46.747542 1.313216,46.690770 1.313216,46.690770 </gml:coordinates>
 	              </gml:LinearRing>
 	            </gml:outerBoundaryIs>
 	          </gml:Polygon>
 	        </gml:polygonMember>
 	        <gml:polygonMember>
 	          <gml:Polygon>
 	            <gml:outerBoundaryIs>
 	              <gml:LinearRing>
 	                <gml:coordinates>0.731300,46.605612 -0.191250,46.704963 -0.191250,46.846894 0.177770,46.988824 0.447438,46.960438 0.589369,46.804315 0.688721,46.832701 0.731300,46.605612 0.731300,46.605612 </gml:coordinates>
 	              </gml:LinearRing>
 	            </gml:outerBoundaryIs>
 	          </gml:Polygon>
 	        </gml:polygonMember>
 	        </gml:MultiPolygon>*/
}

?>