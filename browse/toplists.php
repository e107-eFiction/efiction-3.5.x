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

$current = "toplists";

	$output = "<div id='pagetitle'>"._10LISTS."</div>";
	$lists = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_type = 'L' AND panel_hidden != '1' AND panel_level = '0' ORDER BY panel_order");
	while($l = dbassoc($lists)) {
		$clist[] = "<a href='toplists.php?list=".$l['panel_name']."'>".$l['panel_title']."</a><br />";
	}
	$total = count($clist);
	$count = 0;
	$column = 1;
	$list = floor($total / $displaycolumns);
	if($total % $displaycolumns != 0) $list++;
	$output .= "<div id=\"columncontainer\"><div id=\"browseblock\">".($displaycolumns ? "<div class=\"column\">" : "");
	foreach($clist as $c) {
		$count++;
		$output .= $c;
		if( $count >= $list && $column != $displaycolumns) {
			$output .= "</div><div class=\"column\">";
			if($total % $displaycolumns == $column) $list--;
			$column++;
			$count = 0;
		}
	}
	$output .= "</div>".($displaycolumns ? "</div>" : "")."<div class='cleaner'>&nbsp;</div></div>";
