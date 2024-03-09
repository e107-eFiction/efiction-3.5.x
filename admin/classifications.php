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


$list = true;

if(isset($_GET['type_id']) && isNumber($_GET['type_id'])) $type_id = $_GET['type_id'];
if(isset($_GET['listtype']) && isNumber($_GET['listtype'])) $listtype = $_GET['listtype'];

if(isset($_GET['delete'])) {
	if($_GET['delete'] == "class" && isNumber($_GET["class"])) $thislist = dbquery("SELECT class_id FROM ".TABLEPREFIX."fanfiction_classes WHERE class_id = '".$_GET["class"]."'");
	else {
		list($classname) = dbrow(dbquery("SELECT classtype_name FROM ".TABLEPREFIX."fanfiction_classtypes WHERE classtype_id = '$type_id'"));
		dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_pagelinks WHERE link_name = '".$classname."_link' LIMIT 1");
		$thislist = dbquery("SELECT class_id FROM ".TABLEPREFIX."fanfiction_classes WHERE class_type = '$type_id'");
		dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_classtypes WHERE classtype_id = '$type_id'");
	}
	while($thisclass = dbassoc($thislist)) {
		$class = $thisclass['class_id'];
		$stories = dbquery("SELECT sid, classes FROM ".TABLEPREFIX."fanfiction_stories WHERE FIND_IN_SET('$class', classes) > 0");
		while($s = dbassoc($stories)) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET classes = '".implode(",", array_diff(explode(",", $s['classes']), array($class)))."' WHERE sid = '$s[sid]' LIMIT 1");
		}
		$series = dbquery("SELECT seriesid, classes FROM ".TABLEPREFIX."fanfiction_series WHERE FIND_IN_SET('$class', classes) > 0");
		while($s = dbassoc($series)) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET classes = '".implode(",", array_diff(explode(",", $s['classes']), array($class)))."' WHERE seriesid = '$s[seriesid]' LIMIT 1");
		}
		$code = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'delclass'");
		while($c = dbassoc($code)) {
			eval($c['code_text']);
		}
		dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_classes WHERE class_id = '$class' LIMIT 1");
	}
}
if(isset($_GET['newitems']) && isNumber($_GET['newitems'])) {
	if(isset($_POST['submit'])) {
		for($x = 1; $x <= $itemsperpage; $x++) {
			$item = escapestring(descript(strip_tags($_POST["item_$x"])));
			if($item) {
				$result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_classes(`class_type`, `class_name`) VALUES('$_GET[newitems]', '$item')");
				if(!$result) $output .= write_error(magicunquote($item).": "._ERROR."<br />");
			}
		}
		$listtype = $_GET['newitems'];
	}
	else {
		$type = dbquery("SELECT classtype_title FROM ".TABLEPREFIX."fanfiction_classtypes WHERE classtype_id = '$_GET[newitems]' LIMIT 1");
		list($thistype) = dbrow($type);
		$output .= "<div id=\"pagetitle\">$thistype</div><div id='settingsform'><form method=\"POST\" enctype=\"multipart/form-data\" style=\"width: 450px; margin: 1em auto;\" action=\"admin.php?action=classifications&amp;newitems=$_GET[newitems]\">\n";
		for($x = 1; $x <= $itemsperpage; $x ++) {
			$output .=  "<div><label for=\"item_$x\">"._NEWITEM." $x: </label><input type=\"text\" class=\"textbox\" size='30' name=\"item_$x\"></div>\n";
		}
		$output .= "<div style='text-align: center; margin: 1em auto;'><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></form></div></div>";
		$list = false;
	}
}
if(isset($listtype)) {
	$thislist = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classes WHERE class_type = '$listtype'");
	$output .= "<table class=\"tblborder\" style=\"margin: 0 auto;\"><tr><th clas=\"tblborder\">"._CLASS."</th><th clas=\"tblborder\">"._OPTIONS."</th></tr>";
	if(!dbnumrows($thislist)) $output .= "<tr><td class=\"tblborder\" colspan='2'>"._NORESULTS."</td></tr>";
	while($class = dbassoc($thislist)) {
		$output .= "<tr>
			<td class=\"tblborder\">
				$class[class_name]
			</td>
			<td class=\"tblborder\">
				<a href=\"admin.php?action=classifications&amp;class=$class[class_id]\">"._EDIT."</a> |
				<a href=\"admin.php?action=classifications&amp;delete=class&amp;class=$class[class_id]&amp;listtype=$listtype\">"._DELETE."</a>
			</td></tr>";
	}	
	$output .= "<tr><td class=\"tblborder\" colspan=\"2\" style=\"text-align: center;\"><a href=\"admin.php?action=classifications&newitems=$listtype\">"._ADDNEWCLASSES."</a></td></tr></table>";
	$list = false;
}
if(isset($_GET['type']) && empty($_GET['delete'])) {
	if(isset($_POST['submit'])) {
		$type_name = escapestring(descript(strip_tags($_POST['type_name'])));
		$type_title = escapestring(descript(strip_tags($_POST['type_title'])));
		if(isset($_POST['type_id'])) $result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_classtypes SET classtype_name = '$type_name', classtype_title = '$type_title' WHERE classtype_id = '".$_POST['type_id']."' LIMIT 1");
		else {
			$result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_classtypes(classtype_name, classtype_title) VALUES('$type_name', '$type_title')");
			$id = dbinsertid($result);
			$result2 = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_pagelinks(link_name, link_text, link_url) VALUES('".$type_name."_link', '$type_title', 'browse.php?type=class&amp;type_id=$id')");
		}
		if($result) $output .= write_message(_ACTIONSUCCESSFUL);
		else $output .= write_error(_ERROR);
	}
	else {
		if(isNumber($_GET['type'])) {
			$typequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classtypes WHERE classtype_id = '$_GET[type]' LIMIT 1");
			$thistype = dbassoc($typequery);
		}
		$output .= "<div id='settingsform'><form method=\"POST\" style=\"width: 100%; margin: 1em auto;\" enctype=\"multipart/form-data\" action=\"admin.php?action=classifications&amp;type=$_GET[type]\">";
		if(isset($thistype)) $output .= "<input type=\"hidden\" name=\"type_id\" value=\"".$thistype['classtype_id']."\">";
		$output .= "<div><label for=\"type_name\">"._NAME.": </label> <input type=\"text\" class=\"textbox\" name=\"type_name\" value=\"".(isset($thistype) ? $thistype['classtype_name'] : "")."\"></div>
			<div><label for=\"type_name\">"._TITLE.": </label> <input type=\"text\" class=\"textbox\" name=\"type_title\" value=\"".(isset($thistype) ? $thistype['classtype_title'] : "")."\"></div>
			<div style='text-align: center; margin: 1em auto;'><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></div></form></div>";
		$output .= write_message(_CLASSNOTE);
		$list = false;
	}
}
if(isset($_GET['class']) && empty($_GET['delete'])) {
	if(isset($_POST['submit'])) {
		if(isset($_GET['class']) && isNumber($_GET['class'])) $class_id = $_GET['class'];
		$class_name = escapestring(descript(strip_tags($_POST['class_name'])));
		$class_type = isNumber($_POST['type_id']) ? $_POST['type_id'] : false;
		if(isset($class_id)) $result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_classes SET class_name = '$class_name', class_type = '$class_type' WHERE class_id = '$class_id' LIMIT 1");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL);
		else $output .= write_error(_ERROR);
	}
	else {
		if(isNumber($_GET['class'])) {
			$typequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classes WHERE class_id = '$_GET[class]' LIMIT 1");
			$thisclass = dbassoc($typequery);
		}
		$output .= "<div id='settingsform'><form method=\"POST\" style=\"width: 90%; margin: 1em auto;\" enctype=\"multipart/form-data\" action=\"admin.php?action=classifications&amp;class=$_GET[class]\">";
		if(isset($thisclass)) $output .= "<div><label for=\"class_name\">"._NAME.": </label> <input type=\"text\" class=\"textbox\" name=\"class_name\" value=\"".(isset($thisclass) ? $thisclass['class_name'] : "")."\"></div>
			<div><label for=\"type_id\">"._CLASSTYPES.":</label> <select name=\"type_id\" id=\"type_id\">";
		$types = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classtypes");
		while($t = dbassoc($types)) {
			$output .= "<option value='".$t['classtype_id']."'".($t['classtype_id'] == $thisclass['class_type'] ? " selected" : "").">".$t['classtype_title']."</option>";
		}
		$output .= "</select></div>
			<div id='submitdiv'><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></div></form><div class='cleaner'>&nbsp;</div></div>";
		$output .= write_message(_CLASSNOTE);
		$list = false;
	}
}
if($list) {
	$types = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classtypes");
	$output .= "<div style='text-align: center;'><table class=\"tblborder\" style=\"margin: 0 auto; text-align: left;\"><tr><th clas=\"tblborder\">"._CLASSTYPES."</th><th clas=\"tblborder\">"._OPTIONS."</th></tr>";
	if(!dbnumrows($types)) $output .= "<tr><td class=\"tblborder\" colspan='2'>"._NORESULTS."</td></tr>";
	while($type = dbassoc($types)) {
		$output .= "<tr>
			<td class=\"tblborder\">
				<a href=\"admin.php?action=classifications&amp;listtype=$type[classtype_id]\">$type[classtype_title]</a>
			</td>
			<td class=\"tblborder\">
				<a href=\"admin.php?action=classifications&amp;type=$type[classtype_id]\">"._EDIT."</a> |
				<a href=\"admin.php?action=classifications&amp;delete=type&amp;type_id=$type[classtype_id]\">"._DELETE."</a>
			</td></tr>";
	}	
	$output .= "<tr><td class=\"tblborder\" colspan=\"2\" style=\"text-align: center;\"><a href=\"admin.php?action=classifications&type=new\">"._ADDNEWCLASSTYPE."</a></td></tr></table></div>";
	$output .= write_message(_CLASSLISTNOTE);
}
?>