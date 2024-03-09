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

$output .= "<div class='sectionheader'>"._HIDDENSKINS."</div>";

if(isset($_POST['submit'])) {
	$hiddenskins = isset($_POST['hiddenskins']) ? implode(",", $_POST['hiddenskins']) : "";
	$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET hiddenskins = '$hiddenskins' WHERE sitekey ='".SITEKEY."' LIMIT 1");
	if($result) $output .= write_message(_ACTIONSUCCESSFUL);
	else $output .= write_error(_ERROR);
}
else {
	$directory = opendir(_BASEDIR."skins");
	if(!isset($hiddenskins)) $hiddenskins = array( );
	if(is_string($hiddenskins)) $hiddenskins = explode(",", $hiddenskins);
	while($filename = readdir($directory)) {
		if($filename== "." || $filename== ".." || !is_dir(_BASEDIR."skins/".$filename)) continue;
		$skinlist[strtolower($filename)] = "<input type='checkbox' value='$filename' name='hiddenskins[]' id='hiddenskins'".(is_array($hiddenskins) && in_array($filename, $hiddenskins) ? " checked" : "")."> $filename<br />\n";
	}
	ksort($skinlist);
	$output .= "<div><form method=\"POST\" style='width: 250px; margin: 1em auto;' class='tblborder' name=\"form\" enctype=\"multipart/form-data\" action=\"admin.php?action=skins\">";
	foreach($skinlist as $s) { $output .= $s; }
	unset($skinlist, $s);
	closedir($directory);
	$output .= "<INPUT type=\"submit\" class=\"button\" style='display: block; margin: 1ex auto;' id=\"submit\" value=\""._SUBMIT."\" name=\"submit\"></form></div>";
	$output .= write_message(_HIDDENNOTE);
}
?>