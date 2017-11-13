<?php
/**
 * DescribeFeatureType Class
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

$errornumber = 0;
$errorexceptionstring = "";

if ($database->databaseGetErrorMessage() != "") {
    $errornumber = -1;
    $errorexceptionstring = $database->databaseGetErrorMessage();
}

if ($errornumber != 0) {
    $sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
} else {
    if ($map_paras['outputformat'] != "" && strcasecmp($map_paras['outputformat'], "text/xml") != 0) {
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

    header("Content-type: text/xml;charset=utf-8");
    print('<?xml version="1.0" encoding="UTF-8"?>');
    print('<xsd:schema targetNamespace="' . $wmsmetadata["ServerHost"] . 'wfs/Schemas/gml"
	elementFormDefault="qualified"
	version="1.0"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:ingr="' . $wmsmetadata["ServerHost"]. 'wfs/Schemas/gml"
	xmlns:gml="http://www.opengis.net/gml">
    <xsd:import namespace="http://www.opengis.net/gml" schemaLocation="' . $wmsmetadata["ServerHost"] . 'wfs/Schemas/gml/2.1.2/feature.xsd" />
    <xsd:element name="featureCollection" type="ingr:featureCollectionType" substitutionGroup="gml:_FeatureCollection" /> ');

    print('<xsd:complexType name="featureCollectionType">
    <xsd:complexContent>
    <xsd:extension base="gml:AbstractFeatureCollectionType" />
    </xsd:complexContent>
    </xsd:complexType>');

    for ($i = 0; $i < $numberofvalueslayer; $i++) {
        print('<xsd:element name="' . $layersvalues[$i] . '" type="ingr:' . $layersvalues[$i] . 'Type" substitutionGroup="gml:_Feature" /> ');

        print('<xsd:complexType name="' . $layersvalues[$i] . 'Type">
       <xsd:complexContent>
       <xsd:extension base="gml:AbstractFeatureType">');

        try {
            $rs2 = $database->getRows4GeomByLayer($aid, $layersvalues[$i]);
            $line2 = $database->getColumns($rs2);
            // this will get only one sample row from the featuregeometry table, not all rows
            $onesamplerow = $line2["attributes"];
            if ($onesamplerow != "" AND strstr($onesamplerow, '<attributes>')) {
                print('<xsd:sequence>');

                $xml = simplexml_load_string($onesamplerow);
                foreach ($xml->attribute as $attribute) {
                    switch ($attribute['type']) {
                        case 'C':$type = $elementtype["C"];
                            break;
                        case 'N':$type = $elementtype["N"];
                            break;
                    }
                    print('<xsd:element name = "' . $attribute['name'] . '" minOccurs = "0" type = "xsd:' . $type . '" />');
                }

                print('</xsd:sequence>');
            }
        }
        catch (Exception $e) {
            $sendexceptionclass->sendexception($errornumber, $e);
        }

        print('</xsd:extension>
        </xsd:complexContent>
        </xsd:complexType>');
    }

    print('</xsd:schema>');
}
$database->databaseClose();
exit();
?>