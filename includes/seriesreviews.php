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

/* This include recalculates the average rating and review count for a series. */

$storylist = array( );
$serieslist = array( );
$stinseries = dbquery("SELECT sid, subseriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '$thisseries'");
while($st = dbassoc($stinseries)) {
	if($st['sid']) $storylist[] = $st['sid'];
	else if($st['subseriesid']) $serieslist[] = $st['subseriesid'];
}
$newrating = dbquery("SELECT AVG(rating) as totalreviews FROM ".TABLEPREFIX."fanfiction_reviews 
	WHERE ((item = '$thisseries' AND type = 'SE')".
	(count($storylist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $storylist))."') > 0 AND type = 'ST')" : "").
	(count($serieslist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $serieslist))."') > 0 AND type = 'SE')" : "").
	") AND rating != '-1'");
list($totalreviews) = dbrow($newrating);
$newcount = dbquery("SELECT count(reviewid) as totalcount FROM ".TABLEPREFIX."fanfiction_reviews 
	WHERE ((item = '$thisseries' AND type = 'SE')".
	(count($storylist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $storylist))."') > 0 AND type = 'ST')" : "").
	(count($serieslist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $serieslist))."') > 0 AND type = 'SE')" : "").
	") AND review != 'No Review'");
list($totalcount) = dbrow($newcount);
if($totalcount) $update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET rating = '".round($totalreviews??0)."', reviews = '$totalcount' WHERE seriesid = '$thisseries'");
$parentq = dbquery("SELECT seriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE subseriesid = '$thisseries' AND seriesid != '$thisseries'");
if(dbnumrows($parentq) > 0) list($parent) = dbrow($parentq);
else $parent = false;
while($parent) {
	$pstinseries = dbquery("SELECT sid, subseriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '$thisseries'");
	$pstorylist = array( );
	$pserieslist = array( );
	while($pst = dbassoc($pstinseries)) {
		if($pst['sid']) $pstorylist[] = $pst['sid'];
		else if($pst['subseriesid']) $pserieslist[] = $pst['subseriesid'];
	}
	$pnewrating = dbquery("SELECT AVG(rating) as totalcount FROM ".TABLEPREFIX."fanfiction_reviews 
		WHERE ((item = '$parent' AND type = 'SE')".
		(count($pstorylist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $pstorylist))."') > 0 AND type = 'ST')" : "").
		(count($pserieslist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $pserieslist))."') > 0 AND type = 'SE')" : "").
		") AND rating != '-1'");
	$pnewcount = dbquery("SELECT count(review) as totalcount FROM ".TABLEPREFIX."fanfiction_reviews 
		WHERE ((item = '$parent' AND type = 'SE')".
		(count($pstorylist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $pstorylist))."') > 0 AND type = 'ST')" : "").
		(count($pserieslist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $pserieslist))."') > 0 AND type = 'SE')" : "").
		") AND review != 'No Review'");
	list($total) = dbrow($pnewrating);
	list($totalcount) = dbrow($pnewcount);
	if($total) $update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET rating = '".round($total??0)."', reviews = '$totalcount' WHERE seriesid = '$parent' AND rating != '-1'");
	$parentq = dbquery("SELECT seriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE subseriesid = '$parent'");
	if(dbnumrows($parentq)) list($parent) = dbrow($parentq);
	else $parent = false;
}
?>