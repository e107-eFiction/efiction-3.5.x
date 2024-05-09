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

	if(isset($_POST['submit']) && preg_match("!^[-a-z0-9_ ]{3,30}$!i", $_POST['penname'])) {
		if(!defined("_LOGINCHECK")) exit( );
		define("_BASEDIR", "");
		include_once("config.php");
		include(_BASEDIR . "includes/dbfunctions.php");
		$settings = dbquery("SELECT tableprefix, maintenance, sitekey, debug FROM ".$settingsprefix."fanfiction_settings WHERE sitekey = '".$sitekey."'");
		list($tableprefix, $maintenance, $sitekey, $debug) = dbrow($settings);
		$tempdebug = $debug;
		$debug = 0;
		define("TABLEPREFIX", $tableprefix);
		define("SITEKEY", $sitekey);
		include_once("includes/queries.php");
		$result = dbquery("SELECT *, "._UIDFIELD." as uid FROM "._AUTHORTABLE." LEFT JOIN ".$tableprefix."fanfiction_authorprefs AS ap ON ap.uid = "._UIDFIELD." WHERE "._PENNAMEFIELD." = '".$_POST['penname']."'");
		$passwd = dbassoc($result);
		if(!dbnumrows($result)) {
			require_once("header.php");
			//make a new TemplatePower object
			if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
			else $tpl = new TemplatePower("default_tpls/default.tpl");
			include_once("includes/pagesetup.php");
			$output = write_error(_NOSUCHACCOUNT);
			$tpl->assign("output", $output);
			$tpl->printToScreen( );
			dbclose( );
			exit( );
		}
		if($maintenance && $passwd['level'] < 0) {
			header("Location: maintenance.php");
			exit( );
		}
		$encryptedpassword = md5($_POST['password']);
		if($passwd['level'] == -1) {
			require_once("header.php");
			//make a new TemplatePower object
			if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
			else $tpl = new TemplatePower("default_tpls/default.tpl");
			include_once("includes/pagesetup.php");
			$output = write_error(_ACCOUNTLOCKED);
			$tpl->assign("output", $output);
			$tpl->printToScreen( );
			dbclose( );
			exit( );
		}
		if($passwd['password'] == $encryptedpassword) {
			if(isset($_POST['cookiecheck'])) {
				setcookie($sitekey."_useruid",$passwd['uid'], time()+60*60*24*30, "/");
				setcookie($sitekey."_salt", md5($passwd['email'] . $encryptedpassword),  time()+60*60*24*30, "/");
			}
			if(!isset($_SESSION)) session_start( );
			$_SESSION[$sitekey."_useruid"] = $passwd['uid'];
			$_SESSION[$sitekey."_salt"] = md5($passwd['email'] . $encryptedpassword);
			$logincode = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'login'");
			while($code = dbassoc($logincode)) {
				eval($code['code_text']);
			}
		}

		else { 
			require_once("header.php");
			//make a new TemplatePower object
			if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
			else $tpl = new TemplatePower("default_tpls/default.tpl");
			include_once("includes/pagesetup.php");
			$output .= "<div id=\"pagetitle\">"._MEMBERLOGIN."</div>";
			$output .= "<div style='text-align: center;'>"._WRONGPASSWORD."</div>";
			$tpl->assign("output", $output);
			$tpl->printToScreen( );
			dbclose( );
			exit( );
		}
		$debug = $tempdebug;
	}
	else {
		require_once("header.php");
		if(!isMEMBER) {
		
		$output .= "<div id=\"pagetitle\">"._MEMBERLOGIN."</div>";
		$output .= "<div style=\"width: 250px; margin: 0 auto; text-align: center;\"><form method=\"POST\" enctype=\"multipart/form-data\" action=\"user.php?action=login".(isset($_GET['sid']) && isNumber($_GET['sid']) ? "&amp;sid=".$_GET['sid'] : "")."\">
		<div class=\"label\" style=\"float: left;  width: 30%; text-align: right;\"><label for=\"penname\">"._PENNAME.":</label></div><INPUT type=\"text\" class=\"textbox\" name=\"penname\" id=\"penname\"><br />
		<div class=\"label\" style=\"float: left; width: 30%; text-align: right;\"><label for=\"pswd\">"._PASSWORD.":</label></div><INPUT type=\"password\" class=\"textbox\" id=\"pswd\" name=\"password\"><br />
		<INPUT type=\"checkbox\" class=\"checkbox\" name=\"cookiecheck\" id=\"cookiecheck\" value=\"1\"><label for=\"cookiecheck\">"._REMEMBERME."</label><br />
		<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\">
		</form></div>";
		$linkquery = dbquery("SELECT * from ".TABLEPREFIX."fanfiction_pagelinks WHERE link_name = 'login' OR link_name = 'lostpassword'");
		while($link = dbassoc($linkquery)) {
			if($link['link_access'] && !isMEMBER) continue;
			if($link['link_access'] == 2 && !isADMIN) continue;
			$pagelinks[$link['link_name']] = array("id" => $link['link_id'], "text" => $link['link_text'], "url" => _BASEDIR.$link['link_url'], "link" => "<a href=\"".$link['link_url']."\" title=\"".$link['link_text']."\"".($link['link_target'] ? " target=\"_blank\"" : "").($current == $link['link_name'] ? " id=\"current\"" : "").">".$link['link_text']."</a>");
		}
		$output .= "<div style='text-align: center;'>";
		if(isset($pagelinks['register'])) {
			$output .= $pagelinks['register']['link']." | ";
		}
		$output .= $pagelinks['lostpassword']['link']."</div>";
		 
	}
}
?>
