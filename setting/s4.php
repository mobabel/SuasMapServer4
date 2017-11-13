<?php
include '../config.php';
include_once '../global.php';
require_once '../models/menu.inc';
require_once '../models/setting.inc';
require_once '../models/Installation.class.php';

$tbfselect = $_POST['btbfs'];
$dbserver=$_POST['dbserver'];
$dbusername=$_POST['dbusername'];
$dbpassword=$_POST['dbpassword'];
$dbname=$_POST['dbname'];

$tbname=$_POST['tbname'];
if(empty($tbname)){
	$tableprefix = $_POST['tables'];
	$tbname = $tableprefix.mapTableFeaturegeometry;
	$tbmetaname = $tableprefix . mapTableFeatureclass;
}


$setmetadata = true;
$error = "";
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$softName?> Settting</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../cssjs/setup.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../cssjs/common.js"></script>
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
		echo '<div id="progressbar"><div id="process" style="width: 45%;"></div></div>';
	else
		echo '<div id="progressbar"><div id="process" style="width: 60%;"></div></div>';
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
				<li class="error">General Settings</li>
				<li>Data Import</li>
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
		if (isset($setmetadata))
		{
			// Success - tables created
			$path = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
            $curpath = basename(dirname($_SERVER['PHP_SELF']));
            $path = str_replace($curpath,'',$path);
		?>
			<p id="intro">One database table with Standard Field has been selected.</p>
			<div id="errormessage" class="error"></div>
			<br />

				<h2>Metadata Settings</h2>
				<p>&#8226; Please fill out the information below, which will occur in the XML document of GetCapabilities Request.<br>
				   &#8226; You can edit these metadata in <font color="red">config.php</font> file later.<br>
				   &#8226; If you are unsure of your Server Host, you should contact your web hosting company.</p>
				<form name="settings" id="settings" method="post" action="s5.php" onSubmit="return chkMetadataformInput();">
				    <table class="tableBlock">
                    	<tr>
								<td colspan="2">
								<p>Please end Server Host with "/".</p>
					            </td>
						</tr>
                   		<tr>
                    		<td>Server Host : <font class="error">*</font> <image src="../img/help.png"  border="0" onmouseover="tooltip('Server Host','Description:','It is the absolute URL path where you put <?=$softName?> in. Please end the path with slash. If you are not clear, please use the default value.');" onmouseout="exit();"></td>
                    		<td><input name="ServerHost" type="text" id="ServerHost" size="35" value="<?=$wmsmetadata['ServerHost']?>" class="smallInput"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                   		<tr>
                    		<td>Enable Stretch Map :<image src="../img/help.png"  border="0" onmouseover="tooltip('Enable Stretch Map','Description:','In the case that a GetMap request is made where the aspect ratio of the selected BBOX and the ratio of the WIDTH/HEIGHT parameters is different, the returned map will be stretched if Stretch Map is enabled. Otherwise the returned map will be in the center of the image with nochanging WIDTH/HEIGHT.');" onmouseout="exit();"></td>
                    		<td>
<?                      if($enablestretchmap ==1){
							echo '<input type="radio" name= "enablestretchmap"  class="button3" value="1" CHECKED>yes';
                    		echo '<input type="radio" name= "enablestretchmap"  class="button3" value="0">no';
                    	}
                    	else{
                            echo '<input type="radio" name= "enablestretchmap"  class="button3" value="1">yes';
                    		echo '<input type="radio" name= "enablestretchmap"  class="button3" value="0" CHECKED>no';
						}
?>
							</td>
                   		</tr>
                   		<tr>
                    		<td>Enable Cache :<image src="../img/help.png"  border="0" onmouseover="tooltip('Enable Cache','Description:','Enable Cache will speed up the server request, it will store the temperary files of outputted map, XML or SQL query in folder cache. Please make sure that the cache folder is writable.');" onmouseout="exit();"></td>
                    		<td>
<?                      if($enablecache ==1){
							if(!isCacheDirectoryWritabale()){
								echo '<input type="radio" name= "enablecache"  class="button3" value="1" disabled>yes';
								echo '<input type="radio" name= "enablecache"  class="button3" value="0" CHECKED>no';
							}
							else{
								echo '<input type="radio" name= "enablecache"  class="button3" value="1" CHECKED>yes';
                    			echo '<input type="radio" name= "enablecache"  class="button3" value="0">no';
                    			}
                    	}
                    	else{
                    		if(!isCacheDirectoryWritabale()){
	                            echo '<input type="radio" name= "enablecache"  class="button3" value="1" disabled>yes';
	                    		echo '<input type="radio" name= "enablecache"  class="button3" value="0" CHECKED>no';
                    		}else{
								echo '<input type="radio" name= "enablecache"  class="button3" value="1">yes';
	                    		echo '<input type="radio" name= "enablecache"  class="button3" value="0" CHECKED>no';
							}
						}
?>
							</td>
                   		</tr>
                   		<tr >
                    		<td></td>
                    		<td><input onclick="resetFormOfServerInfo();" name="button" value="Reset" onmouseover="this.className='button1 resetInput'" onmouseout="this.className='button resetInput'" class="button resetInput"></td>
                   		</tr>
                   		<tr  class="even">
                    		<td>Server Title: <font class="error">*</font></td>
                    		<td><input name="ServerTitle" type="text" id="ServerTitle" size="35" value="<?=$wmsmetadata['ServerTitle']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);" onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                   		<tr  class="odd">
                    		<td>Server Abstract: <font class="error">*</font></td>
                    		<td><input name="ServerAbstract" type="text" id="ServerAbstract" size="35" value="<?=$wmsmetadata['ServerAbstract']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                   		<tr  class="even">
                    		<td>Layer Title: <font class="error">*</font></td>
                    		<td><input name="LayerTitle" type="text" id="LayerTitle" size="35" value="<?=$wmsmetadata['LayerTitle']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                   		<tr  class="odd">
                    		<td>Keyword1:</td>
                    		<td><input name="Keyword1" type="text" id="Keyword1" size="35" value="<?=$wmsmetadata['Keyword1']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                   		<tr  class="even">
                    		<td>Keyword2:</td>
                    		<td><input name="Keyword2" type="text" id="Keyword2" size="35" value="<?=$wmsmetadata['Keyword2']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="odd">
                    		<td>ContactPerson: <font class="error">*</font></td>
                    		<td><input name="ContactPerson" type="text" id="ContactPerson" size="35" value="<?=$wmsmetadata['ContactPerson']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="even">
                    		<td>ContactOrganization:</td>
                    		<td><input name="ContactOrganization" type="text" id="ContactOrganization" size="35" value="<?=$wmsmetadata['ContactOrganization']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>

                    	<tr  class="odd">
                    		<td>ContactPosition:</td>
                    		<td><input name="ContactPosition" type="text" id="ContactPosition" size="35" value="<?=$wmsmetadata['ContactPosition']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="even">
                    		<td>ContactAddress:</td>
                    		<td><input name="ContactAddress" type="text" id="ContactAddress" size="35" value="<?=$wmsmetadata['ContactAddress']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="odd">
                    		<td>AddressType:</td>
                    		<td><input name="AddressType" type="text" id="AddressType" size="35" value="<?=$wmsmetadata['ContactAddress']['AddressType']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="even">
                    		<td>Address:</td>
                    		<td><input name="Address" type="text" id="Address" size="35" value="<?=$wmsmetadata['ContactAddress']['Address']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="odd">
                    		<td>City:</td>
                    		<td><input name="City" type="text" id="City" size="35" value="<?=$wmsmetadata['ContactAddress']['City']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="even">
                    		<td>StateOrProvince:</td>
                    		<td><input name="StateOrProvince" type="text" id="StateOrProvince" size="35" value="<?=$wmsmetadata['ContactAddress']['StateOrProvince']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="odd">
                    		<td>PostCode:</td>
                    		<td><input name="PostCode" type="text" id="PostCode" size="35" value="<?=$wmsmetadata['ContactAddress']['PostCode']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="even">
                    		<td>Country:</td>
                    		<td><input name="Country" type="text" id="Country" size="35" value="<?=$wmsmetadata['ContactAddress']['Country']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="odd">
                    		<td>ContactVoiceTelephone:</td>
                    		<td><input name="ContactVoiceTelephone" type="text" id="ContactVoiceTelephone" size="35" value="<?=$wmsmetadata['ContactVoiceTelephone']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="even">
                    		<td>ContactFacsimileTelephone:</td>
                    		<td><input name="ContactFacsimileTelephone" type="text" id="ContactFacsimileTelephone" size="35" value="<?=$wmsmetadata['ContactFacsimileTelephone']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                    	<tr  class="odd">
                    		<td>ContactElectronicMailAddress:</td>
                    		<td><input name="ContactElectronicMailAddress" type="text" id="ContactElectronicMailAddress" size="35" value="<?=$wmsmetadata['ContactElectronicMailAddress']?>" class="smallInput" onmouseover="txtfieldSelectAll(this);"  onclick="txtfieldExtendSize(this);" onblur="txtfieldShortenSize(this);" /></td>
                   		</tr>
                   		<tr >
                    		<td><input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1 backInput'" onmouseout="this.className='button backInput'" class="button backInput">
                            </td>
                    		<td align="right"><input type="submit" name="Submit" value="Continue" onmouseover="this.className='button1 continueInput'" onmouseout="this.className='button continueInput'" class="button continueInput"/></td>
                   		</tr>
                   		   <div id=bdbs style=visibility:hidden>
                           <input name="btbfs" type="text" id="btbfs" value="true" />
                           <input name="dbserver" type="text" id="dbserver" value="<?=$dbserver?>" />
                           <input name="dbusername" type="text" id="dbusername" value="<?=$dbusername?>" />
                           <input name="dbpassword" type="text" id="dbpassword" value="<?=$dbpassword?>" />
                           <input name="dbname" type="text" id="dbname" value="<?=$dbname?>" />
                           <input name="tbname" type="text" id="tbname" value="<?=$tbname?>" />
                           <input name="tbmetaname" type="text" id="tbmetaname" value="<?=$tbmetaname?>" />
                           <input name="tableprefix" type="text" id="tableprefix" value="<?=$tableprefix?>" />
                           </div>
                    	</table>

				</form>
		<?php
		}
		if ($error!="")//else
		{
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
               <input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1 backInput'" onmouseout="this.className='button backInput'" class="button backInput">

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