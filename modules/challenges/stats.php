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

if(file_exists(_BASEDIR."modules/challenges/languages/{$language}.php")) 
	include_once(_BASEDIR."modules/challenges/languages/{$language}.php"); 
else include_once(_BASEDIR."modules/challenges/languages/en.php");

$chalquery = dbquery("SELECT count(chalid) FROM ".TABLEPREFIX."fanfiction_challenges");
list($chalcount) = dbrow($chalquery);
$chalquery = dbquery("SELECT count(DISTINCT uid) FROM ".TABLEPREFIX."fanfiction_challenges");
list($challengers) = dbrow($chalquery);
if(!empty($blocks['info']['style']) && $blocks['info']['style'] == 2) {
	$tpl->assignGlobal("totalchallenges", $chalcount);
	$tpl->assignGlobal("challengers", $challengers);
}
else if(!empty($blocks['info']['style']) && $blocks["info"]["style"] == 1) {
	$content = preg_replace("@\{totalchallenges\}@", $chalcount, $content);
	$content = preg_replace("@\{challengers\}@", $challengers, $content);
}
else $content .= "<div><span class='label'>"._CHALLENGES.": </span>".$chalcount."</div><div><span class='label'>"._CHALLENGERS.": </span>".$challengers."</div>";
?>