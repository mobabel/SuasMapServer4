<?php
/**
 * s2aempty.php
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
 * @Description: Select table or delete table.
 * @contact webmaster@easywms.com
 */

require_once '../config.php';
require_once '../global.php';
require_once '../models/menu.inc';
require_once '../models/setting.inc';
require_once '../models/Installation.class.php';

$database = new Database();
$error=="";

$tbselect = $_POST['btbs'];
$dbserver = $_POST['dbserver'];
$dbusername = $_POST['dbusername'];
$dbpassword = $_POST['dbpassword'];
$dbname = $_POST['dbname'];

if ($tbselect == "true") {
    $emptytbname = $_POST['tables'];
    $btbselect = true;
    $database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname);
    $database->databaseConnect();

    $database->emptyTableForSUAS($emptytbname);
    $error = $database->databaseGetErrorMessage();
}
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
		echo '<div id="progressbar"><div id="process" style="width: 10%;"></div></div>';
	else
		echo '<div id="progressbar"><div id="process" style="width: 25%;"></div></div>';
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
                <li class="error">Table Settings</li>
				<li>General Settings</li>
				<li>Data Import</li>
				<li><a href="8.php">Style Settings</a></li>
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
if ($database->databaseConnection && $error == "") {
    $result0 = $database->getTableName($dbname);
    if (!$database->getRows($result0)) {
        $error = 'There are no Table available in ' . $dbname . ", please select another Database";

    }
    if ($error == null OR $error == "") {

        ?>
				<p id="intro">Now you are ready to set the <?=$softName?> database table.</p>
				<div id="errormessage" class="error"></div>
				<br />

					<h2>Table Settings</h2>
					<p>Please select one table available or delete one useless table.<br>
					<font class="error">Table with Prefix <?=$emptytbname?> has been emptied.</font>
					</p>

<?php
        $result1 = $database->getTableName($dbname);
        if ($row1 = $database->getRows($result1)) {

            ?>
					<form name="tablenameselect" id="tablenameselect" method="post" action="s4.php">

						<table class="tableBlock">
							<tr>
								<td colspan="2"><h3>Select one Table available</h3></td>
							</tr>
							<tr>
								<td colspan="2">TCurrently using Table is <font class="error"><?=$tableprefix?></font> (Prefix)</td>
							</tr>
<?php
            $result = $database->getTableName($dbname);
            showtab($database, $result, $dbname, $tbname);

            ?>
							<tr>
								<td><input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1 backInput'" onmouseout="this.className='button backInput'" class="button backInput">
</td>
								<td ALIGN="right">
								<input onclick="submitEmptyTable();" name="button" value="Empty" onmouseover="this.className='button1 recycleInput'" onmouseout="this.className='button recycleInput'" class="button recycleInput">
								<input type="submit" name="Continue" value="Continue" onmouseover="this.className='button1 continueInput'" onmouseout="this.className='button continueInput'" class="button continueInput"/>
								</td>
							</tr>
                           <div id="bdbs" style="visibility:hidden">
                           <input name="btbs" type="text" id="btbs" value="true" />
                           <input name="dbserver" type="text" id="dbserver" value="<?=$dbserver?>" />
                           <input name="dbusername" type="text" id="dbusername" value="<?=$dbusername?>" />
                           <input name="dbpassword" type="text" id="dbpassword" value="<?=$dbpassword?>" />
                           <input name="dbname" type="text" id="dbname" value="<?=$dbname?>" />
                           </div>
						</table>
						</form>

						<form name="tablenamedelete" id="tablenamedelete" method="post" action="s3b.php" onSubmit="return chkform()">
						<table class="tableBlock">
							<tr>
								<td colspan="2"><h3>Delete an useless Table</h3></td>
							</tr>
							<tr>
								<td colspan="2">Currently using Table is <font class="error"><?=$tableprefix?></font> (Prefix)</td>
							</tr>
<?php
            $result = $database->getTableName($dbname);
            showtabd($result, $dbname, $tbname);

            ?>
							<tr>
								<td>&nbsp;</td>
								<td ALIGN="right">
								<input type="submit" name="Delete" value="Delete" onmouseover="this.className='button1 deleteInput'" onmouseout="this.className='button deleteInput'" class="button deleteInput"/>
								</td>
							</tr>
                           <div id="bdbs" style="visibility:hidden">
                           <input name="btbd" type="text" id="btbd" value="true" />
                           <input name="dbserver" type="text" id="dbserver" value="<?=$dbserver?>" />
                           <input name="dbusername" type="text" id="dbusername" value="<?=$dbusername?>" />
                           <input name="dbpassword" type="text" id="dbpassword" value="<?=$dbpassword?>" />
                           <input name="dbname" type="text" id="dbname" value="<?=$dbname?>" />
                           </div>
						</table>
						</form>
<?php
}
    }

$error = $database->databaseGetErrorMessage();
}

if ($error != "") {

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
<?php
function showtab($database, $tablesobjects, $dbname, $tbname)
{
    //$database = new Database();
    echo "
	<TR class=\"title\">
	<TD><p id=\"intro\">Table ID</p></TD>
	<TD><p id=\"intro\">Table Name(Prefix)</p></TD>
	</TR>";
    $i = 0;
    while ($row = $database->getColumns($tablesobjects)) {
        if (substr_count($row["Tables_in_" . $dbname], mapTableFeaturegeometry) > 0) {
            // check if has the standard fields inside
            $blnSameTable = $database->TableHasSameFields($row["Tables_in_" . $dbname]);
			$detailInfo = $database->getTableDetailInformation($row["Tables_in_" . $dbname]);

            $i = $i + 1;
            if ($row["Tables_in_" . $dbname] != $tbname) {
                if ($i % 2 == 0)
                    echo "<TR class=\"odd\">";
                else echo "<TR class=\"even\">";
            } else {
                echo "<TR class=\"current\">";
            }
            echo "<TD>";
            echo "$i</TD>";
            echo "<TD>";
            echo "<input type=\"radio\" name= \"tables\"  class=\"button3\" value=\"";
            echo getPrefixOfTablename($row["Tables_in_" . $dbname]) . "\"";
            if ($row["Tables_in_" . $dbname] == $tbname && $blnSameTable) {
                echo " CHECKED ";
            }
            if (!$blnSameTable) {
                echo " disabled ";
            }
            echo ">";
            //echo getPrefixOfTablename($row["Tables_in_" . $dbname]);
            echo "<dfn title=\"$detailInfo[0] rows, $detailInfo[1] Kb, created on $detailInfo[2], updated on $detailInfo[3]\">".getPrefixOfTablename($row["Tables_in_" . $dbname])."</dfn>";
            echo "</TD></TR>";
        }
    }
}

function showtabd($result, $DATABASENAME, $tbname)
{
    $database = new Database();
    echo "
<TR class=\"title\">
<TD><p id=\"intro\">Table ID</p></TD>
<TD><p id=\"intro\">Table Name(Prefix)</p></TD>
</TR>";
    $i = 0;
    while ($row=$database->getColumns($result)) {
    	if(substr_count($row["Tables_in_".$DATABASENAME], mapTableFeaturegeometry)>0){
        	$i = $i + 1;
        	if ($row["Tables_in_" . $DATABASENAME] != $tbname) {
	     		if($i%2==0)
	     			echo "<TR class=\"odd\">";
	     		else echo "<TR class=\"even\">";
	     	}else{
		 		echo "<TR class=\"current\">";
		 	}
	     	echo "<TD>";
        	echo "$i</TD>";
	        echo "<TD  ALIGN=left>";
	        echo "<input type=\"radio\" name= \"tabled\"  class=\"button3\" value=\"";
	        echo getPrefixOfTablename($row["Tables_in_".$DATABASENAME]) . "\"";
	        echo ">";
	        echo getPrefixOfTablename($row["Tables_in_".$DATABASENAME]);
	        echo "</TD></TR>";
        }
    }
    // Must select one Table
    echo "<script type=\"text/javascript\">";
    echo "function chkform()";
    echo "{";
    echo "if(";
    // $iradio = 0;
    for ($iradio = 0;$iradio < $i;$iradio++) {
        echo "!document.tablenamedelete.tabled[" . $iradio . "].checked ";
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
        echo "document.tablenamedelete.tabled[" . $iradio . "].checked";
        if ($iradio != $i - 1) {
            echo "||";
        }
    }
    echo ")";
    echo "{";
    echo "if(confirm(\"Are you sure to delete this Table ";
    // echo "document.tablenamedelete.tabled.checked.value";
    echo "?\")){}";
    echo "else{return false;}";
    echo "}";

    echo "}";
    echo "</script>";
}

?>
