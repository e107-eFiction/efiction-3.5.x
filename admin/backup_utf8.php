<?php
// ----------------------------------------------------------------------
// eFiction 3.0
// Copyright (c) 2007 by Tammy Keefer
// Valid HTML 4.01 Transitional
// Based on eFiction 1.1
// Copyright (C) 2003 by Rebecca Smallwood.
// http://efiction.sourceforge.net/
//
// Size fix / UTF-8 feature 2016-05-18
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
define("_BASEDIR", "../");
define("_CHARSET", "utf-8");
include("../config.php");
 
$settingsresults = dbquery("SELECT * FROM ".$settingsprefix."fanfiction_settings WHERE sitekey = '$sitekey'");
$settings = dbassoc($settingsresults);
foreach($settings as $var => $val) {
	$$var = stripslashes($val);
	$settings[$var] = htmlspecialchars($val);
}
if(!defined("SITEKEY")) define("SITEKEY", $settings['sitekey']);
unset($settings['sitekey']);
if(!defined("TABLEPREFIX")) define("TABLEPREFIX", $settings['tableprefix']);
unset($settings['tableprefix']);
$debug = 0;
include("../languages/".$language.".php");
include("../languages/".$language."_admin.php");
$file_name = str_replace(" ", "_", $sitename).date("m-d-Y").".sql";
header("Content-Type: text/html; charset=utf-8");
Header("Content-type: application/octet-stream");
Header("Content-Disposition: attachment; filename=".$file_name."");

session_start( );
require_once("../includes/queries.php");
require_once("../includes/get_session_vars.php");
$debug = false;  // Because we don't want debug info mucking up our backup.

if(!isADMIN) die( );

// Database backup
function datadump ($table) {
    echo "# Dump of $table \r\n";
    echo "# Dump DATE : " . date("d-M-Y") ."\r\n\r\n";

    $tabledata = dbquery("SELECT * FROM $table");

	while($t = dbassoc($tabledata)) {
		echo "INSERT INTO ".$table." ";
		$row = array( );
		foreach($t AS $field => $value) {
			//$value = utf8_encode (escapestring($value) );
			$value = escapestring($value);
			$value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
			$value = str_replace("\n","\\n",$value);
			if (isset($value)) $row[$field] = "\"$value\"";
			else $row[$field] = "\"\"";
		}   
		echo "VALUES(".implode(", ", $row).");\r\n";
	}
	echo "\r\n\r\n\r\n";
}

	$alltables = dbquery("SHOW TABLES");

	while ($table = dbassoc($alltables)) {
			datadump(current($table));
	}

 	if($logging) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_BACKUP, USERPENNAME, USERUID))."', '".USERUID. "', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'AM', " . time() . ")");
	exit( );

?>
