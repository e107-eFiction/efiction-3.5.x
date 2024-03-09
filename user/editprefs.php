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

	$output = "<div id='pagetitle'>".($action == "register" ? _SETPREFS : _EDITPREFS)."</div>";
	if(isset($_POST['submit'])) {
		$useralertson = isset($_POST['useralertson']) && $_POST['useralertson'] == "on" ? 1 : 0;
		$newreviews = isset($_POST['newreviews']) && $_POST['newreviews'] == "on" ? 1 : 0;
		$newrespond = isset($_POST['newrespond']) && $_POST['newrespond'] == "on" ? 1 : 0;
		$ageconsent = isset($_POST['ageconsent']) && isNumber($_POST['ageconsent']) ? $_POST['ageconsent'] : 0;
		$tinyMCE = isset($_POST['tinyMCE']) && $_POST['tinyMCE'] == "on" ? 1 : 0;
		$storyindex = isset($_POST['storyindex']) && $_POST['storyindex'] == "on" ? 1 : 0;
		$sortby = isset($_POST['sortby']) && $_POST['sortby'] == 1 ? 1 : 0;
		$skinnew = descript(strip_tags($_POST['skinnew']));
		if($skinnew != $skin) $_SESSION[$sitekey."_skin"] = $skinnew;
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET alertson = '$useralertson', newreviews = '$newreviews', newrespond = '$newrespond', ageconsent = '$ageconsent', tinyMCE ='$tinyMCE', userskin = '$skinnew', storyindex = '$storyindex', sortby = '$sortby' WHERE uid = '".USERUID."'");
		$output .= write_message(_ACTIONSUCCESSFUL." "._BACK2ACCT);
	}
	else {
		$result = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE uid = '".USERUID."' LIMIT 1");
		if(dbnumrows($result) == 0) {
			dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorprefs(`uid`, `userskin`) VALUES('".USERUID."', '$skin')");
			$result = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE uid = '".USERUID."' LIMIT 1");
		}
		$user = dbassoc($result);
		$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" style='width: 425px; margin: 0 auto;' action=\"user.php?action=editprefs\">
		<label for='newreviews'>"._CONTACTREVIEWS.":</label> <INPUT name=\"newreviews\" type=\"checkbox\" class=\"checkbox\"".($user['newreviews'] ? " checked" : "")."> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_NEWREV."</span></A><br />
		<label for='newrespond'>"._CONTACTRESPOND.":</label> <INPUT name=\"newrespond\" type=\"checkbox\" class=\"checkbox\"".($user['newrespond'] ? " checked" : "")."><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_NEWRESP."</span></A><br />";
		if($alertson) 
		$output .= "<label for='useralertson'>"._ALERTSON2."</label> <INPUT name=\"useralertson\" type=\"checkbox\" class=\"checkbox\"".($user['alertson'] ? " checked" : "")."><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_FAVALERT."</span></A><br />";
		$output .= "<label for='storyindex'>"._DISPLAYINDEX."</label> <INPUT name=\"storyindex\" type=\"checkbox\" class=\"checkbox\"".($user['storyindex'] ? " checked" : "")."><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_TOC."</span></A><br />";
		$output .= "<label for='tinyMCE'>"._USETINYMCE."</label> <INPUT name=\"tinyMCE\" type=\"checkbox\" class=\"checkbox\"".($user['tinyMCE'] ? " checked" : "")."><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_TINYMCE."</span></A><br />";
		if($agestatement) {
			$output .= "<div style=\"margin: 1ex 0;\">"._AGECONSENT." <span style='white-space: nowrap;'>
				<input type=\"radio\" class=\"radio\" id=\"ageyes\" value=\"1\" name=\"ageconsent\" ".($user['ageconsent'] ? " checked" : "")."> <label for=\"ageyes\">"._YES."</label>
				<input type=\"radio\" class=\"radio\" id=\"ageno\" value=\"0\" name=\"ageconsent\" ".(!$user['ageconsent'] ? " checked" : "")."> <label for=\"ageno\">"._NO."</label></span><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_AGE."</span></A></div>";
		}
		$output .= "<label for='sortby'>"._DEFAULTSORT.": </label><select name='sortby' class='textbox'>
				<option value='1'".($user['sortby'] ? " selected" : "").">"._MOSTRECENT."</option>
				<option value='0'".(!$user['sortby'] ? " selected" : "").">"._ALPHA."</option>
			</select><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_DEFAULTSORT."</span></A><br />
			<label for='skinnew'>"._SKIN.":</label> <select name=\"skinnew\">";
		if(!isset($hiddenskins)) $hiddenskins = array( );
		if(is_string($hiddenskins)) $hiddenskins = explode(",", $hiddenskins);
			$directory = opendir(_BASEDIR."skins");
		while($filename = readdir($directory)) {
			if($filename== "." || $filename== ".." || !is_dir(_BASEDIR."skins/".$filename) || (in_array($filename, $hiddenskins) && !isADMIN)) continue;
			$skinlist[strtolower($filename)] = "<option value=\"$filename\"".($siteskin == $filename ? " selected" : "").">$filename</option>";
		}
		ksort($skinlist);
		foreach($skinlist as $s) { $output .= $s; }
		unset($skinlist, $s);
		closedir($directory);
		$output .= "</select><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_SKIN."</span></A><br /><INPUT type=\"submit\" class=\"button\" id=\"submit\" name=\"submit\" value=\""._SUBMIT."\"></form>";
	}

?>