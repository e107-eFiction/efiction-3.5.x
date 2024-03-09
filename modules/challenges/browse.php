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

	$output .= "<div id='pagetitle'>"._CHALLENGES."</div><div class=\"respond\"><a href=\"modules/challenges/challenges.php?action=add\">"._ISSUECHALLENGE."</a></div>".build_alphalinks("browse.php?type=challenges&amp;", $let);
	$numrows = 0;
	$chalquery = array( );
	if(!empty($charid)) {
		$chars = array( );
		foreach($charid as $c) {
			$chars[] = "FIND_IN_SET('$c', characters) > 0";
		}
		$chalquery[] = implode(" OR ", $chars);
	}
	if(isset($catid) && count($catid) > 0) {
		$categories = array( );
		// Get the recursive list.
		foreach($catid as $cat) {
			if($cat == "false" || empty($cat)) continue;
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
	if(isset($authors)) $chalquery[] = "FIND_IN_SET(uid, '".implode(",",$authors)."') > 0";
	if(isset($summary)) $chalquery[] = "summary LIKE '%".$summary."%'";
	if(isset($title)) $chalquery[] = "summary LIKE '%".$title."%'";
	if($let == _OTHER) {
		$chalquery[] = "chal.title REGEXP '^[^a-z]'";
	}
	else if(!empty($let)) {
		$chalquery[] = "chal.title LIKE '$let%'";
	}
	$chalquery = count($chalquery) > 0 ? " WHERE ".implode(" AND ", $chalquery) : "";


	$count = dbquery("SELECT count(chalid) FROM ".TABLEPREFIX."fanfiction_challenges as chal $chalquery");
	$query = "SELECT chal.* FROM ".TABLEPREFIX."fanfiction_challenges as chal $chalquery ORDER BY chalid DESC LIMIT $offset, $itemsperpage";

	list($numrows) = dbrow($count);
	if($numrows > 0) {

		if(file_exists(_BASEDIR."$skindir/challenges.tpl")) $challenges = new TemplatePower(_BASEDIR."$skindir/challenges.tpl");
		else $challenges = new TemplatePower(_BASEDIR."modules/challenges/default_tpls/challenges.tpl");
		$challenges->prepare( );
		$result = dbquery($query);
		$count = 0;
		while($challenge = dbassoc($result)) {
			$challenges->newBlock("challenge");
			if(isADMIN || (USERUID != 0 && USERUID == $challenge['uid'])) $challenges->assign("adminoptions", "<div class=\"adminoptions\"><span class='label'>".(isADMIN ? _ADMINOPTIONS : _OPTIONS).":</span> [<a href=\"modules/challenges/challenges.php?action=edit&amp;chalid=".$challenge['chalid']."\">"._EDIT."</a>] [<a href=\"modules/challenges/challenges.php?action=delete&amp;chalid=".$challenge['chalid']."\">"._DELETE."</a>]</div>");
			$challenges->assign("author", ($challenge['uid'] ? "<a href=\""._BASEDIR."viewuser.php?uid=".$challenge['uid']."\">".$challenge['challenger']."</a>" : $challenge['challenger']));
			$challenges->assign("title", "<a href=\"modules/challenges/challenges.php?chalid=".$challenge['chalid']."\">".stripslashes($challenge['title'])."</a>");
			$challenges->assign("numresponses", "<a href=\"modules/challenges/challenges.php?chalid=".$challenge['chalid']."\">".$challenge['responses']."</a>");
			$challenges->assign("responses", "<a href=\"modules/challenges/challenges.php?chalid=".$challenge['chalid']."\">"._RESPONSES."</a>");
			$challenges->assign("summary", stripslashes($challenge['summary']));
			$challenges->assign("characters", $challenge['characters'] ? charlist($challenge['characters']) : _NONE);
			$challenges->assign("category"   , ($challenge['catid'] > 0 ? catlist($challenge['catid']) : _NONE) );
			if(isMEMBER) $challenges->assign("respond", "<div class=\"respond\"><a href=\"modules/challenges/challenges.php?action=respond&amp;chalid=".$challenge['chalid']."\">"._RESPOND2CHALLENGE."</a></div>");
			$challenges->assign("reportthis", "[<a href=\""._BASEDIR."contact.php?action=report&amp;url=modules/challenges/challenges.php?chalid=".$challenge['chalid']."\">"._REPORTTHIS."</a>]");
			$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'challengeblock'");
			while($code = dbassoc($codequery)) {
				eval($code['code_text']);
			}
			$count++;
			$challenges->assign("skindir", $skindir);
			$challenges->assign("oddeven", ($count % 2 ? "odd" : "even"));
		}
		$tpl->newBlock("listings");
		$tpl->assign("pagelinks", $challenges->getOutputContent( ));
		$tpl->gotoBlock("_ROOT");
		$tpl->newBlock("listings");
		if($numrows > $itemsperpage) {
			$url = "browse.php?$terms".(!empty($let) ? "&amp;let={$let}" : '')."&amp;";
			$tpl->assign("pagelinks", build_pagelinks($url, $numrows, $offset));
		}
		$disablesorts = array("classes", "ratings", "complete", "sorts");
	}
	else $output .= write_message(_NORESULTS);
	unset($cats, $chars);
?>