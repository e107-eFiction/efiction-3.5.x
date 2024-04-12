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

if (isset($pagelinks['rss']))
{
    $output .= "<div id=\"pagetitle\">" . ($recentdays ? _RECENTSTORIES : _MOSTRECENT) . " " . $pagelinks['rss']['link'] . "</div>";
}
else {
    $output .= "<div id=\"pagetitle\">" . ($recentdays ? _RECENTSTORIES : _MOSTRECENT);
}


$update_date = time() - $recentdays * 24 * 60 * 60;
$countquery .= ($recentdays ? " AND updated > '" . $update_date . "'" : "");
$query = $storyquery . ($recentdays ? " AND updated > '" . $update_date . "'" : "");
$query .= " ORDER BY ".(isset($_REQUEST['sort']) && $_REQUEST['sort'] == "alpha" ? "stories.title" : "updated DESC");
$numrows = search(_STORYQUERY.$query, _STORYCOUNT.$countquery, "browse.php?");
