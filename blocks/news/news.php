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

	if(isset($blocks['news']['num'])) $numupdated = $blocks['news']['num'];
	else $numupdated = 1;
	if(file_exists("$skindir/newsbox.tpl")) $news = new TemplatePower( "$skindir/newsbox.tpl" );
	else $news = new TemplatePower( _BASEDIR."default_tpls/newsbox.tpl" );
	if(file_exists(_BASEDIR."blocks/news/".$language.".php")) include_once(_BASEDIR."blocks/news/".$language.".php");
	else include_once(_BASEDIR."blocks/news/en.php");

	$news->prepare();
	$newsquery = dbquery("SELECT nid, author, title, story, time as date, comments FROM ".TABLEPREFIX."fanfiction_news ORDER BY time DESC LIMIT $numupdated");
	$count = 0;
	while($stories = dbassoc($newsquery)) {

		//create a new number_row block
		$news->newBlock("newsbox");
		
		//assign values
		if(!empty($blocks['news']['sumlength']) && isNumber($blocks['news']['sumlength'])) $newsStory = truncate_text($stories['story'], $blocks['news']['sumlength'])."<div class='newsReadMore'><a href='news.php?action=newsstory&amp;nid=".$stories['nid']."'>"._READMORE."</a></div>";
		else $newsStory = format_story($stories['story']);
		$news->assign("newstitle" , $stories['title'] );
		$news->assign("newsstory" , $newsStory);
		$news->assign("newsauthor", $stories['author'] );
		$news->assign("newsdate", date("$dateformat $timeformat", $stories['date']) );
		$news->assign("oddeven", ($count % 2 ? "even" : "odd"));
		$news->assign("newsid", $stories['nid']);
		$news->assign("skindir", $skindir);
		if($newscomments)
			$news->assign("newscomments", "<a href=\"news.php?action=newsstory&amp;nid=".$stories['nid']."\">".$stories['comments']." "._COMMENTS."</a>");
		if(isADMIN) 
			$news->assign("adminoptions", "<a href=\"admin.php?action=news&amp;form=".$stories['nid']."\">"._EDIT."</a> | <a href=\"admin.php?action=news&amp;delete=".$stories['nid']."\">"._DELETE."</a>");
		$count++;
	}
	$news->gotoBlock("_ROOT");
	$content = $news->getOutputContent( );
?>