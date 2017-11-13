<?php
/**
 * cacheclearexc.php
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
require_once '../models/Cache.class.php';
require_once '../models/menu.inc';

$blnSelectAll = $_POST['ckbSelectAll'];
$txtdatefrom = $_POST['txtdatefrom'];
$txtdateto = $_POST['txtdateto'];

$datefrom = explode("/",$txtdatefrom);
$fm = $datefrom[0];
$fd = $datefrom[1];
$fy = $datefrom[2];

$dateto = explode("/",$txtdateto);
$tm = $dateto[0];
$td = $dateto[1];
$ty = $dateto[2];

$cache = new Cache(0, 0);

if($blnSelectAll == 1){
    $suc = $cache->clearCache("all");
}
else{
    $suc = $cache->clearCacheFromDateTo($fm,$fd,$fy,$tm,$td,$ty);
}

$error = $cache->getError();
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
		<? CreateToolsMenu("cacheclear");?>
		</ul>
	</td>
	<td id="content">
	<?if($suc){
	      if($blnSelectAll == 1){
	?>
				<h1>Cache Clear Successfully!</h1>
				<p id="intro"></p>
				<br />

				<div id="options">
					<h3></h3>
					<p>All of the cache has been cleared.</p>

                <br/>
				<br/>
                </div>

     <?
         }
         else{
             print('<h1>Cache Clear Successfully!</h1>
				<p id="intro"></p>
				<br />

				<div id="options">
					<h3></h3>
					<p>Cache from to  has been cleared.</p>

                <br/>
				<br/>
                </div>');
		 }
	 }
	 else{
	     print('<table class="tableError">
<tr>
<td>
			<h4>Failure</h4>
			    <p id="intro">You must correct the error below before installation can continue:<br/><br/>
                <span style="color:#000000">'.$error.'</span><br /><br /></p>
</td>
</tr>
<tr>
<td align="left">
               <input onclick="GoBack();" name="button" value="Back" onmouseover="this.className=\'button1\'" onmouseout="this.className=\'button\'" class="button">

</td>
</tr>
</table>');
	 }

	 ?>
	</td>
</tr>
</table>

</body>
</html>