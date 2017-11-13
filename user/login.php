<?php
/**
 * login.php
 * Copyright (C) 2006-2008  leelight
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
 * @version $Id: login.php,v 1.2 2007/05/10 16:41:46 leelight Exp $
 * @copyright (C) 2006-2007  LI Hui(leelight)
 * @Description: login for user
 * @contact webmaster@easywms.com
 */
//this function disables errors when header code is not on the 1st line of code.
ob_start();

include_once '../config.php';
include_once '../models/setting.inc';
include_once '../models/common.inc';

switchDatabase($dbtype);

//TODO
$display_image_captcha = true;
$tries_default = 5;
$tries_inteval = 900; //15 minutes
$destination = $_POST['destination'];

//check if the user is logged in 1st.
session_start();

if (isset ($_SESSION['username']) && isset ($_SESSION['time']))
{

	//destroys the login sessions
	unset ($_SESSION);
	session_destroy();
	setReturnLink("");
	setSessionMessage(t('You have logged out.'), SITE_MESSAGE_INFO);
}
else if (isset ($_POST['login']))
{

	//check if the anti hacking cookie is set or has reached its limit
	if (!isset ($_COOKIE['tries']) || $_COOKIE['tries'] != '0')
	{

		//define all the vars in case the server don't support the use of global vars
		$usrname = strip_tags ($_POST['username']);
		$pwd = strip_tags ($_POST['password']);
		$rmbpw = $_POST['rmbpw'];
        //if there is need for image validation
		if ($display_image_captcha)
		{
			$code = $_POST['code'];
			$validcode = $_POST['validcode'];
			//incorrect validate code 
			if (md5($code) != $validcode)
			{
				if (isset ($_COOKIE['tries']))
				{
					//reduce the number of tries
					$tries = $_COOKIE['tries'] - 1;
					setcookie ('tries', $tries, time()+$tries_inteval, '/', '', 0);
					setSessionMessage(t('Invalid validation code. <b>#tries</b> tries left.', array('#tries'=>$tries)),SITE_MESSAGE_ERROR);
					setReturnLink("");
					return;
				}
				else
				{
					//set the cookie to hold the variable
					setSessionMessage(t('Invalid validation code. <b>#tries</b> tries left.', array('#tries'=>$tries_default)),SITE_MESSAGE_ERROR);				
					setcookie ('tries', $tries_default, time()+$tries_inteval, '/', '', 0);
					setReturnLink("");
					return;
				}
			}
		}

		$database = new Database();
		$database->setSiteInfo($siteinfo);
		$database->databaseConfig($dbserver, $dbusername, $dbpassword, $dbname, $dbprefix);
		$database->databaseConnect();
	    $error = $database->databaseGetErrorMessage();

		//TODO
	    if ($database->databaseConnection && $error == "") {

	    }

		//check if there is need to validate the account and use suitable MySQL command
		if ($need_to_validate_acct == TRUE)
		{
			$suclogin = $database->login($usrname, $pwd, true);
		}
		else
		{
			$suclogin = $database->login($usrname, $pwd, false);
		}


		if ($suclogin)
		{
			$suclogin['role'] = $database->get_role($suclogin['uid']);
			//valid login!
			if (isset ($rmbpw))
			{
				//set these cookie to remember the user next time he logs in.
				setcookie ('username', $usrname, time()+1209600, '/', '', 0);
				setcookie ('password', base64_encode ($pwd), time()+1209600, '/', '', 0);
			}
			else if(empty ($rmbpw) && isset ($_COOKIE['username']) && isset ($_COOKIE['password']))
			{
				//remove these cookie.
				setcookie ('username', '', time()-60, '/', '', 0);
				setcookie ('password', '', time()-60, '/', '', 0);
			}
			//start the sessions
			session_start();
			//remove the anti-hacking cookie
			setcookie ('tries', '', time()-60, '/', '', 0);
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['time'] = time();
			$_SESSION['user'] = $suclogin;

			setReturnLink($destination);
			setSessionMessage('',SITE_MESSAGE_ERROR);
			exit;
		}
		else
		{
			//invalid login!
			if (isset ($_COOKIE['tries']))
			{
				//reduce the number of tries
				$tries = $_COOKIE['tries'] - 1;
				setcookie ('tries', $tries, time()+$tries_inteval, '/', '', 0);
				setSessionMessage(t('Sorry, unrecognized username or password. Have you forgotten your password? <b>#tries</b> tries left.', array('#tries'=>$tries)),SITE_MESSAGE_ERROR);
				setReturnLink("");
				//cookies can not contain script!!!???
				//setSessionMessage('<script type="text/javascript" >$("errormessage").update("Sorry, unrecognized username or password. Have you forgotten your password?<b>'.$tries.'</b> tries left.</p><p align="center"><a href="'.$_SERVER['HTTP_REFERER'].'">Retry?</a>");$("errormessage").show();</script>',SITE_MESSAGE_ERROR);
			}
			else
			{
				//set the cookie to hold the variable
				setcookie ('tries', $tries_default, time()+$tries_inteval, '/', '', 0);
				setSessionMessage(t('Sorry, unrecognized username or password. Have you forgotten your password? <b>#tries_default</b> tries left.', array('#tries_default'=>$tries_default)),SITE_MESSAGE_ERROR);
				setReturnLink("");
			}
		}
		$database->databaseClose();
	}
	else
	{
		//block the computer from logging in
		//print 'You have entered invalid data for '.$tries_default.' times in a row. Please try logging-in after 15 minutes.';
		setSessionMessage(t('You have entered invalid data for #tries_default times in a row. Please try logging-in after #tries_inteval minutes.',array('#tries_default'=>$tries_default, '#tries_inteval'=>($tries_inteval/60))),SITE_MESSAGE_ERROR);
		setReturnLink("");
	}
}


ob_end_flush();
?>