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

$stat = isset($_GET['stat']) ? $_GET['stat'] : false;
if(!empty($_GET['favstor'])) $stat = "favstor";
if(!empty($_GET['favseries'])) $stat = "favseries";
if(!empty($_GET['favauthor'])) $stat = "favauthor";
if(!isset($uid)) {
	$uid = USERUID;
	$pagetitle =  "<div id='pagetitle'>"._YOURSTATS."</div>";
	$penname = USERPENNAME;
}
else {
	$authquery = dbquery("SELECT "._PENNAMEFIELD." FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid'");
	list($penname) = dbrow($authquery);
	$pagetitle = "<div class='sectionheader'>"._STATSFOR." ".$penname."</div>";
}
$output = $pagetitle;
$storyquery = dbquery("SELECT s.title, s.sid, s.rating, s.reviews, s.count, count(fs.item) as fscount FROM ".TABLEPREFIX."fanfiction_stories as s LEFT JOIN ".TABLEPREFIX."fanfiction_favorites as fs ON s.sid = fs.item AND fs.type = 'ST' LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors AS c ON s.sid = c.sid WHERE (s.uid = '$uid' OR c.uid = '$uid') AND s.validated > 0 GROUP BY s.sid");
$storycount = dbnumrows($storyquery);
$thislink = basename($_SERVER['PHP_SELF']).(basename($_SERVER['PHP_SELF']) == "viewuser.php" ? "?uid=$uid&amp;" : "?");
$authorof[] = "<a href='".$thislink."action=stats&amp;stat=stories'>$storycount "._STORIES."</a>";
if($stat == "stories" && dbnumrows($storyquery)) {
	$hidechapters = isset($_GET["chapters"]) ? $_GET["chapters"] : false;
	$output .= "<p style=\"text-align: right; margin: 1em;\"><a href=\"{$thislink}action=stats&amp;stat=stories&amp;chapters=".($hidechapters != "view" ? "view\">"._VIEWCHAPTERS : "hide\">"._HIDECHAPTERS)."</a></p>
		<table cellpadding=\"3\" cellspacing=\"0\" style=\"width: 90%; margin: 0 auto;\" class=\"tblborder\"><tr><th>"._STORIES."</th>".($reviewsallowed ? "<th>"._REVIEWS."</th>" : "").($favorites ? "<th>"._FAVORITE."</th>" : "")."<th>"._READS."</th></tr>";
	while($story = dbassoc($storyquery)) {
		$output .= "<tr><td class=\"tblborder\"><a href=\"viewstory.php?sid=".$story['sid']."\">".$story['title']."</a> ".ratingpics($story['rating'])."</td>".
			($reviewsallowed ? "<td class=\"tblborder\" align=\"center\"><a href=\"reviews.php?type=ST&amp;item=".$story['sid']."\">".$story['reviews']."</a></td>" : "").
			($favorites ? "<td class=\"tblborder\" align=\"center\">".($story['fscount'] ? "<a href=\"".$thislink."action=stats&amp;favstor=".$story['sid']."\">".$story['fscount']."</a>" : $story['fscount'])."</td>" : "")."
			<td class=\"tblborder\" align=\"center\">".(isset($story['count']) ? $story['count'] : 0 )."</td></tr>";
		if($hidechapters && $hidechapters != "hide") {
			$query2 = dbquery("SELECT chapid, title, inorder, rating, reviews, validated, count FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '".$story['sid']."' ORDER BY inorder"); 
			while($chapter = dbassoc($query2)) {
				$output .= "<tr><td class=\"tblborder\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"viewstory.php?sid=".$story['sid']."&amp;chapid=".$chapter['chapid']."\">".$chapter['title']."</a> ".ratingpics($chapter['rating'])."</td>".
				($reviewsallowed ? "<td class=\"tblborder\" align=\"center\">".($chapter['reviews'] ? "<a href=\"reviews.php?type=ST&amp;item=".$story['sid']."&amp;chapid=".$chapter['chapid']."\">".$chapter['reviews']."</a>" : "0")."</td>" : "").
				($favorites ? "<td class=\"tblborder\" align=\"center\"> --- </td>" : "")."
				<td class=\"tblborder\" align=\"center\">".(isset($chapter['count']) ? $chapter['count'] : 0 )."</td></tr>";			}
		}
	}
	$output .= "</table>";
	if(dbnumrows($storyquery) < 1) $output .= write_message(_NORESULTS);
}
$seriesquery = dbquery("SELECT s.title, s.seriesid, s.rating, s.reviews, count(fs.item) as count FROM ".TABLEPREFIX."fanfiction_series as s LEFT JOIN ".TABLEPREFIX."fanfiction_favorites as fs ON s.seriesid = fs.item AND fs.type = 'SE' WHERE s.uid = '$uid' GROUP BY s.seriesid");
$seriescount = dbnumrows($seriesquery);
$authorof[] =  "<a href='".$thislink."action=stats&amp;stat=series'>$seriescount "._SERIES."</a>";
if($stat == "series" && dbnumrows($seriesquery)) {
	$output .= "<center><h4>"._SERIES."</h4></center><table class=\"tblborder\"  width=\"90%\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"><tr><th>"._TITLE."</th>".($reviewsallowed ? "<th>"._REVIEWS."</th>" : "").($favorites ? "<th>"._FAVORITE."</th>" : "")."</tr>";
	while($series = dbassoc($seriesquery)) {
		$output .= "<tr><td class=\"tblborder\"><a href=\"series.php?seriesid=$series[seriesid]\">".stripslashes($series['title'])."</a> ".ratingpics($series['rating'])."</td>".($reviewsallowed ? "<td class=\"tblborder\" align=\"center\"><a href=\"reviews.php?type=SE&amp;item=$series[seriesid]\">$series[reviews]</a></td>" : "").($favorites ? "<td class=\"tblborder\" align=\"center\">".($series['count'] ? "<a href=\"user.php?action=stats&amp;favseries=$series[seriesid]\">$series[count]</a>" : "0")."</td>" : "")."</td></tr>";
		$serieslist[] = $series['seriesid'];
	}
	$output .= "</table>";
}
$reviewquery = dbquery("SELECT count(reviewid) FROM ".TABLEPREFIX."fanfiction_reviews WHERE uid = '$uid' AND review != 'No Review'");
list($reviewcount) = dbrow($reviewquery);
$authorof[] = "<a href='".$thislink."action=reviewsby'>$reviewcount "._REVIEWS."</a>";
$aocode = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'AO'");
while($ao = dbassoc($aocode)) {
	eval($ao['code_text']);
}
if(empty($stat)) $output .= "<div class='authorstats'><span class='label'>"._AUTHOROF."</span> ". implode(", ", $authorof)."</div>";
if($favorites) {
	$favof = dbquery("SELECT count(uid) FROM ".TABLEPREFIX."fanfiction_favorites WHERE item = '$uid' AND type = 'AU'");
	list($favcount) = dbrow($favof);
	if(isset($_GET['favstor']) && isNumber($_GET['favstor'])) {
		$favstor = $_GET['favstor'];
		$squery = "SELECT title FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$favstor' LIMIT 1";
		$story = dbquery($squery);
		list($title) = dbrow($story);
		$output =  "<div class='sectionheader'>"._FAVORITE.": $title</div>";
		$countquery = "SELECT count(uid) FROM ".TABLEPREFIX."fanfiction_favorites WHERE type = 'ST' AND item = '$favstor'";
		$authorquery = "SELECT ap.stories, "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM ".TABLEPREFIX."fanfiction_favorites as fav LEFT JOIN "._AUTHORTABLE." ON fav.uid = "._UIDFIELD." LEFT JOIN  ".TABLEPREFIX."fanfiction_authorprefs AS ap ON ap.uid = fav.uid WHERE fav.item = '$favstor' AND fav.type = 'ST' GROUP BY fav.uid";
		$pagelink= $thislink."action=stats&amp;favstor=$favstor".($offset > 0 ? "&amp;offset=$offset" : "")."&amp;";
		include("includes/members_list.php");
	}
	else if(isset($_GET['favseries']) && isNumber($_GET['favseries'])) {
		$favseries = $_GET['favseries'];
		$story = dbquery("SELECT title FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '$favseries' LIMIT 1");
		list($title) = dbrow($story);
		$output =  "<div class='sectionheader'>"._FAVORITE.": $title</div>";
		$countquery = "SELECT count(uid) FROM ".TABLEPREFIX."fanfiction_favorites WHERE item = '$favseries' AND type = 'SE'";
		$authorquery = "SELECT ap.stories,"._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM ".TABLEPREFIX."fanfiction_favorites as fav LEFT JOIN "._AUTHORTABLE." ON fav.uid = "._UIDFIELD." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs AS ap ON ap.uid = fav.uid WHERE fav.item = '$favseries' AND fav.type = 'SE' GROUP BY fav.uid";
		$pagelink= $thislink."action=stats&amp;favseries=$favseries".($offset > 0 ? "&amp;offset=$offset" : "")."&amp;";
		include("includes/members_list.php");
	}
	else if($stat == "favauthor") {
		$output =  "<div class='sectionheader'>"._FAVORITE.": $penname</div>";
		$countquery = "SELECT count(uid) FROM ".TABLEPREFIX."fanfiction_favorites WHERE item = '$uid' AND type = 'AU'";
		$authorquery = "SELECT ap.stories, "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM ".TABLEPREFIX."fanfiction_favorites AS fav LEFT JOIN "._AUTHORTABLE." ON fav.uid = "._UIDFIELD." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs as ap ON ap.uid = fav.uid WHERE  fav.item = '$uid' AND fav.type = 'AU' GROUP BY fav.uid";
		$pagelink= $thislink."action=stats&amp;stat=favauthor".($offset > 0 ? "&amp;offset=$offset" : "")."&amp;";
		include("includes/members_list.php");
	}

	$favlist = array( );
	$panelquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_type = 'F' AND panel_name != 'favlist' ORDER BY panel_title ASC");
	if(!$panelquery) $output .= write_error(_ERROR);
	while($panel = dbassoc($panelquery)) {
		$panellink = "";
		if(substr($panel['panel_name'], 0, 3) == "fav" && $type = substr($panel['panel_name'], 3)) {
			if($panel['panel_name'] == "favlist") continue;
			$itemcount = 0;
			$countquery = dbquery("SELECT COUNT(item) FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '$uid' AND type = '$type'");
			list($itemcount) = dbrow($countquery);
			$favlist[] = "<a href=\"".$thislink."action=".$panel['panel_name']."&amp;uid=$uid\">".(isset($itemcount) ? " $itemcount " : "").stripslashes($panel['panel_title'])."</a>";
		}
	}
	if(empty($stat)) $output .= "<div class='authorstats'><span class='label'>"._FAVOF.": </span> <a href='".$thislink."action=stats&amp;stat=favauthor'>$favcount "._MEMBERS."</a><br /><span class='label'>".(USERUID == $uid ? _YOURFAVORITES : _FAVORITESOF." $penname").": </span>".implode(", ", $favlist)."</div>";
}
?>