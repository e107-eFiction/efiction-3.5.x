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

if(empty($favorites)) accessDenied( );

if(!isset($uid)) {
	$output .= "<div id=\"pagetitle\">"._MANAGEFAVORITES."</div>";
	$uid = USERUID;
}
else {
	$authquery = dbquery("SELECT "._PENNAMEFIELD." FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid'");
	list($penname) = dbrow($authquery);
	 $output .= "<div class='sectionheader'>"._FAVORITESOF." $penname</div>";
}
	$output .= "<div class=\"tblborder\" style=\"padding: 5px; width: 200px; margin: 0 auto;\">";
	$panelquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_type = 'F' AND panel_name != 'favlist' ORDER BY panel_title ASC");
	if(!$panelquery) $output .= write_error(_ERROR);
	while($panel = dbassoc($panelquery)) {
		$panellink = "";
		if(substr($panel['panel_name'], 0, 3) == "fav" && $type = substr($panel['panel_name'], 3)) {
			if($panel['panel_name'] == "favlist") continue;
			$itemcount = 0;
			$countquery = dbquery("SELECT COUNT(item) FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '$uid' AND type = '$type'");
			list($itemcount) = dbrow($countquery);
			if(empty($panel['panel_url'])) $output .=  "<a href=\"user.php?action=".$panel['panel_name']."\">".$panel['panel_title']." [$itemcount]</a><br />\n";
			else $output .= "<a href=\"".$panel['panel_url']."\">".$panel['panel_title']." [$itemcount]</a><br />\n";
		}
	}
	$output .= "</div>\n";
?>