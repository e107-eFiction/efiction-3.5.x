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
$version = explode(".", $version);
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;

if($version[0] == 3 && $version[1] < 2) {
if($confirm == "yes") {
	// For the slow-pokes who haven't updated to 3.1
	if($version[0] == 3 && $version[1] == 0 ) {
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
	dbquery("CREATE TABLE `".TABLEPREFIX."fanfiction_stats` (
  `sitekey` varchar(50) collate latin1_general_ci NOT NULL default '0',
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
	dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_stats(`sitekey`) VALUES('".SITEKEY."')");
	dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_inseries` DROP `updated`");
	dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_news` ADD `comments` INT NOT NULL DEFAULT '0'");
	dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_series` ADD `numstories` INT NOT NULL DEFAULT '0'");
	dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_pagelinks` ADD `link_key` CHAR( 1 ) NULL AFTER `link_text`");
	dbquery("ALTER TABLE `".$settingsprefix."fanfiction_settings` ADD `allowseries` TINYINT NOT NULL DEFAULT '2' AFTER `roundrobins`");
	$serieslist = dbquery("SELECT seriesid FROM ".TABLEPREFIX."fanfiction_series");
	while($s = dbassoc($serieslist)) {
		$numstories = count(storiesInSeries($s['seriesid']));
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET numstories = '$numstories' WHERE seriesid = ".$s['seriesid']." LIMIT 1");
	}
	$newslist = dbquery("SELECT count(cid) as count, nid FROM ".TABLEPREFIX."fanfiction_comments GROUP BY nid");
	while($n = dbassoc($newslist)) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_news SET comments = '".$n['count']." WHERE nid = ".$n['nid']);
	}

	$storiesquery =dbquery("SELECT COUNT(sid) as totals, COUNT(DISTINCT uid) as totala, SUM(wordcount) as totalwords FROM ".TABLEPREFIX."fanfiction_stories WHERE validated > 0 ");
	list($stories, $authors, $words) = dbrow($storiesquery);
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET stories = '$stories', authors = '$authors', wordcount = '$words' WHERE sitekey ='".SITEKEY."'"); 

	$chapterquery = dbquery("SELECT COUNT(chapid) as chapters FROM ".TABLEPREFIX."fanfiction_chapters where validated > 0");
	list($chapters) = dbrow($chapterquery);

	$authorquery = dbquery("SELECT COUNT("._UIDFIELD.") as totalm FROM "._AUTHORTABLE);
	list($members) = dbrow($authorquery);

	$newest = dbrow(dbquery("SELECT "._UIDFIELD." as uid FROM "._AUTHORTABLE." ORDER BY "._UIDFIELD." DESC LIMIT 1"));
	$reviewquery = dbquery("SELECT COUNT(reviewid) as totalr FROM ".TABLEPREFIX."fanfiction_reviews WHERE review != 'No Review'");
	list($reviews) = dbrow($reviewquery);
	$reviewquery = dbquery("SELECT COUNT(uid) FROM ".TABLEPREFIX."fanfiction_reviews WHERE review != 'No Review' AND uid != 0");
	list($reviewers) = dbrow($reviewquery);
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET chapters = '$chapters', members = '$members', newestmember = '$newest', reviews = '$reviews', reviewers = '$reviewers' WHERE sitekey ='".SITEKEY."'"); 
	
	$update = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET version = '3.2' WHERE sitekey ='".SITEKEY."'");
	$alltables = dbquery("SHOW TABLES");
	while ($table = dbassoc($alltables)) {
		foreach ($table as $db => $tablename) {
			dbquery("OPTIMIZE TABLE `".$tablename."`");
		}
	}
	if($update) $output .= write_message(_ACTIONSUCCESSFUL);
	else $output .= write_error(_ERROR);
}
else if($confirm == "no") {
	$output .= write_message(_ACTIONCANCELLED);
}
else {
  	$output .= write_message("This update will perform some tasks on the database to better your site's performance.  You are advised to back up your database before starting! <br />
Are you ready to update? <a href='admin.php?action=maintenance&maint=update&amp;confirm=yes'>"._YES."</a> "._OR." <a href='admin.php?action=maintenance&maint=update&amp;confirm=no'>"._NO."</a>");
}
}
else $output .= write_message(_ALREADYUPDATED);
?>