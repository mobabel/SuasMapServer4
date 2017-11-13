<?php

/**
 * DescribeLayer Class
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
	header("Content-type: " . $MAP_WMS_FORMAT['describelayer']['xml'] . ";charset=utf-8");
	print ('<?xml version="1.0" encoding="UTF-8"?>');
?>
<WMS_DescribeLayerResponse version="<?=SUAS_CFG_WMS_VERSION?>">
<?php

	for ($i = 0; $i < $numberofvalueslayer; $i++) {
		$rs1 = $database->getRowsByQueryableLayer($aid, $layersvalues[$i]);
		$line1 = $database->getColumns($rs1);

		$data1 = $line1["layer"];
		$data2 = $line1["description"];

		if ($line1 == null) {
?>
     <LayerDescription name="<?=$layersvalues[$i]?>">
<?php

		}
?>
<?php

		if ($line1 != null) {
?>
    <LayerDescription name="<?=$data1?>" wfs="<?=get_wms_interface($aid)?>">
    <Query typeName="<?=$data2?>" />

<?php

		}
?>
      </LayerDescription>
<?php

	}
?>
</WMS_DescribeLayerResponse>
<?php


}
$database->databaseClose();
exit ();
?>