<?php
/**
 * 8.php
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
 * @Description: Create metadata in featureclass for layers
 * @contact webmaster@easywms.com
 */

require_once '../config.php';
require_once '../global.php';
require_once '../models/tables.php';
require_once '../models/Installation.class.php';
require_once '../models/menu.inc';
require_once '../models/setting.inc';

$database = new Database();

$database->databaseConfig($servername, $username, $password, $dbname);
$database->databaseConnect();

if ($database->databaseGetErrorMessage() != "") {
    $error = $database->databaseGetErrorMessage();
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$softName?> Setting</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../cssjs/install.js"></script>
<script type="text/javascript" src="../cssjs/common.js"></script>
<script type="text/javascript" src="../cssjs/string.protoype.js"></script>
</head>
<body>

<table cellspacing="0" cellpadding="0" id="main">
<tr id="logo"><td colspan="2"><span class="logoprefix"><?=$softName."  ".$softVersion.$softEdition?></span></td></tr>
<tr id="top">
	<td id="left">Setting Progress</td>
	<td id="right">
	<div id="progressbar"><div id="process" style="width: 100%;"></div></div>
	</td>
</tr>


<tr>
	<td id="progress">
		<ul>
                <li class="first"><span><a href="../Demo/index.php">Home</a></span></li>
		<li class="first"><span>Configuration</span></li>
			<ul class="second">
				<li class="done">Database Access</li>
				<li class="done">Database Settings</li>
                		<li class="done">Table Settings</li>
				<li class="done">General Settings</li>
				<li class="done">Data Import</li>
				<li class="done">Style Settings</li>
			    <li class="done">Create Metadata</li>
			</ul>
		<li class="complete">Complete!</li>
		<li class="first"><span>Install</span></li>
			<ul class="second">
				<li class="unactive"><a href="../<?=$installName?>/install.php">Database Installation</a></li>
			</ul>
			<? CreateToolsMenu("default");?>
		</ul>
	</td>
	<td id="content">

<?php
//
if ($database->databaseGetErrorMessage() == "") {
    $tbname = $tableprefix.mapTableFeaturegeometry;
    $tbmetaname = $tableprefix.mapTableFeatureclass;

    $layersnameswithsrslist = $database->getLayersNamesWithDiffSrs($tbname);

    //create new one
    //$sql1 = str_replace(mapTableFeatureclass, $tbmetaname, $tables_sql[2]);
    //$sql2 = str_replace(mapTableFeatureclass, $tbmetaname, $tables_sql[3]);
    //$result1 = $database->databaseAnyQuery($sql1);
    //$result2 = $database->databaseAnyQuery($sql2);

    //empty the old featureclass table
    $database->makeTableEmpty($tbmetaname);

	$error = $database->databaseGetErrorMessage();

    if ($error == "") {
        $blnAlreadyCreateStyle = false;
        $i = 0;
        //create options for priority selection
        for($j=0;$j<=20;$j++){
		    $strOption4Priority .="<option value=$j>$j</option>\n";
		}
        while ($row = $database->getColumns($layersnameswithsrslist)) {
            if ($row["layer"] != "") {
                $aryLayerName[$i] = $row["layer"];
                $arySrsName[$i] = $row["srs"];

                //just check whether the style has been createn for one time
                if ($_POST['sltStyles_'.$arySrsName[$i]."_".$row["layer"]] != "") {
                    $blnAlreadyCreateStyle = true;
                }
                if ($blnAlreadyCreateStyle) {

                $layertype = "Unknown";
                $layer = $aryLayerName[$i];
                $description = $_POST['txtLayerTitle_'.$arySrsName[$i]."_".$aryLayerName[$i]];
                $geomtype = $_POST['LayerType_'.$arySrsName[$i]."_".$aryLayerName[$i]];

                $xylist = $database->getRowsMinMaxXYBySrsLayer($tbname, $arySrsName[$i], $aryLayerName[$i]);
                $xylistrow = $database->getColumns($xylist);
                $xmin = $xylistrow[0];
                $ymin = $xylistrow[1];
                $xmax = $xylistrow[2];
                $ymax = $xylistrow[3];

                $srs = $arySrsName[$i];
                $style = $_POST['sltStyles_'.$arySrsName[$i]."_".$aryLayerName[$i]];
                $queryable = false;
                $visiable  = false;
                $priority = 0;
                $elevation = 0;
                $priority = $_POST['sltPriority_'.$arySrsName[$i]."_".$aryLayerName[$i]];
                $elevation = $_POST['txtLayerElevation_'.$arySrsName[$i]."_".$aryLayerName[$i]];

                    //$tablename, $layertype, $layer, $description, $geomtype, $xmin, $ymin, $xmax, $ymax, $srs, $style, $queryable, $visiable, $priority,$elevation
                    $layertype = $geomtype;
                    if($_POST['chkQueryable_'.$arySrsName[$i]."_".$aryLayerName[$i]]==1){
                        $queryable = true;
					}
					if($_POST['chkVisiable_'.$arySrsName[$i]."_".$aryLayerName[$i]]==1){
                        $visiable = true;
					}

                    $database->createFeatureClassMetadata($tbmetaname,$layertype, $layer, $description, $geomtype, $xmin, $ymin, $xmax, $ymax, $srs, $style, $queryable, $visiable, $priority,$elevation);

                    $i++;
                }
            }
        }

        $error = $database->databaseGetErrorMessage();

        if (!$blnAlreadyCreateStyle) {
            $error = "Please Create Style Defination at first, before you create Metadata!";
        }
        if ($blnAlreadyCreateStyle) {
            if ($error == "") {

                ?>
			<h1><?=$softName?> has been installed successfully</h1>
			<p id="intro">Metadata database has been createn successfully. Now you can do:</p>
			<div id="errormessage" class="error"></div>
			<br />

				<h2>WMS DEMO</h2>
				<div class="begin"><a href="../Demo/wms_GetCapabilities.php">WMS DEMO</a></div><br />

				<h2>WFS DEMO</h2>
				<div class="begin"><a href="../Demo/wfs_GetCapabilities.php">WFS DEMO</a></div><br />

				<h2>Enter the Database Setting</h2>
				<p>Reset the database configuration<br />
				If the Setting folder has been renamed, please go to the new folder and run setting.php directly.<br>
				</p>
				<div class="begin"><a href="../<?=$settingName?>/setting.php">Setting</a></div><br />

<?php
            }
        }
    }
}
if ($error != "") {

    ?>
<table class="tableError">
<tr>
<td>
			<h4>Failure</h4>
			    <p id="intro">You must correct the error below before installation can continue:<br/><br/>
                <span style="color:#000000"><?php echo $error; ?></span><br /><br /></p>
</td>
</tr>
<tr>
<td align="left">
               <input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1 backInput'" onmouseout="this.className='button backInput'" class="button backInput">

</td>
</tr>
</table>
<?php
}

?>
	</td>
</tr>
</table>

</body>
</html>