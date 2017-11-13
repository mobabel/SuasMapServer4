<?php
/**
 * user.php
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
 * @version $Id: atlas.php,v 1.2 2008/04/17 12:28 leelight Exp $
 * @copyright (C) 2006-2008  LI Hui(leelight)
 * @Description: input username and password, begin to setting
 * @contact webmaster@easywms.com
 */
include_once '../models/setting.inc';
include_once '../models/menu.inc';
include_once '../models/common.inc';
include_once '../models/perm.inc';
include_once '../models/mysql.class.php';
include_once '../config.php';
include_once 'user.inc';

header('Content-Type: text/html; charset=utf-8'); 

$page = 'user';
$permview = false;
$permoper = false;

//enable go back
session_cache_limiter('private, must-revalidate'); 
session_start();
if ( isset ($_SESSION['user']) ){
	$user = $_SESSION['user'];
	global $user;
}
$op = strtoupper($_REQUEST['op']);
$uid = $_REQUEST['uid'];

$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

$permoper = perm_user_oper( $uid );
$permview = perm_user_view( $uid );
    
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=SUAS_NAME?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="Bookmark" href="../favicon.ico">
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../cssjs/common.js"></script>
<link type="text/css" href="../cssjs/lib/jquery/css/redmond/jquery-ui-1.7.1.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../cssjs/lib/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="../cssjs/string.prototype.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/jquery.blockUI.js"></script>
</head>
<body>

<table id="main">
<tr id="logo"><td colspan="2">

</td></tr>
<tr id="top">
	<td id="left" class="ui-widget-header">Menu</td>
	<td id="right">
	<div id="progressbar"><div id="process" style="width: 0%;"></div></div>
	</td>
</tr>

<tr>
	<td id="progress">
        <ul>
<?
	menu::get_navigation_block($page, "", $database);
if ( $user){	
	//menu::get_navigation_block($page, "", $database);
}
else{
	menu::get_login_block("", true);
}
?>

		</ul>
	</td>
	<td id="content">
	<div id="<?=SITE_MESSAGE_ERROR?>" class="messages error"></div>
	<div id="<?=SITE_MESSAGE_INFO?>" class="messages info"></div>
	<div id="<?=SITE_MESSAGE_WARN?>" class="messages warn"></div>
<?
if ( !$user){
	setSessionMessage(t('Please login to have all privileges.'), SITE_MESSAGE_INFO);
	displayMessage();
}else{
	if($op == ""){
		setSessionMessage(t('Welcome %user! You have logged in.', array('%user' => $user['name'])), SITE_MESSAGE_INFO);
	}
	else if($op == "SAVE"){
		$save_succ = user_update($database, $_POST);
	}
	
}
if($user){
	if($op == ""){
		user_view($uid, $database);
	}
	else if($op == "SAVE" && $permoper){
		if($save_succ){
			$_SESSION['user'] = $save_succ;
			$user = $_SESSION['user'];
			user_view($uid, $database);
		}else{
			user_edit_block($_POST);
		}
	}
	else if($op == "EDIT" && $permoper){
		user_edit_block($_POST);		
	}
	else if($op == "TRACK" && $permview){
		echo "TODO not finished";	
	}
	else{
		setSessionMessage("You have no permission to access this page.", SITE_MESSAGE_INFO);
	}
	
	displayMessage();
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

<?
ob_end_flush();
?>