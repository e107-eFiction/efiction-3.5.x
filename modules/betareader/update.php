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

$current = "betareader";
include ("../../header.php");

//make a new TemplatePower object
if(file_exists( "$skindir/default.tpl")) $tpl = new TemplatePower("$skindir/default.tpl" );
else $tpl = new TemplatePower(_BASEDIR."default_tpls/default.tpl");
$tpl->assignInclude( "header", "$skindir/header.tpl" );
$tpl->assignInclude( "footer", "$skindir/footer.tpl" );
include(_BASEDIR."includes/pagesetup.php");
if(file_exists(_BASEDIR."languages/".$language."_admin.php")) include_once(_BASEDIR."languages/".$language."_admin.php");
else include_once(_BASEDIR."languages/en_admin.php");
if(file_exists(_BASEDIR."languages/".$language.".php")) include_once(_BASEDIR."languages/".$language.".php");
else include_once(_BASEDIR."languages/en.php");
if(!isADMIN) accessDenied( );
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
include("version.php");
list($currentVersion) = dbrow(dbquery("SELECT version FROM ".TABLEPREFIX."fanfiction_modules WHERE name = '$moduleName' LIMIT 1"));
$installed = dbnumrows(dbquery("SELECT COUNT(code_module) FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_module = 'betareader'"));
if(empty($currentVersion) || $currentVersion < "1.4" || !$installed) {
if($confirm == "yes") {
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_modules SET version = '1.4' WHERE name = '$moduleName' LIMIT 1");
	if($currentVersion < "1.2") dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks` (`code_text`, `code_type`, `code_module`) VALUES ( 'include(_BASEDIR.\"modules/betareader/stats.php\");', 'sitestats', 'betareader');");
	$output = write_message(_ACTIONSUCCESSFUL);
}
else if($confirm == "no") {
	$output = write_message(_ACTIONCANCELLED);
}
else {
	$output = write_message(_CONFIRMUPDATE."<br /><a href='update.php?confirm=yes'>"._YES."</a> "._OR." <a href='update.php?confirm=no'>"._NO."</a>");
}
}
else $output .= write_message(_ALREADYUPDATED);
$tpl->assign("output", $output);
$tpl->printToScreen( );
?>