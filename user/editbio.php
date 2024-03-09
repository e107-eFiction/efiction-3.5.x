<?php
// ----------------------------------------------------------------------
// eFiction 3.2
// Copyright (c) 2007 by Tammy Keefer
// Valid HTML 4.01 Transitional
// Based on eFiction 1.1
// Copyright (C) 2003 by Rebecca Smallwood.
// http://efiction.sourceforge.net/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------

if(!defined("_CHARSET")) exit( );
if(!function_exists("random_char")) {

function random_char($string)
{
	$length = strlen($string);
	$position = mt_rand(0, $length - 1);
	$output = ($string[$position]);
	return $output;
}

function random_string ($charset_string, $length)
{
	$return_string = random_char($charset_string);
	for ($x = 1; $x < $length; $x++)
	$return_string .= random_char($charset_string);
	return $return_string;
}

}
	$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : false;
	if(!$uid) $uid = USERUID;

	if((!isADMIN || uLEVEL > 2) && $uid != USERUID && $action == "editbio") $output .= write_error(_NOTAUTHORIZED);
	if(isMEMBER) $output .= "<div id=\"pagetitle\">"._EDITPERSONAL."</div>";
	else $output .= "<div id=\"pagetitle\">"._NEWACCOUNT."</div>";
	if(!empty($_POST['submit'])) {
		$penname = isset($_POST['newpenname']) ? escapestring($_POST['newpenname']) : false;
		$email = escapestring($_POST['email']);
		if(!isset($email) && !isADMIN) $output .= "<div style='text-align: center;'>"._EMAILREQUIRED."</div>";
		else if($penname && !preg_match("!^[a-z0-9-_ ]{3,30}$!i", $penname)) $output .= "<div style='text-align: center;'>"._BADUSERNAME."</div>";
		else if(!validEmail($email)) $output .= "<div style='text-align: center;'>"._INVALIDEMAIL." "._TRYAGAIN."</div>";
		else if($action == "register") {
			if(!$penname || !preg_match("!^[a-z0-9-_ ]{3,30}$!i", $penname)) $output .= write_error(_PENEMAILREQUIRED);
			else if($pwdsetting && empty($_POST['password'])) $output .= write_error(_PWDREQUIRED."  "._TRYAGAIN);
			else  {
				$result = dbquery("SELECT "._PENNAMEFIELD." FROM "._AUTHORTABLE." WHERE "._PENNAMEFIELD." = '".escapestring($penname)."'");
				$result2 = dbquery("SELECT "._EMAILFIELD." as email FROM "._AUTHORTABLE." WHERE "._EMAILFIELD." = '$email'");
				if($captcha && !captcha_confirm()) $output .= write_error(_CAPTCHAFAIL);
				else if(dbnumrows($result) > 0) $output .= write_error(_PENNAMEINUSE."  "._TRYAGAIN);
				else if(dbnumrows($result2) > 0) $output .= write_error(_EMAILINUSE."  "._TRYAGAIN);
				else if(preg_match("!^[a-z0-9-_ ]{3,30}$!i", $penname)) {
					if(!$pwdsetting) {
						$charset = '23456789' . 'abcdefghijkmnpqrstuvwxyz' . 'ABCDEFGHJKLMNPQRSTUVWXYZ';
						$pass = random_string($charset, 10);
						$encryppass = md5($pass);
					}
					else {
						if($_POST['password'] != $_POST['password2']) {
							$output .=  write_error(_PASSWORDTWICE);
							$tpl->assign("output", $output);
							$tpl->printToScreen( );
							dbclose( );
							exit( );
						}
						$pass = $_POST['password2'];
						$encryppass = md5($pass);
					}
					dbquery("INSERT INTO ".substr(_AUTHORTABLE, 0, strpos(_AUTHORTABLE, "as author"))." (penname, realname, bio, email, date, password) VALUES ('".escapestring($penname)."', '".escapestring(strip_tags($_POST['realname']))."', '".strip_tags(escapestring($_POST['bio']), $allowed_tags)."', '$email', now(), '$encryppass')");
					$useruid = dbinsertid();
					if($logging) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_REGISTER, $penname, $useruid, $_SERVER['REMOTE_ADDR']))."', '".$useruid."', INET_ATON('".$_SERVER['REMOTE_ADDR']."'), 'RG', " . time() . ")");
					if(empty($siteskin)) {
						$skinquery = dbquery("SELECT skin FROM ".$settingsprefix."fanfiction_settings WHERE sitekey = '".SITEKEY."'");
						list($skin) = dbrow($skinquery);
					}
					else $skin = $siteskin;
					dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorprefs(uid, userskin, storyindex, sortby, tinyMCE) VALUES('".$useruid."', '$skin', '$displayindex', '$defaultsort', '$tinyMCE')");
/* The section adds fields from the authorfields table to the authorinfo table allowing dynamic additions to the bio/registration page */
					$fields = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorfields WHERE field_on = '1'");
					while($field = dbassoc($fields)) {
						if(!$uid) continue;
						$oldfield = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorinfo WHERE field='".$field['field_id']."' AND uid = '".$useruid."'");
						if(dbnumrows($oldfield) > 0) {
							$newinfo = isset($_POST["af_".$field['field_name']]) ? escapestring($_POST["af_".$field['field_name']]) : false;
							if(!empty($newinfo)) dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorinfo SET info='$newinfo' WHERE uid = '$useruid' AND field = '".$field['field_id']."'");
							else dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_authorinfo WHERE uid = '$useruid' AND field = '".$field['field_id']."'");
						}
						else if(!empty($_POST["af_".$field['field_name']])) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorinfo(`uid`, `info`, `field`) VALUES('$useruid', '".escapestring($_POST["af_".$field['field_name']])."', '".$field['field_id']."');");
					}
/* End dynamic fields */
					$subject = _SIGNUPSUBJECT;
					$mailtext = _SIGNUPMESSAGE._LOGIN.": $penname\n"._PASSWORD.": $pass \n\n";
					if(!$pwdsetting) $mailtext .= _SIGNUPWARNING;
					include("includes/emailer.php");
					sendemail($penname, $email, $sitename, $siteemail, $subject, $mailtext, "html");
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET newestmember = '".$useruid."', members = members + 1");
					if(defined("AUTHORPREFIX")) dbquery("UPDATE ".AUTHORPREFIX."fanfiction_stats SET newestmember = '".$useruid."', members = members + 1");
					unset($_POST['submit']);
					$output = write_message(_ACTIONSUCCESSFUL);
					define("_LOGINCHECK", true);
					include("user/login.php");
				}
				else $output .= _BADUSERNAME;
			}
		}
		else{
			 if(($_POST['password']) && ($_POST['password2'])) {
				if($_POST['password'] == $_POST['password2']) {
					$encryppassword = md5($_POST['password']);
					dbquery("UPDATE "._AUTHORTABLE." SET password='$encryppassword' WHERE uid = '$uid'");
				}
				else $output .=  write_error(_PASSWORDTWICE);
			}
			if(isset($_POST['oldpenname']) && $penname != $_POST['oldpenname']) {
				$checkresult = dbquery("SELECT * FROM "._AUTHORTABLE." WHERE penname = '".escapestring($penname)."'");
				if(dbnumrows($checkresult)) {
					$output .= write_message(_PENNAMEINUSE."  "._TRYAGAIN);
				}
				else {
					dbquery("UPDATE "._AUTHORTABLE." SET penname = '".escapestring($penname)."' WHERE uid = '$_POST[uid]'");
					if($logging) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_NEWPEN, USERPENNAME, USERUID, $_POST[oldpenname], $uid, $penname))."', '".USERUID."', INET_ATON('".$_SERVER['REMOTE_ADDR']."'), 'EB', " . time() . ")");
				}
			}
/* The section adds fields from the authorfields table to the authorinfo table allowing dynamic additions to the bio/registration page */
			$fields = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorfields WHERE field_on = '1'");
			while($field = dbassoc($fields)) {
				$uid = isset($_POST['uid']) && isNumber($_POST['uid']) ? $_POST['uid'] : false;
				if(!$uid) continue;
				$oldfield = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorinfo WHERE field='".$field['field_id']."' AND uid = '".$uid."'");
				if(dbnumrows($oldfield) > 0) {
					$newinfo = isset($_POST["af_".$field['field_name']]) ? escapestring(descript($_POST["af_".$field['field_name']])) : false;
					if(!empty($newinfo)) dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorinfo SET info='".$newinfo."' WHERE uid = '$uid' AND field = '".descript($field['field_id'])."'");
					else dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_authorinfo WHERE uid = '$uid' AND field = '".$field['field_id']."'");
				}
				else if(!empty($_POST["af_".$field['field_name']])) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorinfo(`uid`, `info`, `field`) VALUES('$uid', '".escapestring($_POST["af_".$field['field_name']])."', '".$field['field_id']."');");
			}
/* End dynamic fields */
			dbquery("UPDATE "._AUTHORTABLE." SET realname='".descript(strip_tags(escapestring($_POST['realname'])), $allowed_tags)."', email='$email', bio='".descript(strip_tags(escapestring($_POST['bio']), $allowed_tags))."', image='".($imageupload && !empty($_POST['image']) ? escapestring($_POST['image']) : "")."' WHERE uid = '$uid'");
			$output .= write_message(_ACTIONSUCCESSFUL."  ".(isset($_GET['uid']) ? _BACK2ADMIN : _BACK2ACCT));
		}
	}
	else {
		if($action != "register") {
			$result = dbquery("SELECT * FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
			$user = dbassoc($result);
			$result2 = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorinfo WHERE uid = '$uid'");
			while($field = dbassoc($result2)) {
				$user["af_".$field['field']] = $field['info'];
			}
		}
		if($action == "register") {
			$query = dbquery("SELECT message_text FROM ".TABLEPREFIX."fanfiction_messages WHERE message_name = 'tos'");
			list($tos) = dbrow($query);
			$output .= "<div class='tblborder' style='width: 90%; margin: 1em auto;'>$tos</div>";
		}
		$output .= "<div id='settingsform'><form method=\"POST\" id=\"editbio\" name=\"editbio\" enctype=\"multipart/form-data\" style='margin: 0 auto;' action=\"user.php?action=$action".($uid != USERUID ? "&uid=".$uid : "")."\">
		<div><label for='newpenname'>"._PENNAME.":</label>";
		if((isADMIN && uLEVEL == 1) || $action == "register")
			$output .= "<INPUT name=\"newpenname\" type=\"text\" class=\"textbox\" maxlength=\"200\" value=\"".(isset($user) ? $user['penname'] : "")."\"><INPUT name=\"oldpenname\" type=\"hidden\" value=\"".(isset($user) ? $user['penname'] : "")."\"><font color=\"red\">*</font> ";
		else if(isset($user)) $output .= " ".$user['penname'];
		$output .= "</div>
	 	<div><label for='realname'>"._REALNAME.": </label><INPUT type=\"text\" class=\"textbox=\" name=\"realname\" maxlength=\"200\" value=\"".(isset($user) ? $user['realname'] : "")."\"></div>
	 	<div><label for='email'>"._EMAIL.":</label><INPUT  type=\"text\" class=\"textbox=\" name=\"email\" value=\"".(isset($user) ? $user['email'] : "")."\" maxlength=\"200\" size=\"35\"><font color=\"red\">*</font></div>
	 	<div><label for='bio'>"._BIO.":</label></div>
		<div style='width: 450px; margin: 0 auto;'><textarea class=\"textbox\" name=\"bio\" cols=\"50\" rows=\"6\">".(isset($user) ? stripslashes($user['bio']) : "")."</TEXTAREA></div>";
/* The section adds fields to the form from the authorfields table to the authorinfo table allowing dynamic additions to the bio/registration page */
		$authorfields = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorfields WHERE field_on = '1'");
		while($field = dbassoc($authorfields)) {
			if($field['field_type'] == 1 || $field['field_type'] == 4 || $field['field_type'] == 6) 
				$output .= "<div><label for='".$field['field_name']."'>".$field['field_title'].":</label>\n<input type='text' class='textbox' name='af_".$field['field_name']."'".(!empty($user["af_".$field['field_id']]) ? "value='".$user["af_".$field['field_id']]."'" : "").">\n</div>\n";
			if($field['field_type'] == 2) {
				$output .= "<div><label for='".$field['field_name']."'>".$field['field_title'].":</label>\n
						<select class='textbox' name='af_".$field['field_name']."'>\n";
				$opts = explode("|#|", $field['field_options']);
				foreach($opts as $opt) {
					$output .= "<option".(!empty($user["af_".$field['field_id']]) && $user["af_".$field['field_id']] == $opt ? " selected" : "").">$opt</option>\n";
				}
				$output .= "</select>\n</div>\n";
			}
			if($field['field_type'] == 5) eval(stripslashes($field['field_code_in']));
			if($field['field_type'] == 3) {
				$output .= "<div class='fieldset'><span class='label'>".$field['field_title'].":</span>\n";
				$output .= "<input type='radio' name='af_".$field['field_name']."' id='af_".$field['field_name']._YES."' value='"._YES."'".(!empty($user["af_".$field['field_id']]) && $user["af_".$field['field_id']] == _YES ? "checked='checked'" : "")."> <label for='".$field['field_name']._YES."'>"._YES."</label>\n
					<input type='radio' name='af_".$field['field_name']."' id='af_".$field['field_name']._NO."' value='"._NO."'".(!empty($user["af_".$field['field_id']]) && $user["af_".$field['field_id']] == _NO ? "checked='checked'" : "")."> <label for='".$field['field_name']._NO."'>"._NO."</label></div>\n";
			}
		}
/* End dynamic fields */
	 	if($imageupload == "1")
	 		$output .= "<div><label for='image'>"._IMAGE.":</label> <INPUT  type=\"text\" class=\"textbox=\" name=\"image\" maxlength=\"200\" value=\"".(!empty($user['image']) ? $user['image'] : "")."\"></div>";
		if($action != "register" || $pwdsetting)
	 	$output .= "<div><label for='password'>"._PASSWORD.":</label>  <INPUT name=\"password\" class=\"textbox\" value=\"\" type=\"password\">".($action == "register" ? "<font color=\"red\">*</font>" : "")."</div>
			<div><label for='password2'>"._PASSWORD2.":</label> <INPUT name=\"password2\" class=\"textbox=\" value=\"\" type=\"password\">".($action == "register" ? "<font color=\"red\">*</font>" : "")."</div>";
		if(!empty($captcha) && $action == "register") $output .= "<div><label for='userdigit'>"._CAPTCHANOTE."</label><input MAXLENGTH=5 SIZE=5 name=\"userdigit\" type=\"text\" value=\"\"><div style='text-align: center;'><img width=120 height=30 src=\""._BASEDIR."includes/button.php\" style=\"border: 1px solid #111;\"></div></div>";
	 	$output .= "<div style='text-align: center; margin: 1em;'><INPUT type=\"hidden\" name=\"uid\" value=\"".(isset($user) ? $user['uid'] : "")."\"><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\">";
	 	if(!isADMIN && $action != "register")
	 	{
			 	$output .= " [<a href=\"admin.php?action=members&delete=$uid\">"._DELETE."</a>]";
	 	}
	 	$output .= "</div></form></div>".write_message("<font color=\"red\">*</font> "._REQUIREDFIELDS);
	}
?>