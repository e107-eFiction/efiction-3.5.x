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


include ("../../header.php");

if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
else $tpl = new TemplatePower("../../default_tpls/default.tpl");
//let TemplatePower do its thing, parsing etc.

include("../../includes/pagesetup.php");

if(file_exists("{$language}.php")) include_once("{$language}.php");
else include_once("en.php");

$shouts = dbquery("SELECT shouts.*, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_shoutbox as shouts LEFT JOIN "._AUTHORTABLE." ON "._UIDFIELD." = shouts.shout_name ORDER BY shout_datestamp DESC");
$totalshouts = dbnumrows($shouts);
$shouts = dbquery("SELECT shouts.*, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_shoutbox as shouts LEFT JOIN "._AUTHORTABLE." ON "._UIDFIELD." = shouts.shout_name ORDER BY shout_datestamp DESC LIMIT $offset, $itemsperpage");
if(dbnumrows($shouts) > 0) {
	if(!empty($blocks['shoutbox']['shoutdate'])) $shoutdate = $blocks['shoutbox']['shoutdate'];
	else $shoutdate = $dateformat." ".$timeformat;
	$output .= "<div class='sectionheader'>"._SHOUTARCHIVE."</div><div style='width: 80%;margin: 0 auto;'>";
	while($shout = dbassoc($shouts)) {
		if(isNumber($shout['shout_name']) && isset($shout['penname'])) $shoutname = "<a href='"._BASEDIR."viewuser.php?uid=".$shout['shout_name']."'>".$shout['penname']."</a>";
		else if(isset($shout['shout_name'])) $shoutname = $shout['shout_name'];
		else $shout = _GUEST; // Just in case.
		$output .= "
<div class='tblborder'><span class='sbname'>$shoutname</span><br />\n
<span class='sbshout'>".stripslashes($shout['shout_message'])."</span><br />\n
<span class='sbdatetime'>".date("$shoutdate", $shout['shout_datestamp'])." [<a href='../../admin.php?action=blocks&amp;admin=shoutbox&amp;delete=".$shout['shout_id']."' class='sbadmin'>"._DELETE."</a>] [<a href='../../admin.php?action=blocks&amp;admin=shoutbox&amp;shout_id=".$shout['shout_id']."' class='sbadmin'>"._EDIT."</a>]</span></div>\n<br />";
	}
	$output .= "</div>";
	if($totalshouts > $itemsperpage) $output .= build_pagelinks("archive.php?", $totalshouts, $offset);
}
else $output .= write_message(_NOSHOUTS);
$tpl->assign("output", "<div id='pagetitle'>"._SHOUTARCHIVE."</div>\n\n$output");
$tpl->printToScreen();

?>