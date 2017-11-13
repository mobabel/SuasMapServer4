<?php
/*
 * Created on 23.06.2009
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
 * favorite.php
 * @version $Id$
 * @copyright (C) 2006-2009 Hui LI
 * @Description : 
 * @contact webmaster@easywms.com
 * 
 */
 
include_once '../models/common.inc';
include_once '../models/perm.inc';
include_once '../config.php';
switchDatabase($dbtype);
session_start();
$perm = false;

$aid = $_REQUEST['aid'];
$uid = $_REQUEST['uid'];
$desc = $_REQUEST['desc'];
$op = $_REQUEST['op'];
$database = new Database();
$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
$database->databaseConnect();
$atlas_info = $database->db_get_atlas($aid);

$perm = perm_atlas_favo($atlas_info, $uid);

if($perm){
	if($op == 1){
		if(atlas_add_favorite($database, $aid, $uid, $desc))
			echo "suc:Atlas has been added into your favorite.";
		else
			echo  "err:".$database->databaseGetErrorMessage();
	}
	else if($op == 0){
		if(atlas_remove_favorite($database, $aid, $uid))
			echo "suc:Atlas has been removed from your favorite.";
		else
			echo  "err:".$database->databaseGetErrorMessage();
	}else{
		echo  "err:Wrong operation!";
	}
}
else{
	echo "err:You have no permission or this is one private atlas";
} 

 
function atlas_add_favorite($database, $aid, $uid, $desc){
	return $database->db_atlas_add_favorite($aid, $uid, $desc);
}
function atlas_remove_favorite($database, $aid, $uid){
	return $database->db_atlas_remove_favorite($aid, $uid);
}
?>
