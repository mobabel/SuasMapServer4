<?php
/**
 * s1.php
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
 * @version $Id: s1.php,v 1.2 2007/05/10 16:41:46 leelight Exp $
 * @copyright (C) 2006-2007  leelight
 * @Description: Select Database or delete database.
 * @contact webmaster@easywms.com
 */
require_once '../config.php';
require_once '../global.php';
require_once '../models/menu.inc';
require_once '../models/setting.inc';

$database = new Database();
$error=="";

// Check all fields filled in
if (empty($_POST['server']) || empty($_POST['username']) || empty($_POST['password']))
{
	$error = 'You must fill out all the fields';
}
else
{
	if ($error=="")
	{
		// Connect to the MySQL server
		$database->databaseConfig($_POST['server'],$_POST['username'],$_POST['password'],"");
        $database->databaseConnectNoDatabase();
        $error = $database->databaseGetErrorMessage();
	}

}
$dbserver=$_POST['server'];
$dbusername=$_POST['username'];
$dbpassword=$_POST['password'];
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
		echo '<div id="progressbar"><div id="process" style="width: 0%;"></div></div>';
	else
		echo '<div id="progressbar"><div id="process" style="width: 10%;"></div></div>';
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
				<li class="error">Database Settings</li>
                                <li>Table Settings</li>
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
<?
                if ($error==""){
?>
				<p id="intro">Now you are ready to set the <?=$softName?> database.</p>
				<div id="errormessage" class="error"></div>
				<br />

					<h2>Database Settings</h2>
					<p>Please select one database available or delete useless database. </p>

					<form name="databasenameselect" id="databasenameselect" method="post" action="s2a.php" onSubmit="return chkform()">
						<table class="tableBlock">
							<tr>
								<td colspan="2"><h3>Select a Database available</h3></td>
							</tr>
							<tr>
								<td colspan="2">Currently using Database is <font class="error"><?=$dbname?></font></td>
							</tr>
<?
$db_list=$database->getDatabaseName();
if($db_list)
{
	showdb($database, $db_list, $dbname);
?>
							<tr>
								<td align="left"><input onclick="GoBack();" name="button" value="Back" onmouseover="this.className='button1 backInput'" onmouseout="this.className='button backInput'" class="button backInput">
</td>
								<td ALIGN="right">
								<input type="submit" name="Selectdb" value="Continue" onmouseover="this.className='button1 continueInput'" onmouseout="this.className='button continueInput'" class="button continueInput"/></td>
							</tr>
                           <div id=bdbs style=visibility:hidden>
                           <input name="bdbs" type="text" id="bdbs" value="true" />
                           <input name="dbserver" type="text" id="dbserver" value="<?echo $dbserver;?>" />
                           <input name="dbusername" type="text" id="dbusername" value="<?echo $dbusername;?>" />
                           <input name="dbpassword" type="text" id="dbpassword" value="<?echo $dbpassword;?>" />
                           </div>
<?
}
?>
						</table>
						</form>

						<form name="databasenamedelete" id="databasenamedelete" method="post" action="s2b.php" onSubmit="return chkformd()">
						<table class="tableBlock">
							<tr>
								<td colspan="2"><h3>Delete a Databases</h3></td>
							</tr>
							<tr>
								<td colspan="2">Currently using Database is <font class="error"><?=$dbname?></font></td>
							</tr>
<?
$db_list=$database->getDatabaseName();
if($db_list)
{
	showdbd($database, $db_list,$dbname);
?>
							<tr>
								<td>&nbsp;</td>
								<td ALIGN="right">
<?
      $blnDatabase = $database->databaseCheckCreateAndDropDatabasePrivelege();
      if($blnDatabase)
          echo("<input type=\"submit\" name=\"Deletedb\" value=\"Delete\" onmouseover=\"this.className='button1 deleteDatabaseInput'\" onmouseout=\"this.className='button deleteDatabaseInput'\" class=\"button deleteDatabaseInput\"/>");
      else
          echo("<input type=\"submit\" name=\"Deletedb\" value=\"Delete\" onmouseover=\"this.className='button1 deleteDatabaseInput'\" onmouseout=\"this.className='button deleteDatabaseInput'\" class=\"button deleteDatabaseInput\" disabled/></td><tr>
		  <tr><td colspan=\"2\"><font class=\"error\">You have no privelege to delete database. Please ask your host supporter.</font>
		  ");
?>
                            </td>
							</tr>
                           <div id="bdbs" style="visibility:hidden">
                           <input name="bdbd" type="text" id="bdbd" value="true" />
                           <input name="dbserver" type="text" id="dbserver" value="<?echo $dbserver;?>" />
                           <input name="dbusername" type="text" id="dbusername" value="<?echo $dbusername;?>" />
                           <input name="dbpassword" type="text" id="dbpassword" value="<?echo $dbpassword;?>" />
                           </div>
<?
}
?>
						</table>
						</form>

<?
}
if($error!=""){
$error =  $database->databaseGetErrorMessage();
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
<?
}
?>
	</td>
</tr>
</table>

</body>
</html>
<?php
function showdb($database, $result,$dbname)
{
    echo "
<TR class=\"title\">
<TD><p id=\"intro\">Database ID</p></TD>
<TD><p id=\"intro\">Database Name</p></TD>
</TR>";
    $i=0;
    while($row=$database->getColumns($result))
    {
	     //Jump the buildin table in MySQL
	    if ($row["Database"]<> "information_schema" && $row["Database"]<> "mysql" ) {
		     $i=$i+1;
		     if ($row["Database"]!= $dbname) {
		     if($i%2==0)
		     	echo "<TR class=\"odd\">";
		     else
			 	echo "<TR class=\"even\">";
		     }else{
			 	echo "<TR class=\"current\">";
			 }
		     echo "<TD>";
		     echo $i;
		     echo "</TD>";
		     echo "<TD>";
		     echo "<input type=\"radio\" name= \"databases\" class=\"button3\"  value=\"";
		     echo $row["Database"]."\"";
		     if ($row["Database"]== $dbname) {
		     	echo " CHECKED";
		     }
		     echo ">";
		     echo $row["Database"];
		     echo "</TD></TR>";
	     }
     }
     //Must select one database
         echo "<script type=\"text/javascript\">";
              echo "function chkform()";
              echo "{";
                echo "if(";
              //$iradio = 0;
              for ($iradio=0;$iradio<$i;$iradio++)
            {
              echo "!document.databasenameselect.databases[" .$iradio. "].checked ";
              if ($iradio != $i - 1 )
							{
                echo "&&";
              }
              //++$iradio;
            }
              echo ")";
              echo "{";
              echo "showErrorMessage(\"Please select a database!\");";
              echo "return false;";
              echo "}";
              echo "}";
              echo "</script>";
}

function showdbd($database, $result,$dbname)
{
    echo "
<TR class=\"title\">
<TD><p id=\"intro\">Database ID</p></TD>
<TD><p id=\"intro\">Database Name</p></TD>
</TR>";
    $i=0;
    while($row=$database->getColumns($result))
    {
     //Jump the buildin table in MySQL
    if ($row["Database"]<> "information_schema" && $row["Database"]<> "mysql" ) {
         $i=$i+1;
     if ($row["Database"]!= $dbname) {
     if($i%2==0)
     echo "<TR class=\"odd\">";
     else echo "<TR class=\"even\">";
     }else{
	 echo "<TR class=\"current\">";
	 }
     echo "<TD>";
     echo $i;
     echo "</TD>";
     echo "<TD>";
     echo "<input type=\"radio\" name= \"databased\" class=\"button3\"  value=\"";
     echo $row["Database"]."\">";
     echo $row["Database"];
     echo "</TD></TR>";
     }
     }
     //Must select one database
              echo "<script type=\"text/javascript\">";
              echo "function chkformd()";
              echo "{";
                echo "if(";
              //$iradio = 0;
              for ($iradio=0;$iradio<$i;$iradio++)
            {
              echo "!document.databasenamedelete.databased[" .$iradio. "].checked ";
              if ($iradio != $i - 1 )
							{
                echo "&&";
              }
              //++$iradio;
            }
              echo ")";
              echo "{";
              echo "showErrorMessage(\"Please select a database!\");";
              echo "return false;";
              echo "}";

              echo "if(";
              for ($iradio=0;$iradio<$i;$iradio++)
             {
              echo "document.databasenamedelete.databased[" .$iradio. "].checked";
              if ($iradio != $i - 1 )
							{
                echo "||";
              }
              }
              echo ")";
              echo "{";
              echo "if(confirm(\"Are you sure to delete this database?";
			  //echo "document.databasenamedelete.databased.checked.value";
			  echo "?\")){}";
			  echo "else{return false;}";
			  echo "}";

              echo "}";
              echo "</script>";
}
?>