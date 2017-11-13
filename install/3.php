<?php
/**
 * 2c.php
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
 * @version $Id: 2c.php,v 1.2 2008/04/22 21:43:39 leelight Exp $
 * @copyright (C) 2006-2008  leelight
 * @Description: Check the database version and functionalities
 * @contact webmaster@easywms.com
 */

require_once '../models/Installation.class.php';
require_once '../models/setting.inc';
require_once '../models/common.inc';
include_once '../models/menu.inc';

$code = $_POST['code'];
$validcode = $_POST['validcode'];

// Check all fields filled in
if (empty($_POST['dbserver']) || empty($_POST['dbusername'])){
	$error = 'You must fill out all the fields';
}
else if(empty($_POST['sqldatabase'])){
	$error = 'Please select at least one database you want to use.';
}
else if (md5($code) != $validcode){
	$error = 'Invalid validation code.';
}
else{
	if (!isset($error)){
		$serverhost = $_POST['ServerHost'];
		$sqldb = $_POST['sqldatabase'];
		$dbserver=$_POST['dbserver'];
		$dbusername=$_POST['dbusername'];
		$dbpassword=$_POST['dbpassword'];

		//TODO
		if($sqldb == "mysql"){
			$dbtype = 0;
		}else if($sqldb == "pgsql"){
			$dbtype = 1;
		}
		switchDatabase($dbtype);
		$database = new Database();

		$database->databaseConfig($dbserver,$dbusername,$dbpassword,"");
        $database->databaseConnectNoDatabase();
        $error = $database->databaseGetErrorMessage();
	}
}

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=SUAS_NAME?> Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="../cssjs/lib/jquery/css/redmond/jquery-ui-1.7.1.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../cssjs/common.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../cssjs/string.prototype.js"></script>
</head>
<body>

<table cellspacing="0" cellpadding="0" id="main">
<tr id="logo"><td colspan="2"><span class="logoprefix"><?=SUAS_NAME . "  " . SITE_VERSION .".". SITE_VERSION_EDITION?></span></td></tr>
<tr id="top">
	<td id="left" class="ui-widget-header">Setting Progress</td>
	<td id="right">
	<?
	if(!empty($error))
		echo '<div id="progressbar"><div id="process" style="width: 20%;"></div></div>';
	else
		echo '<div id="progressbar"><div id="process" style="width: 30%;"></div></div>';
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
				<li class="error">Database Checking</li>
				<li>Database Setting</li>
				<li>Table Checking</li>
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
<?
	if (empty($error)){
?>
				<h2>Database Checking</h2>
				<div class="messages">Database functionalities checking.</div>
                <div id="errormessage" class="error"></div>	
					<form name="databasename" id="databasename" method="post" action="4.php" onSubmit="return chkform()">
						<table class="tableContent">
							<tr>
								<td colspan="2"></td>
							</tr>
							<tr>
								<td width="30%">
								Version:
								</td>
								<td>
<?
$version = $database->getDatabaseVersion();
if($version)
{
	$versioncheck = checkDatabaseVersion($version, $databasetype);
?>
								</td>
							</tr>
							<tr>
								<td>
								Create&Drop Database Privilege:
								</td>
								<td>
<?
$createdatabase = $database->databaseCheckCreateAndDropDatabasePrivelege();
if($createdatabase){
	print('<span style="color:#009900">Test Passed - You can create&Drop new database.</span>');
}
else{
	print('<span style="color:#CC0000">Test Failed - You have no privelege to create new database.</span>');
}

?>
								</td>
							</tr>
							<tr>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td align="left">
								<input onclick="GoBack();" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
                                 </td>
								<td ALIGN="right">
<?
if($versioncheck)	{
?>
								<input type="submit" name="Selectdb" value="Continue" class="ui-button ui-state-default ui-corner-all"/>
<?
}
?>
								</td>
							</tr>
							<input name="ServerHost" type="hidden" id="ServerHost" value="<?=$serverhost?>" />
                           	<input name="dbtype" type="hidden" id="dbtype" value="<?=$dbtype?>" />
                           	<input name="createdatabase" type="hidden" id="createdatabase" value="<?=$createdatabase?>" />
                           	<input name="dbserver" type="hidden" id="dbserver" value="<?=$dbserver?>" />
                           	<input name="dbusername" type="hidden" id="dbusername" value="<?=$dbusername?>" />
                           	<input name="dbpassword" type="hidden" id="dbpassword" value="<?=$dbpassword?>" />
<?
}
?>
						</table>
						</form>

<?
}
else{

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