<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
// Also Like Module developed for eFiction 3.0
// // http://efiction.hugosnebula.com/
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
$current = "adminarea";
include ("../../header.php");
                                     
//make a new TemplatePower object
if(file_exists( "$skindir/default.tpl")) $tpl = new TemplatePower("$skindir/default.tpl" );
else $tpl = new TemplatePower(_BASEDIR."default_tpls/default.tpl");
$tpl->assignInclude( "header", "$skindir/header.tpl" );
$tpl->assignInclude( "footer", "$skindir/footer.tpl" );
include(_BASEDIR."includes/pagesetup.php");
include_once(_BASEDIR."languages/".$language."_admin.php");
if(!isADMIN) accessDenied( );       
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
if($confirm == "yes") {
	list($installed) = dbrow(dbquery("SELECT COUNT(code_module) FROM ".TABLEPREFIX. "fanfiction_codeblocks WHERE code_module = 'notifications'"));
	if(!$installed) {
 
		$check= dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_settings LIKE 'notifications'"));
		if (!$check) dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_settings` ADD `notifications` TEXT NOT NULL default ''");
 
		include("version.php");
		dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_modules`(`version`, `name`) VALUES('$moduleVersion', '$moduleName')");
 	    }
	$output = write_message(_ACTIONSUCCESSFUL);
}
else if($confirm == "no") {
	$output = write_message(_ACTIONCANCELLED);
}
else {
	$output = write_message(_CONFIRMINSTALL."<br /><a href='install.php?confirm=yes'>"._YES."</a> "._OR." <a href='install.php?confirm=no'>"._NO."</a>");
}
$tpl->assign("output", $output);
$tpl->printToScreen( );
?>
