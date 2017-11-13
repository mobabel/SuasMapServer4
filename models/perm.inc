<?php
/*
 * Created on 25.06.2009
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
 * perm.inc
 * @version $Id$
 * @copyright (C) 2006-2009 LI Hui
 * @Description :
 * @contact webmaster@easywms.com
 *
 */

include_once 'setting.inc';

/**
 * 
 */
function perm_atlas_view($atlas){
	$perm = false;
	if (isset ($_SESSION['user']) ){
		$user = $_SESSION['user'];
		
		//if visitor is the owner, or vistor is super admin
		if($user['uid'] == $atlas['uid'] || $user['uid'] == 1){
			$perm = true;
		}
		//if not owner, but status is public
		else{
			if($atlas['status'] == 1){
				$perm = true;
			}
		}
	}else{
		//check if the atlas status is public
		if($atlas['status'] == 1){
			$perm = true;
		}
	}
	
	return $perm;	
}

/**
 * 
 */
function perm_atlas_oper($atlas, $db){
	$perm = false;
	//only owner has the permission

	if (isset ($_SESSION['user']) ){
		$user = $_SESSION['user'];
		//if visitor is the owner, or vistor is super admin
		if($user['uid'] == $atlas['uid'] /*|| $user['uid'] == 1*/){
			$perm = true;
		}
		//if not owner, but status is wiki
		else{
			//TODO check the wiki contributer list, if there is uid inside
			if($atlas['status'] == 2){
				//if(uid in wiki)
				//$perm = true;
			}
		}
	}
	
	return $perm;	
}

/**
 * 
 */
function perm_atlas_favo($atlas, $uid){
	$perm = false;
	if (isset ($_SESSION['user']) ){
		$user = $_SESSION['user'];
		
		//if user is not the owner, and param uid in request is same as it's session uid, and status must not be private
		if($user['uid'] != $atlas['uid'] && $uid == $user['uid'] && $atlas['status'] != 0 ){
			$perm = true;
		}
	}
	
	return $perm;
}

/**
 * if visitor is authorized user
 */
function perm_user_view($uid){
	$perm = false;
	if (isset ($_SESSION['user']) ){
		$perm = true;
	}
	
	return $perm;	
}

/**
 * 
 */
function perm_user_oper($uid){
	$perm = false;
	if (isset ($_SESSION['user']) ){
		$user = $_SESSION['user'];
		
		//if visitor is the owner, or vistor is super admin
		if($user['uid'] == $uid || $user['uid'] == 1){
			$perm = true;
		}
	}
	
	return $perm;	
}

function perm_admin_oper(){
	$perm = false;
	if (isset ($_SESSION['user']) ){
		$user = $_SESSION['user'];
		
		//if vistor is super admin
		if($user['uid'] == 1){
			$perm = true;
		}
	}
	
	return $perm;	
}
?>