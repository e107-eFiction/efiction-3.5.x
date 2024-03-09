<?php
// ----------------------------------------------------------------------
// eFiction 3.0
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

	if(isset($_GET['do'])) $do = $_GET["do"];
	else $do = false;
	if(isset($_GET["revoke"])) {
		if(isset($_GET["confirm"])) {
			$output .= "<div id='pagetitle'>"._REVOKEADMIN."</div>";
			if($_GET["confirm"] == "yes" && USERUID != $_GET['revoke']) {
				$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET level = '0' WHERE uid = '".$_GET['revoke']."'");
				if($result) $output .=  write_message(_ACTIONSUCCESSFUL);
				else {
					$result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorprefs(`level`, `uid`) VALUES ('0', '".$_GET['revoke']."')"); // shouldn't be possible, but better safe than sorry.
					$output .= write_error(_ERROR." "._TRYAGAIN);
				}
			}
			else 
			$output .= write_message(_ACTIONCANCELLED);
		}
		else {
			$output .= write_message(_CONFIRMADMINREVOKE." [ <a href=\"admin.php?action=admins&confirm=yes&revoke=".$_GET['revoke']."\">"._YES."</a> | <a href=\"admin.php?action=admins&confirm=no&revoke=".$_GET['revoke']."\">"._NO."</a> ]</center>");
		}
	}
	else if($do) {
		if(isset($_POST['submit'])) {
			$contact = (isset($_POST['contact']) && $_POST['contact'] == "on" ? "1" : "0");
			if($_POST['catid']) $categories = $_POST['catid'];
			else $categories = "0";
			if(check_prefs($_POST['uid'])) $result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET level = '".$_POST['adlevel']."', contact = '$contact', categories = '$categories' WHERE uid = '".$_POST['uid']."'");
			else $result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorprefs(`level`, `contact`, `categories`,  `uid`) VALUES ('".$_POST['adlevel']."', '$contact', '$categories', '".$_POST['uid']."')");
			if($result) $output .= write_message(_ACTIONSUCCESSFUL._BACK2ADMIN);
			else $output .= write_message(_ERROR);
		}
		else {
			if($do == "edit") {
				$uid = $_GET['uid'];
				$info = dbquery("SELECT "._PENNAMEFIELD." as penname, level, categories, contact FROM "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs as ap ON ap.uid = "._UIDFIELD." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
				list($penname, $adlevel, $adcategories, $adcontact) = dbrow($info);
				$catid = explode(",", $adcategories);
			}
			$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : false;
			if(!isNumber($uid)) $uid = false;
			$output .= "<div id=\"pagetitle\">".($do == "edit" ? _EDITADMIN : _ADDADMIN)."</div>
				<form method=\"POST\" name=\"form\" enctype=\"multipart/form-data\" action=\"admin.php?action=admins&do=$do\">
				<div><label for=\"uid\">"._PENNAME.": </label>";
			if($do == "new") {
				$query = dbquery("SELECT "._PENNAMEFIELD." AS penname, "._UIDFIELD." AS uid, ap.level FROM "._AUTHORTABLE." LEFT  JOIN ".TABLEPREFIX."fanfiction_authorprefs AS ap ON "._UIDFIELD." = ap.uid ORDER  BY "._PENNAMEFIELD);
				$output .= "<select name=\"uid\">";
				while($users = dbassoc($query)) {
					if(!$users['level']) $output .= "<option value=\"".$users['uid']."\"".($uid == $users['uid'] ? " selected" : "").">".$users['penname']."</option>";
				}	
				$output .= "</select></div>";
			}
			else if($do == "edit" && !empty($uid)) {
				$output .= $penname."<input type=\"hidden\" name=\"uid\" value=\"$uid\"></div>";
			}
			$output .= "<div><label for=\"adlevel\">"._LEVEL.":</label>  <select name=\"adlevel\">";
				for($x = 1; $x < 5; $x++) {
					$output .= "<option value=\"$x\"".(isset($adlevel) && $adlevel == $x ? " selected" : "").">$x</option>";
				}
			$output .= "</select><A HREF=\"#adlevel\" class=\"pophelp\">[?]<span>"._HELP_ADLEVEL."</span></A></div>";
			include("includes/categories.php");
			$output .= (isset($adcategories) ? "<input type=\"hidden\" name=\"oldcats\" value=\"$adcategories\">" : "")."
				<div><label for=\"contact\">"._CONTACT.":</label>
				<INPUT type=\"checkbox\" class=\"checkbox\" name=\"contact\"".(isset($adcontact) && $adcontact == 1 ? " checked" : "").">
				<A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_CONTACTSUB."</span></A></div>
				<INPUT type=\"submit\" class=\"button\" id=\"submit\" value=\""._SUBMIT."\" name=\"submit\">
				</form>";
		}
	}
	else if(isset($_GET['category'])) {
		$where = "WHERE (categories = '0' OR FIND_IN_SET('".$_GET['category']."', categories) > 0) AND ap.level > 0";
		list($category) = dbrow(dbquery("SELECT category FROM ".TABLEPREFIX."fanfiction_categories WHERE catid = '".$_GET['category']."' LIMIT 1"));
		$output .= "<center><p><b>$category "._ADMINS."</b></p>";
		$authorlink = "<a href=\"admin.php?action=admins&amp;do=edit&amp;uid=";
		$pagelink = "admin.php?action=admin&amp;category=".$_GET['category']."&amp;";
		$countquery = "SELECT count(uid) FROM ".TABLEPREFIX."fanfiction_authorprefs as ap $where";
		$authorquery = "SELECT count(stories.sid) as stories, "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM ".TABLEPREFIX."fanfiction_authorprefs as ap, "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_stories as stories ON ("._UIDFIELD." = stories.uid) AND "._UIDFIELD." > 0 AND stories.validated > 0 $where AND ap.level > 0 ".(isset($letter) ? " AND $letter" : "")." AND ap.uid = "._UIDFIELD." GROUP BY "._UIDFIELD;
		include("includes/members_list.php");
		$output .= "<p><a href=\"admin.php?action=admins&do=new&category=".$_GET['category']."\">"._ADDADMIN."</a></p>";
	}
	else {

		if($let == _OTHER) $letter = _PENNAMEFIELD." REGEXP '^[^a-z]'";
		else $letter = _PENNAMEFIELD." LIKE '".$let."%'";
		$output .= "<div class=\"sectionheader\">"._ADMINS."</div>";
		$authorlink = "<a href=\"admin.php?action=admins&amp;do=edit&amp;uid=";
		$pagelink = "admin.php?action=admins&amp;";
		$countquery = "SELECT count(uid) FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE level > 0";
		$authorquery = "SELECT "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid, count(stories.uid) as stories FROM (".TABLEPREFIX."fanfiction_authorprefs as ap, "._AUTHORTABLE.") LEFT JOIN ".TABLEPREFIX."fanfiction_stories as stories ON ("._UIDFIELD." = stories.uid) AND stories.validated > 0 WHERE ap.level > 0 ".(isset($letter) ? " AND $letter" : "")." AND ap.uid = "._UIDFIELD." GROUP BY "._UIDFIELD;
		include("includes/members_list.php");
		$output .= write_message("<a href=\"admin.php?action=admins&do=new\">"._ADDADMIN."</a>");
	}

?>