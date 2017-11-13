<?php
/**
 * register.php
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
 * @version $Id: register.php,v 1.2 2007/05/10 16:41:46 leelight Exp $
 * @copyright (C) 2006-2007  LI Hui(leelight)
 * @Description: register one user
 * @contact webmaster@easywms.com
 */

include_once '../models/setting.inc';
include_once '../models/common.inc';
include_once '../models/perm.inc';
include_once '../models/mysql.class.php';
include_once '../config.php';
include_once '../models/menu.inc';
include_once '../user/user.inc';

header('Content-Type: text/html; charset=utf-8'); 

switchDatabase($dbtype);
session_cache_limiter('private, must-revalidate'); 
session_start();
if ( isset ($_SESSION['user']) ){
	$user = $_SESSION['user'];
	global $user;
}

$page = 'user';
define('USERNAME_MAX_LENGTH', 60);
define('EMAIL_MAX_LENGTH', 64);

//this function disables errors when header code is not on the 1st line of code.
ob_start();

$data_validate = false;
$suc_register = false;

//register one new user
if (isset ($_POST['register'])){
	$re_name = $_POST['name'];
	$re_mail = $_POST['mail'];
	$re_conf_mail = $_POST['conf_mail'];
	$re_pass1 = $_POST['pass1'];
	$re_pass2 = $_POST['pass2'];
	
	$re_data['name'] = $re_name;
	$re_data['mail'] = $re_mail;
	$re_data['conf_mail'] = $re_conf_mail;
	$re_data['pass1'] = $re_pass1;
	$re_data['pass2'] = $re_pass2;
	
	$code = $_POST['code'];
	$validcode = $_POST['validcode'];
	//incorrect validate code 
	if (md5($code) != $validcode){
		setSessionMessage(t('Invalid validation code'),SITE_MESSAGE_ERROR);
		$suc_register = false;
	}else{
		
		$database = new Database();
		$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
		$database->databaseConnect();
		if($database->databaseGetErrorMessage()!=""){
			setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		}
		
		$errmsg = user_validate_alldata($re_data,$database) ;
		if( !empty($errmsg) ){
			$data_validate = false;
			setSessionMessage($errmsg,SITE_MESSAGE_ERROR);
		}
		else{
			$data_validate = true;
			$uid = user_register($re_data, $database);
			if(!$uid){
				$suc_register = false;
				setSessionMessage(t($database->databaseGetErrorMessage()), SITE_MESSAGE_ERROR);
			}
			//if successfully register, go to index to login
			else{
				$suc_register = true;
				setSessionMessage("You have registered as new user. After 3 Second will jump to home automatically.", SITE_MESSAGE_INFO);
			}
		}
	}
	
}

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=SUAS_NAME?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="Bookmark" href="../favicon.ico">
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="../cssjs/lib/jquery/css/redmond/jquery-ui-1.7.1.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../cssjs/common.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../cssjs/string.prototype.js"></script>
<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/jquery.blockUI.js"></script>
</head>
<body>

<table cellspacing="0" cellpadding="0" id="main">
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
menu::get_navigation_block($page, "" , $database);
if ( $user ){
	//menu::get_navigation_block($page, "" , $database);
}
else{
	//menu::get_login_block("user/user", true, $page);
}

?>
		</ul>
	</td>
	<td id="content">
	<div id="<?=SITE_MESSAGE_ERROR?>" class="messages error"></div>
	<div id="<?=SITE_MESSAGE_INFO?>" class="messages info"></div>
<?
displayMessage();
if(!$suc_register){
	print '<div class="messages intro">Create new account</div>
	<br />';
}
if (!isset ($_POST['register']) || !$data_validate || !$suc_register)
	user_register_block($re_data);
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
if($suc_register){
	//reset all session
	$_SESSION = array();
	setReturnLink("user/user", 3);
}
ob_end_flush();
?>