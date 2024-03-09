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

$current = "";

if(isset($_GET['type_id']) && isNumber($_GET['type_id'])) $type_id = $_GET['type_id'];
if(isset($_GET['classid']) && isNumber($_GET['classid'])) $classid = $_GET['classid'];

if(!empty($classid)) { 
	$output .= "<div id='pagetitle'>".$classtypelist[$type_id]['title'].": ".$classlist[$classid]['name']."</div>";
	$disablesorts = array($classtypelist[$type_id]['name']);
	$storyquery .= " AND FIND_IN_SET('$classid', stories.classes) > 0".$storyquery._ORDERBY;
	$countquery .= " AND FIND_IN_SET('$classid', stories.classes) > 0";
	$seriesquery = " FIND_IN_SET('$classid', series.classes) > 0".(empty($seriesquery) ? "" : " AND ".$seriesquery);
	$numrows = search(_STORYQUERY.$storyquery, _STORYCOUNT.$countquery, "browse.php?");
	$classin[] = $classid;
	$searchVars['classin'] = array($classid);
}
else if(isset($type_id)) {
	$output .= "<div id='pagetitle'>".$classtypelist[$type_id]['title']."</div>";
	foreach($classlist as $c => $i) {
		if($i['type'] == $type_id) $clist[] = "<a href='browse.php?type=class&amp;type_id=$type_id&amp;classid=$c'>".$i['name']."</a><br />";
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
}
else $output .= _ERROR;