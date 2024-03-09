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

$current = "characters";
$charid = isset($_GET['charid']) && isNumber($_GET['charid']) ? $_GET['charid'] : 0;

if($charid > 0) {
	$charquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_characters WHERE charid = '$charid' LIMIT 1");
	$char = dbassoc($charquery);
	$output = "<div id='pagetitle'>".stripslashes($char['charname'])."</div>\n<div id='story'>".format_story($char['bio'])."</div>";
	$numrows = search(_STORYQUERY.$storyquery._ORDERBY, _STORYCOUNT.$countquery, "browse.php?");
}
else {
	$output .= "<div id=\"pagetitle\">"._CHARACTERS.($let ? " - $let" : "")."</div>".build_alphalinks("browse.php?$terms&amp;", $let);

	if($let == _OTHER) $query = " charname REGEXP '^[^a-z]'";
	else if($let) $query = " charname LIKE '$let%'";
	else $query = "";

	$charquery = "SELECT * FROM ".TABLEPREFIX."fanfiction_characters".($query ? " WHERE $query" : "");
	$countquery = "SELECT count(charid) FROM ".TABLEPREFIX."fanfiction_characters".($query ? " WHERE $query" : "");
	$charid = 0;
	$count = dbquery($countquery);
	list($numrows)= dbrow($count);
	$limit = $itemsperpage *  $displaycolumns;
	$total = ($numrows > $limit ? $limit : $numrows);
	$list = floor($total /  $displaycolumns);
	if($total % $displaycolumns != 0) $list++;
	$colwidth = (100/ $displaycolumns) -1;
	$count = 0;
	$column = 1;
	$result2 = dbquery($charquery." ORDER BY charname LIMIT $offset,$limit");
	$output .= "<div id=\"columncontainer\"><div id=\"browseblock\">".($displaycolumns ? "<div class=\"column\">" : "");
	while($char = dbassoc($result2)) {
		$count++;
		 $output .= "<a href=\"browse.php?type=characters&amp;charid=$char[charid]\">".stripslashes($char['charname'])."</a><br />";
		if( $count >= $list && $column != $displaycolumns) {
			$output .= "</div><div class=\"column\">";
			if($total % $displaycolumns == $column) $list--;
			$column++;
			$count = 0;
		}
	}
	$output .= "</div>".($displaycolumns ? "</div>" : "")."<div class='cleaner'>&nbsp;</div></div>";
	if ($numrows > $limit) {
		$output .= build_pagelinks("browse.php?type=characters".($let ? "&amp;let=$let" : "")."&amp;", $numrows, $offset, $displaycolumns);
	}
	else if(!$numrows) $output .= write_message(_NORESULTS);
	$numrows = 0;
}	
	if($charid > 0) $charlist1 = $charid;
?>