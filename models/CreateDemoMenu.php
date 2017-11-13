<?php
/**
 *
 * @version $Id$
 * @copyright 2007
 */

function CreateDemoMenu($type)
{
    print('<ul>');
    print('		<li class="first"><span><a href="index.php">Home</a></span></li> ');
    //========================WMS Demo======================
    print('			<li class="first"><span>WMS Demo</span></li>');
    print('				<ul class="second">');

    if ($type == "wms_GetCapabilities")
        print('					<li class="active">GetCapabilities</li>');
    else
        print('					<li class="unactive"><a href="wms_GetCapabilities.php">GetCapabilities</a></li>');

    if ($type == "GetMap")
        print('					<li class="active">GetMap</li>');
    else
        print('					<li class="unactive"><a href="wms_GetMap.php">GetMap</a></li>');

    if ($type == "GetFeatureInfo")
        print('		            <li class="active">GetFeatureInfo</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetFeatureInfo.php">GetFeatureInfo</a></li>');

    if ($type == "DescribeLayer")
        print('		            <li class="active">DescribeLayer</li>');
    else
        print('		            <li class="unactive"><a href="wms_DescribeLayer.php">DescribeLayer</a></li>');

    if ($type == "GetLegendGraphic")
        print('		            <li class="active">GetLegendGraphic</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetLegendGraphic.php">GetLegendGraphic</a></li>');

    if ($type == "GetStyles")
        print('		            <li class="active">GetStyles</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetStyles.php">GetStyles</a></li>');

    print('				</ul>');

    //========================WMS Extension======================
    print('		    <li class="first"><span>WMS Extension</span></li>');
    print('		        <ul class="second">');

   // if ($type == "SUAS Map Client")
        //print('		            <li class="active">SUAS Map Client</li>');
    //else
        print('		            <li class="unactive"><a href="../Plugin/suasclient/index.html" target="_blank">SUAS Map Client</a></li>');

    if ($type == "Map Viewers")
        print('		            <li class="active">Map Viewers</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetMapViewers.php">Map Viewers</a></li>');

    if ($type == "2.5D Navigation")
        print('		            <li class="active">2.5D Navigation</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetMap25D.php">2.5D Navigation</a></li>');

    if ($type == "3D Navigation")
        print('		            <li class="active">3D Navigation</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetMap3D.php">3D Navigation</a></li>');

    if ($type == "GetThematicMap")
        print('		            <li class="active">GetThematicMap</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetThematicMap.php">GetThematicMap</a></li>');

    print('				</ul>');

    //========================WFS Demo======================
    print('		    <li class="first"><span>WFS Demo</span></li>');
    print('		        <ul class="second">');

    if ($type == "wfs_GetCapabilities")
        print('		            <li class="active">GetCapabilities</li>');
    else
        print('		            <li class="unactive"><a href="wfs_GetCapabilities.php">GetCapabilities</a></li>');

    if ($type == "DescribeFeatureType")
        print('		            <li class="active">DescribeFeatureType</li>');
    else
        print('		            <li class="unactive"><a href="wfs_DescribeFeatureType.php">DescribeFeatureType</a></li>');

    if ($type == "GetFeature")
        print('		            <li class="active">GetFeature</li>');
    else
        print('		            <li class="unactive"><a href="wfs_GetFeature.php">GetFeature</a></li>');

    if ($type == "GetGmlObject")
        print('		            <li class="active">GetGmlObject</li>');
    else
        print('		            <li class="unactive"><a href="wfs_GetGmlObject.php">GetGmlObject</a></li>');

    if ($type == "Transaction")
        print('		            <li class="active">Transaction</li>');
    else
        print('		            <li class="unactive"><a href="wfs_Transaction.php">Transaction</a></li>');

    print('				</ul>');
    print('</ul>');
}

function CreateToolsMenu($type)
{

    print('			<li class="first"><span>Tools</span></li>');
    print('				<ul class="second">');

    if ($type == "cacheclear")
        print('					<li class="active">Cahce Clear</li>');
    else
        print('					<li class="unactive"><a href="cacheclear.php" title="Clear Cache">Cahce Clear</a></li>');

    if ($type == "sldbackup")
        print('					<li class="active">SLD Style File Backup</li>');
    else
        print('					<li class="unactive"><a href="sldbackup.php" title="Backup your SLD style file">SLD Style File Backup</a></li>');

    if ($type == "databackup")
        print('		            <li class="active">Data Backup</li>');
    else
        print('		            <li class="unactive"><a href="databackup.php" title="Backup the spatial data or metadata">Data Backup</a></li>');

    if ($type == "datadelete")
        print('		            <li class="active">Data Delete</li>');
    else
        print('		            <li class="unactive"><a href="datadelete.php"  title="Delete the spatial data">Data Delete</a></li>');

    if ($type == "checklog")
        print('		            <li class="active">Check Log</li>');
    else
        print('		            <li class="unactive"><a href="checklog.php"  title="Check delete, or download the log file">Check Log</a></li>');

    if ($type == "checkupdate")
        print('		            <li class="active">Check Update</li>');
    else
        print('		            <li class="unactive"><a href="checkupdate.php" title="Check the new verion of SUAS">Check Update</a></li>');

    if ($type == "help")
        print('		            <li class="active">Help</li>');
    else
        print('		            <li class="unactive"><a href="http://suas.easywms.com" target="_blank" title="Help&Handbook">Help</a></li>');

    if ($type == "bugreport")
        print('		            <li class="active">Report Bugs</li>');
    else
        print('		            <li class="unactive"><a href="http://www.easywms.com/easywms/?q=en/node/158" target="_blank" title="Help&Handbook">Report Bugs</a></li>');

    print('				</ul>');

}

/*function get_login_block($destination, $allow_register){
	print '	<div class="block block-user" id="block-login">
	  			<div class="block-label">User login</div>
	  			<div class="block-content">
				  	<form action="../user/login.php"  accept-charset="UTF-8" method="post" id="user-login-form">
		 			Username:</br>
		 			<input type="text" maxlength="60" name="username" id="edit-username"  size="15" value="" class="smallInput required" onmouseover="txtfieldSelectAll(this);"/></br>
		 			Password:</br>
		 			<input type="password" name="password" id="edit-password"  maxlength="60"  size="15"  class="smallInput required" onmouseover="txtfieldSelectAll(this);"/></br>
					<input type="submit" name="login" id="edit-submit" value="Log in"  onmouseover="this.className=\'login button1\'" onmouseout="this.className=\'login button\'" class="login button" />
					</br>';
	if($allow_register)
		print	'<a href="/user/register" title="Create a new user account.">Create new account</a>';

	print	'<!--a href="/user/password" title="Request new password via e-mail.">Request new password</a-->
					<input type="hidden" name="destination" id="edit-destination" value="'.$destination.'"/>
					</form>
				</div>
			</div>';
}*/


?>