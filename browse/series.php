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

// Page Setup
$current = "series";

$output .= "<div id=\"pagetitle\">"._SERIES.($let ? " - $let" : "")."</div>".build_alphalinks("browse.php?$terms&amp;", $let);

if($let) {
	$seriesquery .= (empty($seriesquery) ? "" : " AND ").($let == _OTHER ? " series.title REGEXP '^[^a-z]'" : "series.title LIKE '$let%'");
}
	if($searchterm) {
		if($searchtype == "title") $scountquery[] = "series.title LIKE '%$searchterm%'";
		if($searchtype == "summary") $scountquery[] = "series.summary LIKE '%$searchterm%'";
		if(!empty($authorlist)) $scountquery[] = "FIND_IN_SET(series.uid, '".implode(",",$authorlist)."') > 0";
		if($searchtype == "advanced" || !$searchtype) $scountquery[] = "(summary LIKE '%$searchterm%' OR title LIKE '%$searchterm%') ";
	}
	if(isset($_REQUEST['authors'])) $scountquery[] = "FIND_IN_SET(series.uid, '".implode(",",$searchVars['authors'])."') > 0";
	foreach($categories as $cat) {
		$catseries[] = "FIND_IN_SET($cat, series.catid) > 0 ";
	}
	// Now implode the SQL list
	if(!empty($catstories)) {
		$scountquery[] = "(".implode(" OR ", $catseries).")";
	}
	if($charid) {
		foreach($charid as $c) {
			if(empty($c)) continue;
			$charseries[] = "FIND_IN_SET($c, series.characters) > 0 ";
		}
		if(!empty($charseries)) {
			$scountquery[] = "(".implode(" OR ", $charseries).")";
		}
	}
	if($classin) {
		foreach($classin as $class) {
			if(empty($class)) continue;
			$scountquery[] = "FIND_IN_SET($class, series.classes) > 0";
		}
	}
	if($classex) {
		foreach($classex as $class) {
			$scountquery[] = "FIND_IN_SET($class, series.classes) = 0";
		}
	}

$count = dbquery(_SERIESCOUNT.(!empty($seriesquery) ? " WHERE ".$seriesquery : ""));
$query = _SERIESQUERY.(!empty($seriesquery) ? " AND ".$seriesquery : "")." ORDER BY series.title LIMIT $offset, $itemsperpage";
list($numrows)= dbrow($count);
$sresult = dbquery($query);
$count = 0;
$tpl->newBlock("listings");
while($stories = dbassoc($sresult)) { include("includes/seriesblock.php"); }	
$tpl->gotoBlock("listings");
if($numrows > $itemsperpage) $tpl->assign("pagelinks", build_pagelinks("browse.php?$terms&amp;", $numrows, $offset));
$tpl->gotoBlock("_ROOT");
if(!$numrows) {
	$tpl->gotoBlock("_ROOT");
	$output .= write_message(_NORESULTS);
}
$disablesorts = array("ratings", "sorts", "complete");
?>