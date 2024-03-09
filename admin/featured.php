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

	if(isset($_GET['retire']) && preg_match("/^[0-9]+$/", $_GET['retire']))
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET featured = 2 WHERE sid = ".$_GET['retire']);
	if(isset($_GET['remove']) && preg_match("/^[0-9]+$/", $_GET['remove']))
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET featured = 0 WHERE sid = ".$_GET['remove']);
	if(isset($_GET['feature']))
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET featured = 1 WHERE sid = ".$_GET['feature']);
	$fresult = dbquery("SELECT stories.*, stories.title as title, "._PENNAMEFIELD." as penname, stories.updated as updated, stories.date as date FROM "._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_stories as stories WHERE stories.featured > 0 AND stories.uid = "._UIDFIELD." ORDER BY stories.featured");
	$output .= "<div id='pagetitle'>"._FEATUREDSTORIES."</div>";
	if(!dbnumrows($fresult)) $output .= write_message(_NORESULTS);
	else {
		$output .= "<table class=\"tblborder\" cellpadding=\"5\" style=\"margin: 1em auto;\"><tr><th class=\"tblborder\">"._STORIES."</th><th class=\"tblborder\">"._STATUS."</th><th class=\"tblborder\">"._OPTIONS."</th></tr>";
		while($story = dbassoc($fresult)) {
			$output .= "<tr><td class=\"tblborder\"><a href=\"viewstory.php?sid=$story[sid]\">$story[title]</a> "._BY." <a href=\"viewuser.php?uid=$story[uid]\">$story[penname]</a></td><td class=\"tblborder\" align=\"center\">".($story['featured'] == 1 ? _ACTIVE : _RETIRED)."</td><td class=\"tblborder\" align=\"center\">".($story['featured'] == 1 ? "<a href=\"admin.php?action=featured&retire=$story[sid]\">"._RETIRE."</a>" : "<a href=\"admin.php?action=featured&feature=$story[sid]\">"._CURRENT."</a>")." | <a href=\"admin.php?action=featured&remove=$story[sid]\">"._REMOVE."</a></td></tr>";
		}
		$output .= "</table>";
		$output .= write_message(_FEATUREDNOTE);
	}
?>