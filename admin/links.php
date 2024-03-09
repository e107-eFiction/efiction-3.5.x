<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
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

	$output .= "<div id=\"pagetitle\">"._LINKS."</div>";
if(isset($_GET['delete']) && isNumber($_GET['delete'])) {
	$result = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_pagelinks WHERE link_id = $_GET[delete] LIMIT 1");
	if($result) $output .= write_message(_ACTIONSUCCESSFUL);
	else $output .= write_error(_ERROR);
}
if(isset($_POST['submit'])) {
	if(!preg_match("!^[a-z0-9_]{3,30}$!i", $_POST['link_name'])) $output .= write_error(_BADVARNAME);
	else {
		if(isNumber($_POST['link_access'])) $link_access = $_POST['link_access'];
		else $link_access = 0;
		$link_name = descript($_POST['link_name']);
		$link_text = descript($_POST['link_text']);
		$link_url = descript(strip_tags($_POST['link_url']));
		$link_target = descript($_POST['link_target']);
		$link_key = strlen($_POST['link_key']) > 1 ? substr($_POST['link_key'], 0, 1) : $_POST['link_key']; // only one letter please.
		if(isset($_GET['edit']) && isNumber($_GET['edit'])) 
			$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_pagelinks SET link_name = '".escapestring($link_name)."', link_text = '".escapestring($link_text)."', link_key = '$link_key', link_url = '".escapestring($link_url)."', link_target = '$link_target', link_access = '$link_access' WHERE link_id = $_GET[edit] LIMIT 1");
		else $result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_pagelinks(`link_name`, `link_text`, `link_key`, `link_url`, `link_target`, `link_access`) VALUES('".escapestring($link_name)."', '".escapestring($link_text)."', '".escapestring($link_key)."', '".escapestring($link_url)."', '$link_target', '$link_access')");
		if($result) {
			$output .= write_message(_ACTIONSUCCESSFUL);
			$linkquery = dbquery("SELECT * from ".TABLEPREFIX."fanfiction_pagelinks ORDER BY link_access ASC");
			if(!isset($current)) $current = "";

			while($link = dbassoc($linkquery)) {
				if($link['link_access'] && !isMEMBER) continue;
				if($link['link_access'] == 2 && !isADMIN) continue;
				$tpl->assignGlobal($link['link_name'], "<a href=\""._BASEDIR.$link['link_url']."\" title=\"".$link['link_text']."\"".($link['link_target'] ? " target=\"_blank\"" : "").($current == $link['link_name'] ? " id=\"current\"" : "").">".$link['link_text']."</a>");
				$pagelinks[$link['link_name']] = array("id" => $link['link_id'], "text" => $link['link_text'], "url" => _BASEDIR.$link['link_url'], "link" => "<a href=\""._BASEDIR.$link['link_url']."\" title=\"".$link['link_text']."\"".($link['link_target'] ? " target=\"_blank\"" : "").($current == $link['link_name'] ? " id=\"current\"" : "").">".$link['link_text']."</a>");
			}
		}
		else $output .= write_error(_ERROR);
		unset($_GET['edit']);
	}
}
if(isset($_GET['edit']) || isset($_GET["new"])) {
	if(isset($_GET['edit'])) {
		$pagequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_pagelinks WHERE link_id = ".$_GET['edit']." LIMIT 1");
		$link =  dbassoc($pagequery);
		$id = $link['link_id'];
		$name = $link['link_name'];
		$text = $link['link_text'];
		$url = $link['link_url'];
		$target = $link['link_target'];
		$access = $link['link_access'];
		$edit = $_GET['edit'];
		$accesskey = $link['link_key'];
	}
	else {
		$link = "";
		$id = "";
		$name = "";
		$text = "";
		$url = "";
		$target = "";
		$link = "";
		$edit = "";
		$access = 0;
		$accesskey = "";
	}
	$output .= "<form method=\"POST\" id=\"settingsform\" enctype=\"multipart/form-data\" action=\"admin.php?action=links".($edit ? "&amp;edit=$edit" : "")."\">
	<p><label for=\"link_name\">"._NAME.":</label> <input type=\"text\" class=\"textbox\" name=\"link_name\" value=\"$name\"> <br />
	<label for=\"link_text\">"._LINKTEXT.":</label> <input type=\"text\" class=\"textbox\" name=\"link_text\" value=\"$text\"><br />
	<label for=\"link_key\">"._LINKKEY.":</label> <input type=\"text\" class=\"textbox\" name=\"link_key\" value=\"$accesskey\" size=\"1\"><br />
	<label for=\"link_url\">"._URL.":</label> <input type=\"text\" class=\"textbox\" name=\"link_url\" value=\"$url\"><br />
	<label for=\"link_target\">"._TARGET.":</label> <select name=\"link_target\" class=\"textbox\">
		<option value=\"0\"".($target ? "" : " selected").">"._SAMEWINDOW."</option>
		<option value=\"1\"".($target ? " selected" : "").">"._NEWWINDOW."</option>
	</select><br />
	<label for=\"link_access\">"._LINKACCESS.":</label> <select name=\"link_access\" class=\"textbox\">
		<option value=\"0\"".($access ? "" : " selected").">"._ALL."</option>
		<option value=\"1\"".($access == 1 ? " selected" : "").">"._MEMBERS."</option>
		<option value=\"2\"".($access == 2 ? " selected" : "").">"._ADMINS."</option>
	</select><br />
	<INPUT type=\"submit\" class=\"button\" name=\"submit\" id=\"submit\" value=\""._SUBMIT."\"></form>";
	$output .= write_message(_NAMECONVENTIONS);
}
else {
// Re-populate the list of links to reflect any changes we just made.
$linkquery = dbquery("SELECT * from ".TABLEPREFIX."fanfiction_pagelinks ORDER BY link_access ASC");
if(!isset($current)) $current = "";
unset($pagelinks);
	$output .= "<table class=\"tblborder\" style=\"margin: 0 auto;\"><tr><th class=\"tblborder\">"._LINKS."</th><th class=\"tblborder\">"._OPTIONS."</th></tr>";
while($link = dbassoc($linkquery)) {
	if($link['link_access'] && !isMEMBER) continue;
	if($link['link_access'] == 2 && !isADMIN) continue;
	$tpl->assignGlobal($link['link_name'], "<a href=\""._BASEDIR.$link['link_url']."\" title=\"".$link['link_text']."\"".($link['link_target'] ? " target=\"_blank\"" : "").($current == $link['link_name'] ? " id=\"current\"" : "").">".$link['link_text']."</a>");
	$pagelinks[$link['link_name']] = array("id" => $link['link_id'], "text" => $link['link_text'], "url" => _BASEDIR.$link['link_url'], "link" => "<a href=\""._BASEDIR.$link['link_url']."\" title=\"".$link['link_text']."\"".($link['link_target'] ? " target=\"_blank\"" : "").($current == $link['link_name'] ? " id=\"current\"" : "").">".$link['link_text']."</a>");
	$output .= "<tr><td class=\"tblborder\"><a href=\""._BASEDIR.$link['link_url']."\" title=\"".$link['link_text']."\"".($link['link_target'] ? " target=\"_blank\"" : "").">".$link['link_text']."</a></td></td>
		<td class=\"tblborder\"><a href=\"admin.php?action=links&amp;edit=".$link['link_id']."\">"._EDIT."</a> | 
		<a href=\"admin.php?action=links&amp;delete=".$link['link_id']."\">"._DELETE."</a></td></tr>";
}

	if(!dbnumrows($linkquery)) $output .= "<tr><td class=\"tblborder\" colspan=\"2\" align=\"center\">"._NORESULTS."</td></tr>";
	$output .= "<tr><td class=\"tblborder\" colspan=\"2\" align=\"center\"><a href=\"admin.php?action=links&amp;new=1\">"._ADDNEWLINK."</a></td></tr>";
	$output .= "</table>";
}
?>