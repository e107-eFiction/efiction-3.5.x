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

function deleteStory($story) {
	global $store, $logging;
	$sid = $story['sid'];
	if($story['validated']) {
		$cats = explode(",", $story['catid']);
		foreach($cats as $cat) {
			categoryitems($cat, -1);			
		}	
	}
	if($store == "files") {
		$chapterquery = dbquery("SELECT chapid, uid FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid'");
		while($chapter = dbassoc($chapterquery)) {
			if (file_exists(STORIESPATH . "/" . $chapter['uid'] . "/" . $chapter['chapid'] . ".txt")) {
				unlink(STORIESPATH."/".$chapter['uid']."/".$chapter['chapid'].".txt"); 
			}
		}
	}
	if($logging) {
		$authorquery = dbquery("SELECT "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '".$story['uid']."' LIMIT 1");
		list($penname) = dbrow($authorquery);
		dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_ADMIN_DEL, USERPENNAME, USERUID, $story['title'], $sid, $penname, $story['uid']))."', '".USERUID."', INET_ATON('".$_SERVER['REMOTE_ADDR'].
		"'), 'DL', " . time() . ")");
	}
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid' LIMIT 1");
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid'");
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_reviews WHERE item = '$sid' AND type = 'ST'");
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_favorites WHERE item = '$sid' AND type = 'ST'");
	// get the coauthors list before we delete the coauthors info.
	$array_coauthors = array();
	if($story['coauthors'] == 1) {
		$coauth = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '$sid'");
		while($c = dbassoc($coauth)) {
			$array_coauthors[] = $c['uid'];
			
		}
	}	
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '$sid'");
	// Delete story from all series it is in.
	$serieslist = dbquery("SELECT inorder, seriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE sid = '".$story['sid']."' LIMIT 1");
	while($series = dbassoc($serieslist)) {
		$countquery = dbquery("SELECT count(seriesid) FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '".$series['seriesid']."'");
		list($count) = dbrow($countquery);
		dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '".$series['seriesid']."' AND sid = '".$story['sid']."' LIMIT 1");
		if($series['inorder'] < $count) dbquery("UPDATE ".TABLEPREFIX."fanfiction_inseries SET inorder = (inorder - 1) WHERE seriesid = '".$series['seriesid']."' AND inorder > '".$series['inorder']."'");
		seriesreview($series); 
	}
	$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'delstory'");
	while($code = dbassoc($codequery)) {
		eval($code['code_text']);
	}
	if($story['validated'] > 0) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET stories = stories - 1");
		if(!empty($array_coauthors)) {
			$array_coauthors[] = $story['uid'];
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories - 1 WHERE FIND_IN_SET(uid, '".implode(",", $array_coauthors)."') > 0");
		}
		else dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories - 1 WHERE uid = '".$story['uid']."' LIMIT 1");
		list($chapters, $words) = dbrow(dbquery("SELECT COUNT(chapid), SUM(wordcount) FROM ".TABLEPREFIX."fanfiction_chapters WHERE validated > 0"));
		list($authors) = dbrow(dbquery("SELECT COUNT(uid) FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE stories > 0"));
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats set wordcount = '$words', chapters = '$chapters', authors = '$authors'");
	}
}

function deleteUser($uid) {
	global $logging;
	
	list($penname) = dbrow(dbquery("SELECT penname FROM ".TABLEPREFIX."fanfiction_authors WHERE uid = '".$uid."' LIMIT 1"));
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_authorprefs where uid = '".$uid."'");
	$stories = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_stories where uid = '".$uid."'");
	$numstories = dbnumrows($stories);
	if($numstories > 0 ) {
		while($story = dbassoc($stories)) {
			if($story['uid'] == $uid) {
				if(empty($story['coauthors'])) { // No co-authors...delete the story
					deleteStory($story);
				}
				else { // Co-authors found...give the story a new author.
					$cQuery = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '".$story['sid']."' LIMIT 1");
					$newauthor = 0; $coauthors = 0;
					$array_coauthors = array();
					while($cRes= dbassoc($cQuery)) {
						if(!$newauthor) $newauthor = $cRes['uid'];
						else $array_coauthors[] = $cRes['uid'];
					}			
					if(!empty($newauthor)) {
						$coauthors = count($array_coauthors) > 0 ? 1 : 0;
						dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET uid = '$newauthor', coauthors = '$coauthors' WHERE sid = '".$story['sid']."' LIMIT 1");
						$chapters = dbquery("SELECT chapid FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '".$story['sid']."' AND uid = '$uid'");
						while($chap = dbassoc($chapters)) {
							$chapid = $chap['chapid'];
							$storytext = "";
							if($out = fopen (STORIESPATH."/$uid/$chapid.txt", "r"))
							while (!feof($out)) {
								$storytext .= fgets($out, 10000);
							}
							fclose($out);
							unlink(STORIESPATH."/$uid/$chapid.txt"); 
							if($storytext) {
								if( !file_exists( STORIESPATH."/$newauthor/" ) ) {
									mkdir(STORIESPATH."/$newauthor", 0755);
									chmod(STORIESPATH."/$newauthor", 0777);
								}
								$handle = fopen(STORIESPATH."/$newauthor/$chapid.txt", 'w');
								if ($handle) {
									fwrite($handle, $storytext);
									fclose($handle);
								}
								chmod(STORIESPATH."/$newauthor/$chapid.txt", 0644);
							}
							dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET uid = '$newauthor' WHERE chapid = '$chapid' LIMIT 1");
						}
					}
					else deleteStory($story);
				}
			}
		}
	}
	$stories = dbquery("SELECT *, COUNT(uid) AS count FROM ".TABLEPREFIX."fanfiction_coauthors WHERE uid = '$uid' GROUP BY uid");
	while($s = dbassoc($stories)) {
		if($s['count'] > 1) dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET coauthors = 0 WHERE sid = '".$s['sid']."'");
	}
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_coauthors WHERE uid = '$uid'");
	$stories = dbquery("SELECT seriesid FROM ".TABLEPREFIX."fanfiction_series where uid = '".$uid."'");
	$numstories = dbnumrows($stories);
	while($story = dbassoc($stories)) {
		deleteSeries($story['seriesid']);
	}
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_authorinfo WHERE uid = '".$uid."'");
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".$uid."'");
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_favorites WHERE item = '".$uid."' AND type = 'AU'");
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_comments SET uid = '0' WHERE uid = '".$uid."'");
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_reviews SET uid = '0', reviewer = '$penname' WHERE uid = '".$uid."'");
	$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'deluser'");
	while($code = dbassoc($codeblocks)) {
		eval($code['code_text']);
	}
	if(strpos(_AUTHORTABLE, "fanfiction_authors as author") === false) { 
		$output = write_message(_FOREIGNAUTHORTABLE." "._FOREIGNDELETE);
	}
	else {
		dbquery("DELETE FROM ".substr(_AUTHORTABLE, 0, strpos(_AUTHORTABLE, "as author"))." WHERE ".substr(_UIDFIELD, 7)." = '".$uid."'");
		$output = write_message(_ACTIONSUCCESSFUL);
	}
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET members = members - 1");
	return $output;
}

function deleteSeries($seriesid) {
	global $logging;

	$seriesinfo = dbquery("SELECT title, uid FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '$seriesid'");
	list($title, $uid) = dbrow($seriesinfo);
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '$seriesid'");
	// Delete series from other series where it is a sub-series
	$serieslist = dbquery("SELECT inorder, seriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE subseriesid = '".$seriesid."' LIMIT 1");
	while($series = dbassoc($serieslist)) {
		$countquery = dbquery("SELECT count(seriesid) FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '".$series['seriesid']."'");
		list($count) = dbrow($countquery);
		dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '".$series['seriesid']."' AND subseriesid = '".$seriesid."' LIMIT 1");
		if($series['inorder'] < $count) dbquery("UPDATE ".TABLEPREFIX."fanfiction_inseries SET inorder = (inorder - 1) WHERE seriesid = '".$series['seriesid']."' AND inorder > '".$series['inorder']."'");
		seriesreview($series); 
	}	
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '$seriesid'");
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_favorites WHERE type = 'SE' AND item = '$seriesid'");
	dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_reviews WHERE type = 'SE' AND item = '$seriesid'");
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET series = series - 1");
	$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'deleteseries'");
	while($code = dbassoc($codequery)) {
			eval($code['code_text']);
	}
	if($logging && isADMIN) {
		if($uid != USERUID) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_ADMIN_DEL_SERIES, USERPENNAME, USERUID, $title))."', '".USERUID."', INET_ATON('".$_SERVER['REMOTE_ADDR']."'), 'DL', " . time() . ")");
	}
}
?>