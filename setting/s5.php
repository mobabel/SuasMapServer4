<?php
/**
 * s5.php
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
 * @version $Id: s5.php,v 1.2 2007/05/10 16:41:46 leelight Exp $
 * @copyright (C) 2006-2007  leelight
 * @Description : Create config file and input data
 * @contact webmaster@easywms.com
 */

require_once '../global.php';
require_once '../models/Installation.class.php';
require_once '../models/menu.inc';
require_once '../models/setting.inc';

$error = "";
$tbfselect = $_POST['btbfs'];
$dbserver = $_POST['dbserver'];
$dbusername = $_POST['dbusername'];
$dbpassword = $_POST['dbpassword'];
$dbname = $_POST['dbname'];
$tbname = $_POST['tbname'];
$tbmetaname = $_POST['tbmetaname'];
$tableprefix = $_POST['tableprefix'];

$success = false;
$error = "";

if (trim($_POST['ServerTitle'])=="" OR trim($_POST['ServerAbstract'])=="" OR trim($_POST['LayerTitle'])=="" OR trim($_POST['ContactPerson']=="")) {
    $error = 'You must fill out the fields with star *';
}
else if(strrpos(trim($_POST['ServerHost']), "/")!= (strlen(trim($_POST['ServerHost']))-1) || !strrpos(trim($_POST['ServerHost']), "/")){
	$error = 'Server Host must be ended with anti slash /';
}
else if (empty($dbserver) || empty($dbusername) || empty($dbpassword) || empty($dbname) || empty($tbname)) {
    $error = 'Database or Table has not been selected';
}
// No errors? Good. Proceed with caution Mr Spock
if (empty($error)) {
    @ $file = fopen('../config.php', 'w+');
    if (!$file) {
        $error = 'Error whilst attempting to open config.php. Please ensure it is writable/it exists.';
    } else {
        // Create data to go into config.php
        $data = '<?php ' . "\r\n";
        $data .= '// Database config data' . "\r\n";
        // $data.= '$config[\'database\'][\'username\'] 	= \''.$dbusername.'\';'."\r\n";
        // $data.= '$config[\'database\'][\'password\'] 	= \''.$dbpassword.'\';'."\r\n";
        // $data.= '$config[\'database\'][\'server\'] 		= \''.$dbserver.'\';'."\r\n";
        // $data.= '$config[\'database\'][\'dbname\'] 		= \''.$dbname.'\';'."\r\n";
        // $data.= '$config[\'database\'][\'tbname\'] 		= \''.$tbname.'\';'."\r\n";
        // $data.= '$config[\'database\'][\'persistant\'] 	= 0;'."\r\n";
        // $data.= 'define (\'tbname\', \''.$tbname.'\');'."\r\n";
        $data .= '$servername = \'' . $dbserver . '\';' . "\r\n";
        $data .= '$username   = \'' . $dbusername . '\';' . "\r\n";
        $data .= '$password   = \'' . $dbpassword . '\';' . "\r\n";
        $data .= '$dbname     = \'' . $dbname . '\';' . "\r\n";
        $data .= '$tbname     = \'' . $tbname . '\';' . "\r\n";
        $data .= '$tbmetaname     = \'' . $tbmetaname . '\';' . "\r\n";
        $data .= '$tableprefix     = \'' . $tableprefix . '\';' . "\r\n";

        $data .= '$wmsservice     = \'WMS\';' . "\r\n";
        $data .= '$wmsversion     = \'1.1.1\';' . "\r\n";
        $data .= '$wfsservice     = \'WFS\';' . "\r\n";
        $data .= '$wfsversion     = \'1.1.1\';' . "\r\n";

        $data .= '// WMS Metadata for GetCapabilities Request' . "\r\n";

        $data .= '$enablestretchmap     = ' . $_POST['enablestretchmap'] . ';' . "\r\n";
        $data .= '$enablecache     = ' . $_POST['enablecache'] . ';' . "\r\n";
        $data .= '$wmsmetadata = array();' . "\r\n";
        $data .= '$wmsmetadata[\'ServerHost\'] 				= \'' . trim($_POST['ServerHost']) . '\';' . "\r\n";

        $data .= '$wmsmetadata[\'ServerTitle\'] 				= \'' . trim($_POST['ServerTitle']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ServerAbstract\'] 				= \'' . trim($_POST['ServerAbstract']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'LayerTitle\'] 				= \'' . trim($_POST['LayerTitle']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'Keyword1\'] 				= \'' . trim($_POST['Keyword1']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'Keyword2\'] 				= \'' . trim($_POST['Keyword2']) . '\';' . "\r\n";

        $data .= '$wmsmetadata[\'ContactPerson\'] 				= \'' . trim($_POST['ContactPerson']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactOrganization\'] 	= \'' . trim($_POST['ContactOrganization']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactPosition\'] 			= \'' . trim($_POST['ContactPosition']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactAddress\'][\'AddressType\'] 			= \'' . trim($_POST['AddressType']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactAddress\'][\'Address\'] 					= \'' . trim($_POST['Address']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactAddress\'][\'City\'] 						= \'' . trim($_POST['City']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactAddress\'][\'StateOrProvince\'] 	= \'' . trim($_POST['StateOrProvince']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactAddress\'][\'PostCode\'] 				= \'' . trim($_POST['PostCode']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactAddress\'][\'Country\'] 					= \'' . trim($_POST['Country']) . '\';' . "\r\n";

        $data .= '$wmsmetadata[\'ContactVoiceTelephone\'] 			= \'' . trim($_POST['ContactVoiceTelephone']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactFacsimileTelephone\'] 			= \'' . trim($_POST['ContactFacsimileTelephone']) . '\';' . "\r\n";
        $data .= '$wmsmetadata[\'ContactElectronicMailAddress\'] 			= \'' . trim($_POST['ContactElectronicMailAddress']) . '\';' . "\r\n";
        $data .= '?>';

        @ $write = fwrite($file, $data);
        fclose($file);
        if (!$write) {
            $error = 'Error while attempting to write to config.php. Please ensure it is writable/it exists.';
        } else {
            $success = true;
        }
    }
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$softName?> Settting</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../cssjs/common.js"></script>
<script type="text/javascript" src="../cssjs/menu.js"></script>
<script type="text/javascript" src="../cssjs/string.protoype.js"></script>
</head>
<body>

<table cellspacing="0" cellpadding="0" id="main">
<tr id="logo"><td colspan="2"><span class="logoprefix"><?=$softName."  ".$softVersion.$softEdition?></span></td></tr>
<tr id="top">
	<td id="left">Setting Progress</td>
	<td id="right">
		<?
	if(!empty($error))
		echo '<div id="progressbar"><div id="process" style="width: 60%;"></div></div>';
	else
		echo '<div id="progressbar"><div id="process" style="width: 70%;"></div></div>';
	?>
	</td>
</tr>

<tr>
	<td id="progress">
		<ul>
                <li class="first"><span><a href="../Demo/index.php">Home</a></span></li>
		<li class="first"><span>Configuration</span></li>
			<ul class="second">
				<li class="done">Database Access</li>
				<li class="done">Database Settings</li>
                <li class="done">Table Settings</li>
				<li class="done">General Settings</li>
				<li class="error">Data Import</li>
				<li>Style Settings</li>
				<li>Create Metadata</li>
			</ul>
		<li class="first"><span>Install</span></li>
			<ul class="second">
				<li class="unactive"><a href="../<?=$installName?>/install.php">Database Installation</a></li>
			</ul>
			<? CreateToolsMenu("default");?>
		</ul>
	</td>
	<td id="content">

<?php
if ($success && empty($error)) {
?>
			<p id="intro">Your database has been completely set up now and please choose one way to input the data.<br>
			Please upload the data into <font class="error">data</font> directory through FTP, if you use Remote Files Import.</p>
			<div id="errormessage" class="error"></div>
			<br />

	<div id="header">
	<ul id="primary">
		<li><a href="#" onClick="show_tbl('content1_',2,1)" id="content1_1_menu" class="current">Local Files</a>
		<ul id="secondary">
		<div id="content1_1_second" align="right">
         Local Files Import&nbsp;
		<image src="../img/help.png"  border="0" onmouseover="tooltip('Local Files Import','Description:','In most case you are allowed to upload files from local(your) computer less than 2Mb because of server limitation.');" onmouseout="exit();">
		</div>
		</ul>
		</li>
		<li><a href="#" onClick="show_tbl('content1_',2,2)" id="content1_2_menu">Remote Files</a>
		<ul id="secondary">
		<div id="content1_2_second" align="right" style="display:none">
         Remote Files Import&nbsp;
		<image src="../img/help.png"  border="0" onmouseover="tooltip('Remote Files Import','Description:','If you want to input files with more than 2Mb size, please upload the files into <font class=error>data</font> (In <?=$softName?>) in Remote Server, using FTP tools such as CuteFTP. Do not make folder in data folder, just put files there.');" onmouseout="exit();">
		</div>
		</ul>
		</li>
	</ul>
	</div>
	<div id="main">
		<div id="contents">
          <table id="content1_1" class="tableContent">
          <tr>
            <td>
                <!--local file content-->
                <?include_once '../Models/LocalFileImport.php';?>
                <!--local file content-->
			  </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table>

        <table id="content1_2"  style="display:none" class="tableContent">
          <tr>
            <td>
                <!--Remote file content-->
                <?include_once '../Models/RemoteFileImport.php';?>
                <!--Remote file content-->
			  </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table>
		</div>
	</div>
		<br><br>
<?
$database = new Database();

$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname);
$database->databaseConnect();
if ($database->databaseGetErrorMessage() == "") {
    $layersnameslist = $database->getAllLayersNames($tbname);
    $number = $database->getRowsNumber($layersnameslist);
    if($number>0){

?>
<table class="tableContent">
          <tr>
            <td>
                <h2>Style Setting</h2>
            </td>
		  </tr>
		  <tr>
            <td>
				<p>Records have been found.<br>
				You can create a Style (display range and symbology) for each layer that imported in the previous step.<br />
				</p>
			</td>
		  </tr>
		  <tr align="right">
            <td>
				<div class="begin"><a href="8.php">Style Defination</a></div><br />
			</td>
		  </tr>
</table>
<?php
}
}
}

if (!empty($error)) { // else
    // Failure
    ?>
<table class="tableError">
<tr>
<td>
			<h4>Failure</h4>
			    <p id="intro">You must correct the error below before installation can continue:<br/><br/>
                <span style="color:#000000"><?php echo $error; ?></span><br /><br /></p>
</td>
</tr>
<tr>
<td align="left">
               <input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1'" onmouseout="this.className='button'" class="button">

</td>
</tr>
</table>
<?php
}

?>
	</td>
</tr>
</table>

</body>
</html>