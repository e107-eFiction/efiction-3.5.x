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



global $language;
$content = "";
$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks WHERE block_name = 'categories'");
while($block = dbassoc($blockquery)) {
	if ($block['block_variables'])
	{
		$blocks[$block['block_name']] = unserialize($block['block_variables']);
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	 $blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
if(empty($blocks['categories']['tpl'])) {
	include("blocks/".$blocks['categories']['file']);
	$tpl->gotoBlock("_ROOT");
}
if(file_exists("blocks/categories/{$language}.php")) include("blocks/categories/{$language}.php");
else include("blocks/categories/en.php");
	if(isset($_POST['submit'])) {
		$blocks['categories']['columns'] = $_POST['columns'];
		$blocks['categories']['template'] = $_POST['template'];
		$output .= "<center>"._ACTIONSUCCESSFUL."</center>";
		save_blocks( $blocks );
	}
	else  {
		$template = (!empty($blocks['categories']['template']) ? $blocks['categories']['template'] : "{image} {link} [{count}] {description}"); 
		if(empty($blocks['categories']['tpl'])) $output .= "<div style='text-align: center;'><b>"._CURRENT.":</b><br /><div class=\"tblborder\" style=\"width: 80%; margin: 0 auto; text-align: left;\">$content</div><br /></div>";
		$output .= "<div><form method=\"POST\" id=\"settingsform\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks&admin=categories\">
			<textarea name=\"template\" id=\"template\" rows=\"5\" cols=\"40\">$template</textarea><br />";
		if($tinyMCE) 
			$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('template');\"><label for='toggle'>"._TINYMCETOGGLE."</label></div>";	
		$output .= "<select name=\"columns\" class=\"textbox\" style='margin: 3px;'><option value=\"0\"".(empty($blocks['categories']['columns']) ? " selected" : "").">"._ONECOLUMN."</option>
					<option value=\"1\"".(!empty($blocks['categories']['columns']) ? " selected" : "").">"._MULTICOLUMN."</option></select> 
			<select name=\"tpl\" class=\"textbox\" style='margin: 3px;'><option value=\"0\"".(empty($blocks['categories']['tpl']) ? " selected" : "").">"._DEFAULT."</option>
					<option value=\"1\"".(!empty($blocks['categories']['tpl']) ? " selected" : "").">"._USETPL."</option></select><br />
			<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></form><div style='clear: both;'>&nbsp;</div>".write_message(_CATBLOCKNOTE)."</div>";
	}
?>