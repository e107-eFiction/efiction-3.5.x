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
	include("includes/emailer.php");
	$cat = isset($_GET['cat']) ? $_GET['cat'] : -1;

	$output .= "<div style='text-align: center;'><h4>"._MAILUSERS."</h4></div>";
	if(isset($_POST['submit'])) {
		$who = isset($_POST['who']) ? $_POST['who'] : false;
		$category = isset($_POST['category']) ? $_POST['category'] : false;
		if($who == "authors") $select = "SELECT "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_authorprefs as ap WHERE "._UIDFIELD." = ap.uid AND ap.stories > 0";
		else if($who == "admins") $select = "SELECT "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_authorprefs as ap WHERE "._UIDFIELD." = ap.uid AND ap.level > 0";
		else $select = "SELECT "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE;
		if($who == "admins" && $_POST['category'] != "all" && isNumber($_POST['category'])) $select .= " AND (FIND_IN_SET(".$_POST['category'].", ap.categories) > 0 OR ap.categories = 0)";
		$query = dbquery($select);
		$subject = strip_tags(descript($_POST['subject']));
		$mailtext = descript($_POST['mailtext']);
		$sent = 0;
		while($result = dbassoc($query)){
			$mailresult = sendemail($result['penname'], $result['email'], $sitename, $siteemail, $subject, $mailtext, "html");
			if($mailresult) $sent++;
			$output .= $result['penname']." <img src=\"images/".($mailresult ? "check.gif\" alt=\"check\" title=\"check\"" : "X.gif\" alt=\"X\" title=\"X\"")."><br />";		
		}
		if($sent) $output .= write_message(_MESSAGESENT." $sent<br/>"._ACTIONSUCCESSFUL);
		else $output .= write_message(_NOMAILSENT);
	}
	else {
		$output .= "<form method=\"POST\" name=\"form\" enctype=\"multipart/form-data\" action=\"admin.php?action=mailusers\">
		<table align=\"center\"><tr><td><label for=\"who\">"._EMAIL.":</label> </td><td><select name=\"who\" id=\"who\">
			<option value=\"all\">"._ALLMEMBERS."</option>
			<option value=\"authors\">"._AUTHORSONLY."</option>
			<option value=\"admins\">"._ADMINS."</option>
		</select></td></tr><tr><td><label for=\"category\">"._CATEGORY.":</label> </td><td><select name=\"category\" id=\"category\" onChange='if(this.selectedIndex.value == false) return false; else document.location = document.location.pathname + \"?action=mailusers&amp;cat=\" + document.form.category.options[document.form.category.selectedIndex].value;'><option value=\"all\">"._ALL."</option>";
		$result = dbquery("SELECT category, catid, parentcatid FROM ".TABLEPREFIX."fanfiction_categories ORDER BY leveldown, displayorder");
		while($cats = dbassoc($result)) {
			if($cats['catid'] == $cat || $cats['parentcatid'] == $cat) {
				$output .= "<option value=\"".$cats['catid']."\"";
				if($cats['catid'] == $cat) $output .= " selected";
				$output .= ">".$cats['category'];
				$output .= "</option>";
			}		
		}
		$output .= "</select></td></tr>
		<tr><td><label for=\"subject\">"._SUBJECT.":</label> </td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"subject\"></td></tr>
		<tr><td valign=\"top\"><label for=\"mailtext\">"._TEXT.":</label> </td><td><textarea class=\"textbox\" name=\"mailtext\" cols=\"40\" rows=\"6\"></TEXTAREA></td></tr>
		<tr><td><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></td></tr></table></form>";
		$output .= "<br /><br /><div style='text-align: center;'>"._EMAILWARNING."</div><br />";
	}

?>