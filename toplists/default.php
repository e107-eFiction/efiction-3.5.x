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

		$output = "<div id=\"pagetitle\">".$panel['panel_title']."</div>";
		$where = false;
	switch($list) {
		case "popskins":
			$result = dbquery("SELECT userskin, COUNT(userskin) as count FROM ".TABLEPREFIX."fanfiction_authorprefs GROUP BY userskin ORDER BY count DESC");
			$count = 1;
			while($popskin = dbassoc($result)) {
				$output .= "$count. ".$popskin['userskin']."<br />";
				$count++;
			}
			break;
		case "favauthors":
			$result = dbquery("SELECT count( fav.item ) AS count, "._UIDFIELD." as uid, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_favorites AS fav, "._AUTHORTABLE." WHERE fav.item = "._UIDFIELD." AND fav.type = 'AU' GROUP  BY fav.item ORDER  BY count DESC LIMIT 10");
			if(dbnumrows($result) == 0) $output .= write_message(_NORESULTS);
			$count = 1;
			while($author = dbassoc($result)) {
				$output .= "$count. <a href=\"viewuser.php?uid=".$author['uid']."\">".$author['penname']."</a> [".$author['count']."]<br /> ";
				$count++;
			}
			break;
		case "favstories":
			$result = dbquery("SELECT count( fav.item ) AS count, stories.*, "._PENNAMEFIELD." as penname, stories.date as date, stories.updated as updated FROM ".TABLEPREFIX."fanfiction_favorites AS fav, "._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_stories as stories WHERE stories.uid = "._UIDFIELD." AND stories.validated > 0 AND fav.item = stories.sid AND fav.type = 'ST' GROUP  BY stories.sid ORDER  BY count DESC LIMIT 10");
			if(dbnumrows($result) == 0) $output .= write_message(_NORESULTS);
			$tpl->newBlock("listings");
			while($stories = dbassoc($result)) { 
				$tpl->newblock("storyblock");
				include("includes/storyblock.php"); 
			}
			break;
		case "favseries" :
			$result = dbquery("SELECT count(fav.item) AS count, series.*,  "._PENNAMEFIELD." as penname FROM (".TABLEPREFIX."fanfiction_series AS series, ".TABLEPREFIX."fanfiction_favorites as fav, "._AUTHORTABLE.") WHERE series.uid = "._UIDFIELD." AND fav.item = series.seriesid AND fav.type = 'SE' GROUP BY series.seriesid ORDER BY count DESC LIMIT 10");
			if(dbnumrows($result) == 0) $output .= write_message(_NORESULTS);
			$tpl->newBlock("listings");
			while($stories = dbassoc($result)) { include("includes/seriesblock.php"); }
			break;
		case "largeseries":
			$result = dbquery("SELECT count(inorder.seriesid) AS count, sub.*,  "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_series AS sub, ".TABLEPREFIX."fanfiction_inseries as inorder, "._AUTHORTABLE." WHERE sub.uid = "._UIDFIELD." AND inorder.seriesid = sub.seriesid GROUP BY sub.seriesid ORDER BY count DESC LIMIT 10");
			if(dbnumrows($result) == 0) $output .= write_message(_NORESULTS);
			$tpl->newBlock("listings");
			while($stories = dbassoc($result)) { include("includes/seriesblock.php"); }
			break;
		case "smallseries":
			$result = dbquery("SELECT count(inorder.seriesid) AS count, sub.*,  "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_series AS sub, ".TABLEPREFIX."fanfiction_inseries as inorder, "._AUTHORTABLE." WHERE sub.uid = "._UIDFIELD." AND inorder.seriesid = sub.seriesid GROUP BY sub.seriesid ORDER BY count LIMIT 10");
			if(dbnumrows($result) == 0) $output .= write_message(_NORESULTS);
			$tpl->newBlock("listings");
			while($stories = dbassoc($result)) { include("includes/seriesblock.php"); }
			break;
		case "reviewedseries":
			$result = dbquery("SELECT series.*,  "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_series AS series, "._AUTHORTABLE." WHERE series.uid = "._UIDFIELD." ORDER BY series.reviews DESC LIMIT 10");
			if(dbnumrows($result) == 0) $output .= write_message(_NORESULTS);
			$tpl->newBlock("listings");
			while($stories = dbassoc($result)) { include("includes/seriesblock.php"); }
			break;
		case "prolificauthors":
			$result = dbquery("SELECT ap.stories AS count, "._PENNAMEFIELD." as penname , "._UIDFIELD." as uid FROM ".TABLEPREFIX."fanfiction_authorprefs as ap LEFT JOIN "._AUTHORTABLE." ON ap.uid = "._UIDFIELD." ORDER BY count DESC LIMIT 10");
			if(dbnumrows($result) == 0) $output .= write_message(_NORESULTS);
			$count = 1;
			while($author = dbassoc($result)) {
				$output .= "$count. <a href=\"viewuser.php?uid=".$author['uid']."\">".$author['penname']."</a> [".$author['count']."]<br /> ";
				$count++;
			}
			break;	
		case "prolificreviewers":
			$result = dbquery("SELECT count(reviews.uid) AS count, "._PENNAMEFIELD." as penname , "._UIDFIELD." as uid FROM ".TABLEPREFIX."fanfiction_reviews as reviews, "._AUTHORTABLE." WHERE reviews.uid = "._UIDFIELD." AND reviews.review != 'No Review' GROUP BY reviews.uid ORDER BY count DESC LIMIT 10");
			if(dbnumrows($result) == 0) $output .= write_message(_NORESULTS);
			$count = 1;
			while($author = dbassoc($result)) {
				$output .= "$count. <a href=\"viewuser.php?uid=".$author['uid']."\">".$author['penname']."</a> [".$author['count']."]<br /> ";
				$count++;
			}
			break;
		case "shortstories":
			$where = "ORDER BY stories.wordcount ASC";
			break;
		case "longstories":
			$where = "ORDER BY stories.wordcount DESC";
			break;
		case "reviewedstories":
			$where = "AND stories.reviews > 0 ORDER BY stories.reviews DESC";
			break;
		case "readstories":
			$where = "ORDER BY stories.count DESC";
			break;
	}
	if($where) {
		$result2 = dbquery(_STORYQUERY." $where LIMIT 10");
		if(dbnumrows($result2) == 0) $output .= "</div>".write_message(_NORESULTS);
		$tpl->newBlock("listings");

		while($stories = dbassoc($result2)) { 
			$tpl->newBlock("storyblock");
			include("includes/storyblock.php"); 
		}
	}
	$tpl->gotoBlock( "_ROOT" );
?>