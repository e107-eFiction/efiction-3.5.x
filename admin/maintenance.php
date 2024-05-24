<?php
// ----------------------------------------------------------------------
// eFiction 3.0
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

$maint = isset($_GET['maint']) ? $_GET['maint'] : false;
$output .= "<div id='pagetitle'>"._ARCHIVEMAINT."</div>";
if($maint == "update") {
	if(file_exists("admin/update.php")) include_once("admin/update.php");
}
if($maint == "reviews") {
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET rating = '0', reviews = '0'"); // Set them all to 0 before we re-insert.
	if($ratings == "3") {
		$stories = dbquery("SELECT COUNT(rating) as count, item FROM " . TABLEPREFIX . "fanfiction_reviews WHERE type = 'ST' AND rating != '-1' GROUP BY item");
		while ($s = dbassoc($stories))
		{
			dbquery("UPDATE " . TABLEPREFIX . "fanfiction_stories SET rating = '" . round($s['count']) . "' WHERE sid = '" . $s['item'] . "'");
		}
	}
	else {
		$stories = dbquery("SELECT AVG(rating) as average, item FROM " . TABLEPREFIX . "fanfiction_reviews WHERE type = 'ST' AND rating != '-1' GROUP BY item");
		while ($s = dbassoc($stories))
		{
			dbquery("UPDATE " . TABLEPREFIX . "fanfiction_stories SET rating = '" . round($s['average']) . "' WHERE sid = '" . $s['item'] . "'");
		}
	}
 
	$stories = dbquery("SELECT COUNT(reviewid) as count, item FROM ".TABLEPREFIX."fanfiction_reviews WHERE type = 'ST' AND review != 'No Review' GROUP BY item");
	while($s = dbassoc($stories)) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET reviews = '".$s['count']."' WHERE sid = '".$s['item']."'");
	}
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET rating = '0', reviews = '0'");
	if ($ratings == "3")
	{
		$chapters = dbquery("SELECT COUNT(rating) as count, chapid FROM " . TABLEPREFIX . "fanfiction_reviews WHERE type = 'ST' AND rating != '-1' GROUP BY chapid");
		while ($c = dbassoc($chapters))
		{
			dbquery("UPDATE " . TABLEPREFIX . "fanfiction_chapters SET rating = '" . round($c['count']) . "' WHERE chapid = '" . $c['chapid'] . "'");
		}
	}
	else {
		$chapters = dbquery("SELECT AVG(rating) as average, chapid FROM " . TABLEPREFIX . "fanfiction_reviews WHERE type = 'ST' AND rating != '-1' GROUP BY chapid");
		while ($c = dbassoc($chapters))
		{
			dbquery("UPDATE " . TABLEPREFIX . "fanfiction_chapters SET rating = '" . round($c['average']) . "' WHERE chapid = '" . $c['chapid'] . "'");
		}
	}

	$chapters = dbquery("SELECT COUNT(reviewid) as count, chapid FROM ".TABLEPREFIX."fanfiction_reviews WHERE type = 'ST' AND review != 'No Review' GROUP BY chapid");
	while($c = dbassoc($chapters)) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET reviews = '".$c['count']."' WHERE chapid = '".$c['chapid']."'");
	}
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET rating = '0', reviews = '0'");
	$series = dbquery("SELECT seriesid FROM ".TABLEPREFIX."fanfiction_series");
	while($s = dbassoc($series)) {
		$thisseries = $s['seriesid'];
		include("includes/seriesreviews.php");
	}
	// For modules which may allow reviews.
	$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'revfix'");
	while($code = dbassoc($codequery)) {
		$eval($code['code_text']);
	}
	if($logging) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_RECALCREVIEWS, USERPENNAME, USERUID))."', '".USERUID. "', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'AM', " . time() . ")");
	$output .= write_message(_ACTIONSUCCESSFUL);
}
else if($maint == "stories") {
		$authors = dbquery("SELECT uid, count(uid) AS count FROM ".TABLEPREFIX."fanfiction_stories WHERE validated > 0 GROUP BY uid");
		while($a = dbassoc($authors)) {
			$alist[$a['uid']] = $a['count'];
		}
		$coauthors = dbquery("SELECT uid, count(sid) AS count FROM ".TABLEPREFIX."fanfiction_coauthors GROUP BY uid");
		while($ca = dbassoc($coauthors)) {
			if(isset($alist[$ca['uid']])) $alist[$ca['uid']] = $alist[$ca['uid']] + $ca['count'];
			else $alist[$ca['uid']] = $ca['count'];
		}
		foreach($alist AS $a => $s) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = '$s' WHERE uid = '$a' LIMIT 1");
		}
		$count =  dbquery("SELECT SUM(wordcount) as count, sid FROM ".TABLEPREFIX."fanfiction_chapters WHERE validated = '1' GROUP BY sid");
		while($c = dbassoc($count)) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET wordcount = '".$c['count']."' WHERE sid = '".$c['sid']."'");
		}
	$output .= write_message(_ACTIONSUCCESSFUL);
}
else if($maint == "categories") {
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET numitems = '0'");
	$cats = dbquery("SELECT catid FROM ".TABLEPREFIX."fanfiction_categories ORDER BY leveldown DESC");
	while($cat = dbrow($cats)) {
		unset($subcats);
		$subs = dbquery("SELECT catid FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = $cat[0]");
		$subcats = array( );
		while($sub = dbrow($subs)) {
			$subcats[] = $sub[0];
			if($categories[$sub[0]]) $subcats = array_merge($subcats, $categories[$sub[0]]);
		}
		$categories[$cat[0]] = $subcats;
		$countquery = dbquery("SELECT count(sid) FROM ".TABLEPREFIX."fanfiction_stories WHERE FIND_IN_SET('$cat[0]', catid) ".(count($subcats) > 0 ? " OR FIND_IN_SET(". implode(", catid) OR FIND_IN_SET(",$subcats).", catid)" : "")." AND validated > 0");
		list($count) = dbrow($countquery);
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET numitems = $count WHERE catid = $cat[0]");
	}
	if($logging) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_CATCOUNTS, USERPENNAME, USERUID))."', '".USERUID. "', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'AM', " . time() . ")");
	$output .= write_message(_CATCOUNTSUPDATED);
}
else if($maint == "categories2") {
	$selectA = "SELECT category, catid FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = -1 ORDER BY displayorder";
	$resultA = dbquery($selectA);
	$countA = 1;
	while($cat = dbassoc($resultA)) {
		$count = 1;
		if($cat['parentcatid'] = -1) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = $countA WHERE catid = $cat[catid]");
			$countA++;
		}
		$selectB = "SELECT category, catid FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = '$cat[catid]' ORDER BY displayorder";
		$resultB = dbquery($selectB);
		while($sub = dbassoc($resultB)) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = $count WHERE catid = $sub[catid]");
			$count++;
		}
	}
	$output .= write_message(_ACTIONSUCCESSFUL);
}
else if($maint == "stats") {
	// check that the statisics is working before doing anything else
	$check = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_stats WHERE sitekey = '".SITEKEY."' LIMIT 1");
	if(dbnumrows($check) < 1) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_stats(`sitekey`) VALUES('".SITEKEY."')");

	$serieslist = dbquery("SELECT seriesid FROM ".TABLEPREFIX."fanfiction_series");
	$totalseries = dbnumrows($serieslist);
	while($s = dbassoc($serieslist)) {
		$numstories = count(storiesInSeries($s['seriesid']));
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET numstories = '$numstories' WHERE seriesid = ".$s['seriesid']." LIMIT 1");
	}

	$newslist = dbquery("SELECT count(cid) as count, nid FROM ".TABLEPREFIX."fanfiction_comments GROUP BY nid");
	while($n = dbassoc($newslist)) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_news SET comments = '".$n['count']."' WHERE nid = ".$n['nid']);
	}

	$storiesquery =dbquery("SELECT COUNT(sid) as totals, SUM(wordcount) as totalwords FROM ".TABLEPREFIX."fanfiction_stories WHERE validated > 0 ");

	list($stories, $words) = dbrow($storiesquery);
	list($authors) = dbrow(dbquery("SELECT count(uid) FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE stories > 0"));
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET stories = '$stories', authors = '$authors', wordcount = '$words' WHERE sitekey = '".SITEKEY."'"); 

	$chapterquery = dbquery("SELECT COUNT(chapid) as chapters FROM ".TABLEPREFIX."fanfiction_chapters where validated > 0");
	list($chapters) = dbrow($chapterquery);

	$authorquery = dbquery("SELECT COUNT("._UIDFIELD.") as totalm FROM "._AUTHORTABLE);
	list($members) = dbrow($authorquery);

	list($newest) = dbrow(dbquery("SELECT "._UIDFIELD." as uid FROM "._AUTHORTABLE." ORDER BY "._UIDFIELD." DESC LIMIT 1"));
	$reviewquery = dbquery("SELECT COUNT(reviewid) as totalr FROM ".TABLEPREFIX."fanfiction_reviews WHERE review != 'No Review'");
	list($reviews) = dbrow($reviewquery);
	$reviewquery = dbquery("SELECT COUNT(DISTINCT uid) FROM ".TABLEPREFIX."fanfiction_reviews WHERE review != 'No Review' AND uid != 0");
	list($reviewers) = dbrow($reviewquery);
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET series = '$totalseries', chapters = '$chapters', members = '$members', newestmember = '$newest', reviews = '$reviews', reviewers = '$reviewers' WHERE sitekey = '".SITEKEY."'"); 
	$news = dbquery("SELECT count(nid) as count, nid FROM ".TABLEPREFIX."fanfiction_comments GROUP BY nid");
	while($n = dbassoc($news)) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_news SET comments = '".$n['count']."' WHERE nid = '".$n['nid']."' LIMIT 1");
	}
	$output .= write_message(_ACTIONSUCCESSFUL);
}
else if($maint == "panels") {
	$ptypes = dbquery("SELECT panel_type FROM ".TABLEPREFIX."fanfiction_panels GROUP BY panel_type");
	while($ptype = dbassoc($ptypes)) {
		if($ptype['panel_type'] == "A") {
			for($x = 1; $x < 5; $x++) {
				$count = 1;
				$plist = dbquery("SELECT panel_name, panel_id FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_hidden = '0' AND panel_type = '".$ptype['panel_type']."' AND panel_level = '$x' ORDER BY panel_level, panel_order");
				while($p = dbassoc($plist)) {
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_panels SET panel_order = '$count' WHERE panel_id = '".$p['panel_id']."' LIMIT 1");
					$count++;
				}
			}
		}
		else {
			$count = 1;
			$plist = dbquery("SELECT panel_name, panel_id FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_hidden = '0' AND panel_type = '".$ptype['panel_type']."' ORDER BY ".($ptype['panel_type'] == "A" ? "panel_level," : "")."panel_order");
			while($p = dbassoc($plist)) {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_panels SET panel_order = '$count' WHERE panel_id = '".$p['panel_id']."' LIMIT 1");
				$count++;
			}
		}
	}
	$output .= write_message(_ACTIONSUCCESSFUL);
}
else if($maint == "optimize") {
	$alltables = dbquery("SHOW TABLES");

	while ($table = dbassoc($alltables)) {
		foreach ($table as $db => $tablename) {
			dbquery("OPTIMIZE TABLE `".$tablename."`");
		}
	}
 	if($logging) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_OPTIMIZE, USERPENNAME, USERUID))."', '".USERUID. "', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'AM', " . time() . ")");
	$output .= write_message(_ACTIONSUCCESSFUL);
}
else if($maint == "backup") {
}
else {
	$output .= "
<ul>
	<li><a href='admin.php?action=maintenance&amp;maint=reviews'>"._RECALCREVIEWS."</a> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_RECALCREVIEWS."</span></A></li>
	<li><a href='admin.php?action=maintenance&amp;maint=stories'>"._RECALCSTORIES."</a> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_RECALCSTORIES."</span></A></li>
	<li><a href='admin.php?action=maintenance&amp;maint=categories'>"._COUNTCATS."</a> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_CATCOUNTS."</span></A></li>
	<li><a href='admin.php?action=maintenance&amp;maint=categories2'>"._CATORDER."</a> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_CATORDER."</span></A></li>
	<li><a href='admin.php?action=maintenance&amp;maint=stats'>"._STATS."</a> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_STATS."</span></A></li>
	<li><a href='admin.php?action=maintenance&amp;maint=panels'>"._PANELORDER."</a> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_PANELORDER."</span></A></li>
	<li><a href='admin.php?action=maintenance&amp;maint=optimize'>"._OPTIMIZE."</a> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_OPTIMIZE."</span></A></li>
	<li><a href='admin/backup.php' target='_new'>"._BACKUP."</a> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_BACKUP."</span></A></li>
	<li><a href='admin/backup_utf8.php' target='_new'>"._BACKUP."</a> (UTF-8) <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_BACKUP."</span></A></li>
	<li><a href='admin.php?action=maintenance&amp;maint=update'>"._UPDATE."</a>  <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_UPDATE."</span></A></li>
</ul>";
}
?>
