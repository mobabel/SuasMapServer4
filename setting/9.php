<?php
/**
 * 9.php
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
 * @Description : Create Style and Set metadata in featureclass for layers
 * @contact webmaster@easywms.com
 */
require_once '../config.php';
require_once '../global.php';
require_once '../models/menu.inc';
require_once '../models/Installation.class.php';
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
<tr id="logo"><td colspan="2"><span class="logoprefix"><?=$softName . "  " . $softVersion . $softEdition?></span></td></tr>
<tr id="top">
	<td id="left">Setting Progress</td>
	<td id="right">
	<div id="progressbar"><div id="process" style="width: 90%;"></div></div>
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
			    <li class="error">Create Metadata</li>
			</ul>
		<li class="first"><span>Install</span></li>
			<ul class="second">
				<li class="unactive"><a href="../<?=$installName?>/install.php">Database Installation</a></li>
			</ul>
			<?php CreateToolsMenu("default");
?>
		</ul>
	</td>
	<td id="content">

<?php
if ($database->databaseGetErrorMessage() == "") {
    $srsnameslist = $database->getAllSrssNames($tbname);
    $error = $database->databaseGetErrorMessage();
    $i = 0;
    $count = 1;

    if ($error == "") {
        $srsnum = $database->getRowsNumber($srsnameslist);
        while ($srsnames = $database->getColumns($srsnameslist)) {
            $srsname = "";
            $srsname = $srsnames["srs"];

            if ($count == 1) {
                $tabmenu .= "<li><a href=\"#\" onClick=\"show_tbl('content_',$srsnum,$count)\" id=\"content_" . $count . "_menu\" class=\"current\">$srsname</a></li>\n";
                $tabmenucontent .= "<table id=\"content_$count\" class=\"tableContent\">\n";
            } else {
                $tabmenu .= "<li><a href=\"#\" onClick=\"show_tbl('content_',$srsnum,$count)\" id=\"content_" . $count . "_menu\">$srsname</a></li>\n";
                $tabmenucontent .= "<table id=\"content_$count\" class=\"tableContent\" style=\"display:none\">\n";
            }

            $tabmenucontent .= "
                        <tr>
                    		<td colspan=\"7\"><h2>Create Metadata</h2></td>
                   		</tr>
                   		<tr>
                    		<td colspan=\"7\"></td>
                   		</tr>
                   		        <TR>
          <TD>Layer Name &nbsp;&nbsp;(Type)</TD>
          <TD>Queryable <image src=\"../img/help.png\"  border=\"0\" onmouseover=\"tooltip('Queryable Layer','Description:','Indicates if Layer supports GetFeatureInfo operation.Layers with type of image or text typically do not support this operation.');\" onmouseout=\"exit();\"> </TD>
          <TD>Visible <image src=\"../img/help.png\"  border=\"0\" onmouseover=\"tooltip('Visible Layer','Description:','Indicates if Layer could be requested to display.');\" onmouseout=\"exit();\"> </TD>
          <TD>Priority <image src=\"../img/help.png\"  border=\"0\" onmouseover=\"tooltip('Layer Priority','Description:','Set the priority of layer, layer with high value will overlay the layer with low value.');\" onmouseout=\"exit();\"> </TD>
          <TD>Descriptive Title</TD>
          <TD>Elevation <image src=\"../img/help.png\"  border=\"0\" onmouseover=\"tooltip('Layer Elevation','Description:','Set the elevation of layer, the value will be used to create 3D model.');\" onmouseout=\"exit();\"></TD>
          <TD>Style</TD></TR>
          <tr>
             <td colspan=\"7\">&nbsp;</td>
          </tr>
          ";
            $blnAlreadyCreateStyle = false;

            $layersnameslist = null;
            $metadataTable = null;
            $layersnameslist = $database->getAllLayersNamesInSrs($tbname, $srsname);
            while ($row = $database->getColumns($layersnameslist)) {
                if ($row["layer"] != "") {
                    $arrLayerName[$i] = $row["layer"];
                    $arrLayerType[$i] = $row["geomtype"];

                    $layersnameslistinmeta = $database->getRowsByLayer($tbmetaname, $row["layer"]);
                    $rowinmeta = $database->getColumns($layersnameslistinmeta);
                    //if layer existes
                    if ($rowinmeta != null) {
                        $arrLayerQueryable[$i] = $rowinmeta["queryable"];
                        $arrLayerVisiable[$i] = $rowinmeta["visiable"];
                        $arrLayerPriority[$i] = $rowinmeta["priority"];
                        $arrLayerTitle[$i] = $rowinmeta["description"];
                        $arrLayerElevation[$i] = $rowinmeta["elevation"];
                    } else { // if added new layers before, not in the metadata
                        if (strtoupper($arrLayerType[$i]) == "IMAGE" OR strtoupper($arrLayerType[$i]) == "TEXT") {
                            $arrLayerQueryable[$i] = 0;
                        } else {
                            $arrLayerQueryable[$i] = 1;
                        }
                        $arrLayerVisiable[$i] = 1;
                        $arrLayerPriority[$i] = 0;
                        $arrLayerElevation[$i] = 0;
                        $arrLayerTitle[$i] = $arrLayerName[$i];
                    }
                    $arrayFullLayerName[$i] = $srsname."_".$arrLayerName[$i];
                    $arraySrsNamePre[$i] = $srsname."_";

                    // just check whether the style has been createn for one time
                    if ($_POST[$srsname."_".$row["layer"] . 'txtStyleName'] != "") {
                        $blnAlreadyCreateStyle = true;
                    }
                    if ($blnAlreadyCreateStyle) {
                        // $hiddenInput4Meta .= "<input type=hidden name=\"" . $row["layer"] . "\" value=\"" . $_POST[$row["layer"] . 'txtStyleName'] . "\">" . "\n";
                        if ($i % 2 == 0)
                            $metadataTable .= "<TR class=\"odd\">";
                        else $metadataTable .= "<TR class=\"even\">";

                        $metadataTable .= "\n<TD>\n<INPUT readOnly size=\"16\" value=\"$arrLayerName[$i]\" name=\"txtLayerName_$arrayFullLayerName[$i]\" class=\"smallInput\">($arrLayerType[$i])\n"
                         . "<INPUT type=\"hidden\" value=\"$arrLayerType[$i]\" name=\"LayerType_$srsname"."_"."$arrLayerName[$i]\">\n</TD>\n";
                        if (strtoupper($arrLayerType[$i]) == "IMAGE" OR strtoupper($arrLayerType[$i]) == "TEXT" OR $arrLayerQueryable[$i] == 0) {
                            $metadataTable .= "<TD>\n<INPUT type=\"checkbox\" value=\"1\" name=\"chkQueryable_$arrayFullLayerName[$i]\" class=\"button3\">\n</TD>\n";
                        } else {
                            $metadataTable .= "<TD>\n<INPUT type=\"checkbox\" checked value=\"1\" name=\"chkQueryable_$arrayFullLayerName[$i]\" class=\"button3\">\n</TD>\n";
                        }
                        if ($arrLayerVisiable[$i] == 1) {
                            $metadataTable .= "<TD>\n<INPUT type=\"checkbox\" checked value=\"1\" name=\"chkVisiable_$arrayFullLayerName[$i]\" class=\"button3\">\n</TD>\n";
                        } else {
                            $metadataTable .= "<TD>\n<INPUT type=\"checkbox\" value=\"1\" name=\"chkVisiable_$arrayFullLayerName[$i]\" class=\"button3\">\n</TD>\n";
                        }
                        $metadataTable .= "<TD>\n<SELECT name=\"sltPriority_$arrayFullLayerName[$i]\" class=\"button4\">\n" . printOption4PrioritySelected(20, $arrLayerPriority[$i]) . "\n</SELECT></TD>\n";
                        $metadataTable .= "<TD>\n<INPUT size=\"20\" value=\"$arrLayerTitle[$i]\" name=\"txtLayerTitle_$arrayFullLayerName[$i]\" class=\"smallInput\">\n</TD>\n";
						$metadataTable .= "<TD>\n<INPUT size=\"8\" value=\"$arrLayerElevation[$i]\" name=\"txtLayerElevation_$arrayFullLayerName[$i]\" class=\"smallInput\">\n</TD>\n";
                        $metadataTable .= "<TD>\n<SELECT name=\"sltStyles_$arrayFullLayerName[$i]\" class=\"button4\">\n<OPTION value=\"" . $_POST[$arrayFullLayerName[$i] . 'txtStyleName'] . "\" selected>" . $_POST[$arrayFullLayerName[$i] . 'txtStyleName'];
                        $metadataTable .= "</OPTION>\n</SELECT>\n</TD></TR>";

                        $i++;
                    }
                }
            }
            $count++;
            $tabmenucontent .= $metadataTable;
            $tabmenucontent .= "</table>";
        }
        createWmsStyles($arrLayerName,$arraySrsNamePre, $arrLayerType, $dbname . $tableprefix);

        $error = $database->databaseGetErrorMessage();

        if (!$blnAlreadyCreateStyle) {
            $error = "Please Create Style Defination at first, before you create Metadata!";
        }
        if ($blnAlreadyCreateStyle) {
            if ($error == "") {

                ?>
			<p id="intro">Layer style has been generated successfully.<br>
			Now Metadata for the database will be createn.</p>
			<div id="errormessage" class="error"></div>
			<br />
<div id="header">
	<ul id="primary">
		<?=$tabmenu?>
	</ul>
	</div>
	<div id="main">
		<div id="contents">
		<form name="frmCreateMetadata" action="10.php" method="post">
		<table class="tableContent">
		<tr>
		<td colspan="2">
         <?=$tabmenucontent?>
         </td>
         </tr>
         <tr>
         <td align="left">
		 <input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1 backInput'" onmouseout="this.className='button backInput'" class="button backInput">
         </td>
         <td align="right">
		 <input type="submit" name="btnContinue" value="Continue" onmouseover="this.className='button1 continueInput'" onmouseout="this.className='button continueInput'" class="button continueInput"/>
		 </td>
         </tr>
         </table>
        </form>
		</div>
	</div>

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
                <span style="color:#000000"><?php echo $error;
    ?></span><br /><br /></p>
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