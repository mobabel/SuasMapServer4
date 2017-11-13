<?php
/**
 * s3a.php
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
 * @version $Id: s3a.php,v 1.2 2007/05/10 16:41:46 leelight Exp $
 * @copyright (C) 2006-2007  leelight
 * @Description: Compare the featuregeometry table
 * @contact webmaster@easywms.com
 */
require_once '../global.php';
require_once '../models/menu.inc';
require_once '../models/setting.inc';

$database = new Database();

$tbselect = $_POST['btbs'];
$dbserver = $_POST['dbserver'];
$dbusername = $_POST['dbusername'];
$dbpassword = $_POST['dbpassword'];
$dbname = $_POST['dbname'];

if ($tbselect == "true") {
    $tbname = $_POST['tables'].mapTableFeaturegeometry;
    $btbselect = true;
    $database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname);
    $database->databaseConnect();

    $error = $database->databaseGetErrorMessage();
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$softName?> Settting</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../cssjs/common.js"></script>
<script type="text/javascript" src="../cssjs/string.protoype.js"></script>
</head>
<body>

<table cellspacing="0" cellpadding="0" id="main">
<tr id="logo"><td colspan="2"><span class="logoprefix"><?=$softName."  ".$softVersion.$softEdition?></span></td></tr>
<tr id="top">
	<td id="left">Setting Progress</td>
	<td id="right">
		<?
	if(!empty($error))
		echo '<div id="progressbar"><div id="process" style="width: 25%;"></div></div>';
	else
		echo '<div id="progressbar"><div id="process" style="width: 45%;"></div></div>';
	?>
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
<?php
if ($error!="") {
    echo "
     			<li class=\"error\">Table Settings</li>
				<li>General Settings</li>";
}else{
	if (isset($btbselect)) {
	    echo "
			        <li class=\"error\">Table Settings</li>
					<li>General Settings</li>";
	}
}

?>
                <li>Data Import</li>
				<li><a href="8.php">Style Settings</a></li>
				<li>Create Metadata</li>
			</ul>
		<li class="first"><span>Install</span></li>
			<ul class="second">
				<li class="unactive"><a href="../<?=$installName?>/install.php">Database Installation</a></li>
			</ul>
			<? CreateToolsMenu("default");?>
		</ul>
	</td>
	<td id="content">
<?php
if (isset($btbselect) && $error=="") {

    ?>
				<p id="intro">Now you are ready to check the database table.</p>
				<div id="errormessage" class="error"></div>
				<br />

				<div id="options">
					<h2>Table Settings</h2>
					<p>Please compare the Fields of standard table and selected table below. You should make sure that the Field Name and Field Type must be the same.
					If all the fields are with same name and type, please click next; if they are different, please click back to select again or create new.</p>
					<form name="tablename" id="tablename" method="post" action="s4.php" onSubmit="return chkform()">

						<table class="tableBlock">
							<tr>
								<td colspan="2"><h3>Compare the Tables</h3></td>
							</tr>
							<tr>
                                    <td width="50%">Standard Field</td>
                                    <td width="50%">Selected Field</td>
							</tr>
                            <tr>
                                <td><table class="tableInBlock">
                                            <tr class="title">
                                              <td>Field Name</td>
                                              <td>Field Type</td>
                                            </tr>
                                             <tr class="even">
                                              <td>id</td>
                                              <td>int( 11 )</td>
                                            </tr>
                                            <tr class="odd">
                                              <td>layer</td>
                                              <td>varchar(60)</td>
                                            </tr>
                                            <tr class="even">
                                              <td>recid</td>
                                              <td>varchar(20)</td>
                                            </tr>
                                            <tr class="odd">
                                              <td>geomtype</td>
                                              <td>varchar(20)</td>
                                            </tr>
                                            <tr class="even">
                                              <td>xmin</td>
                                              <td>double</td>
                                            </tr>
                                            <tr class="odd">
                                              <td>ymin</td>
                                              <td>double</td>
                                            </tr>
                                            <tr class="even">
                                              <td>xmax</td>
                                              <td>double</td>
                                            </tr>
                                            <tr class="odd">
                                              <td>xmax</td>
                                              <td>double</td>
                                            </tr>
                                            <tr class="even">
                                              <td>geom</td>
                                              <td>geometry</td>
                                            </tr>
                                            <tr class="odd">
                                              <td>svgxlink</td>
                                              <td>text</td>
                                            </tr>
                                            <tr class="even">
                                              <td>srs</td>
                                              <td>varchar(30)</td>
                                            </tr>
                                            <tr class="odd">
                                              <td>attributes</td>
                                              <td>text</td>
                                            </tr>
                                            <tr class="even">
                                              <td>style</td>
                                              <td>varchar(20)</td>
                                            </tr>
                                          </table></td>
								<td>
<?php

$result=$database->getColumnsFromTable($tbname);
if($database->databaseGetErrorMessage() ==""){
    $blnSameTable = showcol($result);
    }
        ?>
								</td>
							</tr>

							<tr>
								<td><input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1 backInput'" onmouseout="this.className='button backInput'" class="button backInput">
                                </td>
								<td ALIGN="right">
<?php
    if ($result && $blnSameTable) {

        ?>
                                <input type="submit" name="Continue" value="Continue" onmouseover="this.className='button1 continueInput'" onmouseout="this.className='button continueInput'" class="button continueInput"/></td>
<?php
    }
    if(!$result || !$blnSameTable) {
         print('<font class="error">The table you selected has different structure.<br> Please go back to select or create new table.</font>');
     }

    ?>
							</tr>
                           <div id=bdbs style=visibility:hidden>
                           <input name="btbfs" type="text" id="btbfs" value="true" />
                           <input name="dbserver" type="text" id="dbserver" value="<?=$dbserver?>" />
                           <input name="dbusername" type="text" id="dbusername" value="<?=$dbusername?>" />
                           <input name="dbpassword" type="text" id="dbpassword" value="<?=$dbpassword?>" />
                           <input name="dbname" type="text" id="dbname" value="<?=$dbname?>" />
                           <input name="tbname" type="text" id="tbname" value="<?=$tbname?>" />
                           </div>

						</table>
						</form>
<?php
}

else if ($error!="") {
        // Failure
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
<?php
function showcol($result)
{
$blnSameTable = true;
$database = new Database();
    echo "<table class=\"tableInBlock\">\n
<TR class=\"title\">\n
<TD >Field Name</TD>\n
<TD >Field Type</TD>\n
</TR>\n";

    $i = 0;
    while ($row=$database->getColumns($result)) {
    $i = $i + 1;
     if($i%2==0)
     echo "<TR class=\"odd\">";
     else echo "<TR class=\"even\">";

        echo "\n<TD >";
        echo $tem =$row["Field"];
        echo "</TD>\n";
        echo "<TD >";
        echo $row["Type"];
        echo "</TD>\n</TR>\n";
       if($tem!="id"&&$tem!="layer"&&$tem!="recid"&&$tem!="geomtype"&&$tem!="xmin"&&$tem!="ymin"&&$tem!="xmax"&&$tem!="ymax"&&$tem!="geom"&&$tem!="svgxlink"&&
$tem!="srs"&&$tem!="attributes"&&$tem!="style"){
            $blnSameTable = false;
        }
    }
    echo "</table>\n";
    return $blnSameTable;
}

?>