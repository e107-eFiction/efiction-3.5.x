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

if(!defined("_CHARSET")) exit( );

// Adds the categories selection section to a form.

if(!isset($catid)) $catid = array( );

$output .= "<div style='width: 99.9%; float: left;'>\r\n
		<div style='width: 40%; float: left;'><label for='catoptions'>"._CATOPTIONS."</label> <br />
		<select name='catoptions' id='catoptions' multiple='multiple' size='8' onchange='resetCats(\"catoptions\");' style='width: 100%;'>";
$selectedCats = "";
$cats = array( );
foreach($catlist as $cat => $info) {
	if($info['pid'] == -1) 
		$output .= "<option value='$cat'".(isset($info['locked']) ? " class='locked'" : "").">".$info['name']."</option>\r\n";
	if((is_array($catid) && in_array($cat, $catid)) && $info['locked'] != 1) {
		$selectedCats .= "<option value='$cat'>".$info['name']."</option>\r\n";
		$cats[] = $cat;
	}
}
$output .= "</select></div>
		<div style='float: left; width: 20%; text-align: center; padding-top: 3em;'>
			<input type='button' class='button' value='>' name='Select' onClick='addCat(\"catoptions\", \"selectCats\");'><br /><br />
			<input type='button' class='button' value='"._REMOVE."' onClick='removeCat(\"selectCats\");'>
		</div>
		<div style='width: 40%; float: left;'>
			<label for='catoptions'>"._SELECTCATS."</label> ".(count($cats) < 1 ? "<span style=\"font-weight: bold; color: red\">*</span>" : "")."<br />
			<select name='selectCats' id='selectCats' multiple='multiple' size='8' style='width: 100%;'>".
			(!empty($selectedCats) ? $selectedCats : "")."</select></div>
		</div>
		<input type='hidden' name='catid' id='catid' value='".(isset($cats) ? implode(",", $cats) : "")."'>";
?>