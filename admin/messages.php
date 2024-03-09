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

	$output .= "<div id=\"pagetitle\">"._MESSAGESETTINGS."</div>";

if(isset($_POST['submit'])) {
	$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_messages SET message_text = '".escapestring(descript($_POST['text']))."' WHERE message_name = '".$_GET['message']."' LIMIT 1");
	if($result) $output .= write_message(_ACTIONSUCCESSFUL);
	else $output .= write_error(_ERROR);
}
else {
	$pagequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_messages WHERE message_name = '".$_GET['message']."' LIMIT 1");
	$message =  dbassoc($pagequery);
	$text = $message['message_text'];
	if(!$message) dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_messages` (`message_name` , `message_title` , `message_text` ) VALUES ('".$_GET['message']."', '', '')");
	$output .= "<div class='sectionheader'>".preg_replace("@\{sitename\}@", $sitename, $message['message_title'])."</div>
		<div style='width: 100%;'><div  id=\"settingsform\"><form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=messages&message=".$_GET['message']."\">
		<textarea rows=\"10\" cols=\"60\" style=\"width: 100%;\" ".($_GET['message'] == "tinyMCE" ? "class='mceNoEditor'" :"")." name=\"text\">$text</textarea>";
	if($tinyMCE && $_GET['message'] != "tinyMCE") 
		$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('text');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
	$output .= "<div style='clear: both;'>&nbsp;</div><INPUT type='submit' class='button' id='submit' value='"._SUBMIT."' name='submit'>
				</form></div><div style='clear: both;'>&nbsp;</div></div>";
}
?>