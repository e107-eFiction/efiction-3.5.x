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


$querystring = "";
foreach($_GET as $key=>$value) {
	if($key != "skin") $querystring .= "&amp;$key=$value";
}
if(!isset($hiddenskins)) $hiddenskins = array( );
if(is_string($hiddenskins)) $hiddenskins = explode(",", $hiddenskins);

$content = "<select name=\"skin\" onChange=\"document.location = '".$_SERVER['PHP_SELF']."?skin=' + this.options[this.selectedIndex].value + '$querystring';\">";
$directory = opendir(_BASEDIR."skins");
while($filename = readdir($directory)) {
	if($filename== "." || $filename== ".." || !is_dir(_BASEDIR."skins/".$filename) || (in_array($filename, $hiddenskins) && !isADMIN)) continue;
	$skinlist[strtolower($filename)] = "<option value=\"$filename\"".($siteskin == $filename ? " selected" : "").">$filename</option>";
}
ksort($skinlist);
foreach($skinlist as $s) { $content .= $s; }
unset($skinlist, $s);
closedir($directory);
$content .= "</select>";
?>