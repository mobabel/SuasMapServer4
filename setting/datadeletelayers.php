<?php
/**
 * Data delete layers in srs.php
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
 * @copyright (C) 2006-2007  LI Hui
 * @Description: input username and password, begin to setting
 * @contact webmaster@easywms.com
 */

require_once '../config.php';
require_once '../global.php';
require_once '../models/menu.inc';
require_once '../models/setting.inc';

$database = new Database();

$database->databaseConfig($servername, $username, $password, $dbname);
$database->databaseConnect();

if ($database->databaseGetErrorMessage() != "") {
    $error = $database->databaseGetErrorMessage();
}

$srs = $_GET['SRS'];
$blndelete = $_GET['blndelete'];
$deletelayername = $_GET['LAYERS'];
$blndeletesuccuss = false;

if($deletelayername!="" AND $blndelete=="true"){

     $database->deleteLayersBySRS($tbname,$srs, $deletelayername);
     $database->deleteLayersBySRS($tbmetaname,$srs, $deletelayername);

     $error = $database->databaseGetErrorMessage();
     if($error=="")
     $blndeletesuccuss = true;
     else $blndeletesuccuss = false;
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$softName?> Settting</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../cssjs/common.js"></script>
</head>
<body>

<table cellspacing="0" cellpadding="0" id="main">
<tr id="logo"><td colspan="2"><span class="logoprefix"><?=$softName."  ".$softVersion.$softEdition?></span></td></tr>
<tr id="top">
	<td id="left">Setting Progress</td>
	<td id="right">
	<div id="progressbar"><div id="process" style="width: 0%;"></div></div>
	</td>
</tr>

<tr>
	<td id="progress">
        <ul>
                <li class="first"><span><a href="../Demo/index.php">Home</a></span></li>
		<li class="first"><span>Configuration</span></li>
			<ul class="second">
				<li><a href="setting.php" title="Go back to access database">Database Access</a></li>
				<li>Database Settings</li>
                <li>Table Settings</li>
				<li>General Settings</li>
				<li>Data Import</li>
				<li><a href="8.php" title="Set the style directly">Style Settings</a></li>
				<li>Create Metadata</li>
			</ul>
		<li class="first"><span>Install</span></li>
			<ul class="second">
				<li class="done"><a href="../<?=$installName?>/install.php" title="Create a new database or table from here">Database Installation</a></li>
			</ul>
		<? CreateToolsMenu("datadelete");?>
		</ul>
	</td>
	<td id="content">
				<h1>Data Delete</h1>
				<p id="intro"></p>
				<br />
<?php

if ($error == "") {
    echo "<script type=\"text/javascript\">";
    echo("\n");
    echo "function chkform()";
    echo("\n");
    echo "{";

    $rs2 = $database->getLayersBySrsGroupByLayer($tbname, $srs);
    echo("\n");
    echo "if(";
    $iLayers = 1;
    $num = $database->getRowsNumber($rs2);
    while ($line2 = $database->getColumns($rs2)) {
        echo "(document.nameFormDeleteLayers.layer" . $iLayers . ".checked != true )";

        if ($iLayers != $num) {
            echo "&&";
        }
        ++$iLayers;
    }
    echo ")";
    echo("\n");
    echo " { ";
    echo("\n");
    echo "showErrorMessage(\"please select a layer!\");";
    echo("\n");
    echo "return false;";
    echo("\n");
    echo "}";
    echo("\n");
    echo " var layerstr = \"\";";
    echo("\n");
    echo " var stylestr = \"\";";
    echo("\n");
    echo "for  (i = 1; i <= $num ; ++ i)";
    echo("\n");
    echo "{";
    echo "var chk= \"layer\" + i;";
    echo("\n");
    echo "var fila=document.getElementsByName(chk);";
    echo("\n");
    echo "<!--- var fila= \"layer\" + i;-->";
    echo("\n");
    echo "if (fila[0].checked == true){layerstr = layerstr + fila[0].value + \",\"; ";
    echo("}");
    echo("\n");
    echo "}";
    echo("\n");
    echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";

    // echo	 "alert(\"you have selected LAYERS=\" + layerstr) ;";
    echo("\n");
    echo "document.nameFormDeleteLayers.LAYERS.value = layerstr;";
    echo "document.nameFormDeleteLayers.blndelete.value = true;";
    echo "}";
    echo("\n");

    echo "</script>";
    echo("\n");

				if($blndeletesuccuss)
					echo "<p><font  class=\"error\">Layers $deletelayername have been deleted.</font></p>";
?>
                    <h2></h2>
					<p>Select layers to delete.</p>
					<form name="nameFormDeleteLayers" id="idFormDeleteLayers" method="get" action="datadeletelayers.php" onSubmit="return chkform()">
					    <input type="hidden" name="blndelete" value="false">
					    <input type="hidden" name="SRS" value="<?=$srs?>">
					    <input type="hidden" name="LAYERS" value="LayerProblem">
						<table class="tableContent">
						<tr>
            			<td>Select All?</td>
						<td><input type="checkbox" name="chkall" class="button1" onclick="checkall(this.form)"></td>
            			</tr>
<?
   $layerslist = $database->getLayersBySrsGroupByLayer($tbname, $srs);
   $i=1;
    while ($layernames = $database->getColumns($layerslist)) {
        $layername = $layernames["layer"];
        if($i%2==0)echo "<tr class=\"odd\">";
        else echo "<tr class=\"even\">";

        echo "<td>$layername</td><td> <input type=\"checkbox\" name=\"layer$i\" value=\"$layername\" class=\"button3\">";
        echo "					 </td>
				    </tr>";

        $i++;
    }
    if (!$layername) {

        ?>
                    <tr>
					 <td colspan="2"><p>These no layers data, all layers have been deleted or please check your database.</p></td>
					</tr>
					<tr>
					 <td colspan="2" align="right"><div class="begin"><a href="datadelete.php" title="Select other SRS">Select SRS</a></div></td>
					</tr>
<?php
    }
    if ($layername) {

        ?>
					<tr>
					<td align="left">
					<div class="begin"><a href="datadelete.php" title="Select other SRS">Select SRS</a></div>
					</td>
				 	 <td align="right">
                     <input type="submit" name="DeleteLayers" value="Delete layers" onmouseover="this.className='button1'" onmouseout="this.className='button'" class="button"/>
					</tr>
<?php
    }

    ?>

						</table>
					</form>
                <br/>
<?php
}
    if ($error != "") {

        ?>
<table class="tableError">
<tr>
<td>
			<h4>Failure</h4>
			    <p id="intro">You must correct the error below before installation can continue:<br/><br/>
                <span style="color:#000000"><?=$error?></span><br /><br /></p>
</td>
</tr>
<tr>
<td align="left">
               <input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1'" onmouseout="this.className='button'" class="button">

</td>
</tr>
</table>
<?php
    }
$database->databaseClose();
?>
	</td>
</tr>
</table>

</body>
</html>