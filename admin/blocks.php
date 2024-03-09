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

function save_blocks( $blocks ) {
	
	foreach($blocks as $block =>$value) {
		unset($blockvars);
		if((isset($_GET['admin']) && $_GET['admin'] != $block) || (isset($_GET['init']) && $_GET['init'] != $block)) continue;
		foreach($value as $var=>$val) {
			if($var != "name" && $var != "title" && $var != "file" && $var != "status") $blockvars[$var] = $val;
		}
		$tmp_title = isset($value['title']) ? escapestring($value['title']) : "";
		$tmp_file  = isset($value['file']) ?  $value['file'] : "";
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_blocks SET block_name = '$block', block_title = '". $tmp_title."', block_file = '". $tmp_file."', block_status = '".$value['status']."', block_variables =  '".(isset($blockvars) ? addslashes(serialize($blockvars)) : "")."' WHERE block_name = '$block'");
	}
}
if(isset($_GET['admin'])) $admin = $_GET['admin'];
else $admin = false;
$content = "";

	if($admin) {
		$output .= "<div id='pagetitle'>"._ADMIN." - ".(isset($blocks[$_GET['admin']]['title']) ? $blocks[$_GET['admin']]['title'] : "")."</div>";
		include("blocks/".$_GET['admin']."/admin.php");
		save_blocks( $blocks );
	}
	if(isset($_GET['init'])) {
		if(file_exists("blocks/".$_GET['init']."/init.php")) include("blocks/".$_GET['init']."/init.php");
		else return _ERROR;
		save_blocks( $blocks );
	}

	if(isset($_POST['submit']) && empty($admin)) {
		$x = 1;
 
		while(isset($_POST[$x])) {
			// activate inactive blocks.
			if(isset($blocks[$_POST[$x]])) {
				$tmp_title  = isset($_POST[$x . "_title"]) ? descript($_POST[$x . "_title"]) : "";
				$tmp_status = isset($_POST[$x . "_status"]) ? descript($_POST[$x . "_status"]) : "";
				$blocks[$_POST[$x]]['title'] = $tmp_title;
				$blocks[$_POST[$x]]["status"] = $tmp_status;
			}
			$x++;	
		}
		save_blocks( $blocks );
		$output .= write_message(_ACTIONSUCCESSFUL);
	}
// In case the skin has already overridden the block settings or we've just changed the settings.

$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks");
while ($block = dbassoc($blockquery))
{
	if ($block['block_variables'] > 0)
	{
		$block_vars = @unserialize($block['block_variables']);
		if ($block_vars)
		{
			$blocks[$block['block_name']] = $block_vars;
		}
		//else
		//{
		//	print_r($block);
		//}
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	$blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
	unset($content);

if(empty($admin)) {
	$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks\"><center><table class=\"tblborder\" cellpadding=\"3\"><tr><th>"._NAME."</th><th>"._TITLE."</th><th>"._STATUS."</th><th>"._ADMIN."</th></tr>";
	$x = 1;
	$directory = opendir("blocks");
	while($filename = readdir($directory)) {
			if($filename=="." || $filename==".." || !is_dir("blocks/".$filename)) continue;
			$output .= "<tr><td class=\"tblborder\"><input type=\"hidden\" name=\"$x\" value=\"$filename\">$filename</td>";
			if(isset($blocks[$filename]['file'])) $output .= "<td class=\"tblborder\"><input name=\"{$x}_title\" type=\"text\" class=\"textbox\" value=\"".stripslashes($blocks[$filename]['title'])."\"><input name=\"{$x}_file\" type=\"hidden\" value=\"".$blocks[$filename]['file']."\"></td>
				<td class=\"tblborder\"><select name=\"{$x}_status\">
					<option value=\"0\"".(!$blocks[$filename]['status'] ? " selected" : "").">"._INACTIVE."</option>
					<option value=\"1\"".($blocks[$filename]['status'] == 1? " selected" : "").">"._ACTIVE."</option>
					<option value=\"2\"".($blocks[$filename]['status'] == 2 ? " selected" : "").">"._INDEXONLY."</option>
					</select></td>
				<td class=\"tblborder\">".(file_exists("blocks/$filename/admin.php") ? "<a href=\"admin.php?action=blocks&admin=$filename\">"._OPTIONS."</a>" :  _NONE);
			else $output .= "<td class=\"tblborder\" colspan=\"3\" align=\"center\"><a href=\"admin.php?action=blocks&init=$filename\">"._INITIALIZE."</a>";
			$output .= "</td></tr>";
			$x++;
	}
	closedir($directory);
	$output .= "</table><br /><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></form>";
}
// Now load the skin settings back in.
	if(file_exists("$skindir/variables.php")) include("$skindir/variables.php"); 
?>