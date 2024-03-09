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
if(!empty($stories['challenges'])) {
	$challengelinks = array( );
	$chalresult = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_challenges WHERE FIND_IN_SET(chalid, '".$stories['challenges']."') > 0");
	while($chal = dbassoc($chalresult)) {
		$challengelinks[] = "<a href='"._BASEDIR."modules/challenges/challenges.php?chalid=".$chal['chalid']."'>".stripslashes($chal['title'])."</a>";
	}
	$tpl->assign("challengelinks", (!empty($challengelinks) ? implode(", ", $challengelinks) : _NONE));
	$allclasslist .= "<span class='label'>"._CHALLENGES.": </span> ".implode(", ", $challengelinks)."<br />";
}
else $tpl->assign("challengelinks", _NONE);
if($current == "challenges") $adminlinks  .= " [<a href='challenges.php?action=remove&amp;chalid=".$chalid."&amp;sid=".$stories['sid']."'>"._REMOVECHALLENGE."</a>]";

?>