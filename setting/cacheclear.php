<?php
/**
 * clearcahe.php
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
 * @Description: input username and password, begin to setting
 * @contact webmaster@easywms.com
 */

require_once '../global.php';
require_once '../models/menu.inc';

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$softName?> Settting</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<link href="../cssjs/calendar.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../cssjs/common.js"></script>
<script type="text/javascript" src="../cssjs/CalendarPopup.js"></script>
<script language="JavaScript" id="js18">
var cala = new CalendarPopup("divCalandar");
cala.setCssPrefix("TEST");
</script>
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
		<? CreateToolsMenu("cacheclear");?>
		</ul>
	</td>
	<td id="content">
				<h1>Cache Clear</h1>
				<p id="intro"></p>
				<br />

				<div id="options">
					<h2></h2>
					<p>You could clear all of the cache or clear cache in one period of time</p>
					<form name="namecache" id="idcache" method="post" action="cacheclearexc.php" onSubmit="return chkCacheformInput()">
						<table class="tableContent">
							<tr>
								<td>Select All?</td>
								<td><input type="checkbox" name="ckbSelectAll" value="1" class="button3"></td>
							</tr>
							<tr>
								<td width="20%">From date: <image src="../img/help.png"  border="0" onmouseover="tooltip('From date','Description:','Select one date as beginning time, if you leave it empty, the default value is 01/01/1970');" onmouseout="exit();">
								</td>
								<td width="80%">
								<input name="txtdatefrom" value="" size="25" type="text" class="smallInput">
                                <a href="#" onclick="cala.select(document.namecache.txtdatefrom,'datefrom','MM/dd/yyyy'); return false;" title="Select date from:" name="datefrom" id="datefrom"><img src="../img/calandar.png" alt="select date" border="0"></a>
								</td>
							</tr>
							<tr>
								<td width="20%">To date: <image src="../img/help.png"  border="0" onmouseover="tooltip('To date','Description:','Select one date as ending time, if you leave it empty, the default value is today');" onmouseout="exit();">
								</td>
								<td width="80%">
								<input name="txtdateto" value="" size="25" type="text" class="smallInput">
                                <a href="#" onclick="cala.select(document.namecache.txtdateto,'dateto','MM/dd/yyyy'); return false;" title="Select date to:" name="dateto" id="dateto"><img src="../img/calandar.png" alt="select date" border="0"></a>
								</td>
							</tr>
						</table>
						<p>&nbsp;</p>
					<input type="submit" name="Submit" value="Clear" onmouseover="this.className='button1'" onmouseout="this.className='button'" class="button"/>
					<DIV ID="divCalandar" STYLE="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></DIV>
					</form>
                <br/>
				<br/>
            </div>
	</td>
</tr>
</table>

</body>
</html>