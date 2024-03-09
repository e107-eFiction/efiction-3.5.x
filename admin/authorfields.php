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

$field = isset($_GET['field']) ? $_GET['field'] : false;
$list = true;
$fieldtypes = array("1" => _FIELDURL, "2" => _FIELDSELECT, "3" => _FIELDYESNO, "4" => _FIELDIDURL, "5" => _FIELDCUSTOM, "6" => _TEXT);
if(!empty($_GET['delete'])) {
	$delete = $_GET['delete'];
	$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
	if($confirm == "yes" && isNumber($delete)) {
		$result = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_authorfields WHERE field_id = '$delete' LIMIT 1");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL);
		else $output .= write_error(_ERROR);

	}
	else if($confirm == "no") {
		$output .= write_message(_ACTIONCANCELLED);
	}
	else {
		$output .= write_message(_CONFIRMDELETE."<br /><br />
					[ <a href=\"admin.php?action=authorfields&amp;delete=$delete&amp;confirm=yes\">"._YES."</a> | <a href=\"admin.php?action=authorfields&amp;delete=$delete&amp;confirm=no\">"._NO."</a> ]");

	}
}
if(!empty($_GET['edit'])) {
	$new = $_GET['edit'] == "new" ? true : false;
	if(isset($_POST['submit'])) {
		$field_name = isset($_POST['field_name']) ? descript($_POST['field_name']) : "";
		$field_title = isset($_POST['field_title']) ? descript($_POST['field_title']) : "";
		$field_type = isset($_POST['field_type']) && isNumber($_POST['field_type'])? $_POST['field_type'] : "0";
		$field_on = isset($_POST['field_on']) && $_POST['field_on'] == "on" ? "1" : "0";
		$field_options = "";
		$code_in = "";
		$code_out = "";
		if(!$field_type) {
			$output .= write_error(_NOFIELDTYPE);	
		}
		else {
			if($field_type == 4) $field_options = isset($_POST['options_4']) ? descript($_POST['options_4']) : "";
			if($field_type == 2) $field_options = implode("|#|", explode("\r\n", ltrim(strip_tags(preg_replace( '!<p>!iU', "\r\n",  stripslashes(trim($_POST['options_2']))), preg_replace("!<p>!iu", "", $allowed_tags)))));
			if($field_type == 5) {
				$code_in = isset($_POST['code_in']) ? descript($_POST['code_in']) : "";
				$code_out = isset($_POST['code_out']) ? descript($_POST['code_out']) : "";
			}
			if($new) {
				$result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_authorfields(`field_name`, `field_title`, `field_type`, `field_options`, `field_code_in`, `field_code_out`, `field_on`) 
					VALUES('".escapestring($field_name)."', '".escapestring($field_title)."', '$field_type', '".escapestring($field_options)."', '".escapestring($code_in)."', '".escapestring($code_out)."', '$field_on');");
			}
			else {
				$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorfields SET field_type = '$field_type', field_name = '".escapestring($field_name)."', field_title = '".escapestring($field_title)."', field_options = '".escapestring($field_options)."', field_code_in = '".escapestring($code_in)."', field_code_out = '".escapestring($code_out)."', field_on = '$field_on' WHERE field_id = '".$_GET['edit']."'");
			}
			if($result) $output .= write_message(_ACTIONSUCCESSFUL);
			else $output .= write_error(_ERROR);
		}
	}
	else {
		if($new) {
			$fieldinfo = array("field_type" => "", "field_id" => "", "field_name" => "", "field_title" => "", "field_options" => "", "field_code_in" => "", "field_code_out" => "", "field_on" => "0");
		}
		else {
			$fieldinfo = dbassoc(dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorfields WHERE field_id = '".$_GET['edit']."' LIMIT 1"));
		}
		$output .= "
		<div class='sectionheader'>".($new ? _ADDFIELD : _EDITFIELD)."</div>
			<div><form method=\"POST\" id='settingsform' enctype=\"multipart/form-data\" action=\"admin.php?action=authorfields&amp;edit=".$_GET['edit']."\">
			<label for='field_name'>"._NAME.":</label> <input type='text' class='textbox' name='field_name' id='field_name' value='".$fieldinfo['field_name']."'><br />
			<label for='field_title'>"._TITLE.":</label> <input type='text' class='textbox' name='field_title' id='field_title' value='".$fieldinfo['field_title']."'><br />
			<div><label for='field_on'>"._FIELDON.":</label> <input type='checkbox' name='field_on' id='field_on'".($fieldinfo['field_on'] ? " checked='checked'" : "")."></div>
			<label for='field_type'>"._TYPE.":</label> <select name='field_type' id='field_type' onchange=\"javascript:displayTypeOpts()\">";
		foreach($fieldtypes as $id => $name) {
				$output .= "<option value='$id'".(isset($fieldinfo) && $fieldinfo['field_type'] == $id ? " selected" : "").">$name</option>";
		}
		$output .= "</select> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_FIELDTYPE."</span></A><br />
			<div id='opt_2'".($fieldinfo['field_type'] == 2 ? "" :"style='display: none;'")."><label for='options_2'>"._FIELDSELECT.":</label><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_FIELDSELECT."</span></A><div style='margin-left: 30%; padding-left: 1em;'><textarea style='width: 100%; height: 150px;' name='options_2' cols='35' rows='4' class='mceNoEditor' id='options_2'>".($fieldinfo['field_type'] == 2 ? preg_replace("@\|#\|@", "\n", stripslashes($fieldinfo['field_options'])) : "")."</textarea></div>";
				if($tinyMCE) 
				$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('options_2');\"><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
		$output .= "</div>
			<div id='opt_4'".($fieldinfo['field_type'] == 4 ? "" :"style='display: none;'")."><label for='options_4'>"._FIELDIDURL.":</label><input type='text' name='options_4' id='options_4' ".($fieldinfo['field_type'] == 4 ? "value='".stripslashes($fieldinfo['field_options'])."'" : "")."><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_FIELDIDURL."</span></A></div>
			<div id='opt_5'".($fieldinfo['field_type'] == 5 ? "" :"style='display: none;'").">
				<label for='code_in'>"._FIELDCODEIN.":</label><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_FIELDCODEIN."</span></A><div style='margin-left: 30%; padding-left: 1em;'><textarea cols='35' rows='4' style='width: 100%; height: 150px;' name='code_in' class='mceNoEditor' id='code_in'>".stripslashes($fieldinfo['field_code_in'])."</textarea>";
		$output .= "</div>
				<label for='code_out'>"._FIELDCODEOUT.":</label><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_FIELDCODEOUT."</span></A><div style='margin-left: 30%; padding-left: 1em;'><textarea name='code_out' cols='35' rows='4'  style='width: 100%; height: 150px;' class='mceNoEditor' id='code_out'>".stripslashes($fieldinfo['field_code_out'])."</textarea>";
		$output .= "</div>
			</div>
			<div id='submitdiv'><INPUT type='submit' id='submit' class='button' value='"._SUBMIT."' name='submit'></div></form><div class='cleaner'>&nbsp;</div></div>";

		$list = false;
	}
}
else if(!empty($_GET['delete'])) {

}
if($list) {
	$fields = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_authorfields");
	if(dbnumrows($fields) > 0) {
		$output .= "<table class='tblborder' style='margin: 0 auto;'><tr><th class='tblborder'>"._NAME."</th><th class='tblborder'>"._STATUS."</th><th class='tblborder'>"._OPTIONS."</th></tr>";
		while($field = dbassoc($fields)) {
			$output .= "<tr><td class='tblborder'>".(empty($field['field_title']) ? $field['field_name'] : $field['field_title'])."</td><td class='tblborder' style='text-align: center;'>".($field['field_on'] ? _ON : _OFF)."</td><td class='tblborder'><a href='admin.php?action=authorfields&amp;edit=".$field['field_id']."'>"._EDIT."</a> | <a href='admin.php?action=authorfields&amp;delete=".$field['field_id']."'>"._DELETE."</a></td></tr>";
		}
		$output .= "<tr><td class='tblborder' colspan='3' style='text-align: center;'><a href='admin.php?action=authorfields&amp;edit=new'>"._ADDNEWFIELD."</a></td></tr></table>";
	}
}
?>