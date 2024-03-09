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

function preview_story($stories) {
	global $extendcats, $skindir, $catlist, $charlist, $store, $storiespath, $classlist, $featured, $retired, $rr, $reviewsallowed, $star, $halfstar, $ratingslist, $classtypelist, $dateformat, $recentdays, $current;
		$count = 0;

		if(isset($_GET['textsize'])) $textsize = $_GET['textsize'];
		else $textsize = 0;
		
		if(file_exists("./$skindir/viewstory.tpl")) $tpl = new TemplatePower("./$skindir/viewstory.tpl");
		else $tpl = new TemplatePower(_BASEDIR."default_tpls/viewstory.tpl");
		$tpl->prepare( );			
		include("includes/storyblock.php");
		$adminlinks = "<div class=\"adminoptions\"><span class='label'>"._ADMINOPTIONS.":</span> <a href=\"admin.php?action=validate&amp;sid=$stories[sid]&amp;chapid=$stories[chapid]&amp;validate=yes\">"._VALIDATE."</a> | "._EDIT." - <a href=\"stories.php?action=editstory&amp;sid=$stories[sid]&amp;admin=1\">"._STORY."</a> "._OR." <a href=\"stories.php?action=editchapter&amp;chapid=$stories[chapid]&amp;admin=1\">"._CHAPTER."</a> | "._DELETE." - <a href=\"stories.php?action=delete&amp;sid=$stories[sid]\">"._STORY."</a> "._OR." <a href=\"stories.php?action=delete&amp;chapid=$stories[chapid]&amp;sid=$stories[sid]&amp;admin=1&amp;uid=$stories[uid]\">"._CHAPTER."</a> | <a href=\"javascript:pop('admin.php?action=yesletter&amp;uid=$stories[uid]&amp;chapid=$stories[chapid]', 400, 350, 'yes')\">"._YESLETTER."</a> | <a href=\"javascript:pop('admin.php?action=noletter&amp;uid=$stories[uid]&amp;chapid=$stories[chapid]',400, 350, 'yes')\">"._NOLETTER."</a></div>";
		$tpl->assign("adminlinks", $adminlinks);
		if($stories['inorder'] == 1 && !empty($stories['storynotes'])) {
			$tpl->gotoBlock("_ROOT");
			$tpl->newBlock("storynotes");
			$tpl->assign( "storynotes", stripslashes($stories['storynotes']));
			$tpl->gotoBlock("_ROOT");
		}
		if(!empty($stories['notes'])) {
			$tpl->newBlock("notes");
			$tpl->assign( "notes", $stories['notes']);
			$tpl->gotoBlock("_ROOT");
		}
		if(!empty($stories['endnotes'])) {
			$tpl->newBlock("endnotes");
			$tpl->assign( "endnotes", $stories['endnotes']);
			$tpl->gotoBlock("_ROOT");
		}
		if($store == "files")
		{
			$file = STORIESPATH."/$stories[uid]/$stories[chapid].txt";
			$log_file = fopen($file, "r");
			$file_contents = fread($log_file, filesize($file));
			$storytext = $file_contents;
			fclose($log_file);
		}
		else if($store == "mysql")
		{
			$storytext = $stories['storytext'];
		}
		$storytext = format_story($storytext);
		$tpl->gotoBlock("_ROOT");
		$tpl->assign("chaptertitle", $stories['chaptertitle']);
		$tpl->assign("chapternumber", $stories['inorder']);
		$tpl->assign( "story", "<span style=\"font-size: ".(100 + ($textsize * 20))."%;\">$storytext</span>" );
		return $tpl->getOutputContent( );
}

	$output .= "<div id='pagetitle'>"._VIEWSUBMITTED."</div>";
	if(isset($_GET['validate']) && $_GET['validate'] == "yes") {
		$storyquery = dbquery("SELECT story.validated, story.catid, story.sid, story.title, story.summary, story.uid, "._PENNAMEFIELD." as penname, chapter.inorder, story.coauthors FROM ".TABLEPREFIX."fanfiction_stories as story, ".TABLEPREFIX."fanfiction_chapters  as chapter, "._AUTHORTABLE." WHERE "._UIDFIELD." = story.uid AND chapter.sid = story.sid AND chapter.chapid ='$_GET[chapid]' LIMIT 1");
		list($storyvalid, $catid, $sid, $title, $summary, $authoruid, $author, $inorder, $coauthors) = dbrow($storyquery);
		if(uLEVEL == 1 || (empty($admincats) || sizeof(array_intersect(explode(",", $catid), explode(",", $admincats))))) {
			include("includes/emailer.php");
			if(!$storyvalid) {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET validated = '1', updated = '" . time() . "' WHERE sid = '$_GET[sid]'");
				foreach(explode(",", $catid) as $cat) {
					categoryitems($cat, 1);
				}
				$au[] = $authoruid;
				if($coauthors == 1) {
					$au = array();
					$coauth = dbquery("SELECT "._PENNAMEFIELD." as penname, co.uid FROM ".TABLEPREFIX."fanfiction_coauthors AS co LEFT JOIN "._AUTHORTABLE." ON co.uid = "._UIDFIELD." WHERE co.sid = '".$_GET['sid']."'");
					while($c = dbassoc($coauth)) {
						$au[] = $c['uid'];
					}
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories + 1 WHERE FIND_IN_SET(uid, '".implode(",", $au)."') > 0");
				}
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories + 1 WHERE uid = '$authoruid'");	
				$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'addstory'");
				while($code = dbassoc($codequery)) {
					eval($code['code_text']);
				}
				if($alertson) {
					if($au) $cond = " FIND_IN_SET(fav.item, '".implode(",", $au).",$authoruid') > 0";
					else $cond = "fav.item = $authoruid";
					$subject = _NEWSTORYAT." $sitename";
					$mailtext = sprintf(_AUTHORALERTNOTE, $title, $author, $summary, $sid);
					$favorites = dbquery("SELECT "._UIDFIELD." as uid, "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname, alertson FROM ".TABLEPREFIX."fanfiction_favorites as fav, ".TABLEPREFIX."fanfiction_authorprefs as ap, "._AUTHORTABLE." WHERE $cond AND fav.type = 'AU' AND fav.uid = "._UIDFIELD." AND ap.uid = "._UIDFIELD." AND ap.alertson = '1'");
					while($favuser = dbassoc($favorites)) { 
						sendemail($favuser['penname'], $favuser['email'], $sitename, $siteemail, $subject, $mailtext, "html");
					}				
				}
				if($logging) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_VALIDATE_STORY, USERPENNAME, USERUID, $title, $sid, $author, $authoruid))."', '".USERUID."', INET_ATON('".$_SERVER['REMOTE_ADDR']."'), 'VS', " . time() . ")");
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET stories = stories + 1");
			}
			else if($alertson) {
				$subject = _STORYALERT;
				$mailtext = sprintf(_STORYALERTNOTE, $title, $author, $sid, $inorder);
				$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'addchapter'");
				while($code = dbassoc($codequery)) {
					eval($code['code_text']);
				}
				$favorites = dbquery("SELECT "._UIDFIELD." as uid, "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname, alertson FROM ".TABLEPREFIX."fanfiction_favorites as fav, ".TABLEPREFIX."fanfiction_authorprefs as ap, "._AUTHORTABLE." WHERE fav.item = '$sid' AND fav.type = 'ST' AND fav.uid = "._UIDFIELD." AND ap.uid = "._UIDFIELD." AND ap.alertson = '1'");
				while($favuser = dbassoc($favorites)) { 
					sendemail($favuser['penname'], $favuser['email'], $sitename, $siteemail, $subject, $mailtext, "html");
				}
				if($logging) dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_VALIDATE_CHAPTER, USERPENNAME, USERUID, $title, $sid, $author, $authoruid, $inorder))."', '".USERUID."', INET_ATON('".$_SERVER['REMOTE_ADDR']."'), 'VS', " . time() . ")");
			}
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET validated = '1' WHERE chapid = '$_GET[chapid]'");
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET updated = '" . time() . "' WHERE sid = '$sid'");
			$count =  dbquery("SELECT SUM(wordcount) as totalcount FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid' and validated = 1");
			list($totalcount) = dbrow($count);
			if($totalcount) {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET wordcount = '$totalcount' WHERE sid = '$sid'");
			}
			list($chapters, $words) = dbrow(dbquery("SELECT COUNT(chapid), SUM(wordcount) FROM ".TABLEPREFIX."fanfiction_chapters WHERE validated = 1"));
//			list($authors) = dbrow(dbquery("SELECT COUNT(DISTINCT uid) FROM ".TABLEPREFIX."fanfiction_chapters WHERE validated > 0"));
			list($authors) = dbrow(dbquery("SELECT COUNT(uid) FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE stories > 0"));
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats set wordcount = '$words', chapters = '$chapters', authors = '$authors'");
			$output .= write_message(_STORYVALIDATED);
		}
		else
			$output .= write_error(_NOTAUTHORIZEDADMIN."  "._TRYAGAIN);
	}
	else {
		if(isNumber($_GET['chapid'])) {
			$result = dbquery("SELECT stories.*, stories.title as title, "._PENNAMEFIELD." as penname, stories.updated as updated, stories.date as date, chapter.uid as uid, chapter.inorder, chapter.title as chaptertitle, chapter.storytext, chapter.chapid, chapter.notes, chapter.endnotes FROM "._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_stories as stories, ".TABLEPREFIX."fanfiction_chapters as chapter WHERE chapter.chapid = '$_GET[chapid]' AND chapter.sid = stories.sid AND chapter.uid = "._UIDFIELD);
			$stories = dbassoc($result);
			$output .= preview_story($stories);

		}
	}
?>