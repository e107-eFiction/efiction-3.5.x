<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
// Also Like Module developed for eFiction 3.0
// // http://efiction.hugosnebula.com/
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
if(file_exists(_BASEDIR."modules/storyend/languages/{$language}.php")) include_once(_BASEDIR."modules/storyend/languages/{$language}.php");
else include_once(_BASEDIR."modules/storyend/languages/en.php");

$storyinfo = dbassoc(dbquery(_STORYQUERY." AND sid = '$sid'"));
	$output .= "<div id=\"pagetitle\">".sprintf(_AL_BROWSE, title_link($storyinfo))."</div>";
$favquery = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_favorites WHERE item = '$sid' AND type = 'ST'");
$favcount = dbnumrows($favquery);
if($favcount > 0) {
	while($favau = dbassoc($favquery)) {
		$authlist[] = $favau['uid'];
	}
	$authlist = implode(",", $authlist);
}
	$alquery = dbquery("SELECT item FROM ".TABLEPREFIX."fanfiction_favorites WHERE type = 'ST' AND FIND_IN_SET(uid, '$authlist') > 0 AND item != '$sid' GROUP BY item");
	while($al = dbassoc($alquery)){
		$alist[] = $al['item'];
	}
	$alist = isset($alist) ? implode(",", $alist) : array( );
	$searchVars['sid'] = $sid;
	$storyquery .= " AND FIND_IN_SET(sid, '$alist') > 0 ";
	$countquery .= " AND FIND_IN_SET(sid, '$alist') > 0 ";
	$numrows = search(_STORYQUERY.$storyquery, _STORYCOUNT.$countquery, "browse.php?type=alsolike&amp;sid=$sid&amp;");
?>