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

$paneltypes = array("A" => _ADMIN, "U" => _USERACCOUNT, "P" => _PROFILE, "F" => _FAVOR, "S" => _SUBMISSIONS, "B" => _BROWSE, "L" => _10LISTS);

if(isset($_GET['type'])) $type = $_GET['type'];
else $type = false;
if(!empty($_GET['go'])) {
	$go = $_GET['go'];
	$order = $_GET['order'];
	if($go == "up") $oneabove = $order - 1;
	else $oneabove = $order + 1;
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_panels SET panel_order = '$order' WHERE panel_type = '$type' and panel_order = '$oneabove'");
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_panels SET panel_order = '$oneabove' WHERE panel_id = '".$_GET['panel']."'");	
}
if(!empty($_GET['edit'])) {
	if(!empty($_POST['submit'])) {
		$panel_name = addslashes(strip_tags(descript($_POST['panel_name'])));
		$panel_title = addslashes(strip_tags(descript($_POST['panel_title'])));
		$panel_url = addslashes(strip_tags(descript($_POST['panel_url'])));
		$panel_level = (!empty($_POST['panel_level']) && isNumber($_POST['panel_level']) ? $_POST['panel_level'] : 0);
		$panel_hidden = (!empty($_POST['panel_hidden']) ? 1 : 0);
		$panel_type = strip_tags(descript($_POST['panel_type']));
		if(!$panel_hidden) {
			$oldinfo = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_id = '".$_GET['edit']."'");
			$panelinfo = dbassoc($oldinfo);
			$nextorder = 0;
			if($panelinfo['panel_hidden'] || $_GET['edit'] == "new") {
				$order = dbquery("SELECT COUNT(panel_id) FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_type = '$panel_type' AND panel_hidden = 0".($panel_type == "A" ? " AND panel_level = '$panel_level'" : ""));
				list($nextorder) = dbrow($order);
				$nextorder++;
			}
			else {
				$nextorder = 0;
			}
		}
		else if($panel_hidden) $nextorder = 0;
		if($_GET['edit'] != "new") $result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_panels SET panel_name = '$panel_name', panel_title = '$panel_title', panel_url = '$panel_url', panel_level = '$panel_level', panel_hidden = '$panel_hidden', panel_type = '$panel_type'".(!empty($panel_hidden) ? ", panel_order = '$nextorder'" : "")." WHERE panel_id = '".$_GET['edit']."'");
		else $result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_panels(`panel_name`, `panel_title`, `panel_url`, `panel_level`, `panel_hidden`, `panel_type`, `panel_order`) VALUES( '$panel_name', '$panel_title', '$panel_url', '$panel_level', '$panel_hidden', '$panel_type', '$nextorder')");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL);
		else $output .= write_error(_ERROR);
	}
	else {
		$panelquery= dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_id = '".$_GET['edit']."' LIMIT 1");
		$panelinfo = dbassoc($panelquery);
		$output .= "<div class='sectionheader'>".($_GET['edit'] == "new" ? _ADDPANEL : _EDITPANEL)."</div>
			<div id='settingsform'><form method=\"POST\" enctype=\"multipart/form-data\" style='width: 450px; margin: 0 auto;' action=\"admin.php?action=panels&amp;edit=".$_GET['edit']."\">
			<label for='panel_name'>"._NAME.":</label> <input type='text' class='textbox' name='panel_name' id='panel_name' value='".$panelinfo['panel_name']."'><br />
			<label for='panel_title'>"._TITLE.":</label> <input type='text' class='textbox' name='panel_title' id='panel_title' value='".$panelinfo['panel_title']."'><br />
			<label for='panel_url'>"._PANELURL.":</label> <input type='text' class='textbox' name='panel_url' id='panel_url' value='".$panelinfo['panel_url']."'><br />
			<label for='panel_level'>"._LEVEL.":</label> <select name='panel_level' id='panel_level'>";
		for($x = 0; $x < 5; $x++) { $output .= "<option".($panelinfo['panel_level'] == $x ? " selected" : "").">$x</option>"; }
		$output .= "</select><br />
			<label for='panel_hidden'>"._HIDDEN.":</label> <input type='checkbox' class='checkbox' name='panel_hidden' id='panel_hidden' ".($panelinfo['panel_hidden'] ? " checked" : "")."><br />
			<label for='panel_type'>"._TYPE.":</label> <input type='text' class='textbox' name='panel_type' id='panel_type' value='".$panelinfo['panel_type']."' size='2'><br />
			<div style='margin: 1em; text-align: center;'><INPUT type=\"submit\" class=\"button\" value=\""._SUBMIT."\" name=\"submit\"></div></form></div>";
	}
}
else if(!empty($_GET['delete'])) {
	$panel_id = $_GET['delete'];
	$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
	if($confirm == "yes" && isNumber($panel_id)) {
		$result = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_id = '$panel_id' LIMIT 1");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL);
		else $output .= write_error(_ERROR);
	}
	else if ($confirm == "no") 	{
		$output .= write_message(_ACTIONCANCELLED);
	}
	else {
		$output .= write_message(_CONFIRMDELETE."<br /><br />
			[ <a href=\"admin.php?action=panels&amp;delete=$panel_id&amp;confirm=yes\">"._YES."</a> | <a href=\"admin.php?action=panels&amp;delete=$panel_id&amp;confirm=no\">"._NO."</a> ]");
	}
}
else {
	$output .= "<form name=\"list\" action=\"\"><div class='sectionheader'>"._PANELS." <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_PANELS."</span></A><br /> <select name=\"list\" onchange=\"if(this.selectedIndex.value != 'false') document.location = document.list.list.options[document.list.list.selectedIndex].value\">";
	$output .= "<option value=\"admin.php?action=panels\">"._ALL."</option>";
	$paneltypelist = dbquery("SELECT DISTINCT panel_type FROM ".TABLEPREFIX."fanfiction_panels ORDER BY panel_type");
	while($t = dbrow($paneltypelist)) {
		$output .= "<option value='admin.php?action=panels&amp;type=$t[0]'".($type == $t[0] ? " selected" : "").">$t[0] - ".$paneltypes[$t[0]]."</option>";
	}
	$output .= "</select>";
	if($type == "A") {
		$output .= " "._LEVEL." <select name=\"list2\" onchange=\"if(this.selectedIndex.value != 'false') document.location = document.list.list2.options[document.list.list2.selectedIndex].value\">
			<option value=\"admin.php?action=panels&amp;type=A\">"._ALL."</option>";
		for($x = 1; $x < 4; $x++) {
			$output .= "<option value='admin.php?action=panels&amp;type=A&amp;level=$x'".(isset($_GET['level']) && $_GET['level'] == $x ? " selected" : "")."> $x </option>";
		}
		$output .= "</select>";
	}
	$output .= "</div></form>";
	$list = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels".($type ? " WHERE panel_type = '$type'".($type == "A" && !empty($_GET['level']) ? " AND panel_level = '".$_GET['level']."'" : "") : "")." ORDER BY panel_type, panel_hidden DESC, ".($type == "A" ? "panel_level DESC,  " : "")."panel_order ASC");
	$num = dbnumrows($list);
	if($num > 0) $output .= "<table class='tblborder' style='margin: 0 auto;'><tr><th class='tblborder'>"._NAME."</th>".(!$type || ($type == "A" && empty($_GET['level'])) ? "<th class='tblborder'>"._TYPE."</th>" : "<th class='tblborder'>"._ORDER."</th>")."<th class='tblborder'>"._OPTIONS."</th></tr><tr><td class='tblborder' colspan='3' align='center'><a href='admin.php?action=panels&amp;edit=new'>"._ADDNEWPANEL."</a></td></tr>";
	$count = 0;
	while($panel = dbassoc($list)) {
		if(!$panel['panel_hidden']) $count++;
		else $num--;
		$output .= "<tr><td class='tblborder'>".$panel['panel_title']."</td><td class='tblborder'>";
		if(!$type) $output .= $panel['panel_type']." - ".$paneltypes[$panel['panel_type']];
		else if($type == "A" && empty($_GET['level'])) $output .= $panel['panel_hidden'] ? _HIDDEN :  _LEVEL." ".$panel['panel_level'];
		else {
			if($panel['panel_hidden']) $output .= _HIDDEN;
			else {
				$output .= $count > 1 ? "<a href='admin.php?action=panels&amp;type=$type&amp;go=up&amp;panel=".$panel['panel_id'].(!empty($_GET['level']) ? "&amp;level=".$_GET['level'] : "")."&amp;order=".$panel['panel_order']."'>$up</a>" : "";
				$output .= $count < $num ? "<a href='admin.php?action=panels&amp;type=$type&amp;go=down&amp;panel=".$panel['panel_id'].(!empty($_GET['level']) ? "&amp;level=".$_GET['level'] : "")."&amp;order=".$panel['panel_order']."'>$down</a>" : "";
			}
		}
		$output .= "</td><td class='tblborder'><a href='admin.php?action=panels&amp;edit=".$panel['panel_id']."'>"._EDIT."</a> | <a href='admin.php?action=panels&amp;delete=".$panel['panel_id']."'>"._DELETE."</a></td></tr>";
	}
	$output .= "</table>";
}
?>
