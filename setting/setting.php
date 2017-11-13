<?php
/**
 * setting.php
 * Copyright (C) 2006-2008  leelight
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
 * @version $Id: setting.php,v 1.2 2007/05/10 16:41:46 leelight Exp $
 * @copyright (C) 2006-2007  LI Hui(leelight)
 * @Description: input username and password, begin to setting
 * @contact webmaster@easywms.com
 */

include_once '../models/setting.inc';
include_once '../models/menu.inc';
include_once '../models/common.inc';

session_start();

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=SUAS_NAME?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../cssjs/common.js"></script>
<script type="text/javascript" src="../cssjs/lib/prototype/prototype.js"></script>
<script type="text/javascript" src="../cssjs/string.protoype.js"></script>
</head>
<body>

<table cellspacing="0" cellpadding="0" id="main">
<tr id="logo"><td colspan="2"><span class="logoprefix"><?=$softName."  ".$softVersion.$softEdition?></span></td></tr>
<tr id="top">
	<td id="left">Menu</td>
	<td id="right">
	<div id="progressbar"><div id="process" style="width: 0%;"></div></div>
	</td>
</tr>

<tr>
	<td id="progress">
        <ul>
            <!--li class="first"><span><a href="../Demo/index.php">Home</a></span></li-->
<?
if (isset ($_SESSION['user']) ){
	$user = $_SESSION['user'];
	global $user;
	menu::get_user_block($siteinfo, $user['name'], $user['role']);
}
else{
	menu::get_login_block("setting", true);
}
?>
			<li class="first"><span>Configuration</span></li>
			<ul class="second">
				<li class="error">Database Access</li>
				<li>Database Settings</li>
                <li>Table Settings</li>
				<li>General Settings</li>
				<li><a href="7.php" title="Import the data directly">Data Import</a></li>
				<li><a href="8.php" title="Set the style directly">Style Settings</a></li>
				<li>Create Metadata</li>
			</ul>
		<? menu::CreateToolsMenu("default");?>
		</ul>
	</td>
	<td id="content">
	<div id="<?=SITE_MESSAGE_ERROR?>" class="messages error"></div>
	<div id="<?=SITE_MESSAGE_INFO?>" class="messages info"></div>
<?
	displayMessage();
	if (isset ($_SESSION['user']) ){
		print '<div class="messages intro">Welcome '.$user['name'].'! You have logged in.</div>';
	}else{
		print '<div class="messages intro">Please login to have all privileges.</div>';
	}
?>


				<br/>

                <br/>
                <!--table class="tableBlock">
<tr>
                                <td>
                                <h2>Data Import</h2>
				<p>Import the new data with your previous database configuration.<br />
				</p>
</td>
                                </tr>
<tr>
                                <td align="right">
				<div class="begin"><a href="7.php" title="Import the data directly">Import Data</a></div><br />
</td>
                                </tr>
                           </table>
                           <table class="tableBlock">
<tr>
                                <td>
                                <h2>Style Setting</h2>
				<p>Set the Style (display range and symbology) if data has been imported.<br />
				</p>
</td>
                                </tr>
<tr>
                                <td align="right">
				<div class="begin"><a href="8.php" title="Set the style directly">Style Defination</a></div><br />
</td>
                                </tr>
                </table-->

	</td>
</tr>
</table>

</body>
</html>