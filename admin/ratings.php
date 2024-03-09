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
$output .= "<div id=\"pagetitle\">"._RATINGS."</div>";
	$showlist = 1;
	if(isset($_GET["delete"]) && isNumber($_GET["delete"])) {
		$rid = $_GET["delete"];
		$confirm = isset($_GET["confirm"]) ? $_GET["confirm"] : false;
		if($confirm == "yes")
		{
			$result5 = dbquery("SELECT rating FROM ".TABLEPREFIX."fanfiction_ratings WHERE rid = '$rid'");
			$ratings = dbassoc($result5);
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET rid = '"._NONE."' WHERE sid = '$ratingresult[sid]'");
			dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_ratings WHERE rid = '$rid'");
			$output .= write_message(_ACTIONSUCCESSFUL);
		}
		else if ($confirm == "no")
		{
			$output .= write_message(_ACTIONCANCELLED);
		}
		else
		{
			$showlist = 0;
			$output .= write_message(_CONFIRMDELETE."<br /><br />
[ <a href=\"admin.php?action=ratings&delete=$rid&confirm=yes\">"._YES."</a> | <a href=\"admin.php?action=ratings&delete=$rid&confirm=no\">"._NO."</a> ]");
		}
	}
	if (isset($_POST["submit"]) && ((isset($_GET['rid']) && isNumber($_GET['rid'])) || $_GET['rid'] == "new")) {
		$newrate = escapestring(strip_tags(descript($_POST['rating'])));
		$newtext = escapestring(strip_tags(descript($_POST['warningtext'])));
		$ratingwarning = isset($_POST['ratingwarning']) ? 1 : 0;
		if(isset($_POST['rusersonly'])) $ratingwarning = $ratingwarning + 4;
		if(isset($_POST['ageconsent'])) $ratingwarning = $ratingwarning + 2;
		if($_GET["rid"] == "new") $ratingquery = "INSERT INTO ".TABLEPREFIX."fanfiction_ratings (rating, ratingwarning, warningtext) VALUES ('$newrate', '$ratingwarning', '$newtext')";
		else $ratingquery = "UPDATE ".TABLEPREFIX."fanfiction_ratings SET rating = '$newrate', ratingwarning = '$ratingwarning', warningtext = '$newtext' WHERE rid = '".$_GET['rid']."'";
		dbquery($ratingquery);
		if($_GET["rid"] != "new" && $newrate != $_POST['oldrating']) dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET rid = '$newrate' WHERE rid = '".$_POST['oldrating']."'");
		$output .= write_message(_ACTIONSUCCESSFUL);
	}
	else if(isset($_GET["rid"])) {
		$showlist = 0;
		$new = $_GET['rid'] == "new" ? true : false;
		if(!$new && isNumber($_GET["rid"])) {
			$ratingquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_ratings WHERE rid = '".$_GET['rid']."' LIMIT 1");
			$rating = dbassoc($ratingquery);
		}
		$output .= "<form method=\"POST\" id='settingsform' enctype=\"multipart/form-data\" action=\"admin.php?action=ratings&rid=".$_GET['rid']."\">
			<div class='sectionheader'>".($new ? _NEWRAT : _EDITRAT)."</div>
			<div><label for='rating'>"._RATING.": </label> 
			<INPUT  type=\"text\" class=\"textbox=\" name=\"rating\"".($new ? "" : "value=\"".$rating['rating']."\"")."><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_RATING."</span></A>";
		if(isset($rating)) 
			$output .= "<input type=\"hidden\" name=\"oldrating\" value=\"".$rating['rating']."\">";
		$warninglevel = isset($rating) ? sprintf("%03b", $rating['ratingwarning']) : array(0,0,0);
		$output .= "</div><div><label for='ratingwarning'>"._WARNINGPOP.": </label>
		<INPUT type=\"checkbox\" class=\"checkbox\" name=\"ratingwarning\"".(!$new && $warninglevel[2] ? " checked" : "")."><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_RATINGWARNING."</span></A></div>
		<div><label for='ageconsent'>"._AGECHECK.": </label>
		<INPUT type=\"checkbox\" class=\"checkbox\" name=\"ageconsent\"".(!$new && $warninglevel[1] ? " checked" : "")."><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_RATINGCONSENT."</span></A></div>
		<div><label for='rusersonly'>"._RUSERSONLY.": </label>
		<INPUT type=\"checkbox\" class=\"checkbox\" name=\"rusersonly\"".(!$new && $warninglevel[0] ? " checked" : "")."><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_RATINGUSERS."</span></A></div>
		<div><label for='warningtext'>"._WARNINGTEXT.": </label>
		<textarea class=\"textarea\" name=\"warningtext\" cols=\"35\" rows=\"4\">".($new ? "" : $rating['warningtext'])."</TEXTAREA>";
		if($tinyMCE) 
			$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('warningtext');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";	
		$output .= "</div><div style='text-align: center;'><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_RATINGWARNTEXT."</span></A></div><INPUT type=\"submit\" class=\"button\" id='submit' value=\""._SUBMIT."\" name=\"submit\">";
	}
	if($showlist) {
		$result = dbquery("SELECT * from ".TABLEPREFIX."fanfiction_ratings");

		//List of current ratings
		$output .= "<table class=\"tblborder\" cellspacing=\"0\" cellpadding=\"o\" align=\"center\" style='margin: 0 auto;'>
		<tr><th colspan=\"3\" align=\"center\" class=\"tblborder\">"._RATINGS."</th></tr>";
		$output .= "<tr><td class=\"tblborder\"><b>"._RATING."</b></td><td class=\"tblborder\"><b>"._WARNING."</b></td><td class=\"tblborder\"><b>"._OPTIONS."</b></td></tr>";
		while ($ratingresults = dbassoc($result))
		{
		$output .= "<tr><td class=\"tblborder\">".$ratingresults['rating'];
			if($ratingresults['ratingwarning'] >= 1)
				$output .= "</td><td class=\"tblborder\">"._YES."";
			else
				$output .= "</td><td class=\"tblborder\">"._NO."";
			$output .= "</td><td class=\"tblborder\"><a href=\"admin.php?action=ratings&rid=".$ratingresults['rid']."\">"._EDIT."</a> | <a href=\"admin.php?action=ratings&delete=".$ratingresults['rid']."\">"._DELETE."</td></tr>";
		}
		$output .= "<tr><td colspan=\"3\" align=\"center\" class=\"tblborder\"><a href=\"admin.php?action=ratings&rid=new\">"._ADDRAT."</a></td></tr></table>";
	}
?>