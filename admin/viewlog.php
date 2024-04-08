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

$logtypes = array("RG" => _NEWREG, "ED" => _ADMINEDIT, "DL" => _ADMINDELETE, "VS" => _VALIDATESTORY, "LP"=> _LOSTPASSWORD, "BL" => _BADLOGIN, "RE" => "Reviews", "AM" => "Admin Maintenance", "EB" => "Edit Bio");
$typequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'logtype'");
while($code = dbassoc($typequery)) {
	eval($code['code_text']);
}

$type = isset($_GET['type']) ? $_GET['type'] : false;
if(isset($_GET['purge'])) {
	$result = dbquery("TRUNCATE ".TABLEPREFIX."fanfiction_log");
	if($result) $output .= write_message(_ACTIONSUCCESSFUL);
}
$output .= "<div id=\"pagetitle\">"._VIEWLOG."</div>";
$output .= "<div style='text-align: center; margin: 1ex;'><form name=\"list\" action=\"\"><select name=\"list\" onchange=\"if(this.selectedIndex.value != 'false') document.location = document.list.list.options[document.list.list.selectedIndex].value\">";
$logtypelist = dbquery("SELECT DISTINCT log_type FROM ".TABLEPREFIX."fanfiction_log ORDER BY log_type");
$output .= "<option value=\"admin.php?action=viewlog\">"._ALL."</option>";
while($t = dbrow($logtypelist)) {
	$output .= "<option value='admin.php?action=viewlog&amp;type=$t[0]'".($type == $t[0] ? " selected" : "").">$t[0] - ".(isset($logtypes[$t[0]]) ? $logtypes[$t[0]] : "???")."</option>";
}
$output .= "</select></form></div>";
$countquery = dbquery("SELECT COUNT(log_id) FROM ".TABLEPREFIX."fanfiction_log".($type ? " WHERE log_type = '$type'" : ""));
list($count) = dbrow($countquery);
if($count > 0) {
	$result = dbquery("SELECT log_action, INET6_NTOA(log_ip) as log_ip,  log_timestamp as log_timestamp FROM ".TABLEPREFIX."fanfiction_log".($type ? " WHERE log_type = '$type'" : "")." ORDER BY log_timestamp DESC LIMIT $offset, $itemsperpage");
	$output .= "<table class=\"tblborder\" cellspacing=\"0\" cellpadding=\"3\" style=\"width: 90%; margin: 0 auto;\"><tr class=\"tblborder\"><th>"._ACTION."</th><th>"._IP."</th><th>"._DATE."</th></tr>";
	while ($item = dbassoc($result)) {
			$output .= "<tr class=\"tblborder\">
					<td class=\"tblborder\">".stripslashes($item['log_action'])."</td>
					<td class=\"tblborder\">".$item['log_ip']."</td>
					<td class=\"tblborder\">".date("$dateformat", $item['log_timestamp'])."</td>

				    </tr>";
	}
	$output .= "</table>";
	if($count > $itemsperpage) $output .= build_pagelinks("admin.php?action=viewlog".($type ? "&amp;type=$type&amp;" : "&amp;"), $count, $offset);
}
else $output .= write_message(_NORESULTS);
$output .= write_message("<a href='admin.php?action=viewlog&amp;purge=1'>"._PURGELOG."</a>");
?>
