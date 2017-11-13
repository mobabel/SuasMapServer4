<?php

/**
 * getsld
 * Copyright (C) 2006-2008  H.Li
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
 * @version 1.0
 * @copyright (C) 2006-2008  H.Li(leelight)
 * @Description : This show the copyright .
 * @contact webmaster@easywms.com
 */
require_once '../models/Installation.class.php';
include_once '../models/common.inc';
include_once '../models/perm.inc';
include_once '../config.php';
include_once 'atlas.inc';

$perm = false;
switchDatabase($dbtype);
session_start();
$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();


$QUERY_STRING = $_SERVER ['QUERY_STRING'];
$a = explode('&', $QUERY_STRING);
$i = 0;
while ($i < count($a)) {
    $b = split('=', $a[$i]);
    $text_upper = strtoupper($b[0]);
    if ($text_upper == "AID") {
        $aid = htmlspecialchars(urldecode($b[1]));
    }
    if ($text_upper == "STID") {
        $stid = htmlspecialchars(urldecode($b[1]));
    }
    $i++;
}
$atlas_info = $database->db_get_atlas($aid);
$perm = perm_atlas_oper($atlas_info, $database);
if(!$perm){
	echo "err:You have no permission. Please log in.";
	return;
}

$strStyleInfoContainer = atlas_get_style($database, $aid, $stid);
header("Content-type: text/html;charset=UTF-8;");
header("Cache-Control: no-cache, must-revalidate");
if($strStyleInfoContainer == null){
	echo "err:Can not get srs list";
}else{
	if (count($strStyleInfoContainer) > 0) {
        foreach($strStyleInfoContainer as $k => $v) {
            echo $strStyleInfoContainer[$k];
        }
    }
	else {
        echo "err:There is no records in database!";
    }
}

?>