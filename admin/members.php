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

function random_char($string)
{
	$length = strlen($string);
	$position = mt_rand(0, $length - 1);
	return($string[$position]);
}

function random_string ($charset_string, $length)
{
	$return_string = random_char($charset_string);
	for ($x = 1; $x < $length; $x++)
	$return_string .= random_char($charset_string);
	return($return_string);
}

unset($countquery, $authorquery);
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
	if(isset($_GET['lock']) && isNumber($_GET['lock'])) {
		$output .= "<div class='sectionheader'>"._LOCKMEM."</div>";
		if($confirm == "yes") {
			if(check_prefs($_GET['lock'])) dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET level = '-1' WHERE uid = '".$_GET['lock']."'");
			else dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorprefs(`uid`, `level`) VALUES('".$_GET['lock']."', '-1')");
			$output .= write_message(_ACTIONSUCCESSFUL);
		}
		else if ($confirm == "no") {
			$output .= write_message(_ACTIONCANCELLED);
		}
		else {
			$output .= write_message(_LOCKCONFIRM."<br /><br />
						[ <a href=\"admin.php?action=members&confirm=yes&lock=".$_GET['lock']."\">"._YES."</a> | <a href=\"admin.php?action=members&confirm=no&lock=".$_GET['lock']."\">"._NO."</a> ]");
		}
	}
	else if(isset($_GET["unlock"]) && isNumber($_GET["unlock"])) {
		$output .= "<div class='sectionheader'>"._UNLOCK."</div>";
		if($confirm == "yes")
		{
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET level = '0' WHERE uid = '".$_GET['unlock']."'");
			$output .= write_message(_ACTIONSUCCESSFUL);
		}
		else if ($confirm == "no")
		{
			$output .= write_message(_ACTIONCANCELLED);
		}
		else
		{
			$output .= write_message(_CONFIRMUNLOCK."<br /><br />
						[ <a href=\"admin.php?action=members&confirm=yes&unlock=".$_GET['unlock']."\">"._YES."</a> | <a href=\"admin.php?action=members&confirm=no&unlock=".$_GET['unlock']."\">"._NO."</a> ]");
		}
	
	}
	else if(isset($_GET["release"]) && isNumber($_GET['release'])) {
		$output .= "<div class='sectionheader'>"._RELEASED."</div>";
		if(_AUTHORTABLE != TABLEPREFIX."fanfiction_authors as author") $output .= write_message("<br />"._FOREIGNAUTHORTABLE);
		if($confirm == "yes")
		{
			include("includes/emailer.php");
			dbquery("UPDATE "._AUTHORTABLE." SET admincreated = '0' WHERE "._UIDFIELD." = '".$_GET['release']."'");
			$emailquery = dbquery("SELECT "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE uid = '".$_GET['release']."'");
			$email = dbassoc($emailquery);
			mt_srand((double)microtime() * 1000000);
			$charset = '23456789' . 'abcdefghijkmnpqrstuvwxyz' . 'ABCDEFGHJKLMNPQRSTUVWXYZ';
			$pass = random_string($charset, 10);
			$encryppass = md5($pass);
			//$headers = "From: $sitename\n";
			dbquery("UPDATE "._AUTHORTABLE." SET "._PASSWORDFIELD." = '$encryppass' WHERE uid = '".$_GET['release']."'");
			
			$subject = _SIGNUPSUBJECT;
			
			$letter = sprintf(_RELEASEMESSAGE, $email['penname'], $pass);

			$mailresult = sendemail($email['penname'], $email['email'], $sitename, $siteemail, $subject, $letter);
			if($mailresult) $output .= write_message(_AUTHORRELEASED);
			else $output .= write_error(_EMAILFAILED);
		}
		else if ($confirm == "no")
		{
			$output .= write_message(_ACTIONCANCELLED);
		}
		else {
			$output .= write_message(_CONFIRMAUTHORRELEASE."<br /><br />
						[ <a href=\"admin.php?action=members&confirm=yes&release=$_GET[release]\">"._YES."</a> | <a href=\"admin.php?action=members&confirm=no&release=$_GET[release]\">"._NO."</a> ]");
		}
	}	
	else if(isset($_GET["revoke"]) && isNumber($_GET["revoke"])) {
		$output .= "<div class='sectionheader'>"._REVOKEVAL."</div>";
		if($confirm == "yes")
		{
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET validated = '0' WHERE uid = '$_GET[revoke]'");
			$output .= write_message(_ACTIONSUCCESSFUL);
		}
		else if ($confirm == "no")
		{
			$output .= write_message(_ACTIONCANCELLED);
		}
		else
		{
			$output .= write_message(_CONFIRMVALREVOKE."<br /><br />
						[ <a href=\"admin.php?action=members&amp;confirm=yes&amp;revoke=$_GET[revoke]\">"._YES."</a> | <a href=\"admin.php?action=members&amp;confirm=no&amp;revoke=$_GET[revoke]\">"._NO."</a> ]");
		}
	}
	else if(isset($_GET["delete"]) && isNumber($_GET["delete"])) {
		$output .= "<div class='sectionheader'>"._DELETEUSER."</div>";
		if(_AUTHORTABLE != TABLEPREFIX."fanfiction_authors as author") $output .= write_message("<br />"._FOREIGNAUTHORTABLE);
		else if($confirm == "yes") {
			include("includes/deletefunctions.php");
			$output .= deleteUser($_GET['delete']);
		}
		else if ($confirm == "no") {
			$output .= write_message(_ACTIONCANCELLED);
		}
		else {
			$output .= write_message(_CONFIRMDELETE."<br /><br />
						[ <a href=\"admin.php?action=members&confirm=yes&delete=$_GET[delete]\">"._YES."</a> | <a href=\"admin.php?action=members&confirm=no&delete=$_GET[delete]\">"._NO."</a> ]");
		}
	}
	else if(isset($_GET["validate"]) && isNumber($_GET["validate"])) {
		$output .= "<div class='sectionheader'>"._NONVALMEMBERS."</div>";
		if($confirm == "yes") {
			if(check_prefs($_GET['validate'])) dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET validated = '1' WHERE uid = '".$_GET['validate']."'");
			else dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorprefs(`uid`, `validated`) VALUES('".$_GET['validate']."', '1')");
			$output .= write_message(_ACTIONSUCCESSFUL);
		}
		else if ($confirm == "no") {
			$output .= write_message(_ACTIONCANCELLED);
		}
		else {
			$output .= write_message(_CONFIRMVALIDATE."<br /><br />
						[ <a href=\"admin.php?action=members&amp;confirm=yes&amp;validate=".$_GET['validate']."\">"._YES."</a> | <a href=\"admin.php?action=members&confirm=no&amp;validate=".$_GET['validate']."\">"._NO."</a> ]");
		}
	}
	else if(isset($_GET["do"]) && $_GET['do'] == "add") {
		if(_AUTHORTABLE != TABLEPREFIX."fanfiction_authors as author") $output .= write_message("<br />"._FOREIGNAUTHORTABLE);
		$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=members\"><table align=\"center\"><tr><td colspan=\"2\">
			<div style='text-align: center; font-weight: bold;'>"._ADDAUTHOR." <A HREF=\"javascript:n_window('docs/adminhints.htm#addauthor');\">[?]</A></div></td></tr>
			<tr><td><label for=\"penname\">"._PENNAME.":</label></td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"penname\"></td></tr>
			<tr><td><label for=\"realname\">"._REALNAME.":</label></td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"realname\"></td></tr>
			<tr><td><label for=\"email\">"._EMAIL.":</label></td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"email\"></td></tr>
			<tr><td colspan=\"2\"><INPUT type=\"submit\" class=\"button\" value=\""._SUBMIT."\" name=\"submit\"></form></td></tr></table>";
		}
		else if (isset($_POST['submit'])) {
			if(preg_match("!^[a-z0-9_ ]{3,30}$!i", $_POST['penname'])) {
				dbquery("INSERT INTO ".substr(_AUTHORTABLE, 0, strpos(_AUTHORTABLE, "as author"))."(penname, realname, email, admincreated, date) VALUES ('".escapestring(descript($_POST['penname']))."', '".escapestring(descript($_POST['realname']))."', '".escapestring(descript($_POST['email']))."', '1', now())");
				$newuid = dbinsertid( );
				if($logging) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_ADMIN_REG, descript($_POST['penname']), $newuid, USERPENNAME, USERUID, $_SERVER['REMOTE_ADDR']))."', '".USERUID. "', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'RG', " . time() . ")");
				if(!$skin) {
					$skinquery = dbquery("SELECT skin FROM ".$settingsprefix."fanfiction_settings WHERE sitekey ='".SITEKEY."'");
					list($skin) = dbrow($skinquery);
				}
				dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorprefs(uid, userskin, storyindex, sortby) VALUES('$newuid', '$skin', '$displayindex', '$defaultsort')");
				
				$output .= write_message(_ACTIONSUCCESSFUL);
			}
		else $output .= write_error(_BADUSERNAME);
	}
	else {
		$output .= "<div class='sectionheader'>";
		if(isset($_GET['list'])) $list = $_GET['list'];
		else $list = false;
		$where = "";

		if($list  == "admincreated") {
			if(_AUTHORTABLE != TABLEPREFIX."fanfiction_authors as author") {
				$fieldtest = dbquery("SHOW COLUMNS FROM ".substr(_AUTHORTABLE, 0, strlen(_AUTHORTABLE) - 9)." LIKE 'admincreated'");
			}
			if(isset($fieldtest) && !dbnumrows($fieldtest)) {
				$where = "";
				$output .= write_message(_FUNCTIONDISABLED);
				$tpl->assign("output", $output);
				$tpl->printToScreen( );
				dbclose( );
				exit();
			}
			else {
				$where = " WHERE author.admincreated = '1'";
				$do = "release";
				$output .= _INPUTBYADMIN;
				$message = write_message(_RELEASEAUTHORS);
			}
		}
		else if($list == "admins") {
			$where = " WHERE ap.level > 0";
			$do = false;
			$authorlink = "<a href=\"admin.php?action=admins&amp;do=edit&amp;uid=";
			$output .= _ADMINS;
		}
		else if($list == "noval") {
			$where = " WHERE  ap.validated IS NULL OR ap.validated = '0'";
			$do = "validate";
			$output .= _NONVALMEMBERS;
		}
		else if($list == "validated") {
			$where = " WHERE ap.validated = '1'";
			$do = "revoke";
			$output .= _VALMEMBERS;
		}
		else if($list == "locked") {
			$where = " WHERE ap.level = -1";
			$do = "unlock";
			$output .= _LOCKMEM;
		}
		else if($list == "unlocked") {
			$where = " WHERE (ap.level IS NULL OR ap.level > -1) ";
			$do = "lock";
			$output .= _UNLOCKMEMBERS;
			$message = write_message(_LOCKMEMBERS);
		}
		else if($list == "authors") {
			$where = " WHERE ap.stories > 0";
			$authorlink = "<a href=\"user.php?action=editbio&amp;uid=";
			$output .= _AUTHORS;
		}
		else {
			$where = "";
			$do = "members";
			$authorlink = "<a href=\"user.php?action=editbio&amp;uid=";
			$output .= _MEMBERS;
		}
		if($let == _OTHER) {
			$where .= (empty($where) ? " WHERE " : " AND ")._PENNAMEFIELD." REGEXP '^[^a-z]'";
			$output .= " - "._OTHER;
		}
		else if($let){
			$where .= (empty($where) ? " WHERE " : " AND ")._PENNAMEFIELD." LIKE '$let%'";
			$output .= " - $let";
		}
		$list = $list;
		if(!$list) $list = "members";
		$output .= "</div>";
		if(!isset($authorlink)) $authorlink = "<a href=\"admin.php?action=members&amp;do=list&amp;$do=";
		if(!isset($countquery)) $countquery = _MEMBERCOUNT." $where";
		if(!isset($authorquery)) $authorquery = _MEMBERLIST." $where GROUP BY "._UIDFIELD;
		$output .= "<p align=\"center\">
			<a href=\"admin.php?action=members\">"._ALLMEMBERS."</a> | <a href=\"admin.php?action=members&amp;do=list&amp;list=admins\">"._ADMINS."</a> |  <a href=\"admin.php?action=members&amp;do=list&amp;list=authors\">"._AUTHORS."</a> | <a href=\"admin.php?action=members&amp;do=list&amp;list=admincreated\">"._INPUTBYADMIN."</a> <br />
			<a href=\"admin.php?action=members&amp;do=list&amp;list=noval\">"._NONVALMEMBERS."</a> | <a href=\"admin.php?action=members&amp;do=list&amp;list=validated\">"._VALMEMBERS."</a> | <a href=\"admin.php?action=members&amp;do=list&amp;list=locked\">"._LOCKMEMLIST."</a> | <a href=\"admin.php?action=members&amp;do=list&amp;list=unlocked\">"._UNLOCKMEMBERS."</a></p>";
		$pagelink = "admin.php?action=members&amp;do=list&amp;list=$list&amp;".($let ? "let=$let&amp;" :"");

		include("includes/members_list.php");
		if(_AUTHORTABLE != TABLEPREFIX."fanfiction_authors as author") {
			$fieldtest = dbquery("SHOW COLUMNS FROM ".substr(_AUTHORTABLE, 0, strlen(_AUTHORTABLE) - 9)." LIKE 'admincreated'");
			if(dbnumrows($fieldtest)) $output .= write_message("<a href=\"admin.php?action=members&do=add\">"._ADDAUTHOR."</a>");
		}
		else $output .= write_message("<a href=\"admin.php?action=members&do=add\">"._ADDAUTHOR."</a>");
		if(isset($message)) $output .= $message;
	}
?>
