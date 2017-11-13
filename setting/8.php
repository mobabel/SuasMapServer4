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
 * @Description : Set Style for layers
 * @contact webmaster@easywms.com
 */

require '../config.php';
require_once '../global.php';
require_once '../Parser/StyleReader.class.php';
require_once '../models/Installation.class.php';
require_once '../models/menu.php';
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
<title><?=$softName?> Settting</title>
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
	<div id="progressbar"><div id="process" style="width: 80%;"></div></div>
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
				<li class="error">Style Settings</li>
			    <li>Create Metadata</li>
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

    if ($error == "") {
        $i = 0;
        $styleparser = new StyleParser();
        $styleparser->prefix = $dbname . $tableprefix;
        $aryXmlUserLayerNode = $styleparser->createStyleNode4layer();

        $srsnum = $database->getRowsNumber($srsnameslist);
        while ($srsnames = $database->getColumns($srsnameslist)) {
            $srsname = "";
            $srsname = $srsnames["srs"];

            $layersnameslist = $database->getAllLayersNamesInSrs($tbname, $srsname);
            $layerselectbox .= "<optgroup label=\"---$srsname---\">";
            while ($row = $database->getColumns($layersnameslist)) {
                if ($row["layer"] != "") {
                    $layerselectbox .= "<OPTION value=" . $srsname."_". $row["layer"] . ">" . $row["layer"] . "</OPTION>" . "\n";
                    // ===============begin to read style for each node============
                    $styleparser->getLayerStyleFromStyleNode($row["layer"], $row["geomtype"], $aryXmlUserLayerNode);
                    $xmlUserLayerName = $styleparser->xmlUserLayerName;
                    $xmlUserLayerTitle = $styleparser->xmlUserLayerTitle;
                    $xmlMinScaleDenominator = $styleparser->xmlMinScaleDenominator;
                    $xmlMaxScaleDenominator = $styleparser->xmlMaxScaleDenominator;
                    // For point,linestring,polygon,,text
                    $xmlSize = $styleparser->xmlSize;
                    // For point,polygon,text,linestring
                    $xmlFillColor = $styleparser->xmlFillColor;
                    // For linestring,polygon
                    $xmlStrokeColor = $styleparser->xmlStrokeColor;
                    // For text
                    $xmlFont = $styleparser->xmlFont;
                    // For point
                    $xmlWellKnownName = $styleparser->xmlWellKnownName;
                    // For linestring, point
                    $xmlStrokeOpacity = $styleparser->xmlStrokeOpacity;
                    // For polygon, point, image
                    $xmlFillOpacity = $styleparser->xmlFillOpacity;
                    // For text
                    $xmlFont = $styleparser->xmlFont;
                    $xmlFontStyle = $styleparser->xmlFontStyle;
                    $xmlFontWeight = $styleparser->xmlFontWeight;
                    // For linestring
                    $xmlLineJoin = $styleparser->xmlLineJoin;
                    $xmlLineCap = $styleparser->xmlLineCap;
                    // =======================finish reading========================
                    $strStyleInfoContainer[$i] = getStyleInfoContainerFromSLD($srsname."_".$row["layer"], $row["geomtype"], $xmlUserLayerName, $xmlUserLayerTitle,
                        $xmlMinScaleDenominator, $xmlMaxScaleDenominator,
                        $xmlSize, $xmlFillColor, $xmlStrokeColor, $xmlFont, $xmlWellKnownName, $xmlStrokeOpacity,
                        $xmlFillOpacity, $xmlFont, $xmlFontStyle, $xmlFontWeight, $xmlLineJoin, $xmlLineCap);

                    $i++;
                    // ===============initialize  the variant============
                    $xmlUserLayerName = "";
                    $xmlUserLayerTitle = "";
                    $xmlMinScaleDenominator = "";
                    $xmlMaxScaleDenominator = "";
                    // For point,linestring,polygon,,text
                    $xmlSize = "";
                    // For point,polygon,text,linestring
                    $xmlFillColor = "";
                    // For linestring,polygon
                    $xmlStrokeColor = "";
                    // For text
                    $xmlFont = "";
                    // For point
                    $xmlWellKnownName = "";
                    // For linestring, point
                    $xmlStrokeOpacity = "";
                    // For polygon, point, image
                    $xmlFillOpacity = "";
                    // For text
                    $xmlFont = "";
                    $xmlFontStyle = "";
                    $xmlFontWeight = "";
                    // For linestring
                    $xmlLineJoin = "";
                    $xmlLineCap = "";
                    // =======================finish initializing========================
                }
            }

            $layerselectbox .= "</optgroup>";
        }
        $error = $database->databaseGetErrorMessage();
        if ($database->getFieldsNumber($layersnameslist) == 0) {
            $error .= "\nSorry, there is no records in database!";
        }
        if ($error == "") {

            ?>
			<p id="intro">Create a Style (display range and symbology) for each Layer that created in the previous step.</p>
			<div id="errormessage" class="error"></div>
			<br />

        <FORM name=frmStyles>
        <TABLE class="tableContent">
        <TBODY>
        <tr>
        <td colspan="3"><h2>Style Defination</h2></td>
        </tr>
        <tr>
        <td colspan="3">
		</td>
        </tr>
        <TR class="title">
          <TD width=100>Layers</TD>
          <TD>Range <image src="../img/help.png"  border="0" onmouseover="tooltip('Range','Description:','Range value calculation: for '+
            'example, a map to be displayed covers a 2-degree by 1-degree area in '+
            'the EPSG:4326 (WGS-1984) coordinate system. <br>The linear size of this '+
            'area for conversion to scales would be considered to be: '+
            '2&ordm; &times; (6378137m &times; 2 &times; &Pi;) &divide; 360&ordm; = 222638.9816 meters (m). So, the map extent would be approximately '+
            '222639 x 111319 meters linear distance for the purpose of '+
            'calculating the scale. If the image size for the map is 600 x 300 '+
            'pixels, then the standard scale value for the map would be: '+
            '222638.9816m / (600 pixels &times; 0.00028m per pixel) = 1325226.19. <br>This '+
            'calculation is based on a standardized rendering pixel size of 28mm '+
            'x 28mm. All most of all computer monitors are using this standard pixel size.<br> '+
			'If your clients pixel size does not match those values, you '+
            'will need to change the calculation. Please refer to the OGC SLD '+
            '(Styled Layer Descriptor) specification for details.');" onmouseout="exit();"> and Symbology</TD>
          <TD>Color Picker</TD></TR>
        <TR>
          <TD>
              <TABLE>
              <TBODY>
              <TR>
                <TD><SELECT
                  onchange="writeDivStyleDialog(this.options [this.selectedIndex].value); "
                  size="15" name="sltLayers" class="button4">
                  <?=$layerselectbox?>
				  </SELECT>
				</TD></TR></TBODY>
				</TABLE>
		  </TD>
          <TD width=300>
            <DIV id=divStyleDialog></DIV>
		  </TD>
          <TD>

          <table width="180" style="background-color:#f6f6f6;border:0px dotted #666;padding:5px;margin:0px auto;">
           <tr><td align=right></td>
           <td style="border:1px outset #CCF;background-color:#ffe;width=160px">
            <div id=temoin style='float:right;width:32px;height:128px;'> </div>
            <script type="text/javascript" src="../cssjs/colorpicker.js"></script>
           <td></tr>
          </table>

      </TD></TR>
	   <tr>
	    <td align="left">
		 <input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1 backInput'" onmouseout="this.className='button backInput'" class="button backInput">
         </td>
        <td colspan="2" align="right">
	  </FORM>
      <FORM name=frmPost action="9.php" method="post">
     <?php
            if (count($strStyleInfoContainer) > 0) {
                foreach($strStyleInfoContainer as $k => $v) {
                    echo $strStyleInfoContainer[$k];
                }
            } else $error = "Sorry, there is no records in database!";

            ?>
		<input type="submit" name="btnContinue" value="Continue" onmouseover="this.className='button1 continueInput'" onmouseout="this.className='button continueInput'" class="button continueInput"/>
	</FORM>
		</td>
        </tr>
	  </TBODY></TABLE>


<?php

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