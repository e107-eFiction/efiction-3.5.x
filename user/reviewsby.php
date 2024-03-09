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
if(!function_exists("catlist")) include("includes/listings.php");

if(!isset($uid)) {
	$uid = USERUID;
	$output .=  "<div id='pagetitle'>"._YOURREVIEWS."</div>";
}
else {
	$authquery = dbquery("SELECT "._PENNAMEFIELD." FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid'");
	list($penname) = dbrow($authquery);
	$output .= "<div class='sectionheader'>"._REVIEWSBY." ".$penname."</div>";
}
$revcount = dbquery("SELECT COUNT(reviewid) FROM ".TABLEPREFIX."fanfiction_reviews WHERE uid = '$uid' AND review != 'No Review'");
list($reviewcount) = dbrow($revcount);
if($reviewcount) {
	$revquery = dbquery("SELECT rev.*, rev.date as date, "._PENNAMEFIELD." FROM ".TABLEPREFIX."fanfiction_reviews as rev, "._AUTHORTABLE." WHERE rev.uid = "._UIDFIELD." AND rev.uid = '$uid' AND rev.review != 'No Review' ORDER BY type, item LIMIT $offset, $itemsperpage");
	$counter = 0;
	$count = 0;
	while($reviews = dbassoc($revquery)) {
		$tpl->newBlock("listings");
		if(empty($lastreview)) $lastreview = array('type' => '', 'item' => '');
		$adminlink = "";
		if(file_exists("$skindir/reviewblock.tpl")) $revlist = new TemplatePower( "$skindir/reviewblock.tpl" );
		else $revlist = new TemplatePower("default_tpls/reviewblock.tpl");
		$revlist->prepare( );
		if($reviews['type'] == 'ST') {
			$storyquery = dbquery(_STORYQUERY." AND sid = '".$reviews['item']."' LIMIT 1");
			$stories = dbassoc($storyquery);
			$authoruid = $stories['uid'];
			if($lastreview['type'] != 'ST' || $lastreview['item'] != $reviews['item']) {
				$tpl->newBlock("storyblock");
				include("includes/storyblock.php");
			}
			if($reviews['chapid']) {
				$chapquery = dbquery("SELECT title, inorder FROM ".TABLEPREFIX."fanfiction_chapters WHERE chapid = '".$reviews['chapid']."' LIMIT 1");
				list($chaptitle, $chapnum) = dbrow($chapquery);
			}
		}
		else if($reviews['type'] == "SE" && ($lastreview['type'] != 'SE' || $lastreview['item'] != $reviews['item'])) {
			$storyquery = dbquery(_SERIESQUERY." AND seriesid = '".$reviews['item']."' LIMIT 1");
			$stories = dbassoc($storyquery);
			$authoruid = $stories['uid'];
			include("includes/seriesblock.php");
		}
		else { 
			$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'reviewsby'");
			while($code = dbassoc($codeblocks)) {
				eval($code['code_text']);
			}
		}
		if(isADMIN) $adminlink = _ADMINOPTIONS.": [<a href=\"reviews.php?action=edit&amp;reviewid=".$reviews['reviewid']."\">"._EDIT."</a>]";
		if( isADMIN || (USERUID && USERUID == $reviews['uid'])) $adminlink .= " [<a href=\"reviews.php?action=delete&amp;reviewid=".$reviews['reviewid']."\">"._DELETE."</a>]";
		if($reviews['uid']) {
			if(USERUID == $reviews['uid'] && $revdelete == 2 && !isADMIN) $adminlink .= " [<a href=\"reviews.php?action=delete&amp;reviewid=".$reviews['reviewid']."\">"._DELETE."</a>]";
			$reviewer = "<a href=\"viewuser.php?uid=".$reviews['uid']."\">".$reviews['reviewer']."</a>";
			$member = _SIGNED;
		}
		else {
			if(USERUID == $authoruid && $revdelete && !isADMIN) $adminlink .= " [<a href=\"reviews.php?action=delete&amp;reviewid=".$reviews['reviewid']."\">"._DELETE."</a>]";
			$reviewer = $reviews['reviewer'];
			$member = _ANONYMOUS;
		}
		if(!empty($authoruid) && USERUID == $authoruid) $adminlink .= " [<a href=\"user.php?action=revres&amp;reviewid=".$reviews['reviewid']."\">"._RESPOND."</a>]";
		$revlist->newBlock("reviewsblock");
		$revlist->assign("reviewer"   , $reviewer );
		$revlist->assign("review"   , $reviews['review']);
		$revlist->assign("reviewdate", date("$dateformat", $reviews['date']) );
		$revlist->assign("rating", ratingpics($reviews['rating']) );
		$revlist->assign("member", $member );
		$revlist->assign("chapter", (isset($chaptitle) ? _CHAPTER." $chapnum: $chaptitle" : (!empty($stories['title']) ? $stories['title'] : _NONE)) );
		$revlist->assign("chapternumber", isset($chapnum) ? $chapnum : "" );
		if(!empty($adminlink)) $revlist->assign("adminoptions", "<div class=\"adminoptions\">$adminlink</div>");
		$revlist->assign("oddeven", ($counter % 2 ? "odd" : "even"));
		unset($adminlink);
		$tpl->gotoBlock("listings");
		$tpl->assign("pagelinks", $revlist->getOutputContent( ));
		$tpl->gotoBlock("_ROOT");
		$counter++;
		$lastreview = $reviews;
	}
	if($reviewcount > $itemsperpage) {
		$tpl->gotoBlock("_ROOT");
		$tpl->newBlock("listings");
		$tpl->assign("pagelinks", build_pagelinks(basename($_SERVER['PHP_SELF'])."?action=reviewsby&amp;uid=$uid&amp;", $reviewcount, $offset));
	}		
}
else {
	$output .= write_message(_NORESULTS);
}
	$tpl->gotoBlock("_ROOT");
	$tpl->assign("output", $output);
?>