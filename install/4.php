<?php
/**
* 2a.php
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
* @version $Id: 2a.php,v 1.2 2007/05/10 16:40:39 leelight Exp $
* @copyright (C) 2006-2007  leelight
* @Description : Display the database list or create database.
* @contact webmaster@easywms.com
*/

require_once '../models/Installation.class.php';
require_once '../models/setting.inc';
require_once '../models/common.inc';
include_once '../models/menu.inc';


$serverhost = $_POST['ServerHost'];
$dbtype = $_POST['dbtype'];
$dbserver = $_POST['dbserver'];
$dbusername = $_POST['dbusername'];
$dbpassword = $_POST['dbpassword'];
$createdatabase = $_POST['createdatabase'];

$flagDbOperation = strtoupper($_POST['flagDbOperation']);

switchDatabase($dbtype);
$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, "");
$database->databaseConnectNoDatabase();
$error = $database->databaseGetErrorMessage();

if (empty($error) && $flagDbOperation == "DROP") {
	$dbname_Temp = $_POST['databases'];
	$database->databaseDeleteDatabase($dbname_Temp);
    $error = $database->databaseGetErrorMessage();
    if(empty($error)){
    	setSessionMessage("Database <b>".$dbname_Temp."</b> has been deleted.", SITE_MESSAGE_INFO);
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
    echo '<div id="progressbar"><div id="process" style="width: 30%;"></div></div>';
else
    echo '<div id="progressbar"><div id="process" style="width: 40%;"></div></div>';

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
				<li class="error">Database Setting</li>
				<li>Table Checking</li>
				<li>Table Setting</li>
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
    			<h2>Database Setting</h2>
				<div class="messages">Now you are ready to install the <?=SUAS_NAME?> database.</div>
				<h2>Database Selecting</h2>
				<div class="messages">Please select one database available or create one new database. </div>
					<form name="databasename" id="databasename" method="post" action="5.php" onSubmit="return chkform()">
						<table class="tableContent">
							<tr>
								<td colspan="2"></td>
							</tr>
<?php
    $db_list = $database->getDatabaseName();
    if ($db_list) {
        showdb($db_list, $database);

        ?>
							<tr>
								<td align="left">
								<input onclick="GoBack();" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
                            </td>
								<td ALIGN="right">
<?php
        if ($createdatabase) {

            ?>
								<input onclick="return chkdropDatabase();" type="submit" name="flagDbOperation" value="Drop" class="ui-button ui-state-default ui-corner-all">
<?php
        }

        ?>
								<input type="submit" name="Selectdb" value="Continue" class="ui-button ui-state-default ui-corner-all"/>
								</td>
							</tr>
                           <input type="hidden" name="bdbs" id="bdbs" value="true" />
                           <input name="ServerHost" type="hidden" id="ServerHost" value="<?=$serverhost?>" />
                           <input type="hidden" name="dbtype" id="dbtype" value="<?=$dbtype?>" />
                           <input type="hidden" name="dbserver" id="dbserver" value="<?=$dbserver?>" />
                           <input type="hidden" name="dbusername"  id="dbusername" value="<?=$dbusername?>" />
                           <input type="hidden" name="dbpassword"  id="dbpassword" value="<?=$dbpassword?>" />
                           <input type="hidden" name="createdatabase" id="createdatabase" value="<?=$createdatabase?>" />
<?php
    }

    ?>
						</table>
						</form>

						<form name="databasenamecreate" id="databasenamecreate" method="post" action="5.php" onSubmit="return chkDabaseCreateInput()">
						<table class="tableContent">
							<tr>
								<td  colspan="2"><h2>Or you can create one new Database</h2></td>
							</tr>
							<tr>
								<td width="30%"><p id="intro">Database Name:</p></td>
								<td>

<?php
    if ($createdatabase)
        print('<input name="databasei" type="text" id="databasei" size="15"  class="smallInput" onmouseover="txtfieldSelectAll(this);" />');
    else
        print('<input name="databasei" type="text" id="databasei" value="locked" size="15"  class="smallInput" disabled/></td><tr>
		  <tr><td colspan="2"><font class="error">You have no privelege to create new database. Please ask your host supporter.</font>
		  ');

    ?>
                            </td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td ALIGN="right">
<?php
    if ($createdatabase)
        echo "<input type=\"submit\" name=\"Createdb\" value=\"Create\" class=\"ui-button ui-state-default ui-corner-all\"/>";
    else
        echo "<input type=\"submit\" name=\"Createdb\" value=\"Create\" class=\"ui-button ui-state-default ui-corner-all\" disabled/>";

    ?>
                                 </td>
						   </tr>
                           <input type="hidden" name="bdbc" id="bdbc" value="true" />
                           <input name="ServerHost" type="hidden" id="ServerHost" value="<?=$serverhost?>" />
                           <input type="hidden" name="dbtype" id="dbtype" value="<?=$dbtype?>" />
                           <input type="hidden" name="dbserver"  id="dbserver" value="<?=$dbserver?>" />
                           <input type="hidden" name="dbusername"  id="dbusername" value="<?=$dbusername?>" />
                           <input type="hidden" name="dbpassword" id="dbpassword" value="<?=$dbpassword?>" />
                           <input type="hidden" name="createdatabase" id="createdatabase" value="<?=$createdatabase?>" />
						</table>
						<p>&nbsp;</p>
					</form>

<?php
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
<?php
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
function showdb($result, $database)
{
    echo "
         <TR class=\"title\">\n
         <TD width=\"30%\"><p id=\"intro\">Database ID</p></TD>\n
         <TD><p id=\"intro\">Database Name</p></TD>\n
         </TR>\n";
    $i = 0;
    while ($row = $database->getColumns($result)) {
        // Jump the buildin table in MySQL
        // while ($row = mysql_fetch_object($result)) {
        // echo $row->Database . "\n";}
        if ($row["Database"] <> "information_schema" && $row["Database"] <> "mysql") {
            if ($i % 2 == 0)
                echo "<TR class=\"odd\">";
            else echo "<TR class=\"even\">";

            echo "\n<TD>";
            echo $i;
            echo "</TD>\n";
            echo "<TD  ALIGN=left>\n";
            echo "<input type=\"radio\" name= \"databases\" class=\"button3\"  value=\"";
            echo $row["Database"] . "\">\n";
            echo $row["Database"];
            echo "</TD></TR>\n";
            $i++;
        }
    }
    // Must select one database
    echo "<script type=\"text/javascript\">\n";
    echo "function chkform()";
    echo "{\n";
    echo "if(";
    // $iradio = 0;
    for ($iradio = 0;$iradio < $i;$iradio++) {
        echo "!document.databasename.databases[" . $iradio . "].checked ";
        if ($iradio != $i - 1) {
            echo "&&";
        }
        // ++$iradio;
    }
    echo ")";
    echo "{\n";
    // echo "alert(\"Please select a database!\");\n";
    echo "showErrorMessage(\"Please select a database!\");\n";
    echo "return false;\n";
    echo "}\n";
    echo "}\n";

    echo "function chkdropDatabase()";
    echo "{";
    echo "if(";
    // $iradio = 0;
    for ($iradio = 0;$iradio < $i;$iradio++) {
        echo "!document.databasename.databases[" . $iradio . "].checked ";
        if ($iradio != $i - 1) {
            echo "&&";
        }
        // ++$iradio;
    }
    echo ")";
    echo "{";
    echo "showErrorMessage(\"Please select a database!\");";
    echo "return false;";
    echo "}";

    echo "if(";
    for ($iradio = 0;$iradio < $i;$iradio++) {
        echo "document.databasename.databases[" . $iradio . "].checked";
        if ($iradio != $i - 1) {
            echo "||";
        }
    }
    echo ")";
    echo "{";
    echo "if(confirm(\"Are you sure to delete this database?";
    // echo "document.databasenamedelete.databased.checked.value";
    echo "\")){
    	document.databasename.flagDbOperation.value=\"Drop\";
		document.databasename.action =\"4.php\";
    	document.databasename.submit();
	}";
    echo "else{return false;}";
    echo "}";

    echo "}";
    echo "</script>\n";
}

?>
