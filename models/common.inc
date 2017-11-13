<?php
/*
 * Created on 07.05.2007
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
 * common.inc
 * @version $Id$
 * @copyright (C) 2006-2009 LI Hui
 * @Description :
 * @contact webmaster@easywms.com
 *
 */

include_once 'setting.inc';
@include_once '../config.php';
/**
 *
 * @description :Limits the maximum execution time
 * @param  SITE_MAX_TIMEOUT_LIMIT from setting.inc
 */
function setConnectionTime ($maxTimeOutLimit){
	// Check for safe mode
	if (ini_get('safe_mode')) {
		// Do it the safe mode way
		// Do nothing
	} else {
		// Do it the regular way
		set_time_limit ($maxTimeOutLimit);
	}
}

function doConnectionTimeout(){
	// if connection expired
	if (connection_timeout()) {
		print('<li class="error">Data Input</li>');
		print('              <li>Style Defination</li>
			<li>Create Metadata</li>
			</ul>
			<li class="first"><span>Setting</span></li>
			<ul class="second">
			<li>Database Settings</li>
			</ul>
			<li class="complete">Complete!</li>
			</ul>
			</td>
		<td id="content">');
		
		print('	<h1>Failure</h1>
			<p id="intro">The data processing meet connection timeout error. <br />
			Please set your maximum timeout value in php.ini file.<br /><br />
			<a href="javascript: history.go(-1)">Click here to go back</a>.</p>
			
			</td>
			</tr>
			</table>
			
			</body>
		</html>');
	}
	// if not connection expired
	if (!connection_timeout()) {
		print('<li class="error">Data Input</li>');
		print('              <li>Style Defination</li>
			<li>Create Metadata</li>
			</ul>
			<li class="first"><span>Setting</span></li>
			<ul class="second">
			<li>Database Settings</li>
			</ul>
			<li class="complete">Complete!</li>
			</ul>
			</td>
		<td id="content">');
		
		print('	<h1>Failure</h1>
			<p id="intro">You have aborted the data processing. <br />
			Maybe some data have been inputted into database, please check it.<br /><br />
			<a href="javascript: history.go(-1)">Click here to go back</a>.</p>
			
			</td>
			</tr>
			</table>
			
			</body>
		</html>');
	}
}
// if the user want to abort or back, if time timeout
function shutdownBeforeConnectionTimeout(){
	register_shutdown_function('doConnectionTimeout');
}

function equalIgnoreCase($a, $b){
	if (strcasecmp($a, $b) == 0)
		return true;
	else
		return false;
}

/*
 * Log text and timestamp in debug file
 */
function recordlog($logtxt){
	$root = "../";
	$recordFileName = $root . recordFileName;
	if (recordLog == 1) {
		if (recordFileName != "") {
			$file = fopen($recordFileName, "a");
			if ($file) {
				$tm = microtime_float();
				$tmf = sprintf("%.3f", $tm - floor($tm));
				fwrite($file, date('d/m/y H:i:s', $tm) . substr($tmf, 1) . " - " . $logtxt . "\r\n");
				fclose($file);
			} else {
				// if not exist, create log file
				createFile($recordFileName);
				$file = fopen($recordFileName, "a");
				$tm = microtime_float();
				$tmf = sprintf("%.3f", $tm - floor($tm));
				fwrite($file, date('d/m/y H:i:s', $tm) . substr($tmf, 1) . " - " . $logtxt . "\r\n");
				fclose($file);
			}
		}
	}
}

/*
 *Get time in millisecs
 */
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/**
 * $mode: Note that you should NOT enter the $mode value with "" or '' sorrounding it.
 */
function createFile($dir, $mode = 0777, $overwrite = false){
	if (!$dir) return false;
	//if it is file, not directory (not end with /)
	
	if(strripos($dir, "/") != strlen($dir)-1){
		$fileHandle = fopen($dir, 'w');
		fclose($fileHandle);
		return true;
	}else{
		$dir = str_replace("\\", "/", $dir);
		
		$mdir = "";
		foreach(explode("/", $dir) as $val) {
			$mdir .= $val . "/";
			if ($val == ".." || $val == "." || trim($val) == "") continue;
			
			if (!file_exists($mdir)) {
				
				if (!@mkdir($mdir, $mode)) {
					//$error = "Can not create " . $dir . " manully in SUAS root.";
					return false;
				}
			}else{
				if($overwrite){
					if (!@mkdir($mdir, $mode)) {
						//$error = "Can not create " . $dir . " manully in SUAS root.";
						//continue, because this directory existes
						continue;
						//return false;
					}
				}
			}
		}
	}
	return true;
}

function removeDir($dir, $DeleteMe = true) {
	if(!$dh = @opendir($dir)) return;
	while (false !== ($obj = readdir($dh))) {
		if($obj=='.' || $obj=='..') continue;
		if (!@unlink($dir.'/'.$obj)) removeDir($dir.'/'.$obj, true);
	}
	
	closedir($dh);
	if ($DeleteMe){
		@rmdir($dir);
	}
}


 /**
  * copy a direction's all files to another direction
  * @uses:
  * xFileCopy("feiy","feiy2",true): copy all files under feiy to feiy2, including sub-directory
  * xFileCopy("feiy","feiy2",false): copy all files under feiy to feiy2, without sub-directory
  * @param datatype paramname:
  * $source:
  * $destination:
  * $child: if copy children (sub-directory)
  */
function xFileCopy($source, $destination, $child){
	if(!is_dir($source)){
		//$source is not a directory, check if destination is folder
		//copy file to file
		if(!is_dir($destination)){
			if (!copy($source, $destination)){
				return false;
			}else{
				return true;
			}
		}
		//copy file to folder
		else{
			if (!file_exists($destination)) {
				@mkdir($destination,0777);
			}
			//get the file name
			$filename = substr(strrchr($source, "/"), 1);
			//echo  $filename;
			if (!copy($source, $destination."/".$filename)){
				return false;
			}else{
				return true;
			}
		}
	}
	if(!is_dir($destination)){
		mkdir($destination,0777);
	}

	$handle=dir($source);
	while($entry=$handle->read()) {
		if(($entry!=".")&&($entry!="..")){
			if(is_dir($source."/".$entry)){
				if($child)
					xFileCopy($source."/".$entry,$destination."/".$entry,$child);
			}
			else{	
				copy($source."/".$entry,$destination."/".$entry);
			}
			
		}
	}
	
	return true;
} 

/*
 * Clear the log file
 * string $fileName filename or all the files
 * return true or false
 */
function clearLog($fileName){
	$temp = $fileName;
	if (file_exists($fileName)) {
		$b = @unlink($fileName);
		if($b){
			//createFile($fileName);//does not work, strange
			$FileHandle = @fopen($temp, 'w') ;
			@fclose($FileHandle);
			return true;
		}else return false;
		
	} else {
		$FileHandle = @fopen($temp, 'w') ;
		@fclose($FileHandle);
		return true;
	}
}

/**
 * When using t(), try to put entire sentences and strings in one t() call.
 * Function from Drupal
 */
function t($string, $args = array(), $langcode = NULL) {
	if (empty($args)) {
		return $string;
	}
	else {
		// Transform arguments before inserting them.
		foreach ($args as $key => $value) {
			switch ($key[0]) {
				case '@':
					// Escaped only.
					//$args[$key] = check_plain($value);
					break;
					
				case '%':
				default:
					// Escaped and placeholder.
					//$args[$key] = theme('placeholder', $value);
					break;
				
				case '!':
					// Pass-through.
				case '#':
			}
		}
		return strtr($string, $args);
	}
}

function formatParamColor($color){
	if($color!="" && strpos($color, "#") !== false){
		$color = "#".$color;
	}
	if($color!="" && strpos($color, "0x") !== false){
		$color = str_replace('0x', '', $color);
		$color = "#".$color;
	}
	return $color;
}

/**
 * store one template message in seesion to cross pages
 */
function setSessionMessage($message, $type){
	$message = str_replace("\r\n", "</br>", $message);
	$message = str_replace("\r", "</br>", $message);
	$message = str_replace("\r", "</br>", $message);
	if($type == SITE_MESSAGE_ERROR){
		$_SESSION[SITE_MESSAGE_ERROR] = $message;
	}
	else if($type == SITE_MESSAGE_INFO){
		$_SESSION[SITE_MESSAGE_INFO] = $message;
	}
	else if($type == SITE_MESSAGE_WARN){
		$_SESSION[SITE_MESSAGE_WARN] = $message;
	}
}

/**
 * display one template message in seesion to cross pages
 * this javascript active the message div, then destroy the session
 */
function displayMessage($isparent = false, $growl = false){
	if(!$isparent){
		if(!empty($_SESSION[SITE_MESSAGE_ERROR])){
			print '<script type="text/javascript" >';
			if(!$growl)
				print 'updateMessageError';
			else
				print 'growlError';
			print '("'.$_SESSION[SITE_MESSAGE_ERROR].'");';
			print '</script>';
			unsetSessionMessage(SITE_MESSAGE_ERROR);
		}
		if(!empty($_SESSION[SITE_MESSAGE_INFO])){
			print '<script type="text/javascript" >';
			if(!$growl)
				print 'updateMessageInfo';
			else
				print 'growlInfo';
			print '("'.$_SESSION[SITE_MESSAGE_INFO].'");';
			print '</script>';
			unsetSessionMessage(SITE_MESSAGE_INFO);
		}
		if(!empty($_SESSION[SITE_MESSAGE_WARN])){
			print '<script type="text/javascript" >';
				if(!$growl)
				print 'updateMessageWarn';
			else
				print 'growlWarn';
			print '("'.$_SESSION[SITE_MESSAGE_WARN].'");';
			print '</script>';
			unsetSessionMessage(SITE_MESSAGE_WARN);
		}
	}
	else{
		if(!empty($_SESSION[SITE_MESSAGE_ERROR])){
			print '<script type="text/javascript" >
			updateMessage("'.$_SESSION[SITE_MESSAGE_ERROR].'", "'.SITE_MESSAGE_ERROR.'", true);
			</script>';
			unsetSessionMessage(SITE_MESSAGE_ERROR);
		}
		if(!empty($_SESSION[SITE_MESSAGE_INFO])){
			print '<script type="text/javascript" >
			updateMessage("'.$_SESSION[SITE_MESSAGE_INFO].'", "'.SITE_MESSAGE_INFO.'", true);
			</script>';
			unsetSessionMessage(SITE_MESSAGE_INFO);
		}
		if(!empty($_SESSION[SITE_MESSAGE_WARN])){
			print '<script type="text/javascript" >
			updateMessage("'.$_SESSION[SITE_MESSAGE_WARN].'", "'.SITE_MESSAGE_WARN.'", true);
			</script>';
			unsetSessionMessage(SITE_MESSAGE_WARN);
		}
	}
}

function clearAllMessage($isparent = false){
	if(!$isparent){
		print '<script type="text/javascript" >';
		print 'updateMessage("", "'.SITE_MESSAGE_ERROR.'", false);';
		unsetSessionMessage(SITE_MESSAGE_ERROR);
		print 'updateMessage("", "'.SITE_MESSAGE_INFO.'", false);';
		unsetSessionMessage(SITE_MESSAGE_INFO);
		print 'updateMessage("", "'.SITE_MESSAGE_WARN.'", false);';
		unsetSessionMessage(SITE_MESSAGE_WARN);
		print '</script>';
	}
	else{
		print '<script type="text/javascript" >';
		print 'updateMessage("", "'.SITE_MESSAGE_ERROR.'", true);';
		unsetSessionMessage(SITE_MESSAGE_ERROR);
		print 'updateMessage("", "'.SITE_MESSAGE_INFO.'", true);';
		unsetSessionMessage(SITE_MESSAGE_INFO);
		print 'updateMessage("", "'.SITE_MESSAGE_WARN.'", true);';
		unsetSessionMessage(SITE_MESSAGE_WARN);
		print '</script>';
	}
}

/**
 * destroy the session
 *
 */
function unsetSessionMessage($type){
	unset ($_SESSION[$type]);
}


/**
 * save one instant message in cookie, just for error message or tip message
 * Not use now
 */
function setCookieMessage($message){
	setcookie ('messages', $message, time()+5, '/', '', 0);
}

function setReturnLink($destination, $time = 0){
	if($destination==""){
		if ($_SERVER['HTTP_REFERER']) $link = $_SERVER['HTTP_REFERER'];
		else $link = $_SERVER['PHP_SELF'];
		header ('Location: '.$link);
	}else{
		if($time!=0){
			print "<meta http-equiv=refresh content=\"$time;url=".get_base_server_host().$destination.".php\">";
		}
		else{
			header ('Location: '.get_base_server_host().$destination.".php");
		}
	}
}

/**
 * writeSiteData
 *
 */
function writeSiteData($data, $type){
	
}

function wsd($data, $type){
	writeSiteData($data, $type);
}

/**
 * getSiteData
 *
 */
function getSiteData($variable){
	$type = substr($variable, 0, 1);
	
	//if string
	//s:11:"fourseasons";
	if($type == 's'){
		$temp = explode(":", $variable);
		$string = explode("\"", $temp[2]);
		return $string[1];
	}
}

function gsd($variable){
	return getSiteData($variable);
}

function switchDatabase($dbtype){
	switch ($dbtype) {
		case 0: {
			include_once '../models/mysql.class.php';
		}
		break;
		case 1: {
			//include_once '../models/pgsql.class.php';
		}
		break;
		case 2: {
			//include_once '../models/oracle.class.php';
		}
		break;
		case 3: {
			//include_once '../models/mssql.class.php';
		}
		break;
		case 4: {
			//include_once '../models/mysqli.class.php';
		}
		break;
	}
}

function switchDatabase_interface($dbtype){
	switch ($dbtype) {
		case 0: {
			include_once '../../../models/mysql.class.php';
		}
		break;
		case 1: {
			include_once '../../../models/pgsql.class.php';
		}
		break;
		case 2: {
			include_once '../../../models/oracle.class.php';
		}
		break;
		case 3: {
			include_once '../../../models/mssql.class.php';
		}
		break;
		case 4: {
			include_once '../../../models/mysqli.class.php';
		}
		break;
	}
}

function switchDatabase_home($dbtype){
	switch ($dbtype) {
		case 0: {
			include_once 'models/mysql.class.php';
		}
		break;
		case 1: {
			//include_once 'models/pgsql.class.php';
		}
		break;
		case 2: {
			//include_once 'models/oracle.class.php';
		}
		break;
		case 3: {
			//include_once 'models/mssql.class.php';
		}
		break;
		case 4: {
			//include_once 'models/mysqli.class.php';
		}
		break;
	}
}

function print_help($header, $title, $content){
	$content = str_replace("\r\n", "</br>", $content);
	$content = str_replace("\r", "</br>", $content);
	$content = str_replace("\r", "</br>", $content);
	print '<image src="../img/help.png"  border="0" onmouseover="tooltip(\''
	.$header.'\',\''
	.$title.':\',\''
	.$content.
	'</br></br>\');" onmouseout="exit();">';
}

//<image src="../img/warningwhite.png"  border="0" onmouseover="warningtip('Overlay Layer','Description:','All of the layers listed below are using SRS with <b>EPSG:4326(WGS84)</b>, if your SRS used here is not the same(EPSG:4326), the overlaying layers will meet errors.');" onmouseout="exitwarning();">
function print_warn($header, $title, $content){
	$content = str_replace("\r\n", "</br>", $content);
	$content = str_replace("\r", "</br>", $content);
	$content = str_replace("\r", "</br>", $content);
	print '<image src="../img/warningwhite.png"  border="0" onmouseover="warningtip(\''
	.$header.'\',\''
	.$title.':\',\''
	.$content.
	'</br></br>\');" onmouseout="exitwarning();">';
}

function do_post_request($url, $data, $optional_headers = null){
	$params = array('http' => array(
		'method' => 'POST',
		'content' => $data
	));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}
	return $response;
}

function compose_array_in_querystring($array){
	$querystring = "";
	if(!is_array($array))
		return $array;
	foreach($array as $key=>$value){
		$querystring .= $key."=".$value."&";
	}
	if(strripos($querystring, "&") == strlen($querystring)-1){
		$querystring = substr($querystring, 0, strlen($querystring)-1);
	}
	
	return $querystring;
}

/**
 * get the base server host url
 */
function get_base_server_host(){
	global $baseserverhost;
	/*	if(substr_count($_SERVER['PHP_SELF'], '/')>1){
	 $url = "http://".$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT']!="80"?(":".$_SERVER['SERVER_PORT']):"").substr($_SERVER['REQUEST_URI'], 0 , stripos(substr($_SERVER['REQUEST_URI'],1), "/")+2);
	 }else{
	 $url = "http://".$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT']!="80"?(":".$_SERVER['SERVER_PORT']):"")."/";
	 }*/
	$url = $baseserverhost;
	
	/*	$path = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	 $curpath = basename(dirname($_SERVER['PHP_SELF']));
	 $url = str_replace($curpath, '', $path);*/
	
	return $url;
}

/**
 *
 */
function get_wms_path($aid){
	if(!empty($aid))
		return get_base_server_host()."files/atlas/".$aid."/";
	else
		return get_base_server_host()."files/atlas/1/";
}

/**
 *
 */
function get_wms_interface($aid){
	if(!empty($aid))
		return get_base_server_host()."files/atlas/".$aid."/wms.php";
	else
		return get_base_server_host()."wms/getmapcap.php";
}

/**
 *
 */
function get_wfs_interface($aid){
	if(!empty($aid))
		return get_base_server_host()."files/atlas/".$aid."/wfs.php";
	else
		return get_base_server_host()."wfs/getmapcap.php";
}

function cal_days_from_dates($old_timestamp){
	$d1 = $old_timestamp;
	$d2 = time();
	
	$days = round(($d2-$d1)/3600/24);
	if($days <=1 ){
		return round(($d2-$d1)/3600)." hours";
	}
	else if($days < 365 && $days>1){
		$weeks = round($days/7) ;
		$daysleft = $days - $weeks*7;
		return $weeks . " weeks " . ($daysleft>0? ($daysleft . " days"):"");
	}
	else{
		$years = round($days/365);
		$daysleft = $days - $years*365;
		$weeks = round($daysleft/7) ;
		return $years." year ". $weeks . " weeks";
	}
	
}

function relative_to_absolute($content, $feed_url) {
    preg_match('/(http|https|ftp):\/\//', $feed_url, $protocol);
    $server_url = preg_replace("/(http|https|ftp|news):\/\//", "", $feed_url);
    $server_url = preg_replace("/\/.*/", "", $server_url);

    if ($server_url == '') {
        return $content;
    }

    if (isset($protocol[0])) {
        $new_content = preg_replace('/href="\//', 'href="'.$protocol[0].$server_url.'/', $content);
        $new_content = preg_replace('/src="\//', 'src="'.$protocol[0].$server_url.'/', $new_content);
    } else {
        $new_content = $content;
    }
    return $new_content;
} 

function get_country_list($cty, $sel=""){
	$out = '<select name="Country" id="Country">';
	foreach($cty as $key=>$val){
		$out .= '<option value="'.$key.'" '.($sel==$key?'selected':'').'>'.$val.'</option>';
	}
	$out .= '</select>';
	return $out;
}

function get_country_name($cty, $sel=""){
	foreach($cty as $key=>$val){
		if($sel == $key){
			return $val;
		}
	}
	return '';
}

?>