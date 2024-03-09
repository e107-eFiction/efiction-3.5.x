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
// This program is distributed in the hope that it will be useful,bridges
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------

if(!defined("_CHARSET")) exit( );

		$output .= build_alphalinks($pagelink, $let);
		$count = dbquery($countquery);
		list($numrows)= dbrow($count);
		$limit = $itemsperpage *  $displaycolumns;
		$total = ($numrows > $limit ? $limit : $numrows);
		$listtotal = floor($total /  $displaycolumns);
		if($total % $displaycolumns != 0) $listtotal++;
		$colwidth = (100/ $displaycolumns) -1;
		$count = 0;
		$column = 1;
		$authorquery = $authorquery. " ORDER BY "._PENNAMEFIELD." LIMIT $offset,$limit";
		$result2 = dbquery($authorquery);
		$output .= "<div id=\"columncontainer\"><div id=\"memberblock\">".($displaycolumns ? "<div class=\"column\">" : "");
		while($author = dbassoc($result2)) {
			$count++;
			if(empty($author['stories'])) $author['stories'] = 0; // For bridges site that may not have author prefs set.
			$output .= (isset($authorlink) ? $authorlink : "<a href=\"viewuser.php?uid=").$author['uid']."\">".stripslashes($author['penname'])."</a> [".$author['stories']."]<br />\n";
			if( $count >= $listtotal && $column != $displaycolumns) {
				$output .= "</div><div class=\"column\">";
				if($total % $displaycolumns == $column) $listtotal--;
				$column++;
				$count = 0;
			}
		}
		$output .= "</div>".($displaycolumns ? "</div>" : "")."<div class='cleaner'>&nbsp;</div></div>";
		if ($numrows > $limit) {
			$pagelink = $pagelink ? $pagelink : "authors.php?action=viewlist&amp;".($let ? "let={$let}&amp;" : "");
			$output .= build_pagelinks($pagelink, $numrows, $offset, $displaycolumns);
		}
?>