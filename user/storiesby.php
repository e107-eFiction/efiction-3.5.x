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
$sort = "<form name=\"sort\" action=\"\"><label for=\"sort\">"._SORT.":</label> <select name=\"sort\" class=\"textbox\" onchange=\"if(this.selectedIndex.value != 'false') document.location = document.sort.sort.options[document.sort.sort.selectedIndex].value\"><option value=\"false\">"._OPTIONS."</option>";
$sort .= "<option value=\"viewuser.php?".($action ? "action=".$action : "")."uid=$uid&amp;sort=alpha\">"._ALPHA."</option>";
$sort.= "<option value=\"viewuser.php?".($action ? "action=".$action : "")."uid=$uid&amp;sort=update\">"._MOSTRECENT."</option></select></form>";
$tpl->assign("sort", $sort);

$countquery = dbquery("SELECT count(stories.sid) FROM ".TABLEPREFIX."fanfiction_stories as stories LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors as coauth ON stories.sid = coauth.sid WHERE validated > 0 AND (stories.uid = '$uid' OR coauth.uid = '$uid')");
list($numstories) = dbrow($countquery);
if($numstories) {
	$count = 0;
	$tpl->newBlock("listings");
	$tpl->assign("stories", "<div class='sectionheader'>"._STORIESBY." $penname</div>");
	$storyquery = dbquery("SELECT stories.*, "._PENNAMEFIELD." as penname, stories.date as date, stories.updated as updated FROM ("._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_stories as stories) LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors as coauth ON coauth.sid = stories.sid WHERE "._UIDFIELD." = stories.uid AND stories.validated > 0 AND (stories.uid = '$uid' OR coauth.uid = '$uid') GROUP BY stories.sid "._ORDERBY." LIMIT $offset, $itemsperpage");
	while($stories = dbassoc($storyquery)) {
		$tpl->newBlock("storyblock");
		include("includes/storyblock.php");
	}
	$tpl->gotoBlock("listings");
	if($numstories > $itemsperpage) $tpl->assign("pagelinks", build_pagelinks("viewuser.php?action=storiesby&amp;uid=$uid".(isset($_GET['sort']) ? ($_GET['sort'] == "alpha" ? "&amp;sort=alpha" : "&amp;sort=update") : "")."&amp;", $numstories, $offset));
	$tpl->gotoBlock("_ROOT");
}
else $output .= write_message(_NORESULTS);
?>
