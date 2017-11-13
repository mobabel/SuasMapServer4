<?php
include_once 'models/setting.inc';
include_once 'models/menu.inc';
include_once 'models/common.inc';
include_once 'models/perm.inc';
require_once 'parser/StyleReader.class.php';
require_once 'models/import.inc';
require_once 'models/page.class.php';
include_once 'config.php';
include_once 'atlas/atlas.inc';

header('Content-Type: text/html; charset=utf-8'); 
//ob_start();
$page = 'home';
$listmax = 5;
switchDatabase_home($dbtype);
session_start();
if ( isset ($_SESSION['user']) ){
	$user = $_SESSION['user'];
	global $user;
}

$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=SUAS_NAME?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="Bookmark" href="favicon.ico">
<link href="cssjs/setup.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="cssjs/lib/jquery/css/redmond/jquery-ui-1.7.1.custom.css" rel="stylesheet" />
<script type="text/javascript" src="cssjs/common.js"></script>
<script type="text/javascript" src="cssjs/lib/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="cssjs/lib/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="cssjs/lib/jquery/js/plugin/jquery.blockUI.js"></script>
<script type="text/javascript" src="cssjs/string.prototype.js"></script>
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
	menu::get_login_block("", true, $page);
}
	menu::get_latestuser_block($database);
	menu::get_randomatlas_block($database, 8);
?>

		</ul>
	</td>
	<td id="content">
	<div id="<?=SITE_MESSAGE_ERROR?>" class="messages error"></div>
	<div id="<?=SITE_MESSAGE_INFO?>" class="messages info"></div>
	<div id="<?=SITE_MESSAGE_WARN?>" class="messages warn"></div>
	<table class="tableNone">
	<tr>
		<td colspan="2">
		
		</td>
	<tr>
	<tr valign="top">
		<td width="50%">
		<?block_atlas_list($database, 5, "created", "desc");?>
		</td>
		<td width="50%" >
		<?block_hotestatlas($database, $page);?>
		</td>
	<tr>
	<tr valign="top">
		<td>
		<table id="table_atlas_list" class="block-panel block-panel-latestcomment" >
		<tr class="block-panel-header block-panel-header-latestcomment">
		<td colspan="2">Latest Comments</td>
		</tr>
		</table>
		</td>
		
		<td>
		<?
		//block_randomatlas_list($database, 8);
		?>
  		
		</td>
	<tr>
	<tr>
		<td colspan="2">
		
		</td>
	<tr>
	</table>
<?
	//setSessionMessage("Home message", SITE_MESSAGE_INFO);
	
	displayMessage();
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
//ob_end_flush();
?>