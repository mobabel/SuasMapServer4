<?php
/**
 * user.inc
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
 * @version $Id: user.inc,v 1.2 2007/05/10 16:41:46 leelight Exp $
 * @copyright (C) 2006-2008  LI Hui(leelight)
 * @Description: functions for user system
 * @contact webmaster@easywms.com
 */

function user_validate_alldata($data, $database) {
	if(!$data['name']){
		return t('You must enter name.');
	}
	else if(strlen($data['name'])>32){
		return t('User name must be less than 32 characters.');
	}
	else if(!$data['mail'] || !$data['conf_mail']){
		return t('You must enter e-mail and confirm e-mail address.');
	}
	else if(!$data['pass1'] || !$data['pass2']){
		return t('You must enter password and confirm password.');
	}
	else if($data['mail'] != $data['conf_mail']){
		return t('The e-mail and confirm e-mail address should be same.');
	}
	else if($data['pass1'] != $data['pass2']){
		return t('The password and confirm password should be same.');
	}
	else if(strlen($data['pass1'])>30 || strlen($data['pass1'])<6
			|| strlen($data['pass2'])>30 || strlen($data['pass2'])<6){
		return t('Password must be between 6 and 30 characters.');
	}
	
	$message = user_validate_name($data['name']);
	if(!empty($message))
		return $message;
	$message = user_validate_mail($data['mail']);
	if(!empty($message))
		return $message;
	
	if(!$database->db_register_user($data['name'],$data['pass1'],$data['mail'], false, true )){
		return t($database->databaseGetErrorMessage());
	}
}

/**
 * if successful, return the uid
 */
function user_register($data, $database) {
	if($database->databaseGetErrorMessage()!=""){
		return false;
	}
	$uid =$database->db_register_user($data['name'],$data['pass1'],$data['mail'], false, false );
	if(!$uid ){
		return false;
	}else{		
		//create folder for uid
		/**
		 * files
		 * -user
		 --uid
		 ----data
		 */
		if(!createFile("../files/user/".$uid."/data/")){
			//TODO log it?
			
		}
		return $uid;
	}
}
/**
 * Verify the syntax of the given name.
 */
function user_validate_name($name) {
	if (!strlen($name)) return t('You must enter a username.');
	if (substr($name, 0, 1) == ' ') return t('The username cannot begin with a space.');
	if (substr($name, -1) == ' ') return t('The username cannot end with a space.');
	if (strpos($name, '  ') !== FALSE) return t('The username cannot contain multiple spaces in a row.');
	if (ereg("[^\x80-\xF7 [:alnum:]@_.-]", $name)) return t('The username contains an illegal character.');
	if (preg_match('/[\x{80}-\x{A0}'.          // Non-printable ISO-8859-1 + NBSP
		'\x{AD}'.                 // Soft-hyphen
		'\x{2000}-\x{200F}'.      // Various space characters
		'\x{2028}-\x{202F}'.      // Bidirectional text overrides
		'\x{205F}-\x{206F}'.      // Various text hinting characters
		'\x{FEFF}'.               // Byte order mark
		'\x{FF01}-\x{FF60}'.      // Full-width latin
		'\x{FFF9}-\x{FFFD}'.      // Replacement characters
		'\x{0}]/u',               // NULL byte
		$name)) {
		return t('The username contains an illegal character.');
	}
	if (strpos($name, '@') !== FALSE && !eregi('@([0-9a-z](-?[0-9a-z])*.)+[a-z]{2}([zmuvtg]|fo|me)?$', $name)) return t('The username is not a valid authentication ID.');
	if (strlen($name) > USERNAME_MAX_LENGTH) return t('The username %name is too long: it must be %max characters or less.', array('%name' => $name, '%max' => USERNAME_MAX_LENGTH));
}


/**
 * Verify the syntax of the given e-mail address. Function from Drupal
 *
 * Empty e-mail addresses are allowed. See RFC 2822 for details.
 *
 * @param $mail
 *   A string containing an e-mail address.
 * @return
 *   TRUE if the address is in a valid format.
 */
function valid_email_address($mail) {
	$user = '[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+';
	$domain = '(?:(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.?)+';
	$ipv4 = '[0-9]{1,3}(\.[0-9]{1,3}){3}';
	$ipv6 = '[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}';
	
	return preg_match("/^$user@($domain|(\[($ipv4|$ipv6)\]))$/", $mail);
}

function user_validate_mail($mail) {
	if (!$mail) return t('You must enter an e-mail address.');
	if (!valid_email_address($mail)) {
		return t('The e-mail address %mail is not valid.', array('%mail' => $mail));
	}
}


function user_fields() {
	static $fields;
	
	if (!$fields) {
		$result = db_query('SELECT * FROM {users} WHERE uid = 1');
		if (db_num_rows($result)) {
			$fields = array_keys(db_fetch_array($result));
		}
		else {
			// Make sure we return the default fields at least
			$fields = array('uid', 'name', 'pass', 'mail', 'picture', 'mode', 'sort', 'threshold', 'theme', 'signature', 'created', 'access', 'login', 'status', 'timezone', 'language', 'init', 'data');
		}
	}
	
	return $fields;
}

function user_register_block($regiester_data){
	$output = '
		<form action="../user/register.php" accept-charset="UTF-8" method="post" id="user-register-form" onSubmit="return chk_form_required_field(\'user-register-form\')">
			 <div>
				<div class="form-item" id="edit-name-wrapper">
			 <label for="edit-name">Username: <span class="form-required" title="This field is required.">*</span></label>
			 <input maxlength="60" name="name" id="edit-name" size="60" value="';
	if($regiester_data['name'])
		$output .= $regiester_data['name'];
	$output .= '" class="form-text form-required" type="text" />
			 <div class="description">Your preferred username; punctuation is not allowed except for periods, hyphens, and underscores.</div>
				</div>

			<div class="form-item" id="edit-name-wrapper">
			 <label for="edit-name">E-mail address: <span class="form-required" title="This field is required.">*</span></label>
			 <input maxlength="64" name="mail" id="edit-mail" size="60" value="';
	if($regiester_data['mail'])
		$output .= $regiester_data['mail'];
	$output .= '" class="form-text required" type="text" />
			 <div class="description">A valid e-mail address. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by e-mail.</div>
			</div>

			<div class="form-item" id="edit-name-wrapper">
			 <label for="edit-name">Confirm e-mail address: <span class="form-required" title="This field is required.">*</span></label>
			 <input maxlength="64" name="conf_mail" id="edit-conf-mail" size="60" value="';
	if($regiester_data['conf_mail'])
		$output .= $regiester_data['conf_mail'];
	$output .= 	 '" class="form-text required" type="text"/>
			 <div class="description">Please re-type your e-mail address to confirm it is accurate.</div>
			</div>

			<div class="form-item" id="edit-name-wrapper">	
			 <label for="edit-name">Password: <span class="form-required" title="This field is required.">*</span></label>
			 <input name="pass1" id="edit-pass-pass1" size="25" class="form-text required" type="password"';
	if($regiester_data['pass1'])
		$output .= 'value="'.$regiester_data['pass1'].'"';
	$output .= 	 ' /></div>

			<div class="form-item" id="edit-name-wrapper">
			 <label for="edit-name">Confirm password: <span class="form-required" title="This field is required.">*</span></label>
			 <input name="pass2" id="edit-pass-pass2" size="25" class="form-text required" type="password"';
	if($regiester_data['pass2'])
		$output .= 'value="'.$regiester_data['pass2'].'"';
	$output .= 	' />
			 <div class="description">Please choose a password for your account; it must be between 6 and 30 characters and spaces are not allowed.</div>
			</div>

			<input name="form_id" id="edit-user-register" value="user_register" type="hidden" />';
	$output .= 	menu::get_captcha_field($page);
	
	$output .=	'<input name="register" id="edit-submit" value="Create new account" type="submit" class="ui-button ui-state-default ui-corner-all" />

			</div>
		</form>
	';
	print $output;
	menu::print_jsscript_captcha($page);
}

function user_edit_block($data){
	global $user;
	if(empty($data)){
		$data = $user;
	}
	$output = '
		<form action="../user/user.php" accept-charset="UTF-8" method="post" id="user-edit">
			 <div>
			 Username: <span class="form-required" title="This field is required.">*</span><br/>
			 <input maxlength="60" name="name" id="edit-name" size="60" value="';
	if($data['name'])
		$output .= $data['name'];
	$output .= '" class="form-text required" type="text" >
			 <div class="description">Your preferred username; punctuation is not allowed except for periods, hyphens, and underscores.</div>

			 E-mail address: <span class="form-required" title="This field is required.">*</span><br/>
			 <input maxlength="64" name="mail" id="edit-mail" size="60" value="';
	if($data['mail'])
		$output .= $data['mail'];
	$output .= '" class="form-text required" type="text">
			 <div class="description">A valid e-mail address. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by e-mail.</div>

			 Password: <br/>
			 <input name="pass1" id="edit-pass-pass1" size="25" class="form-text required" type="password"';
	if($data['pass1'])
		$output .= 'value="'.$data['pass1'].'"';
	$output .= 	 '><br/>


			 Confirm password: <br/>
			 <input name="pass2" id="edit-pass-pass2" size="25" class="form-text required" type="password"';
	if($data['pass2'])
		$output .= 'value="'.$data['pass2'].'"';
	$output .= 	'>
			 <div class="description">Please choose a password for your account; it must be between 6 and 30 characters and spaces are not allowed.</div>';
	
	if($user['uid'] == 1 && $user['uid'] != $data['uid']){
		$output .= '<label>Status: </label><br/>
	 				<label class="option"><input id="edit-status-0" name="status" value="0" class="form-radio" type="radio" ';
	 	if($data['status'])
			$output .= 'checked="checked"';			
		$output .= '> Blocked</label>
	 				<label class="option"><input id="edit-status-1" name="status" value="1" class="form-radio" type="radio" ';
	 	if($data['status'])
			$output .= 'checked="checked"';
		$output .= '> Active</label>';
	}
	
	$output .= 	'<input name="form_id" id="user-edit" value="user_edit" type="hidden">' .
			'<input name="uid" id="uid" value="'.$data['uid'].'" type="hidden">
			<br/><br/>' .
			'<input onclick="javascript:window.location.href=\'user.php?op=&uid=' . $data['uid'] . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">'
			.'&nbsp;<input name="op" id="edit-save" value="Save" type="submit" class="ui-button ui-state-default ui-corner-all">
			';
			if($user['uid'] == 1 && $user['uid'] != $data['uid']){
				$output .= '<input name="op" id="edit-delete" value="Delete" type="submit" class="ui-button ui-state-default ui-corner-all">';
			}
			
	$output .='
			</div>
		</form>
	';
	print $output;
}

function user_update($database, $data){
	//current edited uid
	$uid = $data['uid'];
	if( !empty($data['pass1']) || !empty($data['pass2']) ){
		if($data['pass1'] != $data['pass2']){
			setSessionMessage(t('The password and confirm password should be same.'), SITE_MESSAGE_ERROR);
			return false;			
		}
		else if(strlen($data['pass1'])>30 || strlen($data['pass1'])<6
			|| strlen($data['pass2'])>30 || strlen($data['pass2'])<6){
			setSessionMessage(t('Password must be between 6 and 30 characters.'), SITE_MESSAGE_ERROR);
			return false;
		}
		else{
			$data['pass'] = $data['pass1'];
		}		
	}
	
	$message = user_validate_mail($data['mail']);
	if(!empty($message)){
		setSessionMessage($message, SITE_MESSAGE_ERROR);
			return false;
	}	
	
	if($user = $database->db_update_user($uid, $data)){
		setSessionMessage("You have update profile.", SITE_MESSAGE_INFO);
		return $user;
	}else{
		setSessionMessage(t($database->databaseGetErrorMessage()), SITE_MESSAGE_ERROR);
		return false;
	}
}

function user_view($uid, $database){
	$user = $database->get_user($uid);
	$permoper = perm_user_oper( $uid );
	?>
	<table class="tableNone">
<tr class="title">
	<td colspan="2">
    	<?=$user['name']?>			
	</td>
</tr>
<tr class="odd">
	<td width="30%">
		Member for		
	</td>
	<td >
		<?=cal_days_from_dates($user['created'])?>		
	</td>
</tr>
<tr class="even">
	<td width="30%">
		Last login		
	</td>
	<td >
		<?=date("r", $user['login'])?>		
	</td>
</tr>
<tr >
	<td width="30%">			
	</td>
	<td >
	&nbsp;
	</td>
</tr>
<tr >
	<td width="30%">
			
	</td>
	<td align="right">
<?
if($permoper){
		echo '<a class="ui-button ui-state-default ui-corner-all" href="?uid='.$uid.'&op=edit">Edit</a>  
		&nbsp;	';
}
?>
		<a class="ui-button ui-state-default ui-corner-all" href="?uid=<?=$uid?>&op=track">Track</a>  
	</td>
</tr>
</table>

<?	
}

?>