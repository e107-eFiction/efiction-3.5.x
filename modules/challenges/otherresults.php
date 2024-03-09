<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
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

if(file_exists(_BASEDIR."modules/challenges/languages/{$language}.php")) include_once(_BASEDIR."modules/challenges/languages/{$language}.php");
else include_once(_BASEDIR."modules/challenges/languages/en.php");
if(empty($type) || $type != "challenges") { // If you're already browsing challenges no sense putting them in the other results.
$chalquery = array( );
if(isset($charid)) {
	if(!is_array($charid)) $charid = array($charid);
	if(count($charid) > 0) {
		foreach($charid as $c) {
			$chars[] = "FIND_IN_SET('$c', characters) > 0";
		}
		$chalquery[] = implode(" OR ", $chars);
	}
}
if(is_array($catid) && count($catid) > 0) {
	$categories = array( );
	// Get the recursive list.
	foreach($catid as $cat) {
		if($cat == "false" || empty($cat) || $cat == -1) continue;
		$categories = array_merge($categories, recurseCategories($cat));
	}
	// Now format the SQL
	$cats = array( );
	foreach($categories as $cat) {
		$cats[] = "FIND_IN_SET($cat, catid) > 0 ";
	}
	// Now implode the SQL list
	if(!empty($cats)) $chalquery[] = "(".implode(" OR ", $cats).")";
}
if($searchterm) {
	if($searchtype == "title") $chalquery[] = "title LIKE '%$searchterm%'";
	if($searchtype == "summary") $chalquery[] = "summary LIKE '%$searchterm%'";
}
if(count($chalquery) > 0) {
	$chalquery = "WHERE ".implode(" AND ", $chalquery);
	$query = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_challenges $chalquery");
	$challenges = dbnumrows($query);

	if($challenges > 0) $otherresults[] = "<a href='browse.php?type=challenges&amp;$terms'>$challenges "._CHALLENGES."</a>";
}
	unset($where, $query, $chars, $cats);
}
?>