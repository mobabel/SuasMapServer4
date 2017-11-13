<?php
/**
* GetFeatureInfo Class
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
* @Description : This show the copyright .
* @contact webmaster@easywms.com
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

if (strcasecmp($map_paras['info_format'], $MAP_WMS_FORMAT['getfeatureinfo']['xml']) != 0 AND strcasecmp($map_paras['info_format'], $MAP_WMS_FORMAT['getfeatureinfo']['html']) != 0 AND $map_paras['info_format'] != "") {
    $errornumber = 3;
    $errorexceptionstring = "Invalid Info_Format '" . $map_paras['format'] . "' given.The \"" . $MAP_WMS_FORMAT['getfeatureinfo']['xml'] . "\" and \"" . $MAP_WMS_FORMAT['getfeatureinfo']['html'] . "\"  are the only supported info_format" ;
}
if ($map_paras['info_format'] == "") {
    // set default info format
    $map_paras['info_format'] = $MAP_WMS_FORMAT['getfeatureinfo']['html'];
}

if ($database->databaseGetErrorMessage() != "") {
    $errornumber = -1;
    $errorexceptionstring = $database->databaseGetErrorMessage();
    $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
}
if ($errornumber == 0) {
    if ($map_paras['width'] == "" OR $map_paras['height'] == "") {
        $errornumber = 13;
        $errorexceptionstring = "The width OR the height cannot be empty. " ;
    }
    if ($maxx <= $minx OR $maxy <= $miny) {
        $errornumber = 15;
        $errorexceptionstring = "The BBox parameters are not valid." ;
    }

    $query_layersvalues = explode(",", $map_paras['query_layers']);
    $numberofquery_valueslayer = count($query_layersvalues);

    $size = getStretchWidthHeight($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $atlas_cfg['enablestretchmap']);
    $map_paras['width'] = $size[0];
    $map_paras['height'] = $size[1];

    $newsquare = getSelectSquare($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $map_paras['pixel_x'], $map_paras['pixel_y'], $map_paras['radius'], $atlas_cfg['enablestretchmap']);
    $x_plus = $newsquare[0];
    $x_minus = $newsquare[1];
    $y_plus = $newsquare[2];
    $y_minus = $newsquare[3];
    $x_real = $newsquare[4];
    $y_real = $newsquare[5];

    for ($i = 0; $i < $numberofquery_valueslayer; $i++) {
        // $rs1 = $database->getSelectFeatureInBoxBy($tb_metaname, $minx, $miny, $maxx, $maxy, $x_plus, $x_minus, $y_plus, $y_minus, $query_layersvalues[$i]);
        // $rs2 = $database->getSelectFeatureInSquareBy($tb_metaname, $x_plus, $x_minus, $y_plus, $y_minus, $query_layersvalues[$i]);
        // $line2 = $database->getColumns($rs2);
        /*$line3 is used to justify one layername with no result, $line3 must have something, unless the layer doesnt exist!
        *1, layername is wrong, $line3 is  null, send exception
        *2, layername is right, but $line2 is null, there is no entities inside,but $line3 is not null, then go on to create XML file.
        */

        $rs3 = $database->getRowsByLayer($aid, $query_layersvalues[$i]);
        $line3 = $database->getColumns($rs3);

        $rs7 = $database->getRowsMinMaxXYByLayer($aid, $query_layersvalues[$i]);
        $line7 = $database->getColumns($rs7);
        $totalminx = $line7[0];
        $totalminy = $line7[1];
        $totalmaxx = $line7[2];
        $totalmaxy = $line7[3];
        // check BBox error, if not in bbox, should be skipped
        if ($maxx < $totalminx OR $miny > $totalmaxy OR $minx > $totalmaxx OR $maxy < $totalminy) {
            // $errornumber = 1;
            // $errorexceptionstring = "Invalid bounding box coordinates for Query_Layers =" .$query_layersvalues[$i].". Easting must be between " .$totalminx. " and " .$totalmaxx. " AND Northing  must be between " .$totalminy. " and " .$totalmaxy.".";
        }
        // check X Y error
        if ($map_paras['pixel_x'] > $map_paras['width'] OR $map_paras['pixel_y'] > $map_paras['height'] OR $map_paras['pixel_x'] < 0 OR $map_paras['pixel_y'] < 0) {
            $errornumber = 10;
            $errorexceptionstring = "Invalid feature coordinates in Query_Layers =" . $query_layersvalues[$i] . ". X must be between 0 and " . $map_paras['width'] . " AND Y  must be between 0 and " . $map_paras['height'] . ".";
        }
        // check layer error
        if ($line3 == null AND $map_paras['query_layers'] != "" AND $map_paras['pixel_x'] != "" AND $map_paras['pixel_y'] != "" AND $map_paras['radius'] != "") {
            $errornumber = 2;
            $errorexceptionstring = "LayerNotDefined. Layer " . $query_layersvalues[$i] . " or feature with coordinate " . $map_paras['pixel_x'] . "," . $map_paras['pixel_y'] . " with REDIUS" . $map_paras['radius'] . " is not found, Please check your Layer name AND/OR reset the feature X Y coordinate or radius.";
        } else if ($map_paras['pixel_x'] == "" OR $map_paras['pixel_y'] == "") {
            $errornumber = 3;
            $errorexceptionstring = "Feauture coordinate X Y " . $map_paras['pixel_x'] . " or " . $map_paras['pixel_y'] . " not specified. Please input valid Feauture coordinate X Y!";
        } else if ($map_paras['radius'] == "") {
            $map_paras['radius'] = $params['GetFeatureInfoRedius'];
            // $errornumber = 4;
            // $errorexceptionstring = "Redius " . $map_paras['radius'] . " not specified. Please set it!";
        } else if ($map_paras['query_layers'] == "") {
            $errornumber = 5;
            $errorexceptionstring = "Layer " . $query_layersvalues[$i] . " not specified. Please insert Query Layer names!";
        }
    }
    if ($errornumber != 0) {
        $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
    } else {
        if (strcasecmp($map_paras['info_format'], $MAP_WMS_FORMAT['getfeatureinfo']['xml']) == 0) {
            header("Content-type: text/xml;charset=utf-8");
            print('<?xml version="1.0" encoding="UTF-8"?>');

            ?>
  <FeatureInfoResponse
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:noNamespaceSchemaLocation="">
  <!-- First layer from the requests QUERY_LAYER parameter.
    [Mandatory for each parameter value.]
  -->
<?php
            $newsquare = getSelectSquare($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $map_paras['pixel_x'], $map_paras['pixel_y'], $map_paras['radius'], $atlas_cfg['enablestretchmap']);
            $x_plus = $newsquare[0];
            $x_minus = $newsquare[1];
            $y_plus = $newsquare[2];
            $y_minus = $newsquare[3];
            $x_real = $newsquare[4];
            $y_real = $newsquare[5];

            for ($i = 0; $i < $numberofquery_valueslayer; $i++) {
                $rs2 = $database->getSelectFeatureInSquareBy($tbname, $x_plus, $x_minus, $y_plus, $y_minus, $query_layersvalues[$i]);
 ?>
 <Layer>
   <name><?=$query_layersvalues[$i]?></name>
   <title><?=$query_layersvalues[$i]?></title>
<?php
                while ($line2 = $database->getColumns($rs2)) {
                    $data3 = $line2["srs"];
                    $data4 = $line2["layer"];
                    $data_geomtype = $line2["geomtype"];
                    $data5 = $line2["recid"];
                    $data6 = $line2["xlink"];
                    $data7 = $line2["attributes"];
                    //if it is not xml but php serialize code
					if ($data7 != "" AND !strstr($data7, '<attributes>')) {
						$dataarray = AttributeParser::extractAttribute($data7);
						$attributesdata = AttributeParser::getAttributeStringFromArray($dataarray);
					}else{
						$attributesdata = $data7;
					}
?>
   <Feature fid="<?=$data5?>">
      <Properties>
        <Property>
          <title>ID</title>
          <value><?=$data5?></value>
        </Property>
        <Property>
          <title>Attributes</title>
          <!--
          Units of measure have been given their own attribute.
          -->
          <value><?=$attributesdata?></value>
        </Property>
      </Properties>
      <!-- Links only represent related web resources so the "rel"
        attribute has been dropped, and all link element must
        have an attribute named "title".
      -->
      <link type="text/html"
        href="<?=$data6?>"
        title="<?=$query_layersvalues[$i]?> <?=$data5?>"/>
    </Feature>
<?php
                }
                echo "</Layer>";
            } // end of for ($i=0; $i < $numberofvalueslayer; $i++)
            echo "</FeatureInfoResponse>";
        } elseif (strcasecmp($map_paras['info_format'], $MAP_WMS_FORMAT['getfeatureinfo']['html']) == 0) {
            header("Content-type: text/html;charset=utf-8");

            $newsquare = getSelectSquare($minx, $miny, $maxx, $maxy, $map_paras['width'], $map_paras['height'], $map_paras['pixel_x'], $map_paras['pixel_y'], $map_paras['radius'], $atlas_cfg['enablestretchmap']);
            $x_plus = $newsquare[0];
            $x_minus = $newsquare[1];
            $y_plus = $newsquare[2];
            $y_minus = $newsquare[3];
            $x_real = $newsquare[4];
            $y_real = $newsquare[5];

            for ($i = 0; $i < $numberofquery_valueslayer; $i++) {
                $rs2 = $database->getSelectFeatureInSquareBy($aid, $x_plus, $x_minus, $y_plus, $y_minus, $query_layersvalues[$i]);

                echo "<style type=\"text/css\">
				tr,td {
					font-size : 95%;
				}
				table {
				    border-color: #3366cc;
					border : 0px dashed #5575B7;
				}
				</style>";
                echo "<TABLE><TR>";
                echo '<TD BGCOLOR="#8F8FEF" cospan="2"><font color="white"><b>' . $query_layersvalues[$i] . "</b></font></TD>";
                // echo '<TD BGCOLOR="#8F8FEF"><font color="white"><i>'.""."</i></font></TD>";
                echo "</TR>";

                echo "<TR><TABLE>";
                $count = 0;
				while($line2 = $database->getColumns($rs2)) {
                    $data3 = $line2["srs"];
                	$data4 = $line2["layer"];
                	$data_geomtype = $line2["geomtype"];
                    $data4 = $line2["id"];
                    $data5 = $line2["recid"];
                    $data6 = $line2["xlink"];
                    $data7 = $line2["attributes"];

                    echo "<TR>";
                    echo '<TD BGCOLOR="#B5C4D2">id:</TD>';
                    echo '<TD BGCOLOR="#B5C4D2">' . $data4 . '</TD>';
                    echo "</TR>";

                    echo "<TR>";
                    echo '<TD BGCOLOR="#D7DFE7">link:</TD>';
                    echo '<TD BGCOLOR="#D7DFE7">' . $data6 . '</TD>';
                    echo "</TR>";

                    if ($data7 != "" AND strstr($data7, '<attributes>')) {
                        // print('<xsd:sequence>');
                        $xml = simplexml_load_string(iconv('UTF-8', 'UTF-8', $data7));
                        foreach ($xml->attribute as $attribute) {
                            echo "<TR>";
                            echo '<TD BGCOLOR="#D7DFE7">' . $attribute['name'] . ":</TD>";
                            echo '<TD BGCOLOR="#D7DFE7">' . $attribute . "</TD>";
                            echo "</TR>";
                        }
                    }
                   	//if it is not xml but php serialize code
                    else if($data7 != "" AND !strstr($data7, '<attributes>')){
                    	$dataarray = AttributeParser::extractAttribute($data7);
                    	foreach($dataarray as $key => $value){
							echo "<TR>";
                            echo '<TD BGCOLOR="#D7DFE7">' . $key . ":</TD>";
                            echo '<TD BGCOLOR="#D7DFE7">' . $value . "</TD>";
                            echo "</TR>";
						}
                    }
                    $count++;
                }
                if($count == 0){
					echo "<TR>";
                    echo '<TD BGCOLOR="#D7DFE7" cospan="2">none</TD>';
                    echo "</TR>";
				}
                echo "</TABLE>";
                echo "</TD></TR><TABLE>";
            }
        }
    }
}
$database->databaseClose();
exit();

?>