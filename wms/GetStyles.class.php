<?php

/**
 * GetLegendGraphic Class
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

$rasterimagerender = new RasterImageRender();

if ($database->databaseGetErrorMessage() != "") {
	$errornumber = -1;
	$errorexceptionstring = $database->databaseGetErrorMessage();
	$sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
}
if ($errornumber == 0) {
	$layersvalues = explode(",", $map_paras['layers']);
	$numberofvalueslayer = count($layersvalues);

	for ($i = 0; $i < $numberofvalueslayer; $i++) {
		$rs1 = $database->getRowsByQueryableLayer($aid, $layersvalues[$i]);
		$line1 = $database->getColumns($rs1);

		$rs2 = $database->getRowsByLayer($aid, $layersvalues[$i]);
		$line2 = $database->getColumns($rs2);
		// the layer exists, but not queryable, $line1=0, $line2!=0
		if ($line2 == "" OR $map_paras['layers'] == "") {
			$errornumber = 5;
			$errorexceptionstring = "Layer " . $layersvalues[$i] . " not specified. Please use other Layer names!";
		}
	}
}
if ($errornumber != 0) {
	$sendexceptionclass->sendexception($errornumber, $errorexceptionstring);
} else {
	try {
		$styleparser = new StyleParser($aid, $database);
		$aryXmlUserLayerNode = $styleparser->createStyleNode4layer(true, $stid);

		$doc = new DOMDocument('1.0', 'utf-8');
		// we want a nice output
		$doc->formatOutput = true;
		// create a root node and set an attribute
		$root = $doc->createElement("StyledLayerDescriptor");
		$doc->appendChild($root);
		$root->setAttribute("version", "1.0.0");
		// If this attribute is added, in StyleReader will have problem to parse the xml fragment!
		//$root->setAttribute("xmlns", "http://www.opengis.net/sld");
		$root->setAttribute("xmlns:ogc", "http://www.opengis.net/ogc");
		$root->setAttribute("xmlns:xlink", "http://www.w3.org/1999/xlink");
		$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");

		for ($i = 0; $i < $numberofvalueslayer; $i++) {
			$rs1 = $database->getRowsByLayer($aid, $layersvalues[$i]);
			$line1 = $database->getColumns($rs1);

			$data1 = $line1["layer"];
			$data2 = $line1["description"];
			$data_Layer_Type = strtoupper($line1["layertype"]);

			$objNamedLayer = $doc->createElement("NamedLayer");
			$root->appendChild($objNamedLayer);
			// create element Name for each NamedLayer
			$objName = $doc->createElement("Name", $layersvalues[$i]);
			$objNamedLayer->appendChild($objName);

			//if style xml has no such layernode
			if ($aryXmlUserLayerNode[$layersvalues[$i]] != "") {
				$fragment = $doc->importNode($aryXmlUserLayerNode[$layersvalues[$i]], true);
				$objNamedLayer->appendChild($fragment);
			}

		} // end of for ($i=0; $i < $numberofvalueslayer; $i++)

		
		header("Content-type: text/xml;charset=utf-8");
		echo $doc->saveXML();
	} catch (Exception $e) {
		$sendexceptionclass->sendexception($errornumber = 109, $e);
	}
}
$database->databaseClose();
exit ();
?>