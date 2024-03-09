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
if(!defined("_CHARSET")) exit( );

$current = "authors";

$uid = isset($_GET['uid']) && isNumber($_GET['uid']) ? $_GET['uid'] : 0;

if($uid > 0) {
	$query = dbquery("SELECT "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
	list($penname) = dbrow($query);
	$output = "<div id='pagetitle'>".stripslashes($penname)."</div>\n";
	$squery = "SELECT stories.*, "._PENNAMEFIELD." as penname, stories.date as date, stories.updated as updated FROM ("._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_stories as stories) LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors as coauth ON coauth.sid = stories.sid WHERE "._UIDFIELD." = stories.uid AND stories.validated > 0 $squery AND (stories.uid = '$uid' OR coauth.uid = '$uid') "._ORDERBY;
	$cquery = "SELECT count(stories.sid) FROM ".TABLEPREFIX."fanfiction_stories as stories LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors as coauth ON stories.sid = coauth.sid WHERE validated > 0 $countquery AND (stories.uid = '$uid' OR coauth.uid = '$uid')";
	$numrows = search($squery, $cquery, "browse.php?");
}
else {
	$output .= "<div id=\"pagetitle\">"._AUTHORS.($let ? " - $let" : "")."</div>".build_alphalinks("browse.php?$terms&amp;", $let);

	if($let == _OTHER) $query = " "._PENNAMEFIELD." REGEXP '^[^a-z]'";
	else if($let) $query = " "._PENNAMEFIELD." LIKE '$let%'";
	else $query = "";

	$authorquery = "SELECT "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid, ap.stories FROM "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs AS ap ON "._UIDFIELD." = ap.uid WHERE ap.stories > 0 ".($query ? " AND $query" : "");
	$countquery = "SELECT count(ap.uid) FROM ".TABLEPREFIX."fanfiction_authorprefs AS ap LEFT JOIN "._AUTHORTABLE." ON "._UIDFIELD." = ap.uid ".($query ? " AND $query" : "");
	$uid = 0;
	$count = dbquery($countquery);
	list($numrows)= dbrow($count);
	$limit = $itemsperpage *  $displaycolumns;
	$total = ($numrows > $limit ? $limit : $numrows);
	$list = floor($total /  $displaycolumns);
	if($total % $displaycolumns != 0) $list++;
	$colwidth = (100/ $displaycolumns) -1;
	$count = 0;
	$column = 1;
	$result2 = dbquery($authorquery." ORDER BY "._PENNAMEFIELD." LIMIT $offset,$limit");
	$output .= "<div id=\"columncontainer\"><div id=\"browseblock\">".($displaycolumns ? "<div class=\"column\">" : "");
	while($auth = dbassoc($result2)) {
		$count++;
		 $output .= "<a href=\"browse.php?type=authors&amp;uid=".$auth['uid']."\">".stripslashes($auth['penname'])."</a> [".$auth['stories']."]<br />";
		if( $count >= $list && $column != $displaycolumns) {
			$output .= "</div><div class=\"column\">";
			if($total % $displaycolumns == $column) $list--;
			$column++;
			$count = 0;
		}
	}
	$output .= "</div>".($displaycolumns ? "</div>" : "")."<div class='cleaner'>&nbsp;</div></div>";
	if ($numrows > $limit) {
		$output .= build_pagelinks("browse.php?type=authors".($let ? "&amp;let=$let" : "")."&amp;", $numrows, $offset, $displaycolumns);
	}
	else if(!$numrows) $output .= write_message(_NORESULTS);
	$numrows = 0;
}	
	$seriesquery .= (!empty($seriesquery) ? " AND " : "")."uid = '$uid'";
?>