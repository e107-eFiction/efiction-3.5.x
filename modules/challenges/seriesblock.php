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
if(!defined("_CHALLENGES")) {
	global $language;
	if(file_exists(_BASEDIR."modules/challenges/languages/{$language}.php")) include_once(_BASEDIR."modules/challenges/languages/{$language}.php");
	else include_once(_BASEDIR."modules/challenges/languages/en.php");
}
	global $chalid;
if(isset($stories['challenges'])) {
	$challengelinks = array( );
	$chalinfo = dbquery("SELECT chalid, title FROM ".TABLEPREFIX."fanfiction_challenges WHERE FIND_IN_SET(chalid, '".$stories['challenges']."') > 0");
	while($c = dbassoc($chalinfo)) {
		$challengelinks[] = "<a href='"._BASEDIR."modules/challenges/challenges.php?chalid=".$c['chalid']."'>".stripslashes($c['title'])."</a>";
	}
	$allclasslist .= "<span class='label'>"._CHALLENGES.": </span> ".(!empty($challengelinks) ? implode(", ", $challengelinks) : _NONE)."<br />";
	$tpl->assign("challengelinks", (!empty($challengelinks) ? implode(", ", $challengelinks) : _NONE));
	if((isADMIN && uLEVEL < 4) || USERUID == $stories['uid'])
		if($current == "challenges") $adminlinks .= " [<a href='challenges.php?action=remove&amp;chalid=$chalid&amp;seriesid=".$stories['seriesid']."'>"._REMOVECHALLENGE."</a>]";
}
?>