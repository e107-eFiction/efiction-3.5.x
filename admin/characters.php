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

	$output .= "<div id='pagetitle'>"._CHARACTERS."</div>";
	if(isset($_GET["delete"])) {
		$charid = $_GET["delete"];
		$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
		if($confirm == "yes" && isNumber($charid))
		{
			$result5 = dbquery("SELECT catid, charname FROM ".TABLEPREFIX."fanfiction_characters WHERE charid = '$charid'");
			$chars = dbassoc($result5);
			dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_characters where charid = '$charid'");
			$storyquery = dbquery("SELECT charid, sid FROM ".TABLEPREFIX."fanfiction_stories WHERE FIND_IN_SET('$charid', charid) > 0");
			while($story = dbassoc($storyquery)) {
				$newcharlist = array_diff(explode(",", $story['charid']), array($charid));
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET charid = '".($newcharlist ? implode(",", $newcharlist) : "")."' WHERE sid = '$story[sid]'");
			}
			$seriesquery = dbquery("SELECT characters, seriesid FROM ".TABLEPREFIX."fanfiction_series WHERE FIND_IN_SET('$charid', characters) > 0");
			while($series = dbassoc($seriesquery)) {
				$newcharlist = array_diff(explode(",", $series['characters']), array($charid));
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET characters = '".($newcharlist ? implode(",", $newcharlist) : "")."' WHERE seriesid = '$series[seriesid]'");
			}
			$code = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'delchar'");
			while($c = dbassoc($code)) {
				eval($c['code_text']);
			}
			$output .= write_message(_ACTIONSUCCESSFUL);
		}
		else if ($confirm  == "no") 	{
			$output .= write_message(_ACTIONCANCELLED);
		}
		else {
			$output .= write_message(_CONFIRMDELETE."<br /><br />
				[ <a href=\"admin.php?action=characters&amp;delete=$charid&amp;confirm=yes\">"._YES."</a> | <a href=\"admin.php?action=characters&amp;delete=$charid&amp;confirm=no\">"._NO."</a> ]");
		}
	}		
	if(isset($_GET['do']) && $_GET['do'] == "addform") {
		if(isset($_GET['charid']) && isNumber($_GET['charid'])) $charid = $_GET['charid'];
		if(isset($_POST['numchars']) &&  isNumber($_POST['numchars'])) $numchars = $_POST['numchars'];
		if(!isset($numchars)) $numchars = $itemsperpage;
		if(isset($charid)) {
			$charquery = dbquery("SELECT charname, bio FROM ".TABLEPREFIX."fanfiction_characters WHERE charid = '$charid' LIMIT 1");
			$charinfo = dbassoc($charquery);
			$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" id='settingsform' action=\"admin.php?action=characters&do=update\">
				<input type=\"hidden\" name=\"charid\" value=\"$charid\">";
			$numchars = 1;
		}
		else if(isset($_GET['catid'])) {
			$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" id='settingsform' action=\"admin.php?action=characters&do=add\"><input type=\"hidden\" name=\"numchars\" value=\"$numchars\"><input type=\"hidden\" name=\"catid\" value=\"".$_GET['catid']."\">";
		}
		else {
			$output .= "<div style='width: 100%;'><div  id=\"settingsform\"><form method=\"POST\" enctype=\"multipart/form-data\" id='settingsform'  action=\"admin.php?action=characters&do=add\"><input type=\"hidden\" name=\"numchars\" value=\"$numchars\"><input type=\"hidden\" name=\"catid\" value=\"".$_POST['catid']."\">";
		}
		for($x = 1; $x <= $numchars; $x++) {
			$output .= "<div class='sectionheader'>".(isset($charid) ? _EDITCHAR : _NEWCHAR." $x")."</div>
				<div><label for='character$x'>"._CHARACTER.":</label><input type=\"text\" class=\"textbox\" name=\"character$x\" size=\"35\"".(isset($charinfo['charname']) ? " value=\"".$charinfo['charname']."\"" : "")."> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_CHARS."</span></A></div>
				<div><label for='bio$x'>"._DESC.":</label><textarea class=\"textbox\" rows=\"6\" cols=\"40\" id= 'bio$x' name=\"bio$x\">".(isset($charinfo) ? $charinfo['bio'] : "")."</textarea></div>";
			if($tinyMCE) 
				$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('bio$x');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";	
		}
		$output .= "<div id='submitdiv'><INPUT type=\"submit\" id=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></div></form></div><div style='clear: both;'>";
		unset($_POST['submit']);
	}
	if(isset($_POST["submit"])) {
		if($_GET["do"] == "add") {
			$numchars = $_POST["numchars"];
			if(!isNumber($numchars)) $numchars = $itempsperpage;
			for($x = 1; $x <= $numchars; $x++) {
				if(isset($_POST["character$x"]) && $_POST["character$x"] != "") {
					$result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_characters (charname, bio, catid) VALUES('".escapestring(descript(strip_tags(trim($_POST["character$x"]))))."', '".escapestring(descript($_POST["bio$x"]))."', '$_POST[catid]')");
				}
			}
		}
		else if($_GET["do"] == "update") {
			$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_characters SET charname = '".escapestring(descript(strip_tags(trim($_POST["character1"]))))."', bio = '".escapestring(descript($_POST["bio1"]))."' WHERE charid = '$_POST[charid]'");
		}
		$output .= write_message(_ACTIONSUCCESSFUL);
	}
	if(!isset($_GET['do']) || isset($_POST['submit'])) {
		if(isset($_GET['cat']) && isNumber($_GET['cat'])) $cat =  $_GET['cat'];
		else $cat = -1;
	
		$output .= "<div style='text-align: center;'><form method=\"POST\" name=\"form\" enctype=\"multipart/form-data\" action=\"admin.php?action=characters&do=addform\">
			<label for='catid'>"._CATEGORY.": </label>
			<select name=\"catid\" onChange='document.location = document.location.pathname + \"?action=characters&cat=\"+ document.form.catid.options[document.form.catid.selectedIndex].value;'>
			<option value=\"-1\">"._BACK2CATS."</option><option value=\"-1\"".($cat == "-1" ? " selected" : "").">"._SITEWIDE."</option>";
		foreach($catlist as $category => $info){
			if($category == $cat || $info['pid'] == $cat) $output .= "<option value=\"$category\"".($category == $cat ? " selected" : "").">".$info['name']."</option>";
		}
		$output .= "</select></form></div>";

		// Repopulate the characters list to reflect any changes we made.
		$charlist = array( );
		$result = dbquery("SELECT charname, catid, charid FROM ".TABLEPREFIX."fanfiction_characters ORDER BY charname");
		while($char = dbassoc($result)) {
			$charlist[$char['charid']] = array("name" => stripslashes($char['charname']), "catid" => $char['catid']);
		}

		//List of current characters
		$output .= "<center>"._CHOOSECATNOTE."</center><br /><table class=\"tblborder\" cellspacing=\"0\" cellpadding=\"3\" style='margin: 0 auto;' align=\"center\">
		<tr>
		<th class=\"tblborder\">"._CHARACTER."</th><th class=\"tblborder\">"._OPTIONS."</th>
		</tr>";
		$count = 0;
		foreach($charlist as $char =>$charinfo) {
			if($charinfo['catid'] == $cat) {
				$output .= "<tr><td class=\"tblborder\">
				<a href=\"browse.php?type=characters&amp;charid=$char\">".$charinfo['name']."</a></td><td class=\"tblborder\"><a href=\"admin.php?action=characters&amp;do=addform&amp;charid=$char&amp;cat=$cat\">"._EDIT."</a> | <a href=\"admin.php?action=characters&amp;delete=$char&amp;cat=$cat\">"._DELETE."</a></td></tr>";
				$count++;
			}
		}	
		if($count == 0) $output .= "<tr><td class=\"tblborder\" colspan=\"2\" style=\"text-align: center;\">"._NORESULTS."</td></tr>";
		$output .= "</table></p>";
		$output .= "<div class='sectionheader'>"._ADDNEWCHARS."</div>
		<div><form method=\"POST\" name=\"addform\" enctype=\"multipart/form-data\" id='settingsform' action=\"admin.php?action=characters&do=addform&catid=$cat\">
		<div><label for='numchars'>"._NUMCHARS.":</label><select name=\"numchars\">";
		for($x = 1; $x < $itemsperpage + 1; $x++) {
			$output .= "<option value=\"$x\">$x</option>";
		}
		$output .= "</select><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_NUMCHARS."</span></A></div><div id='submitdiv'><INPUT type=\"submit\" class=\"button\" id='submit' value=\""._SUBMIT."\" name=\"submit\"></div>
			</form><div style='clear: both;'>&nbsp;</div></div>";
	}
?>