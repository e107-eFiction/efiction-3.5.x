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



//function to build story data section of the form.
function storyform($stories, $preview = 0){

	global $admin, $allowed_tags, $multiplecats,  $roundrobins, $catlist, $coauthallowed, $tinyMCE, $action, $sid;

	$classes = explode(",", $stories['classes']);
	$charid = explode(",", $stories['charid']);
	$catid = explode(",", $stories['catid']);
	$title = $stories['title'];
	$summary = $stories['summary'];
	$storynotes = $stories['storynotes'];
	$rr = $stories['rr'];
	$feat = $stories['featured'];
	$rid = $stories['rid'];
	$complete = $stories['completed'];
	$validated = $stories['validated'];
	$uid = $stories['uid'];

	$output = "<br /><label for=\"storytitle\">"._TITLE.":</label> ".(!$title ? "<span style=\"font-weight: bold; color: red\">*</span>" : "")."<input type=\"text\" class=\"textbox\" name=\"title\" size=\"50\"".($title? " value=\"".htmlentities($title)."\"" : "")." maxlength=\"200\" id=\"storytitle\"><br />";
	$authorquery = dbquery("SELECT "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM "._AUTHORTABLE." ORDER BY "._PENNAMEFIELD);
	if($admin) {
		if(!isset($authors)) {
			$authors = "";
			while($authorresult = dbassoc($authorquery)) {	
				$authors .= "<option value=\"$authorresult[uid]\"".($uid == $authorresult['uid'] ? " selected" : "").">$authorresult[penname]</option>";
			}
		}
		$output .= "<br /><label for=\"uid\">"._AUTHOR.":</label> <select name=\"uid\" id=\"uid\">$authors</select><br /><br />";
	}
	if($coauthallowed) {
	$output .= "<script language=\"javascript\" type=\"text/javascript\" src=\""._BASEDIR."includes/userselect.js\"></script>
		<script language=\"javascript\" type=\"text/javascript\" src=\""._BASEDIR."includes/xmlhttp.js\"></script><div style=\"text-align: center;\">"._COAUTHORSEARCH."</div>";
	$output .= "<label for='coauthorsSelect'>"._SEARCH.": <input name='coauthorsSelect' id='coauthorsSelect' size='20' type='text' class='userSelect' onkeyup='setUserSearch(\"coauthors\");' autocomplete='off'></label><br />
<div id='coauthorsDiv' name='coauthorsDiv' style='visibility: hidden;'></div>
<iframe id='coauthorsshim' scr='' scrolling='no' frameborder='0' class='shim'></iframe>
<div><label for='coauthorsSelected'>"._COAUTHORS.": <br /><select name='coauthorsSelected' id='coauthorsSelected' size='8' multiple='multiple' class='multiSelect' onclick='javascript: removeMember(\"coauthors\");'>";
	$array_couids = array() ;
	$couids = "";
	if(is_array($stories['coauthors']) && count($stories['coauthors'])) {
		$coauths = dbquery("SELECT "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM "._AUTHORTABLE." WHERE FIND_IN_SET("._UIDFIELD.", '".implode(",", $stories['coauthors'])."') > 0");
		while($c = dbassoc($coauths)) {
			if($c['uid'] == $stories['uid']) continue;
			$output .= "<option label='".$c['penname']."' value='".$c['uid']."'>".$c['penname']."</option>";
			$array_couids[] = $c['uid'];
		}
		$couids = implode(",", $array_couids);
	}
	$output .= "</select></label>
		<input type='hidden' name='coauthors' id='coauthors' value='$couids'></div>";
	}
	$output .= "<p><label for=\"summary\">"._SUMMARY.":</label> ".(!$summary ? "<span style=\"font-weight: bold; color: red\">*</span>" : "")."<br><textarea class=\"textbox\" rows=\"6\" name=\"summary\" id=\"summary\" cols=\"58\">$summary</textarea>";
	if($tinyMCE) 
		$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('summary');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
	$output .= "</p>
		<p><label for=\"storynotes\">"._STORYNOTES.":</label> <br /><textarea class=\"textbox\" rows=\"6\" name=\"storynotes\" id=\"storynotes\" cols=\"58\">$storynotes</textarea></p>";
	if($tinyMCE) 
		$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('storynotes');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
	if(!$multiplecats) $output .= "<input type=\"hidden\" name=\"catid\" id=\"catid\" value=\"1\">";
	else {
		include("includes/categories.php");
		$output .= "<input type=\"hidden\" name=\"formname\" value=\"stories\">";
	}
	$output .= "<div style='float: left; width: 100%;'>";
	$count = 0;
	$result4 = dbquery("SELECT charname, catid, charid FROM ".TABLEPREFIX."fanfiction_characters ORDER BY charname");
	if(dbnumrows($result4)) {
		$output .= "<div style=\"float: left; width: 49%;\"><label for=\"charid\">"._CHARACTERS.":</label><br><select size=\"5\"  style=\"width: 99%;\" id=\"charid\" name=\"charid[]\" multiple><option value=\"\">"._NONE."</option>";
		while ($charresults = dbassoc($result4)) {
			if((is_array($catid) && in_array($charresults['catid'], $catid)) || $charresults['catid'] == -1) {
				$output .= "<option value=\"".$charresults['charid']."\"".($charid != "" && in_array(stripslashes($charresults['charid']), $charid) ? " selected" : "").">".stripslashes($charresults['charname'])."</option>";
			}
		}
		$output .= "</select></div>";
		$count++;
	}
	unset($result4);
	$result = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classtypes ORDER BY classtype_name");
	while($type = dbassoc($result)) {
		$result2 = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classes WHERE class_type = '$type[classtype_id]' ORDER BY class_name");
		$select = "";
		while ($class = dbassoc($result2)) {
				$select .= "<option value=\"$class[class_id]\"".(is_array($classes) && in_array($class['class_id'], $classes) ? " selected" : "").">$class[class_name]</option>";
		}
		$output .= "<div style=\"float: ".($count % 2 ? "right" : "left")."; width: 49%; margin-bottom: 1em;\"><label for=\"class_".$type['classtype_id']."\">$type[classtype_title]:</label><br />
				 <select name=\"class_".$type['classtype_id']."[]\" id=\"class_".$type['classtype_id']."\"  style=\"width: 99%;\" multiple size=\"5\">$select</select></div>";
		if($count % 2) $output .= "<div style=\"clear: both; height: 1px;\">&nbsp;</div>";
		$count++;
	}
	$output .= "<div style=\"clear: both; height: 1px;\">&nbsp;</div></div>";
	$result5 = dbquery("SELECT rid, rating FROM ".TABLEPREFIX."fanfiction_ratings");
	$output .= "<label for=\"rid\">"._RATING.":</label>".(!$rid ? " <span style=\"font-weight: bold; color: red\">*</span>" : "")." <select size=\"1\" id=\"rid\" name=\"rid\">";
	while ($r = dbassoc($result5)) {
		$output .= "<option value=\"".$r['rid']."\"".($rid == $r['rid'] ? " selected" : "").">".$r['rating']."</option>";
	} 
	$output .= "</select>  <label for=\"complete\">"._COMPLETE.":</label> <input type=\"checkbox\" class=\"checkbox\" id=\"complete\" name=\"complete\" value=\"1\"".($complete == 1 ? " checked" : "") .">";
	if($roundrobins) $output .= " <label for=\"rr\">  "._ROUNDROBIN.":</label>
  			<input type=\"checkbox\" class=\"checkbox\" name=\"rr\" id=\"rr\"value=\"1\"".($rr == 1 ? "checked" : "").">";
	 if(isADMIN && uLEVEL < 4) $output .= "<br /><label for=\"feature\">"._FEATURED.":</label> <select class=\"textbox\" id=\"feature\" name=\"feature\">
				<option value=\"1\"".($feat == 1 ? " selected" : "").">"._ACTIVE."</option>
				<option value=\"2\"".($feat == 2 ? " selected" : "").">"._RETIRED."</option>
				<option value=\"0\"".(!$feat ? " selected" : "").">"._NO."</option>
			</select> 
			<label for=\"validated\">"._VALIDATED.":</label> <select class=\"textbox\" id=\"validated\" name=\"validated\">
				<option value=\"2\"".($validated == 2? " selected" : "").">"._STORY."</option>
				<option value=\"1\"".($validated  == 1? " selected" : "").">"._CHAPTER."</option>
				<option value=\"0\"".(!$validated ? " selected" : "").">"._NO."</option>
			</select>";
	$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'storyform'");
	while($code = dbassoc($codequery)) {
		eval($code['code_text']);
	}
	return $output;
}
// end storyform

// function to build chapter info section of the form.
function chapterform($inorder, $notes, $endnotes, $storytext, $chaptertitle, $uid = 0) {
	global $admin, $tinyMCE, $action, $preview;
	$inorder++;
	$default = _CHAPTER." ". $inorder;
	if($chaptertitle != "") $default = $chaptertitle;
	if($tinyMCE && strpos($storytext, "<br>") === false && strpos($storytext, "<p>") === false && strpos($storytext, "<br />") === false) $storytext = nl2br($storytext);
	$output = "";
	if($admin && ($action == "newchapter" || $action == "editchapter")) {
		$authorquery = dbquery("SELECT "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM "._AUTHORTABLE." ORDER BY penname");
		$output .= "<label for=\"uid\">"._AUTHOR.":</label> <select name=\"uid\" id=\"uid\">";
			while($authorresult = dbassoc($authorquery)) {	
				$output .= "<option value=\"$authorresult[uid]\"".($uid == $authorresult['uid']? " selected" : "").">$authorresult[penname]</option>";
			}
		$output .= "</select><br />";
	}
	$output .= "<p><label for=\"chaptertitle\">"._CHAPTERTITLE.":</label> <input type=\"text\" class=\"textbox\" id=\"chaptertitle\" maxlength=\"200\" name=\"chaptertitle\" size=\"50\" value=\"".htmlentities($default)."\"> </p>
		<p>"._ALLOWEDTAGS."</p>
		<div><label for=\"notes\">"._CHAPTERNOTES.":</label><br /><textarea class=\"textbox\" rows=\"5\" id=\"notes\" name=\"notes\" cols=\"58\">$notes</textarea></div>";
	if($tinyMCE) 
		$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('notes');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
	$output .= "<div><label for=\"storytext\">"._STORYTEXTTEXT.":</label>".(!$storytext ? "<span style=\"font-weight: bold; color: red\">*</span>" : "")."<br><textarea class=\"textbox\" rows=\"15\" id=\"storytext\" name=\"storytext\" cols=\"58\">".$storytext."</textarea></div>";
	if($tinyMCE) 
		$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('storytext');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
	$output .= "<p><strong>"._OR."</strong> </p>
		<p><label for=\"storyfile\">"._STORYTEXTFILE.":</label> <INPUT type=\"file\" id=\"storyfile\" class=\"textbox\" name=\"storyfile\" onClick=\"this.form.storytext.disabled=true\"> </p>
		<div><label for=\"notes\">"._ENDNOTES.":</label><br><textarea class=\"textbox\" rows=\"5\" id=\"endnotes\" name=\"endnotes\" cols=\"58\">$endnotes</textarea></div>";
	if($tinyMCE) 
		$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('endnotes');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
	return $output;
}
// end chapterform
?>