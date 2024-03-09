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

$current = "tens";

include ("header.php");

if(file_exists("$skindir/browse.tpl")) $tpl = new TemplatePower( "$skindir/browse.tpl" );
else $tpl = new TemplatePower("default_tpls/browse.tpl");
if(file_exists("$skindir/listings.tpl")) $tpl->assignInclude("listings", "./$skindir/listings.tpl");
else $tpl->assignInclude( "listings", "./default_tpls/listings.tpl" );
$tpl->assignInclude( "header", "./$skindir/header.tpl" );
$tpl->assignInclude( "footer", "./$skindir/footer.tpl" );

$list = isset($_GET['list']) ? $_GET['list'] : false;
include("includes/pagesetup.php");
	if(!$list) {
		$output = "<div id='pagetitle'>".$pagelinks['tens']['text']."</div>";
		$lists = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_type = 'L' AND panel_hidden != '1' AND panel_level = '0' ORDER BY panel_order");
		if(dbnumrows($lists)) $output .= "<div class='tblborder' id='top10list' style='margin: 0 25%;'>";
		while($l = dbassoc($lists)) {
			$output .= "<a href='toplists.php?list=".$l['panel_name']."'>".$l['panel_title']."</a><br />";
		}
		
		if(dbnumrows($lists)) $output .= "</div>";
	}
	else {
		$panelquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_name = '".escapestring($list)."' AND panel_type = 'L' LIMIT 1");
		if(dbnumrows($panelquery)) {
			$panel = dbassoc($panelquery);
			$output .= "<div id='pagetitle'>".$panel['panel_title']."</div>";
			$numrows = 0;
			if($panel['panel_url'] && file_exists(_BASEDIR.$panel['panel_url'])) include($panel['panel_url']);
			else if(file_exists("toplists/{$type}.php")) include("toplists/{$type}.php");
			else $output .= write_error(_ERROR);
		}
		else $output .= write_error(_ERROR);		
	}

$tpl->assign("output", $output);
$tpl->printToScreen( );
?>
