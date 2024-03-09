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


$output .= "<div id=\"pagetitle\">"._SUBMITTED."</div>";
$view = isset($_GET['view']) ? $_GET['view'] : false;
$output .= 	"<p style=\"text-align: right; margin: 1em;\"><a href=\"admin.php?action=submitted&amp;view=".($view == "all" ? "cats\">"._VIEWMYCATS : "all\">"._VIEWALL)."</a></p>";
$result = dbquery("SELECT story.title as storytitle, chapter.uid, chapter.sid, story.catid, chapter.chapid, chapter.inorder, chapter.title, "._PENNAMEFIELD." as penname FROM (".TABLEPREFIX."fanfiction_chapters as chapter, "._AUTHORTABLE.") LEFT JOIN ".TABLEPREFIX."fanfiction_stories as story ON story.sid = chapter.sid WHERE chapter.validated = '0' AND chapter.uid = "._UIDFIELD." ORDER BY story.title");

if(dbnumrows($result)) {
	$output .= "<table class=\"tblborder\" cellspacing=\"0\" cellpadding=\"0\" style=\"margin: 0 auto; width: 90%;\"><tr class=\"tblborder\"><th>"._TITLE."</th><th>"._AUTHOR."</th><th>"._CATEGORY."</th><th>"._OPTIONS."</th></tr>";
	$array = explode(",", $admincats);
	while ($story = dbassoc($result)) {
		if(!$admincats || $_GET['view'] == "all" || sizeof(array_intersect(explode(",", $story['catid']), explode(",", $admincats)))) {
			$output .= "<tr class=\"tblborder\">";
			$output .= "<td class=\"tblborder\"><a href=\"viewstory.php?sid=$story[sid]\">".stripslashes($story['storytitle'])."</a>";
			if(isset($story['title'])) $output .= " <b>:</b> <a href=\"viewstory.php?sid=$story[sid]&amp;chapter=$story[inorder]\">".stripslashes($story['title'])."</a>";
			$output .= "<td class=\"tblborder\"><a href=\"viewuser.php?uid=$story[uid]\">$story[penname]</a></td>";
			$output .= "<td class=\"tblborder\">".catlist($story['catid'])."</td>";
			$output .= "<td class=\"tblborder\"><a href=\"admin.php?action=validate&amp;chapid=$story[chapid]\">"._VALIDATE."</a><br />"._DELETE.": <a href=\"stories.php?action=delete&amp;chapid=$story[chapid]&amp;sid=$story[sid]&amp;admin=1&amp;uid=$story[uid]\">"._CHAPTER."</a> "._OR." <a href=\"stories.php?action=delete&amp;sid=$story[sid]&amp;admin=1\">"._STORY."</a><br /><a href=\"javascript:pop('admin.php?action=yesletter&uid=$story[uid]&chapid=$story[chapid]', 500, 400, 'yes')\">"._YESLETTER."</a> | <a href=\"javascript:pop('admin.php?action=noletter&uid=$story[uid]&chapid=$story[chapid]', 500, 400, 'yes')\">"._NOLETTER."</a></td></tr>";
		}
	}
	$output .= "</table>";
}
else $output .= write_message(_NORESULTS);
?>