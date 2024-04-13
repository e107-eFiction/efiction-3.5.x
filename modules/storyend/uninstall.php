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
	dbquery("DELETE FROM `".TABLEPREFIX."fanfiction_codeblocks` WHERE code_module = 'storyend'");
	dbquery("DELETE FROM `".TABLEPREFIX."fanfiction_panels` WHERE panel_name = 'alsolike'"); // Removes the also like browse page.
	include("version.php");
	dbquery("DELETE FROM `".TABLEPREFIX."fanfiction_modules` WHERE code_module = '$moduleName'");	
	
	$output = write_message(_ACTIONSUCCESSFUL);
}
else if($confirm == "no") {
	$output = write_message(_ACTIONCANCELLED);
}
else {
	$modquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_module = 'storyend'");
	if(dbnumrows($modquery)) $output = write_message(_CONFIRMUNINSTALL." "._UNINSTALLWARNING."<br /><a href='uninstall.php?confirm=yes'>"._YES."</a> "._OR." <a href='uninstall.php?confirm=no'>"._NO."</a>");
	else $output .= write_message(_MODNOTINSTALLED);
}
$tpl->assign("output", $output);
$tpl->printToScreen( );
?>