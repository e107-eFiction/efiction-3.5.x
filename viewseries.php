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

//Begin basic page setup
$current = "series";


include ("header.php");

if(file_exists( "$skindir/default.tpl")) $tpl = new TemplatePower("$skindir/default.tpl" );
else $tpl = new TemplatePower(_BASEDIR."default_tpls/default.tpl");
if(file_exists("$skindir/listings.tpl")) $tpl->assignInclude( "listings", "$skindir/listings.tpl" );
else $tpl->assignInclude( "listings", _BASEDIR."default_tpls/listings.tpl" );
$tpl->assignInclude( "header", "$skindir/header.tpl" );
$tpl->assignInclude( "footer", "$skindir/footer.tpl" );
include(_BASEDIR."includes/pagesetup.php");


$seriesid = (isset($_GET['seriesid']) && is_numeric($_GET['seriesid'])) ? escapestring($_GET['seriesid']) : false;
$sresult = dbquery(_SERIESQUERY." AND seriesid = '$seriesid' LIMIT 1");
$series = dbassoc($sresult);
if(file_exists("$skindir/series_title.tpl")) $titleblock = new TemplatePower( "$skindir/series_title.tpl" );
else $titleblock = new TemplatePower( "default_tpls/series_title.tpl" );
$titleblock->prepare( );
$titleblock->newBlock("series");
$titleblock->assign("pagetitle", stripslashes($series['title'])." "._BY." <a href=\"viewuser.php?uid=".$series['uid']."\">".$series['penname']."</a>");
$titleblock->assign("summary", stripslashes($series['summary']));
$titleblock->assign("category", $series['catid'] ? catlist($series['catid']) : _NONE);
$titleblock->assign("characters", $series['characters'] ? charlist($series['characters']) : _NONE);
$titleblock->assign("numstories", $series['numstories']);
if($series['classes']) {
	$classes = array( );
	foreach(explode(",", $series['classes']) as $c) {
		$classes[$classlist[$c]['type']][] = "<a href='browse.php?type=class&amp;type_id=".$classlist["$c"]['type']."&amp;classid=$c'>".$classlist[$c]['name']."</a>";
	}
}
$allclasslist = "";
foreach($classtypelist as $num => $c) {
	if(isset($classes[$num])) {
		$titleblock->assign($c['name'], implode(", ", $classes[$num]));
		$allclasslist .= "<span class='label'>".$c['title'].": </span> ".implode(", ", $classes[$num])."<br />";
	}
	else {
		$titleblock->assign($c['name'], _NONE);
		$allclasslist .= "<span class='label'>".$c['title'].": </span> "._NONE."<br />";
	}
}
$seriesType = array(_CLOSED, _MODERATED, _OPEN);
$titleblock->assign("open", $seriesType[$series['isopen']]);
$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'seriestitle'");
while($code = dbassoc($codeblocks)) {
	eval($code['code_text']);
}
$titleblock->assign("classifications", $allclasslist);
if(isADMIN) $titleblock->assign("adminoptions", "<div class=\"adminoptions\">"._ADMINOPTIONS.": [<a href=\"series.php?action=add&amp;add=stories&amp;seriesid=$seriesid\">"._ADD2SERIES."</a>] [<a href=\"series.php?action=edit&amp;seriesid=$seriesid\">"._EDIT."</a>] [<a href=\"series.php?action=delete&amp;seriesid=$seriesid\">"._DELETE."</a>]</div>");
$jumpmenu = "";
if($reviewsallowed && (isMEMBER || $anonreviews)) {
	$titleblock->assign("score", ratingpics($series['rating']));
	$titleblock->assign("reviews", "<a href=\"reviews.php?type=SE&amp;item=$seriesid\">"._REVIEWS."</a>");
	$titleblock->assign("numreviews", "<a href=\"reviews.php?type=SE&amp;item=$seriesid\">".$series['reviews']."</a>");
	$titleblock->assign("submitreviews", "<a href=\"reviews.php?action=add&amp;type=SE&amp;item=$seriesid\">"._SUBMITREVIEW."</a>");
	$jumpmenu .= "<option value=\"reviews.php?action=add&amp;type=SE&amp;item=$seriesid\">"._SUBMITREVIEW."</option>";
}
if(isMEMBER && $favorites) {
	$addtofaves = "[<a href=\"user.php?action=favse&amp;uid=".USERUID."&amp;add=$seriesid\">"._ADDSERIES2FAVES."</a>]";
	$jumpmenu .= "<option value=\"user.php?action=favse&amp;uid=".USERUID."&amp;add=$seriesid\">"._ADDSERIES2FAVES."</option>";
	if($series['isopen'] == 0) { // Only closed series.
		$addtofaves .= " [<a href=\"user.php?action=favau&amp;uid=".USERUID."&amp;add=".$series['uid']."\">"._ADDAUTHOR2FAVES."</a>]";
		$jumpmenu .= "<option value=\"user.php?action=favau&amp;uid=".USERUID."&amp;add=".$series['uid']."\">"._ADDAUTHOR2FAVES."</option>";
	}
}
if($series['isopen'] && isMEMBER) {
	$jumpmenu .= "<option value=\"series.php?action=add&amp;add=stories&amp;seriesid=".$seriesid."&amp;stories=".USERUID."\">"._ADD2SERIES."</option>";
	$titleblock->assign("addtoseries", "[<a href='series.php?action=add&amp;add=stories&seriesid=$seriesid&amp;stories=".USERUID."'>"._ADD2SERIES."</a>]");
}
$jumpmenu = "<form name=\"jump2\" action=\"\"><select name=\"jump2\" onchange=\"if(this.selectedIndex.value != 'false') document.location = document.jump2.jump2.options[document.jump2.jump2.selectedIndex].value\"><option value=\"false\">"._OPTIONS."</option>".$jumpmenu."</select></form>";
$titleblock->assign("jumpmenu", $jumpmenu);
if(isset($addtofaves)) $titleblock->assign("addtofaves", $addtofaves);
$parents = dbquery("SELECT s.title, s.seriesid FROM ".TABLEPREFIX."fanfiction_inseries as i, ".TABLEPREFIX."fanfiction_series as s WHERE s.seriesid = i.seriesid AND i.subseriesid = '$seriesid'");
$plinks = array( );
while($p = dbassoc($parents)) {
	$plinks[] = "<a href='series.php?seriesid=".$p['seriesid']."'>".$p['title']."</a>";
}
$titleblock->assign("parentseries", count($plinks) ? implode(", ", $plinks) : _NONE);
$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'seriestitle'");
while($code = dbassoc($codeblocks)) {
	eval($code['code_text']);
}
$output = $titleblock->getOutputContent( );
$cquery = dbquery("SELECT subseriesid, sid, inorder FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '$seriesid' AND confirmed = 1");
$scount = dbnumrows($cquery);
$serieslist = array( );
if($scount) {
	$subs = array( );
	$stories = array( );
	while($item = dbassoc($cquery)) {
		if($item['subseriesid']) $subs[$item['inorder']] = $item['subseriesid'];
		else $stories[$item['inorder']] = $item['sid'];
	}
	if(count($subs) > 0) {
		$subsquery = dbquery(_SERIESQUERY." AND FIND_IN_SET(seriesid, '".implode(",", $subs)."') > 0");
		while($sub = dbassoc($subsquery)) {
			$serieslist[array_search($sub['seriesid'], $subs)] = $sub;
		}
	}
	if(count($stories)) {
		$seriesstoryquery = dbquery(_STORYQUERY." AND FIND_IN_SET(sid, '".implode(",", $stories)."') > 0");
		while($story = dbassoc($seriesstoryquery)) {
			$serieslist[array_search($story['sid'], $stories)] = $story;
		}
	}
}
ksort($serieslist);
$count = 0;
for($a = $offset + 1; $a <= $itemsperpage + $offset; $a++) {
	$tpl->newBlock("listings");
	$stories = isset($serieslist[$a]) ? $serieslist[$a] : false;
	if(isset($stories['seriesid'])) {
		$tpl->newBlock("seriesblock");
		include("includes/seriesblock.php");
	}
	else if(isset($stories['sid'])) {
		$tpl->newBlock("storyblock");
		include("includes/storyblock.php");
// print_r($stories);
	}
	$tpl->gotoBlock("_ROOT");
}
if($scount > $itemsperpage) {
	$tpl->gotoBlock("listings");
	$tpl->assign("pagelinks", build_pagelinks("viewseries.php?seriesid=$seriesid&amp;", $scount, $offset));
}
$tpl->gotoBlock( "_ROOT" );
$tpl->assign("output", $output);
$tpl->printToScreen( );

?>