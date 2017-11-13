<?php
/**
 * Data delete.php
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

$blndelete = $_GET['blndelete'];$blndelete = "true";
$deletesrsname = $_GET['SRS'];

if($deletesrsname!="" AND $blndelete=="true"){
     $database->deleteLayersInSrs($tbname,$deletesrsname);
     $database->deleteLayersInSrs($tbmetaname,$deletesrsname);
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
<?
if ($error == "") {
    $srsnameslist = $database->getAllSrssNames($tbname);
    echo "<script type=\"text/javascript\">";
    echo("\n");
    echo "function chkform()";
    echo("\n");
    echo "{";
    echo("\n");
    // echo("alert(document.Fgetmap.SRS.length);");
    echo "if(";
    $iSRSs = 0;
    $num = $database->getRowsNumber($srsnameslist);
    while ($line2 = $database->getColumns($srsnameslist)) {
        if ($num == 1) {
            echo "!document.nameFormDelete.SRS.checked ";
        }
        if ($num > 1) {
            echo "!document.nameFormDelete.SRS[" . $iSRSs . "].checked ";
        }

        if ($iSRSs != $num-1) {
            echo "&&";
        }
        ++$iSRSs;
    }
    echo ")";
    echo("\n");
    echo "{";
    echo "showErrorMessage(\"Please select a coordinate reference system!\");";
    echo("\n");
    echo "return false;";
    echo "}";
    echo("\n");
    echo("\n");
    echo("\n");

    echo("\n");
    echo "}";
    echo "</script>";
    echo("\n");

    ?>
				<div id="options">
<?
				if($blndeletesuccuss)
					echo "<p><font  class=\"error\">Layers in $deletesrsname have been deleted.</font></p>";
?>
                    <h2></h2>
					<p>You could delete the layers in one SRS or enter to select layers to delete.</p>
					<form name="nameFormDelete" id="idFormDelete" method="get" action="datadeletelayers.php" onSubmit="return chkform()">
					    <input type="hidden" name="blndelete" value="false">
						<table class="tableContent">
						<tr>
								<td>All layers in the SRS will be deleted.</td>
						</tr>
<?
   $srsnameslist = $database->getAllSrssNames($tbname);
   $i=0;
    while ($srsnames = $database->getColumns($srsnameslist)) {
        $num = $database->getRowsNumber($srsnameslist);
        $srsname = $srsnames["srs"];
        if($i%2==0)echo "<tr class=\"odd\">";
        else echo "<tr class=\"even\">";

        echo "<td>";
        if($i==0)echo "<input type=\"radio\" name=\"SRS\"  value=\"$srsname\" checked>";
        else echo "<input type=\"radio\" name=\"SRS\"  value=\"$srsname\">";
        echo $srsname;
        echo "					 </td>
				    </tr>";

        $i++;
    }
    if (!$srsname) {

        ?>
                    <tr>
					 <td><p>These no SRS data, please check your database.</p></td>
					</tr>
<?php
    }
    if ($srsname) {

        ?>
					<tr>
				 	 <td align="right">
					  <input onclick="submitDeleteSRS();" name="button" value="Delete all layers in SRS" onmouseover="this.className='button1'" onmouseout="this.className='button'" class="button">
					 or <input type="submit" name="Select" value="Enter to delete layers" onmouseover="this.className='button1'" onmouseout="this.className='button'" class="button"/>
					</tr>
<?php
    }

    ?>

						</table>
					</form>
                <br/>
				<br/>
            </div>
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