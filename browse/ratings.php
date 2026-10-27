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


$disablesorts = array("ratings");
$rating = isset($_REQUEST['rating']) && isNumber($_REQUEST['rating']) ? $_REQUEST['rating'] : false;
if($rating) {
	$output .= "<div id='pagetitle'>"._RATING.": ".$ratingslist[$rating]['name']."</div>";
	$storyquery .= " AND rid = '$rating'";
	$storyquery .= _ORDERBY;
	$countquery .= " AND rid = '$rating'";
	$numrows = search(_STORYQUERY.$storyquery, _STORYCOUNT.$countquery, $pagelink = "browse.php?");
}
else {
	$output .= "<div id='pagetitle'>"._RATINGS."</div>";
	if(empty($ratingslist) || !is_array($ratingslist)) $ratingslist = array( ); // a site with no ratings crashed here on PHP 8
	$total = count($ratingslist);
	$count = 0;
	$column = 1;
	$list = floor($total / $displaycolumns);
	if($total % $displaycolumns != 0) $list++;
	if(!empty($ratingslist) && count($ratingslist) > 0) {
		$output .= "<div id=\"columncontainer\"><div id=\"browseblock\">".($displaycolumns ? "<div class=\"column\">" : "");
		foreach($ratingslist as $rating => $info) {
			$count++;
			$output .= "<a href='browse.php?type=ratings&amp;rating=$rating'>".$info['name']."</a><br />";
			if( $count >= $list && $column != $displaycolumns) {
				$output .= "</div><div class=\"column\">";
				if($total % $displaycolumns == $column) $list--;
				$column++;
				$count = 0;
			}
		}
		$output .= "</div>".($displaycolumns ? "</div>" : "")."<div class='cleaner'>&nbsp;</div></div>";
	}
}

?>
