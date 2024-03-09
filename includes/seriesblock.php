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
	if(!isset($count)) $count = 0;
	$adminlinks = "";
	$tpl->newBlock("seriesblock");
	$tpl->assign("seriesid", $stories['seriesid']);
	$tpl->assign("author", "<a href=\""._BASEDIR."viewuser.php?uid=".$stories['uid']."\">".stripslashes($stories['penname'])."</a>");
	$tpl->assign("title", "<a href=\""._BASEDIR."viewseries.php?seriesid=".$stories['seriesid']."\">".stripslashes($stories['title'])."</a>");
	$tpl->assign("summary", stripslashes($stories['summary']));
	$tpl->assign("score", ratingpics($stories['rating']));
	$tpl->assign("numstories", $stories['numstories']);
	if($reviewsallowed == "1") {
		$tpl->assign("reviews", "<a href=\""._BASEDIR."reviews.php?type=SE&amp;item=".$stories['seriesid']."\">"._REVIEWS."</a>");
		$tpl->assign("numreviews", "<a href=\""._BASEDIR."reviews.php?type=SE&amp;item=".$stories['seriesid']."\">$stories[reviews]</a>");
	}
	$tpl->assign("category", ($stories['catid'] ? catlist($stories['catid']) : _NONE));
	$tpl->assign("characters", $stories['characters'] ? charlist($stories['characters']) : _NONE);
	$allclasslist = "";
	if($stories['classes']) {
		unset($storyclasses);
		foreach(explode(",", $stories['classes']) as $c) {
			$storyclasses[$classlist["$c"]['type']][] = "<a href='browse.php?type=class&amp;type_id=".$classlist["$c"]['type']."&amp;classid=$c'>".$classlist[$c]['name']."</a>";
		}
	}
	foreach($classtypelist as $num => $c) {
		if(isset($storyclasses[$num])) {
			$tpl->assign($c['name'], implode(", ", $storyclasses[$num]));
			$allclasslist .= "<span class='label'>".$c['title'].": </span> ".implode(", ", $storyclasses[$num])."<br />";
		}
		else {
			$tpl->assign($c['name'], _NONE);
			$allclasslist .= "<span class='label'>".$c['title'].": </span> "._NONE."<br />";
		}
	}
	if(!isset($seriesType)) $seriesType = array(_CLOSED, _MODERATED, _OPEN);			
	$tpl->assign("open", $seriesType[$stories['isopen']]);
	$tpl->assign("oddeven", ($count % 2 ? "odd" : "even"));
	$parents = dbquery("SELECT s.title, s.seriesid FROM ".TABLEPREFIX."fanfiction_inseries as i, ".TABLEPREFIX."fanfiction_series as s WHERE s.seriesid = i.seriesid AND i.subseriesid = '".$stories['seriesid']."'");
	$plinks = array( );
	while($p = dbassoc($parents)) {
		$plinks[] = "<a href='"._BASEDIR."viewseries.php?seriesid=".$p['seriesid']."'>".$p['title']."</a>";
	}
	$tpl->assign("parentseries", count($plinks) ? implode(", ", $plinks) : _NONE);
	if(!isset($seriescode)) {
		$seriescode = array( );
		$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'seriesblock'");
		while($code = dbassoc($codequery)) {
			$seriescode[] = $code['code_text'];
		}
	}
	if(count($seriescode)) foreach($seriescode as $c) { eval($c); } 
	$tpl->assign("classifications", $allclasslist);
	if(isMEMBER && $favorites) {
		$addtofaves = "[<a href=\"user.php?action=favse&amp;uid=".USERUID."&amp;add=".$stories['seriesid']."\">"._ADDSERIES2FAVES."</a>]";
		if($stories['isopen'] < 2) {
			$addtofaves .= " [<a href=\"viewuser.php?action=favau&amp;uid=".USERUID."&amp;author=".$stories['uid']."\">"._ADDAUTHOR2FAVES."</a>]";
		}
		$tpl->assign("addtofaves", $addtofaves);
	}
	if((isADMIN && uLEVEL < 4) || (USERUID != 0 && USERUID == $stories['uid'])) {
		$tpl->assign("adminoptions", "<div class=\"adminoptions\"><span class='label'>"._ADMINOPTIONS.":</span> [<a href=\""._BASEDIR."series.php?action=add&amp;add=stories&amp;seriesid=".$stories['seriesid']."\">"._ADD2SERIES."</a>] [<a href=\""._BASEDIR."series.php?action=edit&amp;seriesid=".$stories['seriesid']."\">"._EDIT."</a>] [<a href=\""._BASEDIR."series.php?action=delete&amp;seriesid=".$stories['seriesid']."\">"._DELETE."</a>]".(!empty($adminlinks) ? " ".$adminlinks : "")."</div>");
	}
	else if($stories['isopen'] == 2 && USERUID) $tpl->assign("adminoptions", "[<a href=\""._BASEDIR."series.php?action=add&amp;add=stories&amp;seriesid=".$stories['seriesid']."\">"._ADD2SERIES."</a>] ".(!empty($adminlinks) ? " ".$adminlinks : ""));
	$tpl->assign("reportthis", "[<a href=\""._BASEDIR."contact.php?action=report&amp;url=series.php?seriesid=".$stories['seriesid']."\">"._REPORTTHIS."</a>]");
	$count++;
?>