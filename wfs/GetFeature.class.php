<?php
/**
 * GetFeature Class
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
 * @version $3.0$ 2006
 * @Author LI Hui
 * @version $4.0$ 2009.04
 * @Author LI Hui
 */

if($atlas_cfg['enableCache']){
//$cache = new Cache($atlas_cfg['cacheExpiredTime'], Cache::$TYPE_XML, $aid);
//$cache->cacheCheck();
}

$gmlrender = new GMLRender();

$errornumber = 0;
$errorexceptionstring = "";

if ($database->databaseGetErrorMessage() != "") {
    $errornumber = -1;
    $errorexceptionstring = $database->databaseGetErrorMessage();
}
if ($errornumber != 0) {
    $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
} else {
    if ($map_paras['outputformat'] != "" && !equalIgnoreCase($map_paras['outputformat'], "text/xml")) {
        $errornumber = 16;
        $errorexceptionstring = "The OUTPUTFORMAT " . $map_paras['outputformat'] . " is not supported by the server.The only supported OUTPUTFORMAT is text/xml." ;
        $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
    }
    if ($map_paras['outputformat'] == "") {
        $map_paras['outputformat'] = "text/xml";
    }
    if ($map_paras['typename'] == "") {
        $errornumber = 4;
        $errorexceptionstring = "TYPENAME not specified. Please insert  TYPENAME!";
        $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
    }

    $featurenumber = 0;

    $layersvalues = explode(",", $map_paras['typename']);
    $numberofvalueslayer = count($layersvalues);

    for ($i = 0; $i < $numberofvalueslayer; $i++) {
        $rs0 = $database->getRowsByLayer($aid, $layersvalues[$i]);
        $line0 = $database->getColumns($rs0);

        if ($line0 == null AND $map_paras['typename'] != "") {
            $errornumber = 2;
            $errorexceptionstring = "TYPENAME " . $layersvalues[$i] . " not found, Please check your TYPENAME.";
            $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
        }
    }

    /*  <wfs:Query typeName="topp:states">
    <ogc:Filter>
      <ogc:BBOX>
        <ogc:PropertyName>the_geom</ogc:PropertyName>
        <gml:Box srsName="http://www.opengis.net/gml/srs/epsg.xml#4326">
           <gml:coordinates>
               -73.99312376470733,40.76203427979042 -73.9239210030026,40.80129519821393
           </gml:coordinates>
        </gml:Box>
      </ogc:BBOX>
   </ogc:Filter>
  </wfs:Query>
*/

    header("Content-type: text/xml;charset=utf-8");
    print('<?xml version="1.0" encoding="UTF-8"?>');
    print(' <wfs:FeatureCollection service="' . SUAS_CFG_WFS_SERVICE . '" version="' . SUAS_CFG_WFS_VERSION . '"
  outputFormat="GML2"
  xmlns:myns="http://www.ttt.org/myns"
  xmlns:topp="http://www.openplans.org/topp"
  xmlns:wfs="http://www.opengis.net/wfs"
  xmlns:ogc="http://www.opengis.net/ogc"
  xmlns:gml="http://www.opengis.net/gml"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.opengis.net/wfs
                      http://schemas.opengis.net/wfs/1.0.0/WFS-basic.xsd">');

    for ($i = 0; $i < $numberofvalueslayer; $i++) {
        $rs5 = $database->getRows4MetaGroupBy($aid, "srs");
        while ($line5 = $database->getColumns($rs5)) {
            $currentsrs = $line5["srs"];
            $rs6 = $database->getRowsMinMaxXYBySrs($aid, $currentsrs);
            $line6 = $database->getColumns($rs6);
            $totalminx = $line6[0];
            $totalminy = $line6[1];
            $totalmaxx = $line6[2];
            $totalmaxy = $line6[3];
            print('
			<gml:boundedBy>
	<gml:Box srsNam ="' . $currentsrs . '">
	<gml:coordinates>' . $totalminx . ', ' . $totalminy . ', ' . $totalmaxx . ', ' . $totalmaxy . '
	</gml:coordinates >
	</gml:Box>
	</gml:boundedBy>');
            // ===================GML==========================
            $gmlrender->setRender($currentsrs);
            $rs1 = $database->getGeomAsTextBySrsLayer($aid, $currentsrs, $layersvalues[$i]);

            while ($line1 = $database->getColumns($rs1)) {
                if (($map_paras['maxfeatures'] != "" && $featurenumber < $map_paras['maxfeatures']) || $map_paras['maxfeatures'] == "") {
                    $data_id = $line1["id"];
                    $data_Geom = $line1[8];
                    $data_Geom_Type = strtoupper($line1["geomtype"]);
                    $data_xmin = $line1["xmin"];
                    $data_ymin = $line1["ymin"];
                    $data_xmax = $line1["xmax"];
                    $data_ymax = $line1["ymax"];
                    $data_attributes = $line1["attributes"];

                    ?>
	<gml:featureMember>
      <myns:<?=$layersvalues[$i]?> fid="<?=$data_id?>">
        <gml:boundedBy>
        	<gml:Box srsName="<?=$currentsrs?>">
        		<gml:coordinates><?=$data_xmin . "," . $data_ymin . "," . $data_xmax . "," . $data_ymax?></gml:coordinates>
        	</gml:Box>
        </gml:boundedBy>
        <myns:msGeometry>
<?php 
                    switch ($data_Geom_Type) {
                        case 'POINT': {
                                $wktparser = new WKTParser();
                                $wktparser->parse($data_Geom);
                                // readstyle now
                                // $sld->poinStyle = "CIRCLE";
                                $data_x = null;
                                $data_y = null;
                                switch ($wktparser->wktGeomType) {
                                    case "POINT": {
                                            $data_x = $wktparser->wktPointX;
                                            $data_y = $wktparser->wktPointY;
                                            $Number_Point = $wktparser->wktPointNr;
                                            print($gmlrender->createPoints($data_x[0], $data_y[0], $Number_Point));
                                        }
                                        break;
                                    case "MULTIPOINT": {
                                            $data_x = $wktparser->wktPointX;
                                            $data_y = $wktparser->wktPointY;
                                            $Number_Point = $wktparser->wktPointNr;
                                            print($gmlrender->createMultiPoint($data_x, $data_y, $Number_Point));
                                        }
                                }
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
                                // only one point!!!
                                // $gmlrender->createText($data_x[0], $data_y[0], $data_TextContent, $xmlSize, $color_FillColor);
                                print($gmlrender->createPoints($data_x[0], $data_y[0], $Number_Point));
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
                                            print($gmlrender->createLinstring($data_x, $data_y, $Number_Point));
                                        }
                                        break;
                                    case "MULTILINESTRING": {
                                            $data_x = $wktparser->wktMPointX;
                                            $data_y = $wktparser->wktMPointY;
                                            $MNumber_Point = $wktparser->wktMPointNr;
                                            $MLine_Point = $wktparser->wktMLineNr;
                                            print($gmlrender->createMultiLinstring($data_x, $data_y, $MLine_Point, $MNumber_Point));
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
                                            print($gmlrender->createMultiPolygon($data_x, $data_y, $MLine_Point, $MNumber_Point));
                                        }
                                        break;
                                    case "MULTIPOLYGON": {
                                            $data_x = $wktparser->wktMPointX;
                                            $data_y = $wktparser->wktMPointY;
                                            $MNumber_Point = $wktparser->wktMPointNr;
                                            $MLine_Point = $wktparser->wktMLineNr;
                                            print($gmlrender->createMultiPolygon($data_x, $data_y, $MLine_Point, $MNumber_Point));
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
                                $Number_Point = $wktparser->wktPointNr;
                                // $gmlrender->createImage($data_x, $data_y, $Number_Point);
                            }
                            break;
                        // ======================================================================================================
                        // ======================================================================================================
                        case 'UNKNOWN': {
                            }
                            // ======================================================================================================
                            // ======================================================================================================
                    } //switchs
                    print('</myns:msGeometry>');
			//echo $data_attributes;
                    if ($data_attributes != "" && strstr($data_attributes, '<attributes>')) {
                        $dataarray = AttributeParser::extractAttribute($data_attributes);
						if(is_array($dataarray))
						foreach($dataarray as $key => $value){
							//$value = str_replace("&", "&amp;", $value);
							$value = htmlentities(iconv('UTF-8', 'UTF-8//IGNORE', $value), ENT_QUOTES, 'UTF-8');
							if(strtolower($key) == "name"){
								print('<' . $key . '>' . $value . '</' . $key . '>');
							}
							print('<myns:' . $key . '>' . $value . '</myns:' . $key . '>');
						}
                    }
                    else if ($data_attributes != "" && !strstr($data_attributes, '<attributes>')) {
                    	$dataarray = AttributeParser::extractAttribute($data_attributes);
			if(is_array($dataarray))
						foreach($dataarray as $key => $value){	
							//$value = str_replace("&", "&amp;", $value);
							//$value = str_replace("\\\"", "\"", $value);//&quot;
							//$value = str_replace("<br>", "", $value);
							//$value = htmlentities($value, ENT_COMPAT, "UTF-8");
							//TODO
							//mb_convert_encoding($value,'HTML-ENTITIES','UTF-8')
							//iconv('utf-8','utf-8',$value);
							$value = htmlentities(mb_convert_encoding($value,'HTML-ENTITIES','UTF-8'), ENT_QUOTES, 'UTF-8');
							if(strtolower($key) == "name"){
								print('<' . $key . '>' . $value . '</' . $key . '>');
							}						
							print('<myns:' . $key . '>' . $value . '</myns:' . $key . '>');
						}
			else{
                        	print('<myns:Text>' .$dataarray  . '</myns:Text>');
			}
                    }

                    ?>
      </myns:<?=$layersvalues[$i]?>>
    </gml:featureMember>
<?php
                    $featurenumber++;
                }
            }
            if (($map_paras['maxfeatures'] != "" && $featurenumber >= $map_paras['maxfeatures'])) {
                print('<!--MaxFeatures value was set to be ' . $map_paras['maxfeatures'] . '-->');
            }
        }
    }

    print('</wfs:FeatureCollection>');
}

/*if($atlas_cfg['enableCache']){
$cache->caching();
}*/
$database->databaseClose();
exit();
?>