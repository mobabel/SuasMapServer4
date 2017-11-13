<?php
/**
* 2b.php
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
* @version $Id: 2b.php,v 1.2 2007/05/10 16:40:39 leelight Exp $
* @copyright (C) 2006-2008  leelight
* @Description : create the database .
* @contact webmaster@easywms.com
*/

require_once '../models/Installation.class.php';
require_once '../models/setting.inc';
require_once '../models/common.inc';
require_once '../models/tables.php';
include_once '../models/menu.inc';

$serverhost = $_POST['ServerHost'];
$dbtype = $_POST['dbtype'];
$dbserver = $_POST['dbserver'];
$dbusername = $_POST['dbusername'];
$dbpassword = $_POST['dbpassword'];
$dbname = $_POST['dbname'];

switchDatabase($dbtype);
$database = new Database();

$flagDbOperation = strtoupper($_POST['flagDbOperation']);

$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname);
$database->databaseConnect();
$error = $database->databaseGetErrorMessage();

if (empty($error) && $flagDbOperation == "DELETE") {
	$prefix = $_POST['prefix'];
	if(!$database->dropTablesForSUAS($drop_tables_sql, $prefix)){
    	$error = $database->databaseGetErrorMessage();
    }
    else{
    	setSessionMessage("All tables with name prefix <b>".$prefix."</b> has been deleted.", SITE_MESSAGE_INFO);
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
    echo '<div id="progressbar"><div id="process" style="width: 60%;"></div></div>';
else
    echo '<div id="progressbar"><div id="process" style="width: 70%;"></div></div>';

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
				<li class="error">Table Setting</li>
			</ul>
		</ul>
	</td>
	<td id="content">
		<div id="<?=SITE_MESSAGE_ERROR?>" class="messages error"></div>
		<div id="<?=SITE_MESSAGE_INFO?>" class="messages info"></div>
<?
    displayMessage();
?>
<?
if (empty($error)) {

    ?>
    		<h2>Table Settings</h2>
			<div class="messages">Now you are ready to install the <?=SUAS_NAME?> tables. 
			Please select one name available or create one new.</div>
					<form name="tablename" id="tablename" method="post" action="7.php" onSubmit="return chkform()">
						<table class="tableContent">
							<tr>
								<td colspan="2"><h2>Select one Table Name(Prefix) available</h2></td>
							</tr>
<?
        $result = $database->getTableName($dbname);
        $hasrecord = showtab($database, $result, $dbname);
?>
							<tr>
								<td align="left"><input onclick="GoBack();" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
                                </td>
								<td ALIGN="right">
<?
if($hasrecord){
?>
								<input onclick="return chkdropTable();" type="submit" name="flagDbOperation" value="Delete" class="ui-button ui-state-default ui-corner-all"/>
								<input type="submit" name="Select" value="Continue" class="ui-button ui-state-default ui-corner-all"/>
<?
}
?>
								</td>
							</tr>
                           <input name="btbs" type="hidden" id="btbs" value="true" />
                           <input name="ServerHost" type="hidden" id="ServerHost" value="<?=$serverhost?>" />
                           <input name="dbtype" type="hidden" id="dbtype" value="<?=$dbtype?>" />
                           <input name="dbserver" type="hidden" id="dbserver" value="<?=$dbserver?>" />
                           <input name="dbusername" type="hidden" id="dbusername" value="<?=$dbusername?>" />
                           <input name="dbpassword" type="hidden" id="dbpassword" value="<?=$dbpassword?>" />
                           <input name="dbname" type="hidden" id="dbname" value="<?=$dbname?>" />
						</table>
						</form>
<?php
    //}

    ?>
						<form name="tablenamecreate" id="tablenamecreate" method="post" action="7.php" onSubmit="return chkTableCreateInput()">
						<table class="tableContent">
							<tr>
								<td colspan="2"><h2>Or you can create new one</h2></td>
							</tr>
							<tr>
								<td ALIGN=CENTER width="30%"><p id="intro">Table Name(Prefix):</p></td>
                                <td><input name="prefix" type="text" id="prefix" size="15" value=""  class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>

							</tr>
							<tr>
								    <td align="left">
		 					<input onclick="GoBack();" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
         					</td>
								<td ALIGN="right"><input type="submit" name="Create" value="Create" class="ui-button ui-state-default ui-corner-all"/></td>
							</tr>
                           <input name="btbc" type="hidden" id="btbc" value="true" />
                           <input name="ServerHost" type="hidden" id="ServerHost" value="<?=$serverhost?>" />
                           <input name="dbtype" type="hidden" id="dbtype" value="<?=$dbtype?>" />
                           <input name="dbserver" type="hidden" id="dbserver" value="<?=$dbserver?>" />
                           <input name="dbusername" type="hidden" id="dbusername" value="<?=$dbusername?>" />
                           <input name="dbpassword" type="hidden" id="dbpassword" value="<?=$dbpassword?>" />
                           <input name="dbname" type="hidden" id="dbname" value="<?=$dbname?>" />
						</table>
						<p>&nbsp;</p>
					</form>
<?
} else {

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
               <input onclick="GoBack();" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">

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
<?php
function showtab($database, $tablesobjects, $dbname)
{
    echo "
    <TR class=\"title\">
    <TD width=\"30%\">ID</TD>
    <TD>Table Name(Prefix)</TD>
    </TR>\n";
    $i = 0;

    while ($row = $database->getColumns($tablesobjects)) {
        if (substr_count($row["Tables_in_" . $dbname], mapTableFeaturegeometry) > 0) {
            // check if has the standard fields inside
            // $table = $database->getColumnsFromTable($row["Tables_in_" . $dbname]);
            if ($database->databaseGetErrorMessage() == "") {
                $blnSameTable = $database->TableHasSameFields($row["Tables_in_" . $dbname]);
                $detailInfo = $database->getTableDetailInformation($row["Tables_in_" . $dbname]);
                //check the suas version(site_version) in variable table
                $database->databaseSetPrefix(getPrefixOfTablename($row["Tables_in_" . $dbname]));
				$version = $database->getSUASVersion();
				if(!$version)
					$version = "unkonwn";
				$database->databaseSetPrefix();
            }

            if ($i % 2 == 0)
                echo "<TR class=\"odd\">";
            else echo "<TR class=\"even\">";
            echo "<TD>$i</TD>\n";
            echo "<TD>\n";
            echo "<input type=\"radio\" name= \"prefix\"  class=\"button3\" value=\"";
            echo getPrefixOfTablename($row["Tables_in_" . $dbname]) ."\"";
            if (!$blnSameTable && $version != SITE_VERSION) {
                echo " disabled ";
            }
            echo ">";
            // echo getPrefixOfTablename($row["Tables_in_".$dbname]);
            echo "<dfn title=\"$detailInfo[0] rows, $detailInfo[1] Kb, created on $detailInfo[2], updated on $detailInfo[3]\">";
            if(getPrefixOfTablename($row["Tables_in_" . $dbname]) ==""){
				echo "(no prefix name)";
			}else{
				echo getPrefixOfTablename($row["Tables_in_" . $dbname]) ;
			}
			echo " (version: ".$version. ")</dfn>";
            echo "</TD>\n</TR>\n";
            $i++;
        }
    }

    if($i>0){
	    // Must select one Table
	    echo "<script type=\"text/javascript\">\n";
	    echo "function chkform()";
	    echo "{\n";
	    echo "if(";
	    // $iradio = 0;
	    for ($iradio = 0;$iradio < $i;$iradio++) {
	        if ($i == 1) {
	            echo "!document.tablename.prefix.checked ";
	        } else
	            echo "!document.tablename.prefix[" . $iradio . "].checked ";
	        if ($iradio != $i - 1) {
	            echo "&&";
	        }
	        // ++$iradio;
	    }
	    echo ")";
	    echo "{\n";
	    echo "showErrorMessage(\"Please select a Table(Prefix) !\");\n";
	    echo "return false;\n";
	    echo "}\n";
	    echo "}\n";

	    echo "function chkdropTable()";
	    echo "{\n";
	    echo "if(";
	    // $iradio = 0;
	    for ($iradio = 0;$iradio < $i;$iradio++) {
	        if ($i == 1) {
	            echo "!document.tablename.prefix.checked ";
	        } else
	            echo "!document.tablename.prefix[" . $iradio . "].checked ";
	        if ($iradio != $i - 1) {
	            echo "&&";
	        }
	        // ++$iradio;
	    }
	    echo ")";
	    echo "{\n";
	    echo "showErrorMessage(\"Please select a Table(Prefix) !\");\n";
	    echo "return false;\n";
	    echo "}\n";

	    echo "if(";
	    for ($iradio = 0;$iradio < $i;$iradio++) {
	        echo "document.tablename.prefix[" . $iradio . "].checked";
	        if ($iradio != $i - 1) {
	            echo "||";
	        }
	    }
	    echo ")";
	    echo "{";
	    echo "if(confirm(\"Are you sure to delete all tables with this name prefix";
	    // echo "document.tablename.prefix.checked.value";
	    echo "?\")){
			document.tablename.flagDbOperation.value=\"Delete\";
			document.tablename.action =\"6.php\";
	    	document.tablename.submit();
		}";
	    echo "else{return false;}";
	    echo "}";

	    echo "}\n";
	    echo "</script>\n";
    }

    // chkTableCreateInput()
    echo "<script type=\"text/javascript\">\n";
    echo "function chkTableCreateInput()";
    echo "{\n";
    print('
    if(document.tablenamecreate.prefix.value.trim() == ""){
        showErrorMessage("Please input the table name prefix! Can not be empty.");
        document.tablenamecreate.prefix.value = "";
        return false;
    }
	');

	if($i>0){
	    echo "if(";
	    // $iradio = 0;
	    for ($iradio = 0;$iradio < $i;$iradio++) {
	        echo "document.tablename.prefix[" . $iradio . "].value == document.tablenamecreate.prefix.value.toLowerCase().trim()";
	        if ($iradio != $i - 1) {
	            echo "||";
	        }
	        // ++$iradio;
	    }
	    echo ")";
	    echo "{\n";
	    echo "showErrorMessage(\"Table Prefix \"+document.tablenamecreate.prefix.value.toLowerCase().trim()+\" alreay exist,please input a new prefix!\");\n";
	    echo "return false;\n";
	    echo "}\n";
    }
    echo "}\n";
    echo "</script>\n";

    if($i>0)
    	return true;
    else{
        echo "
    <TR >
    <TD colspan=\"2\" align=\"center\">No tables available</TD>
    </TR>\n";
		return false;
	}
}

?>
