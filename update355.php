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
$current = "update";

include("header.php");
 
$blocks['news']['status'] = 0;
$blocks['info']['status'] = 0;

//make a new TemplatePower object
if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
else $tpl = new TemplatePower("default_tpls/default.tpl");
include("includes/pagesetup.php");
if(file_exists("languages/".$language."_admin.php")) include_once("languages/".$language."_admin.php");
else include_once("languages/en_admin.php");
// end basic page setup
 
if(!isADMIN) {
$output .= "<script language=\"javascript\" type=\"text/javascript\">
location = \"maintenance.php\";
</script>";
$tpl->assign( "output", $output );
$tpl->printToScreen();
dbclose( );
exit( );
}
$oldVersion = explode(".", $settings['version']);
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
if($oldVersion[0] == 3 && ($oldVersion[1] < 5 || $oldVersion[2] < 3)) {
if($confirm == "yes") {
	// For the slow-pokes who haven't updated to 3.1
	if($oldVersion[1] == 0 ) {
		list($field) = dbrow(dbquery("SELECT field_id FROM ".TABLEPREFIX."fanfiction_authorfields WHERE field_name = 'betareader'"));
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorinfo SET info = '"._YES."' WHERE field = '$field' AND info = '1'");
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorinfo SET info = '"._NO."' WHERE field = '$field' AND info != '"._YES."'");
		dbquery("alter table ".TABLEPREFIX."fanfiction_authorinfo add primary key(uid,field);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_blocks drop index block_name;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_blocks add unique index block_name (block_name);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_categories drop index category;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_categories drop index parentcatid;");
		dbquery("create index byparent on ".TABLEPREFIX."fanfiction_categories (parentcatid,displayorder);");
		dbquery("create index forstoryblock on ".TABLEPREFIX."fanfiction_chapters (sid,validated);");
		dbquery("create index byname on ".TABLEPREFIX."fanfiction_classes (class_type,class_name,class_id);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_classtypes drop index classtype_title;");
		dbquery("create unique index classtype_name on ".TABLEPREFIX."fanfiction_classtypes(classtype_name);");
		dbquery("create index code_type on ".TABLEPREFIX."fanfiction_codeblocks(code_type);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_comments drop index nid;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_comments add index commentlist (nid,time);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_favorites drop index uid;");
		dbquery("create unique index byitem on ".TABLEPREFIX."fanfiction_favorites (item,type,uid);");
		dbquery("create unique index byuid on ".TABLEPREFIX."fanfiction_favorites (uid,type,item);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_inseries drop index seriesid;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_inseries drop index inorder;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_inseries add index (seriesid,inorder);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_inseries drop index sid;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_inseries add primary key (sid,seriesid);");
		dbquery("create index message_name on ".TABLEPREFIX."fanfiction_messages (message_name);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_pagelinks drop index link_text;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_panels drop index panel_hidden;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_panels drop index panel_type;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_panels add index panel_type (panel_type,panel_name);");
		dbquery("create index avgrating on ".TABLEPREFIX."fanfiction_reviews(type,item,rating);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_reviews drop index sid;");
		dbquery("create index bychapter on ".TABLEPREFIX."fanfiction_reviews (chapid,rating);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_reviews add index byuid (uid,item,type);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_series drop index owner;");
		dbquery("create index owner on ".TABLEPREFIX."fanfiction_series (uid,title);");
		dbquery("alter table ".TABLEPREFIX."fanfiction_stories drop index validated;");
		dbquery("create index validateduid on ".TABLEPREFIX."fanfiction_stories (validated,uid);");
		dbquery("create index recent on ".TABLEPREFIX."fanfiction_stories (updated,validated);");
		$alltables = dbquery("SHOW TABLES");
	}
	// Still a little behind.
	if($oldVersion[1] < 2) { 
		dbquery("
CREATE TABLE `".TABLEPREFIX."fanfiction_stats` (
  `sitekey` varchar(50) NOT NULL default '0',
  `stories` int(11) NOT NULL default '0',
  `chapters` int(11) NOT NULL default '0',
  `series` int(11) NOT NULL default '0',
  `reviews` int(11) NOT NULL default '0',
  `wordcount` int(11) NOT NULL default '0',
  `authors` int(11) NOT NULL default '0',
  `members` int(11) NOT NULL default '0',
  `reviewers` int(11) NOT NULL default '0',
  `newestmember` int(11) NOT NULL default '0'
) ENGINE=MyISAM");
		dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_stats(`sitekey`) VALUES('SITEKEY')");
		dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_inseries` DROP `updated`");
		dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_news` ADD `comments` INT NOT NULL DEFAULT '0'");
		dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_series` ADD `numstories` INT NOT NULL DEFAULT '0'");
		dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_pagelinks` ADD `link_key` CHAR( 1 ) NULL AFTER `link_text`");
		if(!isset($allowseries)) dbquery("ALTER TABLE `".$settingsprefix."fanfiction_settings` ADD `allowseries` TINYINT NOT NULL DEFAULT '2' AFTER `roundrobins`");
		dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_log` CHANGE `log_ip` `log_ip` INT( 11 ) UNSIGNED NULL DEFAULT NULL");
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_inseries SET confirmed = 1");
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
	
		$storiesquery =dbquery("SELECT COUNT(sid) as totals, COUNT(DISTINCT uid) as totala, SUM(wordcount) as totalwords FROM ".TABLEPREFIX."fanfiction_stories WHERE validated > 0 ");
		list($stories, $authors, $words) = dbrow($storiesquery);
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET stories = '$stories', authors = '$authors', wordcount = '$words' WHERE sitekey = 'SITEKEY'"); 

		$chapterquery = dbquery("SELECT COUNT(chapid) as chapters FROM ".TABLEPREFIX."fanfiction_chapters where validated > 0");
		list($chapters) = dbrow($chapterquery);

		$authorquery = dbquery("SELECT COUNT("._UIDFIELD.") as totalm FROM "._AUTHORTABLE);
		list($members) = dbrow($authorquery);

		list($newest) = dbrow(dbquery("SELECT "._UIDFIELD." as uid FROM "._AUTHORTABLE." ORDER BY "._UIDFIELD." DESC LIMIT 1"));
		$reviewquery = dbquery("SELECT COUNT(reviewid) as totalr FROM ".TABLEPREFIX."fanfiction_reviews WHERE review != 'No Review'");
		list($reviews) = dbrow($reviewquery);
		$reviewquery = dbquery("SELECT COUNT(DISTINCT uid) FROM ".TABLEPREFIX."fanfiction_reviews WHERE review != 'No Review' AND uid > 0");
		list($reviewers) = dbrow($reviewquery);
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET series = '$totalseries', chapters = '$chapters', members = '$members', newestmember = '$newest', reviews = '$reviews', reviewers = '$reviewers' WHERE sitekey = 'SITEKEY'"); 
		$news = dbquery("SELECT count(nid) as count, nid FROM ".TABLEPREFIX."fanfiction_comments GROUP BY nid");
		while($n = dbassoc($news)) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_news SET comments = '".$n['count']."' WHERE nid = '".$n['nid']."' LIMIT 1");
		}
	} // End version 3.2 updates.
	if($oldVersion[1] < 3) {
	dbquery("
CREATE TABLE `".TABLEPREFIX."fanfiction_modules` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default 'Test Module',
  `version` varchar(10) NOT NULL default '1.0',
  PRIMARY KEY  (`id`),
  KEY `name_version` (`name`,`version`)
)");
	list($panelCount) = dbrow(dbquery("SELECT count(panel_name) AS count FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_type = 'A' AND panel_level = '1' AND panel_hidden = '0'"));
	$panelCount++;
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_panels` (`panel_name`, `panel_title`, `panel_url`, `panel_level`, `panel_order`, `panel_hidden`, `panel_type`) VALUES ('modules', 'Modules', '', 1, ".$panelCount.", 0, 'A')");
	// Insert the modules into the table.
	$dir = opendir(_BASEDIR."modules");
	while($folder = readdir($dir)) {
		if($folder == "." || $folder == ".." || !is_dir("modules/$folder")) continue;
		if(file_exists("modules/$folder/version.php")) {
			$moduleVersion = ""; $moduleName = ""; 
			include("modules/$folder/version.php");
			if(empty($moduleName)) $moduleName = $folder;
			// The next few lines try to determine if the module is installed.  
			if($folder == "challenges" && !isset($anonchallenges)) continue;
			if($folder == "recommendations" && !isset($anonrecs)) continue;
			
			dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_modules(`name`, `version`) VALUES('$moduleName', '1.0')");
		}
	}
	if($ratings == "1") dbquery("UPDATE ".TABLEPREFIX."fanfiction_reviews SET rating = '-1' WHERE rating = '0'");
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET rating = '0', reviews = '0'"); // Set them all to 0 before we re-insert.
	$stories = dbquery("SELECT AVG(rating) as average, item FROM ".TABLEPREFIX."fanfiction_reviews WHERE type = 'ST' AND rating != '-1' GROUP BY item");
	while($s = dbassoc($stories)) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET rating = '".round($s['average'])."' WHERE sid = '".$s['item']."'");
	}
	$stories = dbquery("SELECT COUNT(reviewid) as count, item FROM ".TABLEPREFIX."fanfiction_reviews WHERE type = 'ST' AND review != 'No Review' GROUP BY item");
	while($s = dbassoc($stories)) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET reviews = '".$s['count']."' WHERE sid = '".$s['item']."'");
	}
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET rating = '0', reviews = '0'");
	$chapters = dbquery("SELECT AVG(rating) as average, chapid FROM ".TABLEPREFIX."fanfiction_reviews WHERE type = 'ST' AND rating != '-1' GROUP BY chapid");
	while($c = dbassoc($chapters)) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET rating = '".round($c['average'])."' WHERE chapid = '".$c['chapid']."'");
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
	} // End 3.3 updates
	// Version 3.3.1 updates
	if($oldVersion[1] == 3 && empty($oldVersion[2])) {
		if(isset($anonchallenges)) dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks` (`code_text`, `code_type`, `code_module`) VALUES('include(_BASEDIR.\"modules/challenges/stats.php\");', 'sitestats', 'challenges');");
	}
	// Versin 3.4 updates
	if($oldVersion[1] < 4) {
		dbquery("CREATE TABLE `".TABLEPREFIX."fanfiction_coauthors` (
  `sid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sid`,`uid`)
) ENGINE=MyISAM;");
		dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_authorprefs` ADD `stories` INT NOT NULL DEFAULT '0'");
		$alist = array( );
		$authors = dbquery("SELECT uid, count(uid) AS count FROM ".TABLEPREFIX."fanfiction_stories WHERE validated > 0 GROUP BY uid");
		while($a = dbassoc($authors)) {
			$alist[$a['uid']] = $a['count'];
		}
		$coauthors = dbquery("SELECT coauthors,sid FROM ".TABLEPREFIX."fanfiction_stories WHERE coauthors != '0'");
		while($ca = dbassoc($coauthors)) {
			$co = explode(",", $ca['coauthors']);
			foreach($co AS $a) {
				if(!isNumber($a)) continue;
				if(in_array($a, $alist)) $alist[$a]++;
				else $alist[$a] = 1;
				dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_coauthors(`uid`, `sid`) VALUES('$a', '".$ca['sid']."')");
			}
		}
		foreach($alist AS $a => $s) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = '$s' WHERE uid = '$a' LIMIT 1");
		}
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET coauthors = '1' WHERE coauthors != '0'");
	}
	if($oldVersion[1] == 4 && !isset($oldVersion[2])) {
		$statsupdate = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET sitekey = '".SITEKEY."' WHERE sitekey = 'SITEKEY' LIMIT 1");
		$coauthors = dbquery("SHOW TABLES LIKE '".TABLEPREFIX."fanfiction_coauthors'"); 
		if(!dbnumrows($coauthors)) { 
			dbquery("CREATE TABLE `".TABLEPREFIX."fanfiction_coauthors` (
			  `sid` int(11) NOT NULL default '0',
			  `uid` int(11) NOT NULL default '0',
			  PRIMARY KEY  (`sid`,`uid`)
			) ENGINE=MyISAM;");
			dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_authorprefs` ADD `stories` INT NOT NULL DEFAULT '0'");
		}		
		$authors = dbquery("SELECT uid, count(uid) AS count FROM ".TABLEPREFIX."fanfiction_stories WHERE validated > 0 GROUP BY uid");
		while($a = dbassoc($authors)) {
			$alist[$a['uid']] = $a['count'];
		}
		$coauthors = dbquery("SELECT uid, count(sid) AS count FROM ".TABLEPREFIX."fanfiction_coauthors GROUP BY uid");
		while($ca = dbassoc($coauthors)) {
			if(in_array($ca['uid'], $alist)) $alist[$ca['uid']] = $alist[$ca['uid']] + $ca['count'];
			else $alist[$ca['uid']] = $ca['count'];
		}
		foreach($alist AS $a => $s) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = '$s' WHERE uid = '$a' LIMIT 1");
		}
	}
	if($oldVersion[1] == 4 && $oldVersion[2] < 2) {
		$coauthors = dbquery("SHOW TABLES LIKE '".TABLEPREFIX."fanfiction_coauthors'"); 
		if(!dbnumrows($coauthors)) { 
			dbquery("CREATE TABLE `".TABLEPREFIX."fanfiction_coauthors` (
			  `sid` int(11) NOT NULL default '0',
			  `uid` int(11) NOT NULL default '0',
			  PRIMARY KEY  (`sid`,`uid`)
			) ENGINE=MyISAM;");
			dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_authorprefs` ADD `stories` INT NOT NULL DEFAULT '0'");
		}
		$storiesAdded = dbquery("SHOW COLUMNS FROM ".TABLEPREFIX."fanfiction_authorprefs LIKE 'stories'");
		if(!$storiesAdded) {
			dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_authorprefs` ADD `stories` INT NOT NULL DEFAULT '0'");
			$authors = dbquery("SELECT uid, count(uid) AS count FROM ".TABLEPREFIX."fanfiction_stories WHERE validated > 0 GROUP BY uid");
			while($a = dbassoc($authors)) {
				$alist[$a['uid']] = $a['count'];
			}
			$coauthors = dbquery("SELECT uid, count(sid) AS count FROM ".TABLEPREFIX."fanfiction_coauthors GROUP BY uid");
			while($ca = dbassoc($coauthors)) {
				if(in_array($ca['uid'], $alist)) $alist[$ca['uid']] = $alist[$ca['uid']] + $ca['count'];
				else $alist[$ca['uid']] = $ca['count'];
			}
			foreach($alist AS $a => $s) {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = '$s' WHERE uid = '$a' LIMIT 1");
			}
		}
	}
	if($oldVersion[1] == 5) { // Try to fix some errors for people
		// Recalculate the story count for authors.
		$authors = dbquery("SELECT uid, count(uid) AS count FROM ".TABLEPREFIX."fanfiction_stories WHERE validated > 0 GROUP BY uid");
		$alist = array( );
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
		// Recalculate the number of authors
		list($authors) = dbrow(dbquery("SELECT count(uid) FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE stories > 0"));
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET authors = '$authors' WHERE sitekey = '".SITEKEY."'"); 
	}
	if($oldVersion[1] == 5 && $oldVersion[2] < 3) {
		// fix the indexes for the inseries table.
		$exists = dbnumrows(dbquery("SHOW INDEX FROM ".TABLEPREFIX."fanfiction_inseries WHERE Key_name = 'seriesid'"));
		if($exists) dbquery("alter table ".TABLEPREFIX."fanfiction_inseries drop index seriesid;");
		$exists = dbnumrows(dbquery("SHOW INDEX FROM ".TABLEPREFIX."fanfiction_inseries WHERE Key_name = 'inorder'"));
		if($exists) dbquery("alter table ".TABLEPREFIX."fanfiction_inseries drop index inorder;");
		dbquery("alter table ".TABLEPREFIX."fanfiction_inseries add index (seriesid,inorder);");
		$exists = dbnumrows(dbquery("SHOW INDEX FROM ".TABLEPREFIX."fanfiction_inseries WHERE Key_name = 'sid'"));
		if($exists) dbquery("alter table ".TABLEPREFIX."fanfiction_inseries drop index sid;");
		$exists = dbnumrows(dbquery("SHOW INDEX FROM ".TABLEPREFIX."fanfiction_inseries WHERE Key_name = 'PRIMARY'"));
		if($exists) dbquery("alter table ".TABLEPREFIX."fanfiction_inseries drop primary key");
	//	dbquery("alter ignore table ".TABLEPREFIX."fanfiction_inseries add primary key (sid,seriesid,subseriesid);");
		dbquery("alter table " . TABLEPREFIX . "fanfiction_inseries add primary key (sid,seriesid);");
	}	
	$update = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET version = '3.5.5' WHERE sitekey = '".SITEKEY."'");
	if($update) {
		$output .= write_message(_ACTIONSUCCESSFUL);
		header("Location: update.php");
		exit();
	}
	else $output .= write_error(_ERROR);
}
else if($confirm == "no") {
	$output .= write_message(_ACTIONCANCELLED);
}
else {
  	if($oldVersion[0] == 3 && ($oldVersion[1] < 4 || $oldVersion[1] == 4 && (!isset($oldVersion[2]) || $oldVersion[2] < 3))) $output .= write_message(_CONFIRMUPDATE. "<br /> <a href='update355.php?confirm=yes'>"._YES."</a> "._OR. " <a href='update355.php?confirm=no'>"._NO."</a>");
	else $output .= write_message("Are you ready to update? <a href='update355.php?confirm=yes'>"._YES."</a> "._OR." <a href='update355.php?confirm=no'>"._NO."</a>");
}
}
else {
	$output .= write_message(_ALREADYUPDATED);
	header("Location: update.php");
	exit();
}
$tpl->assign( "output", $output );
$tpl->printToScreen();
dbclose( );
?>