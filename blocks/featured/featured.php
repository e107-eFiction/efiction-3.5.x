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


if(!defined("_CHARSET")) exit( );
	$content = "";
	$count = 0;
	$limit = isset($blocks['featured']['limit']) ? $blocks['featured']['limit'] : false;
	$use_tpl = isset($blocks['featured']['tpl']) && $blocks['featured']['tpl'] ? true : false;
	$query = dbquery(_STORYQUERY." AND stories.featured = '1'".($limit ? " LIMIT $limit" : ""));
	while($stories = dbassoc($query))
	{
		if(!isset($blocks['featured']['allowtags'])) $stories['summary'] = strip_tags($stories['summary']);
		$stories['summary'] = truncate_text(stripslashes($stories['summary']), (isset($blocks['featured']['sumlength']) ? $blocks['featured']['sumlength'] : 75));
		if(!$use_tpl) $content .= "<div class='featuredstory'>".title_link($stories)." "._BY." ".author_link($stories)." ".$ratingslist[$stories['rid']]['name']."<br />".$stories['summary']."</div>";
		else {
			$tpl->newBlock("featuredblock");
			include(_BASEDIR."includes/storyblock.php");
		}
	}
	if($use_tpl && dbnumrows($query) > 0) $tpl->gotoBlock("_ROOT");	

?>