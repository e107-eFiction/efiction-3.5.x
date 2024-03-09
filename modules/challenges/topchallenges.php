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
if(!isset($anonchallenges)) accessDenied( );

if(file_exists(_BASEDIR."modules/challenges/languages/{$language}.php")) include_once(_BASEDIR."modules/challenges/languages/{$language}.php");
else include_once(_BASEDIR."modules/challenges/languages/en.php");

$chalquery = dbquery("SELECT chal.*, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_challenges as chal, "._AUTHORTABLE." WHERE "._UIDFIELD." = chal.uid ORDER BY chal.responses DESC LIMIT 10");
if(dbnumrows($chalquery) > 0) {
	$count = 0;
	if(file_exists("./$skindir/challenges.tpl")) $challenges = new TemplatePower("./$skindir/challenges.tpl");
	else $challenges = new TemplatePower("./"._BASEDIR."modules/challenges/default_tpls/challenges.tpl");
	$challenges->prepare( );
	while($challenge = dbassoc($chalquery)) {
		$challenges->newBlock("challenge");
		if(isADMIN || USERUID == $challenge['uid']) $challenges->assign("adminoptions", "<div class=\"adminoptions\"><span class=\"label\">".(isADMIN ? _ADMINOPTIONS : _OPTIONS).":</span> [<a href=\"challenges.php?action=edit&amp;chalid=".$challenge['chalid']."\">"._EDIT."</a>] [<a href=\"challenges.php?action=delete&amp;chalid=".$challenge['chalid']."\">"._DELETE."</a>]</div>");
		$challenges->assign("author", ($challenge['uid'] ? "<a href=\"viewuser.php?uid=".$challenge['uid']."\">".$challenge['challenger']."</a>" : $challenge['challenger']));
		$challenges->assign("title", "<a href=\"modules/challenges/challenges.php?chalid=".$challenge['chalid']."\">".stripslashes($challenge['title'])."</a>");
		$challenges->assign("numresponses", "<a href=\"modules/challenges/challenges.php?chalid=".$challenge['chalid']."\">".$challenge['responses']."</a>");
		$challenges->assign("responses", "<a href=\"modules/challenges/challenges.php?chalid=".$challenge['chalid']."\">"._RESPONSES."</a>");
		$challenges->assign("summary", stripslashes($challenge['summary']));
		$challenges->assign("characters", $challenge['characters'] ? charlist($challenge['characters']) : _NONE);
		$challenges->assign("category"   , ($challenge['catid'] > 0 ? catlist($challenge['catid']) : _NONE) );
		$challenges->assign("respond", "<div class=\"respond\"><a href=\"modules/challenges/challenges.php?action=respond&amp;chalid=".$challenge['chalid']."\">"._RESPOND2CHALLENGE."</a></div>");
		$count++;
		$challenges->assign("oddeven", ($count % 2 ? "odd" : "even"));
	}
	$tpl->newBlock("listings");
	if(dbnumrows($chalquery) > $itemsperpage) $tpl->assign("pagelinks", build_pagelinks(basename($_SERVER['PHP_SELF'])."?=challengesby&amp;uid=$uid&amp;", $numchal, $offset));
	$tpl->gotoBlock("_ROOT");
	$output .= $challenges->getOutputContent( );

}
else $output .= write_message(_NORESULTS);
?>
