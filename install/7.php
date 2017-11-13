<?php
/**
* 3.php
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
* @version $Id: 3.php,v 1.2 2007/05/10 16:40:39 leelight Exp $
* @copyright (C) 2006-2007  leelight
* @Description : Create database table and create config file or and compare the table if select old table
* @contact webmaster@easywms.com
*/

require_once '../models/Installation.class.php';
require_once '../models/setting.inc';
require_once '../models/common.inc';
require_once '../models/tables.php';
include_once '../models/menu.inc';

$btbselect = false;
$success = false;

$tbselect = $_POST['btbs'];
$tbcreate = $_POST['btbc'];
$serverhost = $_POST['ServerHost'];
$dbtype = $_POST['dbtype'];
$dbserver = $_POST['dbserver'];
$dbusername = $_POST['dbusername'];
$dbpassword = $_POST['dbpassword'];
$dbname = $_POST['dbname'];

$prefix = $_POST['prefix'];
if(empty($prefix)){
	$prefix = "suas_";
}

switchDatabase($dbtype);
$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname);
$database->databaseConnect();

/**
* this will not be used, the table fields have been checked in 2b.php
*/
if ($tbselect == "true") {
    if ($database->databaseGetErrorMessage() == "") {
        $error = $database->databaseGetErrorMessage();
        $btbselect = true;
    }
}
if ($tbcreate == "true") {
    $btbcreate = true;
    // Connect to the MySQL server: Error handling
    if ($database->databaseGetErrorMessage() == "") {
        if (!$database->createTablesForSUAS($tables_sql, $prefix)) {
            $error = $database->databaseGetErrorMessage();
        } else {
            $success = true;
        }
    }
}
if ($success || $btbselect) {
	if(strrpos(trim($_POST['ServerHost']), "/")!= (strlen(trim($_POST['ServerHost']))-1) OR !strrpos(trim($_POST['ServerHost']), "/")){
		$error = 'Server Host must be ended with slash /';
	}
	if (empty($error)) {
	    @$file = fopen('../config.php', 'w+');
	    if (!$file) {
	        $error = 'Error whilst attempting to open config.php. Please ensure it is writable or exists.';
	    } else {
	        // Create data to go into config.php
	        $data = '<?php ' . "\r\n";

			$data .= '$dbtype 	= ' . $dbtype . ';' . "\r\n";
	        $data .= '$dbserver 	= \'' . $dbserver . '\';' . "\r\n";
	        $data .= '$dbusername   = \'' . $dbusername . '\';' . "\r\n";
	        $data .= '$dbpassword   = \'' . $dbpassword . '\';' . "\r\n";
	        $data .= '$dbname     	= \'' . $dbname . '\';' . "\r\n";
	        $data .= '$dbprefix     = \'' . $prefix . '\';' . "\r\n";
	        $data .= '$baseserverhost 	= \'' . trim($_POST['ServerHost']) . '\';' . "\r\n";
	        //$data .= 'define("baseserverhost" 	, "' . trim($_POST['ServerHost']) . '");' . "\r\n";
	        $data .= 'global $baseserverhost;' . "\r\n";
	        $data .= '?>';
	        @ $write = fwrite($file, $data);
	        if (!$write) {
	            $error = 'Error while attempting to write to config.php. Please ensure it is writable/it exists.';
	        } else {
	            fclose($file);
	            $success = true;
	        }
	    }
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
    echo '<div id="progressbar"><div id="process" style="width: 90%;"></div></div>';
else
    echo '<div id="progressbar"><div id="process" style="width: 100%;"></div></div>';

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
				<li class="done">Table Checking</li>
<?php
if ($error != "") {
    echo "		<li class=\"error\">Table Setting</li>
			</ul>";
} else {
    echo "<li class=\"done\">Table Setting</li>
			</ul>
		<li class=\"complete\">Complete!</li>";
}
?>
		</ul>
	</td>
	<td id="content">
		<div id="<?=SITE_MESSAGE_ERROR?>" class="messages error"></div>
		<div id="<?=SITE_MESSAGE_INFO?>" class="messages info"></div>
<?
    displayMessage();
?>
<?
// if create table of select table
if ($success || $btbselect) {
	//reset all session
	$_SESSION = array();
?>
			<div class="messages">All tables have been created sucessfully.</div>

				<h1><?=SUAS_NAME?> has been installed successfully</h1>
				<form name="formsettings" id="fromsettings" method="post" >
				    <table class="tableContent">
                    	<tr>
							<td colspan="2">
								<div class="begin"><a href="../user/register.php">Register new user</a></div>
					            </td>
						</tr>
						<tr>
                    		<td width="30%"></td>
							<td></td>
                   		</tr>
                        <tr>
                    		<td align="left">
        					</td>
                    		<td align="right">
                            </td>
                   	</tr>
                    	</table>
				</form>
<?php
}

?>
<?
if (!empty($error)) {
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
               <input onclick="GoBack();" name="button" value="Back" class="ui-button ui-state-default ui-corner-all">

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