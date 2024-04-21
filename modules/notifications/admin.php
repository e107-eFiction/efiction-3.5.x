<?php
// ----------------------------------------------------------------------
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
$current = "notifications";
if(isset($_GET['action']) && ($_GET['action'] == "add" || $_GET['action'] == "edit")) $displayform = 1;

if(file_exists(_BASEDIR."languages/{$language}_admin.php")) include_once(_BASEDIR."languages/{$language}_admin.php");
else include_once(_BASEDIR."languages/en_admin.php");
if(file_exists(_BASEDIR."modules/notifications/languages/{$language}.php")) include_once(_BASEDIR."modules/notifications/languages/{$language}.php");
else include_once(_BASEDIR."modules/notifications/languages/en.php");

 
 
$output = "<div id='pagetitle'>"._NOTIFICATIONADMIN."</div>";
if(!isADMIN) accessDenied( );
 
if(isset($_POST['submit'])) {
 
	$dbdata =  serialize($_POST['notifications']);
	$dbdata = addslashes($dbdata); 
	$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET notifications = '$dbdata' WHERE sitekey = '".SITEKEY."'");
	if($result) $output .= write_message(_ACTIONSUCCESSFUL);
	else $output .= write_error(_ERROR);
}
else {
 
	$tmp =  unserialize($notifications);

	$registration_notify = isset($tmp['registration_notify']) ? $tmp['registration_notify'] : "";
	$lostpassword_notify = isset($tmp['lostpassword_notify']) ? $tmp['lostpassword_notify'] : "";
	$registration_toemail = isset($tmp['registration_toemail']) ? $tmp['registration_toemail'] : "";
 
	$output .= "<div  id=\"settingsform\" style=\"width: 99%\">
	  <form method=\"POST\" style=\"margin: 1em auto;\" enctype=\"multipart/form-data\"
	   action=\"admin.php?action=modules&amp;admin=true&amp;module=notifications\">";
	$output .= "<div style='margin-bottom: 1em'>
	        <label for=\"notifications[registration_notify]\">". _REGISTRATION_NOTIFY. ": </label> 
			 <select name=\"notifications[registration_notify]\">
				<option value=\"1\"".($registration_notify == "1" ? " selected" : "").">"._YES."</option>
				<option value=\"0\"".($registration_notify == "0" ? " selected" : "").">"._NO. "</option>
			</select></div>
			<div style='margin-bottom: 1em'>
	        <label for=\"notifications[lostpassword_notify]\">" . _LOST_PASWORD_NOTIFY . ": </label> 
			 <select name=\"notifications[lostpassword_notify]\">
				<option value=\"1\"" . ($lostpassword_notify == "1" ? " selected" : "") . ">" . _YES . "</option>
				<option value=\"0\"" . ($lostpassword_notify == "0" ? " selected" : "") . ">" . _NO . "</option>
			</select></div>
			<div style='margin-bottom: 1em'>
			<label for=\"notifications[registration_toemail]\">". _REGISTRATION_TOEMAIL.":</label> 
				<input type=\"text\" class=\"textbox\" id=\"registration_toemail\" maxlength=\"250\"
			 name=\"notifications[registration_toemail]\" size=\"80\"  value=\"" .  $registration_toemail  . "\"></div>
			
			</div>";
	$output .= "<div id='submitdiv'>
					<INPUT type=\"submit\" id=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\">
				</div>
				</form> 
				</div>";
	 
}
?>
