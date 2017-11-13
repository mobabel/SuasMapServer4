<?php
/**
* 2.php
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
* @version $Id: 2.php,v 1.2 2007/05/10 16:40:39 leelight Exp $
* @copyright (C) 2006-2007  leelight
* @Description : Access the database .
* @contact webmaster@easywms.com
*/

require_once '../models/Installation.class.php';
require_once '../models/setting.inc';
require_once '../models/common.inc';
require_once '../models/menu.inc';

$path = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$curpath = basename(dirname($_SERVER['PHP_SELF']));
$path = str_replace($curpath, '', $path);
//replace last // with /
$pos  = strripos($path, '//');
if($pos == strlen($path)-2){
	$path = substr($path, 0, strlen($path)-1);
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
	<div id="progressbar"><div id="process" style="width: 20%;"></div></div>
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
				<li class="error">Database Access</li>
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
				<h2>Database Access</h2>
				<div class="messages">Now you are ready to access the <?=$softName?> database.</div>
					<form name="formdb" id="formdb" method="post" action="3.php" onSubmit="return chkLoginformInput()">
						<table class="tableContent">
							<tr>
								<td colspan="2">
								<p>Please end Server Base Url with "<font class="error">/</font>"</p>
					            </td>
							</tr>
							<tr class="odd">
                    		<td width="30%">Server Base Url : <span class="form-required" title="This field is required.">*</span> <image src="../img/help.png"  border="0" onmouseover="tooltip('Server Base Url','Description:','It is the absolute URL path where you install <?=$softName?> in. Please end the path with slash. If you are not clear, please use the default value.');" onmouseout="exit();"></td>
                    		<td><input name="ServerHost" type="text" id="ServerHost" size="35" value="<?=$path?>" class="smallInput" /></td>
                   			</tr>
							<tr>
								<td colspan="2">
								<h2>Select Available Database</h2>
								</td>
							</tr>
							<tr>
								<td colspan="2">
								<p>Select one database you want use.</p>
								<br/>
								</td>
							</tr>
							<tr class="odd">
								<td width="30%"><input name="sqldatabase" class="button3" value="mysql" type="radio"
								<?
								if(!checkExtensionMySqlInPHP())
									echo "DISABLED";
								else echo "CHECKED ";
								?>
								/></td>
								<td>MySQL</td>
							</tr>
							<tr class="even">
								<td><input name="sqldatabase" class="button3" value="pgsql" type="radio"
								<?
								if(!checkExtensionPgSqlInPHP())
									echo "DISABLED";
								?>
								></td>
								<td>PostgreSQL</td>
							</tr>
							<tr>
								<td colspan="2">
								<h2>Access Database</h2>
								</td>
							</tr>
							<tr>
								<td colspan="2">
								<p>Please fill out the information below. If you are unsure of your access details, please contact your web hosting service.</p>
								<br/>
								</td>
							</tr>
							<tr class="odd">
								<td>Database Server: <span class="form-required" title="This field is required.">*</span></td>
								<td>
								<input name="dbserver" type="text" id="server" value="localhost" size="15"  class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
							</tr>
							<tr class="even">
								<td width="16%">Database User Name: <span class="form-required" title="This field is required.">*</span></td>
								<td width="84%">
								<input name="dbusername" type="text" id="username" value="root" size="15"  class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
							</tr>
							<tr class="odd">
								<td>Database Password: <span class="form-required" title="This field is required.">*</span></td>
								<td>
								<input name="dbpassword" type="password" id="password" size="15"  class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
<?
echo menu::get_captcha_field();
echo menu::print_jsscript_captcha();
?>
								</td>
							</tr>
							<tr>
								<td align="left"><input onclick="GoBack();" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
</td>
								<td align="right"><input onclick="chkLoginformInput();" type="submit" name="Submit" value="Continue" class="ui-button ui-state-default ui-corner-all"/>
</td>
							</tr>
						</table>
						<p>&nbsp;</p>
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