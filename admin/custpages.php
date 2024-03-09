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


	$output .= "<div id=\"pagetitle\">"._CUSTPAGES."</div>";
if(isset($_GET['delete']) && isNumber($_GET['delete'])) {
	$result = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_messages WHERE message_id = ".$_GET['delete']." LIMIT 1");
	if($result) $output .= write_message(_ACTIONSUCCESSFUL);
	else $output .= write_error(_ERROR);
}
if(isset($_POST['submit'])) {
	if(!preg_match("!^[a-z0-9_]{3,30}$!i", $_POST['name'])) $output .= write_error(_BADVARNAME);
	else {
		$pagename = descript($_POST['name']);
		$pagetitle = descript($_POST['title']);
		if(isset($_GET['edit']) && isNumber($_GET['edit'])) 
			$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_messages SET message_name = '".escapestring($pagename)."', message_title = '".escapestring($pagetitle)."', message_text = '".escapestring(descript($_POST['text']))."' WHERE message_id = ".$_GET['edit']." LIMIT 1");
		else {
			$result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_messages(`message_name`, `message_title`, `message_text`) VALUES( '".escapestring($pagename)."', '".escapestring($pagetitle)."', '".escapestring(descript($_POST['text']))."')");
			if($result) $result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_pagelinks(`link_name`, `link_text`, `link_url`, `link_target`, `link_access`) VALUES('".escapestring($pagename)."_link', '".escapestring($pagetitle)."', 'viewpage.php?page=$pagename', '0', '0')");
		}
		if($result) $output .= write_message(_ACTIONSUCCESSFUL."<br /><br /><a href='viewpage.php?page=$pagename'>$pagetitle</a>");
		else $output .= write_error(_ERROR);
		unset($_GET['edit']);
	}
}
if((isset($_GET['edit']) && isNumber($_GET['edit'])) || isset($_GET["new"]) || isset($_POST['preview'])) {
	unset($message, $id, $name, $title, $text);
	if(isset($_POST['preview'])) {
		$name = descript($_POST['name']);
		$title = descript($_POST['title']);
		$thistext = descript($_POST['text']);
		$edit = isset($_GET['edit']) ? $_GET['edit'] : "";
		if(strpos($thistext, "?>") === false) $output .= "<div id='pagetitle'>$title</div>\n\n$thistext";
		else {
			$text = "";
			eval("?>".$thistext."<?php ");
			$output .= "<div id='pagetitle'>$title</div>\n\n$text";
		}
		$text = $thistext;
	}
	else if(isset($_GET['edit'])) {
		$edit = $_GET['edit'];
		$pagequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_messages WHERE message_id = $edit LIMIT 1");
		$message =  dbassoc($pagequery);
		$id = $message['message_id'];
		$name = $message['message_name'];
		$title = $message['message_title'];
		$text = $message['message_text'];
	}
	else {
		$name = "";
		$title = "";
		$text = "";
	}
	$output .= "<div id='settingsform'><form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=custpages".(isset($edit) ? "&amp;edit=$edit" : "")."\">
		<div><label for=\"name\">"._NAME.":</label> <input type=\"text\" class=\"textbox\" name=\"name\" value=\"$name\"></div>
		<div><label for=\"title\">"._TITLE.":</label> <input type=\"text\" class=\"textbox\" name=\"title\" value=\"$title\" size=\"30\"></div>
		<div><label for=\"text\">"._TEXT.":</label><br />
		<textarea class=\"textbox\" rows=\"10\" cols=\"60\" style='width: 100%;' name=\"text\">$text</textarea>";
	if($tinyMCE) 
		$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('text');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
	$output .= "</div><INPUT type=\"submit\" class=\"button\" name=\"preview\" id=\"preview\" value=\""._PREVIEW."\"><INPUT type=\"submit\" class=\"button\" name=\"submit\" id=\"submit\" value=\""._SUBMIT."\"></form></div>";
	$output .= write_message(_CUSTPAGENOTE);
}
else {
	$listquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_messages");
	$output .= "<table class=\"tblborder\" style=\"margin: 0 auto;\"><tr><th class=\"tblborder\">"._ID."</th><th class=\"tblborder\">"._NAME."</th><th class=\"tblborder\">"._TITLE."</th><th class=\"tblborder\">"._OPTIONS."</th></tr>";
	while($msg = dbassoc($listquery)) {
		// Hidden messages they're listed in the settings.
		if($msg['message_name'] == "welcome" ||  $msg['message_name'] == "nothankyou" || $msg['message_name'] == "thankyou" ||
		$msg['message_name'] == "copyright" || $msg['message_name'] == "printercopyright") continue;
		$output .= "<tr><td class=\"tblborder\">$msg[message_id]</td><td class=\"tblborder\">$msg[message_name]</td><td class=\"tblborder\"><a href='viewpage.php?page=$msg[message_name]'>$msg[message_title]</a></td>
		<td class=\"tblborder\"><a href=\"admin.php?action=custpages&amp;edit=$msg[message_id]\">"._EDIT."</a> | 
		<a href=\"admin.php?action=custpages&amp;delete=$msg[message_id]\">"._DELETE."</a></td></tr>";
	}
	if(!dbnumrows($listquery)) $output .= "<tr><td class=\"tblborder\" colspan=\"3\" align=\"center\">"._NORESULTS."</td></tr>";
	$output .= "<tr><td class=\"tblborder\" colspan=\"4\" align=\"center\"><a href=\"admin.php?action=custpages&amp;new=1\">"._ADDCUSTPAGE."</a></td></tr>";
	$output .= "</table>";
	$output .= write_message(_CUSTPAGENOTE);
}
?>