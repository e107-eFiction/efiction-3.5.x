<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
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
if(isset($_GET['action'])) $current = $_GET['action'];
else $current = "login";

	if($current == "logout" ) {
		define("_LOGOUTCHECK", true);		
		include("user/logout.php");
	}
	
	if(!empty($_POST['submit']) && $current == "login") {
	session_start();
	define("_LOGINCHECK", true);		
		include("user/login.php");
	}

require_once("header.php");
//make a new TemplatePower object
if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
else $tpl = new TemplatePower("default_tpls/default.tpl");
if(file_exists("$skindir/listings.tpl")) $tpl->assignInclude( "listings", "./$skindir/listings.tpl" );
else $tpl->assignInclude( "listings", "./default_tpls/listings.tpl" );
include("includes/pagesetup.php");

if($action) $current = $action;
else $current = "user";
// end main function 
if((empty($action) || $action == "login") && isMEMBER) {
	$output .= "<div id=\"pagetitle\">"._USERACCOUNT."</div>
		<div class=\"tblborder\" id=\"useropts\" style=\"padding: 5px; width: 50%; margin: 1em 25%;\">";
	$panelquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_hidden != '1' AND panel_level = '1' AND (panel_type = 'U' ".(!$submissionsoff || isADMIN ? " OR panel_type = 'S'" : "").($favorites ? " OR panel_type = 'F'" : "").") ORDER BY panel_type, panel_order, panel_title ASC");
	if(!dbnumrows($panelquery)) $output .= _FATALERROR;
	while($panel = dbassoc($panelquery)) {
		if(!$panel['panel_url']) $output .=  "<a href=\"user.php?action=".$panel['panel_name']."\">".$panel['panel_title']."</a><br />\n";
		else $output .= "<a href=\"".$panel['panel_url']."\">".$panel['panel_title']."</a><br />\n";
	}
	$output .= "</div>\n";
}
else if(!empty($action)) {
	$panelquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_name = '$action' AND (panel_type='U' ".(!$submissionsoff || isADMIN ? " OR panel_type = 'S'" : "").($favorites ? " OR panel_type = 'F'" : "").") LIMIT 1");
	if(dbnumrows($panelquery)) {
		$panel = dbassoc($panelquery);
		if($panel['panel_level'] > 0 && !isMEMBER) accessDenied( );
		if($panel['panel_url'] && file_exists(_BASEDIR.$panel['panel_url'])) require_once(_BASEDIR.$panel['panel_url']);
		else if(file_exists("user/{$action}.php")) require_once("user/{$action}.php");
		else $output = write_error(_ERROR);
	}
	else $output .= write_error(_ERROR);
}
else $output = write_error(_NOTAUTHORIZED);
$tpl->assign( "output", $output );
$tpl->printToScreen();
dbclose( );
?>