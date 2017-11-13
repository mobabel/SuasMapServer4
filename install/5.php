<?php
/**
* 2d.php
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
* @version $Id: 2b.php,v 1.2 2007/05/10 16:40:39 leelight Exp $
* @copyright (C) 2006-2008  leelight
* @Description : create the database and check the privilege of table operation .
* @contact webmaster@easywms.com
*/

require_once '../models/Installation.class.php';
require_once '../models/setting.inc';
require_once '../models/common.inc';
include_once '../models/menu.inc';

$success = false;

$dbselect = $_POST['bdbs'];
$dbcreate = $_POST['bdbc'];
$serverhost = $_POST['ServerHost'];
$dbtype = $_POST['dbtype'];
$dbserver = $_POST['dbserver'];
$dbusername = $_POST['dbusername'];
$dbpassword = $_POST['dbpassword'];

switchDatabase($dbtype);
$database = new Database();

if ($dbselect == "true") {
    $dbname = $_POST['databases'];
    $database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname);
    $database->databaseConnect();
} else if ($dbcreate == "true") {
    $dbname = $_POST['databasei'];
    // Database Name could not be only number
    if (eregi("^[0-9]+$", $dbname)) {
        $error = 'Database Name could not be only number.' . '<br>';
        $error = $error . 'Please use Database Name like ' . 'wms_' . "$dbname" . ', or db_' . "$dbname" . '.';
    } else {
        $success = true;
        $database->databaseConfig($dbserver, $dbusername, $dbpassword, "");
        $database->databaseConnectNoDatabase();

	    $database->databaseCreateDatabase($dbname);
	    $error = $database->databaseGetErrorMessage();
	    if (empty($error)) {
	        setSessionMessage("Database <b>" . $dbname . "</b> has been created.", SITE_MESSAGE_INFO);
	        $database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname);
	        $database->databaseConnect();
	    }
    }
}

$privilege = $database->databaseCheckTablePrivelege();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=SUAS_NAME?> Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="../cssjs/lib/jquery/css/redmond/jquery-ui-1.7.1.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../cssjs/common.js"></script>
<script type="text/javascript" src="../cssjs/string.prototype.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/jquery-1.3.2.min.js"></script>
</head>
<body>

<table cellspacing="0" cellpadding="0" id="main">
<tr id="logo"><td colspan="2"><span class="logoprefix"><?=SUAS_NAME . "  " . SITE_VERSION .".". SITE_VERSION_EDITION?></span></td></tr>
<tr id="top">
	<td id="left" class="ui-widget-header">Setting Progress</td>
	<td id="right">
		<?php
if (!empty($error))
    echo '<div id="progressbar"><div id="process" style="width: 50%;"></div></div>';
else
    echo '<div id="progressbar"><div id="process" style="width: 60%;"></div></div>';

?>
	</td>
</tr>
<tr>
	<td id="progress">
		<ul>
		<li class="first"><span>Start</span></li>
			<ul class="second">
				<li class="done">Server Requirements</li>
				<li class="done">License Agreement</li>
			</ul>
		<li class="first"><span>Installation</span></li>
			<ul class="second">
				<li class="done">Database Access</li>
				<li class="done">Database Checking</li>
				<li class="done">Database Setting</li>
				<li class="error">Table Checking</li>
				<li>Table Setting</li>
			</ul>
		</ul>
	</td>
	<td id="content">
		<div id="<?=SITE_MESSAGE_ERROR?>" class="messages error"></div>
		<div id="<?=SITE_MESSAGE_INFO?>" class="messages info"></div>
<?
    displayMessage();
?>
<?php
if (empty($error)) {

    ?>
    			<h2>Table Checking</h2>
				<div class="messages">Now you are checking the table operation privilege.</div>
					<form name="tablecheck" id="tablecheck" method="post" action="6.php">
					<table class="tableContent">
							<tr>
								<td colspan="2"></td>
							</tr>
							<tr class="odd">
								<td width="30%">
								Table CREATE Privilege:
								</td>
								<td>
<?
if($privilege['SELECT'] && $privilege['CREATE']){
	print('<span style="color:#009900">Test Passed</span>');
}else{
	print('<span style="color:#CC0000">Test Failed</span>');
	echo $database->databaseGetErrorMessage();
}
?>
								</td>
							</tr>
							<tr class="even">
								<td>
								Table INSERT Privilege:
								</td>
								<td>
<?
if($privilege['INSERT']){
	print('<span style="color:#009900">Test Passed</span>');
}else{
	print('<span style="color:#CC0000">Test Failed</span>');
	echo $database->databaseGetErrorMessage();
}
?>
								</td>
							</tr>
							<tr class="odd">
								<td>
								Table UPDATE Privilege:
								</td>
								<td>
<?
if($privilege['UPDATE']){
	print('<span style="color:#009900">Test Passed</span>');
}else{
	print('<span style="color:#CC0000">Test Failed</span>');
	echo $database->databaseGetErrorMessage();
}
?>
								</td>
							</tr>
							<tr class="even">
								<td>
								Table DELETE Privilege:
								</td>
								<td>
<?
if($privilege['DELETE']){
	print('<span style="color:#009900">Test Passed</span>');
}else{
	print('<span style="color:#CC0000">Test Failed</span>');
	echo $database->databaseGetErrorMessage();
}
?>
								</td>
							</tr>
							<tr class="odd">
								<td>
								Table DROP Privilege:
								</td>
								<td>
<?
if($privilege['DROP']){
	print('<span style="color:#009900">Test Passed</span>');
}else{
	print('<span style="color:#CC0000">Test Failed</span>');
	echo $database->databaseGetErrorMessage();
}
?>
								</td>
							</tr>
							<tr>
								<td align="left">
								<input onclick="GoBack();" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
                                 </td>
								<td ALIGN="right">
<?
if($privilege['SELECT'] && $privilege['CREATE'] && $privilege['INSERT']
	&& $privilege['UPDATE'] && $privilege['DELETE'] && $privilege['DROP']){
?>
								<input type="submit" name="Selectdb" value="Continue" class="ui-button ui-state-default ui-corner-all"/>
<?
}
?>
								</td>
							</tr>
					</table>
					<input name="ServerHost" type="hidden" id="ServerHost" value="<?=$serverhost?>" />
                    <input type="hidden" name="dbtype" id="dbtype" value="<?=$dbtype?>" />
                    <input type="hidden" name="dbserver" id="dbserver" value="<?=$dbserver?>" />
                    <input type="hidden" name="dbusername"  id="dbusername" value="<?=$dbusername?>" />
                    <input type="hidden" name="dbpassword"  id="dbpassword" value="<?=$dbpassword?>" />
                    <input type="hidden" name="dbname"  id="dbname" value="<?=$dbname?>" />
					</form>

<?
}else {

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
               <input onclick="GoBack();" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">

</td>
</tr>
</table>
<?
}

?>
	</td>
</tr>
<tr id="footer">
<td colspan="2">
<?menu::getFooter();?>
</td></tr>
</table>
<script>
$(function() {jbutton();});
</script>
</body>
</html>