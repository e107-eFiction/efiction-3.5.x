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

// Build the user's profile information
$tpl->newBlock("profile");
$result2 = dbquery("SELECT *, date as date FROM "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs as ap ON ap.uid = "._UIDFIELD." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
$userinfo = dbassoc($result2);
$nameinfo = "";
if($userinfo['email'])
	$nameinfo .= " [<a href=\"viewuser.php?action=contact&amp;uid=".$userinfo['uid']."\">"._CONTACT."</a>]";
if(!empty($favorites) && isMEMBER && $userinfo['uid'] != USERUID) {
	$fav = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND type = 'AU' AND item = '".$userinfo['uid']."'");
	if(dbnumrows($fav) == 0) $nameinfo .= " [<a href=\"user.php?action=favau&amp;uid=".USERUID."&amp;add=".$userinfo['uid']."\">"._ADDAUTHOR2FAVES."</a>]";
}
 
$tpl->assign("userpenname", $userinfo['penname']." ".$nameinfo);

if($userinfo['realname'])
	$tpl->assign("realname", $userinfo['realname']);
if($userinfo['bio']) {
	$bio = nl2br($userinfo['bio']);	
	$tpl->assign("bio", stripslashes($bio));
}
if($userinfo['image'])
	$tpl->assign("image", "<img src=\"".$userinfo['image']."\">");

/* don't display member if it was added by admin - release them rather */
if(isset($userinfo['admicreated']) && $userinfo['admicreated'] == 0) {
	$tpl->assign("membersince", date("$dateformat", $userinfo['date']));
	$tpl->assign("userlevel", isset($userinfo['level']) && $userinfo['level'] > 0 && $userinfo['level'] < 4 ? _ADMINISTRATOR.(isADMIN ? " - ".$userinfo['level'] : "") : _MEMBER);
}
/* Dynamic authorinfo fields */
$result2 = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorinfo WHERE uid = '$uid'");
$dynamicfields = "";
while($field = dbassoc($result2)) {
	if($field['info'] == "") continue;
	$fieldinfo = dbassoc(dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorfields WHERE field_id = '".$field['field']."' LIMIT 1"));
	if($fieldinfo) {
		$thisfield = "";
		if($fieldinfo['field_on'] == 0) continue;
		if($fieldinfo['field_type'] == 1) { $thisfield = format_link($field['info']);
		}
		if($fieldinfo['field_type'] == 4) {
			$thisfield = preg_replace("@\{info\}@", $field['info'], $fieldinfo['field_options']);
			$thisfield = format_link($thisfield);
		}
		if($fieldinfo['field_type'] == 2 || $fieldinfo['field_type'] == 6) {
			$thisfield = stripslashes($field['info']);
		}
		if($fieldinfo['field_type'] == 3) {
			$thisfield = $field['info'];
		}
		else eval($fieldinfo['field_code_out']);
		$tpl->assign($fieldinfo['field_name'], $thisfield);
		$dynamicfields .= "<div class='authorfields'><span class='label'>".$fieldinfo['field_title'].":</span> ".$thisfield."</div>";
	}
}
$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'userprofile'");
while($code = dbassoc($codequery)) {
	eval($code['code_text']);
}
if(!empty($dynamicfields)) $tpl->assign("authorfields", $dynamicfields);
/* End dynamic fields */
$tpl->assign("reportthis", "[<a href=\""._BASEDIR."contact.php?action=report&amp;url=viewuser.php?uid=".$uid."\">"._REPORTTHIS."</a>]");
$adminopts = "";
if(isADMIN && uLEVEL < 3) {
	$adminopts .= "<div class=\"adminoptions\"><span class='label'>"._ADMINOPTIONS.":</span> ".(isset($userinfo['validated']) && $userinfo['validated'] ? "[<a href=\"admin.php?action=members&amp;revoke=$uid\" class=\"vuadmin\">"._REVOKEVAL."</a>] " : "[<a href=\"admin.php?action=members&amp;validate=$uid\" class=\"vuadmin\">"._VALIDATE."</a>] ")."[<a href=\"user.php?action=editbio&amp;uid=$uid\" class=\"vuadmin\">"._EDIT."</a>] [<a href=\"admin.php?action=members&amp;delete=$uid\" class=\"vuadmin\">"._DELETE."</a>]";
	$adminopts .= " [<a href=\"admin.php?action=members&amp;".($userinfo['level'] < 0 ? "unlock=".$userinfo['uid']."\" class=\"vuadmin\">"._UNLOCKMEM : "lock=".$userinfo['uid']."\" class=\"vuadmin\">"._LOCKMEM)."</a>]";
	$adminopts .= " [<a href=\"admin.php?action=admins&amp;".(isset($userinfo['level']) && $userinfo['level'] > 0 ? "revoke=$uid\" class=\"vuadmin\">"._REVOKEADMIN."</a>] [<a href=\"admin.php?action=admins&amp;do=edit&amp;uid=$uid\" class=\"vuadmin\">"._EDITADMIN : "do=new&amp;uid=$uid\" class=\"vuadmin\">"._MAKEADMIN)."</a>]</div>";
	$tpl->assign("adminoptions", $adminopts);
}
$tpl->gotoBlock("_ROOT");
?>
