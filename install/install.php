<?php
/**
 * install.php
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
 * @version $Id: install.php,v 1.2 2007/05/10 16:40:39 leelight Exp $
 * @copyright (C) 2006-2007  leelight
 * @Description : This page check the requirement for the installation.
 * @contact webmaster@easywms.com
 */

require_once '../models/Installation.class.php';
require_once '../models/setting.inc';
require_once '../models/common.inc';
require_once '../config.php';
include_once '../models/menu.inc';

switchDatabase($dbtype);
$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

$version = $database->getSUASVersion();
if($dbtype == 0) $dbtype = "MySQL";
else if($dbtype == 1) $dbtype = "PostgreSQL";
if($version){
	setSessionMessage(t(SUAS_NAME.' has been installed before, version is %version. Database %dbtype is using.', array('%version' => $version, '%dbtype' => $dbtype)),SITE_MESSAGE_INFO);
	$message = t(SUAS_NAME.' has been installed before, version is %version. Database %dbtype is using. Still continue to install?', array('%version' => $version, '%dbtype' => $dbtype));
	$alreadyinstall = true;
}
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
	<div id="progressbar"><div id="process" style="width: 5%;"></div></div>
	</td>
</tr>

<tr>
	<td id="progress">
		<ul>
		<li class="first"><span>Start</span></li>
			<ul class="second">
				<li class="error">Server Requirements</li>
				<li >License Agreement</li>
			</ul>
		<li class="first"><span>Installation</span></li>
			<ul class="second">
				<li>Database Access</li>
				<li>Database Checking</li>
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
		<h2>Server Requirements </h2>
		<div class="messages"><?=SUAS_NAME?> has a number of minimum requirements for installation. 
		These requirements are checked below, and once satisfied you will be able to continue with the installation process.</div>
		    <form name="formRequirements" id="formRequirements" method="post" action="1.php"
<?
if($alreadyinstall){
	echo  "onSubmit=\"return continueInstall('$message')\"";
}
?>
			>
		    <div id="options">
              <table class="tableContent">
			<tr class="title">
				<td colspan="2">
				Mandatory Extensions:
				</td>
			</tr>
			<tr class="odd">
				<td colspan="2">
			<?php
$requirements = checkMandatoryExtensionInPHP($database);
?>

								</td>
							</tr>
			<tr class="title">
				<td colspan="2">
				Optional Extensions:
				</td>
			</tr>
							<tr class="odd">
								<td colspan="2">
			<?php
checkOptionalExtensionInPHP();
?>
								</td>
							</tr>
			<tr class="title">
				<td colspan="2">
				Mandatory Requirement:
				</td>
			</tr>
							<tr class="odd">
								<td colspan="2">

			<?php
$directoryWritbale = checkDirectoryWritabale();
?>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td align="left">
								</td>
								<td align="right">
<?
if ($requirements && $directoryWritbale && !$alreadyinstall) {
	?>
				<input type="submit" name="Continue" value="Continue" class="ui-button ui-state-default ui-corner-all"/>
<?
} 
else if($alreadyinstall){
				
}
else {
	?>
				<p>You need to fix the above problem(s) before you can continue with the installation.</p>
<?
}
?>
								</td>
							</tr>
						</table>
				</div>
              </form>
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