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

// page template setup

$current = "stories";

if(isset($_GET['action'])) {
	if($_GET['action'] != "newchapter") $displayform = 1;
	if($_GET['action'] == "newstory" || $_GET['action'] == "editstory") $current = "addstory";
}

include ("header.php");

$tpl = new TemplatePower( file_exists("$skindir/default.tpl") ?  "$skindir/default.tpl" : "default_tpls/default.tpl");
$tpl->assignInclude( "header", "./$skindir/header.tpl" );
$tpl->assignInclude( "footer", "./$skindir/footer.tpl" );

include("includes/pagesetup.php");
include("includes/storyform.php");


// before doing anything else check if the visitor is logged in.  If they are, check if they're an admin.  If not, check that they're 
// trying to edit/delete/etc. their own stuff then get the penname 
	if(!isMEMBER || ($submissionsoff && !isADMIN) || (!isADMIN && isset($uid))) accessDenied( );
	if(!isADMIN || uLEVEL > 3) {
		if(isset($chapid)) {
			$result = dbquery("SELECT sid, uid FROM ".TABLEPREFIX."fanfiction_chapters WHERE chapid='$chapid' LIMIT 1");
			if($result) list($sid, $author) = dbrow($result);
		}
		if(isset($sid)) {
			$array_coauthors = array( );
			$authorquery = dbquery("SELECT uid, rr, coauthors FROM ".TABLEPREFIX."fanfiction_stories WHERE sid='$sid' LIMIT 1");
			$story = dbassoc($authorquery);
			if($story['coauthors']) {
				$cQuery = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '$sid'");
				while($c = dbassoc($cQuery)) {
					$array_coauthors[] = $c['uid'];
				}
			}
			if($story['uid'] != USERUID && (is_array($array_coauthors) && !in_array(USERUID, $array_coauthors)) && !$story['rr']) accessDenied( );
		}
	}
	else if(isADMIN && uLEVEL < 4 && isset($_GET['admin'])) {
		$admin = 1;
		$uid = isset($_GET['uid']) && isNumber($_GET['uid']) ? $_GET['uid'] : USERUID;
	}
	else {
		$admin = 0;
		$uid = USERUID;
	}

function preview_story($stories) {
	global $current, $new, $extendcats, $skindir, $catlist, $charlist, $classlist, $featured, $retired, $rr, $reviewsallowed, $star, $halfstar, $classtypelist, $dateformat, $ratingslist, $recentdays;

	$count = 0;
	if(file_exists("$skindir/listings.tpl")) $tpl = new TemplatePower( "$skindir/listings.tpl" );
	else $tpl = new TemplatePower("default_tpls/listings.tpl");
	if(is_array($stories['coauthors']) && count($stories['coauthors']) > 0) $stories['coauthors'] = 1;
	$tpl->prepare( );
	$tpl->newBlock("listings");
	$tpl->newBlock("storyblock");
	$tpl->assignGlobal("skindir", $skindir);
	include("includes/storyblock.php");
	$text = $tpl->getOutputContent( );
	$count = 0;
	if(!empty($stories['storytext'])) {
		$text .= "<br /><br />";
		if(isset($_GET['textsize'])) $textsize = $_GET['textsize'];
		else $textsize = 0;
		
		if(file_exists("./$skindir/viewstory.tpl")) $tpl = new TemplatePower("./$skindir/viewstory.tpl");
		else $tpl = new TemplatePower(_BASEDIR."default_tpls/viewstory.tpl");
		$tpl->prepare( );			
		include("includes/storyblock.php");
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
		$tpl->gotoBlock("_ROOT");
		$tpl->assign("chaptertitle", $stories['chaptertitle']);
		$tpl->assign("chapternumber", $stories['inorder']);
		$tpl->assign( "story", "<span style=\"font-size: ".(100 + ($textsize * 20))."%;\">".format_story($stories['storytext'])."</span>" );
		$text .= $tpl->getOutputContent( );
	}
	return $text;
}

// function to add new story to archives.
function newstory( ) {

	global $autovalidate, $sid, $action, $sid, $store, $tpl, $admin, $sitename, $siteemail, $allowed_tags, $admincats, $alertson, $dateformat, $url, $minwords, $maxwords, $charlist, $catlist, $classtypelist;
	$newchapter = $action == "newchapter";
	$output = "<div id=\"pagetitle\">".($newchapter ? _ADDNEWCHAPTER : _ADDNEWSTORY)."</div>";
// to avoid problems with register globals and hackers declare variables and do some clean up.
	if(isset($admin) && isset($_POST['uid']) && isNumber($_POST['uid'])) {
		$uid = $_POST['uid'];
		$author = dbquery("SELECT "._PENNAMEFIELD." FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
		list($penname) = dbrow($author);
	}
	else {
		$uid = USERUID;
		$penname = USERPENNAME;
	}
	$title = isset($_POST['title']) ? descript(strip_tags($_POST['title'], $allowed_tags)) : "";
	$summary = isset($_POST['summary']) ? replace_naughty(descript(strip_tags($_POST['summary'], $allowed_tags))) : "";
	$storynotes = isset($_POST['storynotes']) ? descript(strip_tags($_POST['storynotes'], $allowed_tags)) : "";
	$catid = isset($_POST['catid']) ? array_filter(explode(",", $_POST['catid']), "isNumber") : array( );
	$charid = isset($_POST['charid']) ? array_filter($_POST['charid'], "isNumber") : array( );
	$array_coauthors = isset($_POST['coauthors']) ? array_filter(explode(",", $_POST['coauthors']), "isNumber") : array( );
	$classes = array( );
	$classquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classtypes");
	while($type = dbassoc($classquery)) {
		if(isset($_POST["class_".$type['classtype_id']])) {
			$opts = is_array($_POST["class_".$type['classtype_id']]) ? array_filter($_POST["class_".$type['classtype_id']], "isNumber") : "";
			$classes = array_merge($opts, $classes);
		}
	}
	$rid = isset($_POST['rid']) ? descript($_POST['rid']) : "";
	$rr = isset($_POST['rr']) && isNumber($_POST['rr']) ? $_POST['rr'] : 0;
	$feat = isset($_POST['feature']) && isNumber($_POST['feature']) ? $_POST['feature'] : 0;
	$complete = isset($_POST['complete']) && isNumber($_POST['complete']) ? $_POST['complete'] : 0;
	$validated = isset($_POST['validated']) && isNumber($_POST['validated']) ? $_POST['validated'] : 0;
	$chaptertitle = isset($_POST['chaptertitle']) ? descript(strip_tags($_POST['chaptertitle'], $allowed_tags)) : "";
	$notes = isset($_POST['notes']) ? strip_tags(descript($_POST['notes']), $allowed_tags) : "";
	$endnotes = isset($_POST['endnotes']) ? strip_tags(descript($_POST['endnotes']), $allowed_tags) : "";
	$story = "";
	if(isset($_FILES['storyfile']['name']) && $_FILES['storyfile']['name']) {
		if ($_FILES['storyfile']['type'] != 'text/html' && $_FILES['storyfile']['type'] != 'text/plain') {
 			$failed = _INVALIDUPLOAD;
			$submit = _PREVIEW;
		}
		else {
			$texts = file($_FILES['storyfile']['tmp_name']);
			foreach ($texts as $text) {
				if($_FILES['storyfile']['type'] == 'text/html') $story .= rtrim($text, "\n\r\t")." ";
				else $story .= $text;
			}
		}
	}
	else if(isset($_POST['storytext'])) $story = $_POST['storytext'];
	$storytext = descript(strip_tags($story, $allowed_tags));
	$words_to_count = strip_tags($storytext);
	$pattern = "/[^(\w|\d|\'|\"|\.|\!|\?|;|,|\\|\/|\-\-|:|\&|@)]+/";
	$words_to_count = preg_replace ($pattern, " ", $words_to_count);
	$words_to_count = trim($words_to_count);
	$wordcount = count(explode(" ",$words_to_count)); 
	$au[] = $uid;
	if(count($array_coauthors)) {
		$au = array_merge($au, $array_coauthors);
		$coauthors = 1;
	}
	else $coauthors = 0;
// end variable declarations

	if(isset($_POST['submit']) && $_POST['submit'] == _ADDSTORY && ((!$newchapter && (!$rid || !$title || !$summary || !$catid) || $storytext == "")))
			$submit = _PREVIEW;
	if (isset($_POST['submit'])) {
		if(empty($failed)) $failed = "";
		if(!$storytext) $failed .= "<br />"._NOSTORYTEXT;
		if(!$newchapter && ($rid == "" || $title == "" || $summary == "" || !$catid)) $failed .= "<br />". _MISSINGFIELDS;
		if(find_naughty($title)) $failed .= "<br />"._NAUGHTYWORDS;
		if(($minwords && $wordcount < $minwords) || ($maxwords && $wordcount > $maxwords)) $failed .= "<br />"._WORDCOUNTFAILED;
		$storyvalid = 0;
		if($newchapter) {
			$story = dbquery("SELECT sid, catid, title, summary, validated FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid'");
			if(!dbnumrows($story)) $failed .= "<br />"._ERROR;
			else {
				list($sid, $categories, $storytitle, $summary, $storyvalid) = dbrow($story);
				$catid = explode(",", $categories);
			}
		}
		if(!empty($failed)) {
			$output .= write_error($failed);
			$submit = _PREVIEW;
		}
	}
	if(isset($_POST['submit']) && $_POST['submit'] == _ADDSTORY && !isset($submit)) 
	{

		$result = dbquery("SELECT "._UIDFIELD." as uid, "._PENNAMEFIELD." as penname, "._EMAILFIELD." as email, validated FROM "._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_authorprefs as ap WHERE "._UIDFIELD." = '$uid' AND ap.uid = "._UIDFIELD." LIMIT 1");
		$user = dbassoc($result);
		$authorpenname = $user['penname'];
		if(!$validated && (($autovalidate && !isADMIN) || $user['validated'] || $storyvalid == 2)) $validated = 1;
		else if(!$validated) $validated = 0;
		if($admin && USERUID != $uid) {
			if($admincats && !sizeof(array_intersect( $catid, explode(",", $admincats)))) {
				$output .= write_error(_NOTAUTHORIZEDADMIN."  "._TRYAGAIN);
				return $output;
			}
		}
		if($store == "mysql")
		{
			if(!$newchapter) {
				$insert = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_stories (title, summary, storynotes, catid, classes, charid,  rid, date, updated, uid, validated, rr, completed, wordcount, featured, coauthors) VALUES ('".addslashes($title)."', '".addslashes(format_story($summary))."', '".addslashes(format_story($storynotes))."', '".($catid ? implode(",", $catid) : "")."', '".($classes? implode(",", $classes) : "")."', '".($charid ? implode(",", $charid) : "")."', '$rid', '" . time() ."', '" . time() . "', '$uid', '$validated', '$rr', '$complete', '$wordcount', '$feat', '$coauthors')");
				$sid = dbinsertid( );
				$inorder = 1;
			}
			else {
				$inorder = $_GET['inorder'] + 1;
			}
			$query2 = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_chapters (title, inorder, notes, endnotes, validated, wordcount, sid, uid, storytext) VALUES('".addslashes(($chaptertitle != "" ? $chaptertitle : $title))."', '$inorder', '".addslashes(format_story($notes))."', '".addslashes(format_story($endnotes))."', '$validated', '$wordcount', '$sid', '$uid', '".addslashes($storytext)."')");
			if(!$admin) $output = write_message(_STORYADDED).viewstories( );
			else $output .= write_message(_ACTIONSUCCESSFUL).editstory( $sid );
		}
		else if ($store == "files")
		{
			if(!$newchapter) {
				$insertstory = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_stories (title, summary, storynotes, catid, classes, charid, rid, date, updated, uid, validated, rr, completed, wordcount, featured, coauthors) VALUES ('".addslashes($title)."', '".addslashes(format_story($summary))."', '".addslashes(format_story($storynotes))."', '".($catid ? implode(",", $catid) : "")."', '".($classes ? implode(",", $classes) : "")."', '".($charid ? implode(",", $charid) : "")."', '$rid', '" . time() ."', '" . time() . "', '$uid', '$validated', '$rr', '$complete', '$wordcount', '$feat', '$coauthors')");
				$sid = dbinsertid( );
				$inorder = 1;
			}
			else {
				$inorder = $_GET['inorder'] + 1;
			}
			$insertchapter = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_chapters (title, inorder, notes, endnotes, validated, wordcount, sid, uid) VALUES('".addslashes(($chaptertitle != "" ? $chaptertitle : $title))."', '$inorder', '".addslashes(format_story($notes))."', '".addslashes(format_story($endnotes))."', '".($validated ? 1 : 0)."', '$wordcount', '$sid', '$uid')");
			$chapid = dbinsertid( );
			if( !file_exists("".STORIESPATH."/$uid/" ) )
			{
				mkdir("".STORIESPATH."/$uid/", 0755);
				chmod("".STORIESPATH."/$uid/", 0777);
			}
			$handle = fopen("".STORIESPATH."/$uid/$chapid.txt", 'w');
			if ($handle)
			{
				fwrite($handle, $storytext);
				fclose($handle);
			}
			chmod("".STORIESPATH."/$uid/$chapid.txt", 0644);
			if(($newchapter && $insertchapter != false) || $insertstory != false) {
				if($newchapter) {
					unset($_POST['submit']);
					$output = write_message(_ACTIONSUCCESSFUL).editstory( $sid );
				}
				else {
					unset($_POST['submit']);
					if(!$admin) $output = write_message(_STORYADDED).viewstories( );
					else $output .= write_message(_ACTIONSUCCESSFUL).editstory( $sid );
				}
			}
			else $output .= write_error(_FATALERROR." "._TRYAGAIN);
		}
		if(!$newchapter) {
			if(count($array_coauthors)) {
				foreach($au AS $c) {
					if($c == $uid) continue;
					dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_coauthors(`sid`, `uid`) VALUES('$sid', '$c')");
				}
			}
			$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'addstory'");
			while($code = dbassoc($codequery)) {
				
				eval($code['code_text']);
			}
		}
		$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'addchapter'");
		while($code = dbassoc($codequery)) {
			eval($code['code_text']);
		}
		// validate fic, send story alerts, and mail admins
		if($validated) {
			include("includes/emailer.php");
			if(!isset($storytitle)) $storytitle = $title;
			if(!$newchapter) {
				foreach($catid as $cat) { categoryitems($cat, 1); }
				if($alertson) {
					$pennames[] = $penname;
					$coQuery = dbquery("SELECT "._PENNAMEFIELD." AS penname FROM ".TABLEPREFIX."fanfiction_coauthors AS c LEFT JOIN "._AUTHORTABLE." ON "._UIDFIELD." = c.uid WHERE sid = '$sid'");
					while($c = dbassoc($coQuery)) {
						$pennames[] = $c['penname'];
					}
					$subject = _NEWSTORYAT.$sitename;
					$mailtext = sprintf(_AUTHORALERTNOTE, $title, implode(", ", $pennames), $summary, $sid);
					$favorites = dbquery("SELECT "._UIDFIELD." as uid, "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname, alertson FROM ".TABLEPREFIX."fanfiction_favorites as fav, ".TABLEPREFIX."fanfiction_authorprefs as ap, "._AUTHORTABLE." WHERE FIND_IN_SET(fav.item,'".implode(",", $au)."') > 0 AND fav.type = 'AU' AND fav.uid = "._UIDFIELD." AND ap.uid = "._UIDFIELD." AND ap.alertson = '1'");
					while($favuser = dbassoc($favorites)) { 
						sendemail($favuser['penname'], $favuser['email'], $sitename, $siteemail, $subject, $mailtext, "html");
					}				
				}
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET stories = stories + 1");
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories + 1 WHERE FIND_IN_SET(uid, '".implode(",", $au)."') > 0");
			}
			else if($alertson && $newchapter) {
				$pennames[] = $penname;
				$coQuery = dbquery("SELECT "._PENNAMEFIELD." AS penname FROM ".TABLEPREFIX."fanfiction_coauthors AS c LEFT JOIN "._AUTHORTABLE." ON "._UIDFIELD." = c.uid WHERE sid = '$sid'");
				while($c = dbassoc($coQuery)) {
					$pennames[] = $c['penname'];
				}
				$titlequery = dbquery("SELECT title FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid' LIMIT 1");
				list($title) = dbrow($titlequery);
				$subject = _STORYALERT;
				$mailtext = sprintf(_STORYALERTNOTE, $title, implode(", ", $pennames), $sid, $inorder);
				$favorites = dbquery("SELECT "._UIDFIELD." as uid, "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname, alertson FROM ".TABLEPREFIX."fanfiction_favorites as fav, ".TABLEPREFIX."fanfiction_authorprefs as ap, "._AUTHORTABLE." WHERE fav.item = '$sid' AND fav.type = 'ST' AND fav.uid = "._UIDFIELD." AND ap.uid = "._UIDFIELD." AND ap.alertson = '1'");
				while($favuser = dbassoc($favorites)) { 
					sendemail($favuser['penname'], $favuser['email'], $sitename, $siteemail, $subject, $mailtext, "html");
				}
			}
			$update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET updated = '" . time() . "' WHERE sid = '$sid'");
			list($chapters, $words) = dbrow(dbquery("SELECT COUNT(chapid), SUM(wordcount) FROM ".TABLEPREFIX."fanfiction_chapters WHERE validated > 0"));
			list($authors) = dbrow(dbquery("SELECT COUNT(uid) FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE stories > 0"));
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats set wordcount = '$words', chapters = '$chapters', authors = '$authors'");
			$count =  dbquery("SELECT SUM(wordcount) as totalcount FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid' AND validated = '1'");
			list($totalcount) = dbrow($count);
			if($totalcount) {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET wordcount = '$totalcount' WHERE sid = '$sid'");
			}
		}
		else {
			$adminquery = dbquery("SELECT "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname, contact,categories FROM ".TABLEPREFIX."fanfiction_authorprefs as ap, "._AUTHORTABLE." WHERE "._UIDFIELD." = ap.uid AND level > 0 AND level < 4");
			if(empty($storytitle)) $storytitle = $title;
			include("includes/emailer.php");
			while($admins = dbassoc($adminquery)) {
				global $sitename, $siteemail;
				if($admins['contact'] == 1) {
					if(!$admins['categories']) {
						$subject = _NEWSTORYAT.$sitename;
						$mailtext = sprintf(_NEWSTORYAT2, $storytitle, $authorpenname, $summary)."\n <a href='$url/admin.php?action=submitted'>$url/admin.php?action=submitted</a>";							
						$mailresult = sendemail($admins['penname'], $admins['email'], $sitename, $siteemail, $subject, $mailtext, "html");
					}	
					else {
						if(count(array_intersect($catid, explode(",", $admins['categories'])))) {
							$subject = _NEWSTORYAT.$sitename;
							$mailtext = sprintf(_NEWSTORYAT2, $storytitle, $authorpenname, $summary)."\n <a href='$url/admin.php?action=submitted'>$url/admin.php?action=submitted</a>";
							sendemail($admins['penname'], $admins['email'], $sitename, $siteemail, $subject, $mailtext, "html");
						}
					}
				}
			}
		}
		return $output;
	}
	if($newchapter) {
		$storyinfo = dbquery("SELECT s.*, s.updated as updated, s.date as date, "._PENNAMEFIELD." as penname  FROM ".TABLEPREFIX."fanfiction_stories as s, "._AUTHORTABLE." WHERE "._UIDFIELD." = s.uid AND sid = '$sid' LIMIT 1");
		$stories = dbassoc($storyinfo);
		$uid = $stories['uid'];
		$chapterquery = dbquery("SELECT COUNT(sid) FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid'");
		list($inorder) = dbrow($chapterquery);
	}
	else {
		$inorder = 0;
		$stories['title'] = stripslashes($title);
		$stories['summary'] = stripslashes($summary);
		$stories['storynotes'] = stripslashes($storynotes);
		$stories['catid'] = isset($catid) ? is_array($catid) ? implode(",", $catid) : $catid : "";
		$stories['classes'] = isset($classes) && is_array($classes) ? implode(",", $classes) : $classes;
		$stories['charid'] = isset($charid) ? is_array($charid) ? implode(",", $charid) : $charid : "";
		$stories['coauthors'] = $au;
		$stories['featured'] = $feat;
		$stories['completed'] = $complete;
		$stories['rid'] = $rid;
		$stories['rr'] = $rr;
		$stories['uid'] = $uid;
		$stories['penname'] = $penname;
		$stories['date'] = time( );
		$stories['updated'] = time( );
		$stories['sid'] = false;
		$stories['rating'] = 0;
		$stories['count'] = 0;
		$stories['wordcount'] = $wordcount;
		$stories['reviews'] = 0;
		$stories['validated'] = 0;
	}
	if(!isADMIN || uLEVEL == 4) {
		$rquery = dbquery("SELECT message_title, message_text FROM ".TABLEPREFIX."fanfiction_messages WHERE message_name = 'rules' LIMIT 1");
		list($ruletitle, $ruletext) = dbrow($rquery);
		$output .= "<div class=\"sectionheader\">$ruletitle</div>$ruletext";
	}	
	if($storytext){
		$stories['storytext'] = $storytext;
		$stories['chaptertitle'] = $chaptertitle;
		$stories['inorder'] = $inorder;
		$stories['notes'] = $notes;
		$stories['endnotes'] = $endnotes;
/*
		$output .= "<div><span class=\"label\">"._CHAPTERTITLE.": </span> $chaptertitle</div>
			<div><span class=\"label\">"._AUTHORSNOTES.":</span> ".format_story($notes)."</div>
			<div><span class=\"label\">"._STORY.":</span><br />".format_story($storytext)."</div>
			<div><span class=\"label\">"._ENDNOTES.":</span> ".format_story( $endnotes)."</div>";
*/
	}
	if(isset($_POST['submit']) || $newchapter) $output .= preview_story($stories);

	$submit = isset($_POST['submit']) ? $_POST['submit'] : false;
	if(!$submit) $submit = _PREVIEW;
	$output .= "<div class=\"tblborder\" style=\" padding: 10px; margin: 1em auto;\">
	<form METHOD=\"POST\" name=\"form\" enctype=\"multipart/form-data\" action='stories.php?action=$action".($newchapter ? "&amp;sid=$sid&amp;inorder=$inorder" : "").($admin == 1 ? "&amp;admin=1&amp;uid=$uid" : "")."'>";
	if(!$newchapter) $output .= storyform($stories, $submit);
	$output .= chapterform($inorder, $notes, $endnotes, $storytext, $chaptertitle, $uid);
	$output .= "<div style=\"text-align: center;\"><input type=\"submit\" class=\"button\" value=\""._PREVIEW."\" name=\"submit\">&nbsp; <input type=\"submit\" class=\"button\" 
                 value=\""._ADDSTORY."\" name=\"submit\"></div></form></div>";
	return $output;
}
// end newstory function

function viewstories( ) {
	global $storiespath, $ratings, $autovalidate, $reviewsallowed, $sid, $chapid, $up, $down;

	$output = "<div id=\"pagetitle\">"._MANAGESTORIES."</div>";

	$go = isset($_GET['go']) ? $_GET['go'] : false;
	$com = isset($_GET['com']) ? $_GET['com'] : false;
	$hidechapters = isset($_GET["chapters"]) ? $_GET["chapters"] : false;
	if(($go || $com) && $sid) {
		$inorder = isset($_GET['inorder']) && isNumber($_GET['inorder']) ? $_GET['inorder'] : false;
		if($inorder && $chapid) {
			if($go == "up") $oneabove = $inorder - 1;
			else $oneabove = $inorder + 1;
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET inorder = '$inorder' WHERE sid = '$sid' and inorder = '$oneabove'");
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET inorder = '$oneabove' WHERE chapid = '$chapid'");	
		}
		if($com)  dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET completed = ".($com == "yes" ? "1" : "0")." WHERE sid = '$sid'");
	}
	$output .= "<p style=\"text-align: right; margin: 1em;\"><a href=\"stories.php?action=viewstories&amp;chapters=".($hidechapters != "view" ? "view\">"._VIEWCHAPTERS : "hide\">"._HIDECHAPTERS)."</a></p>
		<div style=\"width: 90%; margin: 0 auto;\"><table cellpadding=\"3\" cellspacing=\"0\" width=\"100%\" class=\"tblborder\"><tr><th class=\"tblborder\">"._STORIES."</th><th colspan=\"3\" class=\"tblborder\">"._OPTIONS."</th>".($reviewsallowed ? "<th class=\"tblborder\">"._REVIEWS."</th>" : "").($autovalidate ? "" : "<th class=\"tblborder\">"._VALIDATED."</th>")."<th class=\"tblborder\">"._READS."</th></tr>";
	
	$squery = "SELECT stories.sid, title, reviews, rating, completed, validated, featured, count FROM " . TABLEPREFIX . "fanfiction_stories AS stories LEFT JOIN " . TABLEPREFIX . "fanfiction_coauthors AS coauth ON stories.sid = coauth.sid WHERE stories.uid = '" . USERUID . "' OR coauth.uid = '" . USERUID . "' GROUP BY stories.sid ORDER BY title ";
	$sresult = dbquery($squery); 
	$stories = dbnumrows($sresult);
	while($story = dbassoc($sresult)) {
		$query2 = dbquery("SELECT chapid, title, inorder, rating, reviews, validated, count FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '".$story['sid']."' ORDER BY inorder"); 
		$chapters =  dbnumrows($query2);
		$output .= "<tr><td class=\"tblborder\"><a href=\"viewstory.php?sid=".$story['sid']."\">".stripslashes($story['title'])."</a> ".ratingpics($story['rating'])." <strong>"._COMPLETE.":</strong> <a href=\"stories.php?action=viewstories&amp;sid=".$story['sid']."&amp;com=".$story['completed']."\"><a href=\"stories.php?action=viewstories&amp;sid=".$story['sid']."&amp;com=".($story['completed'] == 1 ? "no\">"._YES : "yes\">"._NO)."</a></td>
			<td class=\"tblborder\" colspan=\"3\"><a href=\"stories.php?action=editstory&amp;sid=".$story['sid']."\">"._EDIT."</a> - <a href=\"stories.php?action=delete&amp;sid=".$story['sid']."\">"._DELETE."</a> - <a href=\"stories.php?action=newchapter&amp;sid=".$story['sid']."&amp;inorder=$chapters\">"._ADDNEWCHAPTER."</a></td>
			";
		if($reviewsallowed) $output .= "<td class=\"tblborder\" align=\"center\">".($story['reviews'] ? "<a href=\"reviews.php?type=ST&amp;item=".$story['sid']."\">".$story['reviews']."</a>" : "0")."</td>";
		if(!$autovalidate) $output .= "<td class=\"tblborder\" align=\"center\">".($story['validated'] > 0 ? _YES : _NO)."</td>";
		$output .= "<td class=\"tblborder\" align=\"center\">".($story['count'] ? $story['count'] : "0")."</td></tr>";
		if($hidechapters && $hidechapters != "hide") {
			while($chapter = dbassoc($query2)) {
				$output .="<tr><td  class=\"tblborder\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"viewstory.php?sid=".$story['sid']."&amp;chapter=".$chapter['inorder']."\">$chapter[title]</a></td>";
				if($chapters > 1) $output .= "<td  class=\"tblborder\" align=\"center\"><a href=\"stories.php?action=viewstories&amp;go=up&amp;sid=".$story['sid']."&amp;chapid=".$chapter['chapid']."&amp;inorder=".$chapter['inorder']."&amp;chapters=view\">$up</a></td>
					<td class=\"tblborder\" align=\"center\"><a href=\"stories.php?action=viewstories&amp;go=down&amp;sid=".$story['sid']."&amp;chapid=".$chapter['chapid']."&amp;inorder=".$chapter['inorder']."&amp;chapters=view\">$down</a></td>";
				$output .= "<td class=\"tblborder\"".($chapters > 1 ? "" : "colspan=\"3\"")."><a href=\"stories.php?action=editchapter&amp;chapid=".$chapter['chapid']."\">"._EDIT."</a>";
				if($chapters > 1) $output .= " - <a href=\"stories.php?action=delete&amp;chapid=".$chapter['chapid']."&amp;sid=".$story['sid']."\">"._DELETE."</a>";
				$output .= "</td><td class=\"tblborder\" align=\"center\">";
				if($reviewsallowed) $output .= ($chapter['reviews'] ? "<a href=\"reviews.php?type=ST&amp;item=".$story['sid']."&amp;chapid=".$chapter['chapid']."\">".$chapter['reviews']."</a>" : "0");
				if(!$autovalidate) $output .= "</td><td class=\"tblborder\" align=\"center\">".($chapter['validated'] > 0 ? _YES : _NO);
				$output .= "<td class=\"tblborder\" align=\"center\">".$chapter['count']."</td></tr>";
			}
		}
	}
	$output .= "</table></div>";
	if($stories < 1) $output .= write_message(_NORESULTS);
	$output .= write_message("<a href='stories.php?action=newstory'>"._ADDNEWSTORY."</a>");
	return $output;
}
// end viewstories function

function editchapter( $chapid ) {
	global $tpl, $chapid, $store, $alertson, $storiespath, $admin, $tinyMCE, $allowed_tags, $sid, $logging;

	$output = "<div id=\"pagetitle\">"._EDITCHAPTER."</div>";
// get variables from $_POST
	if(isset($_POST['submit'])) {
		$chaptertitle = strip_tags(descript($_POST["chaptertitle"]), $allowed_tags);
		$notes = strip_tags(descript($_POST["notes"]), $allowed_tags);
		$endnotes = strip_tags(descript($_POST["endnotes"]), $allowed_tags);
		if(!empty($_FILES['storyfile']['name'])) {
			if ($_FILES['storyfile']['type'] != 'text/html' && $_FILES['storyfile']['type'] != 'text/plain') {
 				 $failed = _INVALIDUPLOAD;
				$submit = _PREVIEW;
			}
			else {
				$texts = file($_FILES['storyfile']['tmp_name']);
				foreach ($texts as $text) {
					if($_FILES['storyfile']['type'] == 'text/html') $story .= rtrim($text, "\n\r\t")." ";
					else $story .= $text;
				}
			}
		}
		else if(isset($_POST['storytext'])) $story = $_POST['storytext'];
		$storytext = strip_tags(descript($story), $allowed_tags);
		$inorder = $_POST["inorder"];
		$words_to_count = strip_tags($storytext);
		$pattern = "/[^(\w|\d|\'|\"|\.|\!|\?|;|,|\\|\/|\-\-|:|\&|@)]+/";
		$words_to_count = preg_replace ($pattern, " ", $words_to_count);
		$words_to_count = trim($words_to_count);
		$wordcount = count(explode(" ",$words_to_count)); 
	}
	else {
	}
	if(isset($_POST['submit']) && $_POST['submit'] == _ADDSTORY) {
		if(!$chaptertitle || !$storytext ) $submit == _PREVIEW;
		else {
			if($admin && empty($_POST["uid"])) {
				$result2 = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_chapters WHERE chapid = '$chapid' LIMIT 1");
				$user = dbassoc($result2);
				$uid = $user['uid'];
			}
			else if(isset($_POST['uid']) && isNumber($_POST['uid'])) {
				$uid = $_POST['uid'];
			}
			else $uid= USERUID;
			if($store == "mysql") {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET uid = '$uid', title = '".addslashes($chaptertitle)."', notes = '".addslashes($notes)."', endnotes = '".addslashes($endnotes)."', wordcount = '$wordcount', storytext = '".addslashes($storytext)."' WHERE chapid = '$chapid' LIMIT 1");
			}
			else if($store == "files"){
				$updatequery = dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET uid = '$uid', title = '".addslashes($chaptertitle)."', notes = '".addslashes($notes)."', endnotes = '".addslashes($endnotes)."', wordcount = '$wordcount' WHERE chapid = '$chapid' LIMIT 1");
				if( !file_exists( STORIESPATH."/$uid/" ) )
				{
					mkdir(STORIESPATH."/$uid", 0755);
					chmod(STORIESPATH."/$uid", 0777);
				}
				$handle = fopen(STORIESPATH."/$uid/$chapid.txt", 'w+');

				if ($handle)
				{
					fwrite($handle, descript($storytext));
					fclose($handle);
					$storytext = "";
				}

			}
			//  Check that the chapter has been validated before updating the word count of the story.
			$validquery = dbquery("SELECT validated, sid FROM ".TABLEPREFIX."fanfiction_chapters WHERE chapid = '$chapid' LIMIT 1");
			list($valid, $sid) = dbrow($validquery);
			if($valid) {
				$count =  dbquery("SELECT SUM(wordcount) as totalcount FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid'");
				list($totalcount) = dbrow($count);
				if($totalcount) {
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET wordcount = '$totalcount' WHERE sid = '$sid'");
				}
			}
			$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'editchapter'");
			while($code = dbassoc($codequery)) {
				eval($code['code_text']);
			}
			if($admin && $logging && USERUID != $uid) {
				$storyinfo = dbquery("SELECT story.title, story.sid, chapter.uid, chapter.inorder, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_stories as story, ".TABLEPREFIX."fanfiction_chapters as chapter, "._AUTHORTABLE." WHERE "._UIDFIELD." = chapter.uid AND story.sid = chapter.sid AND chapter.chapid = $chapid");
				list($title, $sid, $chapuid, $inorder, $chappenname) = dbrow($storyinfo);
				dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_ADMIN_EDIT_CHAPTER, USERPENNAME, USERUID, $title, $sid, $chappenname, $chapuid, $inorder))."', '".USERUID. "', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'ED', " . time() . ")");
			}
			unset($_POST['submit']);
			$output = write_message(_STORYUPDATED).editstory($sid);
			$tpl->assign( "output", $output );
			$tpl->printToScreen( );
			dbclose( );
			exit( );
		}
	}
	$output .= "<div class=\"tblborder\" style=\"margin: 0 auto; padding: 5px;\">
		<form METHOD=\"POST\"  enctype=\"multipart/form-data\" name=\"form\" action=\"stories.php?action=editchapter&amp;chapid=$chapid".($admin ? "&amp;admin=1" : "")."\">";
	if(!isset($_POST['submit']) || $_POST['submit'] != _PREVIEW) {
		$storyquery = dbquery("SELECT title, inorder, notes, endnotes, uid, storytext, sid FROM ".TABLEPREFIX."fanfiction_chapters WHERE chapid = '$chapid' LIMIT 1");
		$story = dbassoc($storyquery);
		$chaptertitle = stripslashes($story["title"]);
		$storytext = $story["storytext"];
		$inorder = $story["inorder"];
		$notes = stripslashes($story["notes"]);
		$endnotes = stripslashes($story["endnotes"]);
		$sid = $story["sid"];

		if($store == "mysql") $storytext = $story['storytext'];
		else {
			if($out = fopen (STORIESPATH."/".$story['uid']."/$chapid.txt", "r"))
				while (!feof($out)) {
					$storytext .= fgets($out, 10000);
				}
		}
		$submit = _PREVIEW;
		$output .= chapterform($inorder, $notes, $endnotes, stripslashes($storytext), $chaptertitle, $story['uid']);
	}
	else {
		if(isset($failed)) $output .= write_message($failed);
		$output .= "<span class='label'>"._TITLE.":</span> $chaptertitle<br />
			<span class='label'>"._AUTHORSNOTES.":</span> $notes<br />
			<hr><br />".format_story($storytext)."<hr>
			<span class='label'>"._ENDNOTES.":</span> $endnotes<br />";
		$output .= chapterform($inorder, $notes, $endnotes, $storytext, $chaptertitle, (isset($_POST['uid']) ? $_POST['uid'] : false));
		$submit = $_POST['submit'];
	}
	$output .= "<p><input type=\"hidden\" name=\"sid\" value=\"$sid\"><input type=\"hidden\" name=\"inorder\" value=\"$inorder\"><input type=\"submit\" class=\"button\" value=\"$submit\" name=\"submit\">&nbsp; <input type=\"submit\" class=\"button\" value=\""._ADDSTORY."\" name=\"submit\"></p></form></div>";
	return $output;

}
// end editchapter function

function editstory($sid) {
	global $tpl, $storiespath, $store, $allowed_tags, $uid, $admin, $tinyMCE, $up, $down, $dateformat, $classtypelist, $logging, $alertson;

	$output = "<div id=\"pagetitle\">"._EDITSTORY."</div>";
	if(isset($admin) && isset($uid)) {
		$author = dbquery("SELECT "._PENNAMEFIELD." FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
		$authorvalid = 1; // It's an admin edit so it's valid.
		list($penname) = dbrow($author);
	}
	else {  
		$valid = dbquery("SELECT validated FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE uid = '".USERUID."' LIMIT 1");
		list($authorvalid) = dbrow($valid);
		$uid = USERUID;
		$penname = USERPENNAME;
	}
	if(isset($_POST['submit'])) {
		$title = isset($_POST['title']) ? strip_tags(descript($_POST['title']), $allowed_tags) : "";
		$summary = isset($_POST['summary']) ? strip_tags(descript($_POST['summary']), $allowed_tags) : "";
		$storynotes = isset($_POST['storynotes']) ?  strip_tags(descript($_POST['storynotes']), $allowed_tags) : "";
		$rr = isset($_POST['rr']) && isNumber($_POST['rr']) ? $_POST['rr'] : 0;
		$feat = isset($_POST['feature']) && isNumber($_POST['feature']) ? $_POST['feature'] : 0;
		$complete = isset($_POST['complete']) && isNumber($_POST['complete']) ? $_POST['complete'] : 0;
		$validated = isset($_POST['validated']) && isNumber($_POST['validated']) ? $_POST['validated'] : 0;
		$chaptertitle = isset($_POST['chaptertitle']) ? descript(strip_tags($_POST['chaptertitle'], $allowed_tags)) : "";
		$notes = isset($_POST['notes']) ? strip_tags(descript($_POST['notes']), $allowed_tags) : "";
		$endnotes = isset($_POST['endnotes']) ? strip_tags(descript($_POST['endnotes']), $allowed_tags) : "";
		$rid = isset($_POST['rid']) && isNumber($_POST['rid']) ? $_POST['rid'] : 0;
		$catid = isset($_POST['catid']) ? array_filter(explode(",", $_POST['catid']), "isNumber") : array( );
		$charid = isset($_POST['charid']) ? array_filter($_POST['charid'], "isNumber") : array( );
		$array_coauthors = isset($_POST['coauthors']) ? array_filter(explode(",", $_POST['coauthors']), "isNumber") : array( );
		if(isset($_POST['uid']) && isNumber($_POST['uid'])) $uid = $_POST['uid'];
		else $uid = USERUID;
		$classes = array( );
		$classquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classtypes");
		while($type = dbassoc($classquery)) {
			if(isset($_POST["class_".$type['classtype_id']])) {
				$opts = is_array($_POST["class_".$type['classtype_id']]) ? array_filter($_POST["class_".$type['classtype_id']], "isNumber") : "";
				$classes = array_merge($opts, $classes);
			}
		}
		if(!$admin && $authorvalid && !$validated) $validated = 2;
		$au[] = $uid;
		if(count($array_coauthors)) {
			$au = array_merge($au, $array_coauthors);
			$coauthors = 1;
		}
		else $coauthors = 0;
	}
	if(isset($_POST['submit']) && $_POST['submit'] == _ADDSTORY) {
		$oldcats = isset($_POST['oldcats']) ? array_filter(explode(",", $_POST['oldcats']), "isNumber") : array( );
		if (!$rid || !$title || !$summary || !$catid) {
			$output .= write_error(_MISSINGFIELDS);
			$submit = _PREVIEW;
		}
		else {
			// Change author of story.
			if($admin) {
				$authquery = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '".$sid."'");
				list($olduid) = dbrow($authquery);
				if($olduid != $uid) {
					if($store == "files") {
						$chapters = dbquery("SELECT chapid FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid' AND uid = '$olduid'");
						while($chap = dbassoc($chapters)) {
							$chapid = $chap['chapid'];
							$storytext = "";
							if($out = fopen (STORIESPATH."/$olduid/$chapid.txt", "r"))
							while (!feof($out)) {
								$storytext .= fgets($out, 10000);
							}
							fclose($out);
							unlink(STORIESPATH."/$olduid/$chapid.txt"); 
							if($storytext) {
								if( !file_exists( STORIESPATH."/$uid/" ) ) {
									mkdir(STORIESPATH."/$uid", 0755);
									chmod(STORIESPATH."/$uid", 0777);
								}
								$handle = fopen(STORIESPATH."/$uid/$chapid.txt", 'w');
								if ($handle) {
									fwrite($handle, $storytext);
									fclose($handle);
								}
								chmod(STORIESPATH."/$uid/$chapid.txt", 0644);
							}
							dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET uid = '$uid' WHERE chapid = '$chapid' LIMIT 1");
						}
					}
					else $chapupdate = dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET uid = '$uid' WHERE sid = '$sid' AND uid = '$olduid'");
					$switch = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET uid = '$uid' WHERE sid = '$sid' LIMIT 1");
					if($logging) {
						$authorquery = dbquery("SELECT "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$olduid' LIMIT 1");
						list($oldpenname) = dbrow($authorquery);
						$author2query = dbquery("SELECT "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
						list($newpenname) = dbrow($author2query);
						dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_ADMIN_EDIT_AUTHOR, USERPENNAME, USERUID, $title, $sid, $newpenname, $uid, $oldpenname, $olduid))."', '".USERUID. "', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'ED', " . time() . ")");
					}
				}

			}
			// End change author of story
			$oldinfo = dbquery("SELECT title, validated, featured, catid FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid' LIMIT 1");
			list($storytitle, $oldvalid, $oldfeat, $oldcats ) = dbrow($oldinfo);
			$oldcats = explode(",", $oldcats);
			if($validated) {
				if(!$oldvalid) {
					include("includes/emailer.php");
					list($newchapter) = dbrow(dbquery("SELECT validated FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid' AND inorder = '1'"));
					if(!$newchapter) {
						if($alertson) {
							$pennames[] = $penname;
							$coQuery = dbquery("SELECT "._PENNAMEFIELD." AS penname FROM ".TABLEPREFIX."fanfiction_coauthors AS c LEFT JOIN "._AUTHORTABLE." ON "._UIDFIELD." = c.uid WHERE sid = '$sid' ");
							while($c = dbassoc($coQuery)) {
								$pennames[] = $c['penname'];
							}
							$subject = _NEWSTORYAT;
							$mailtext = sprintf(_AUTHORALERTNOTE, $title, implode(", ", $pennames), $summary, $sid);
							$favorites = dbquery("SELECT "._UIDFIELD." as uid, "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname, alertson FROM ".TABLEPREFIX."fanfiction_favorites as fav, ".TABLEPREFIX."fanfiction_authorprefs as ap, "._AUTHORTABLE." WHERE fav.item = $uid AND fav.type = 'AU' AND fav.uid = "._UIDFIELD." AND ap.uid = "._UIDFIELD." AND ap.alertson = '1'");
							while($favuser = dbassoc($favorites)) { 
								sendemail($favuser['penname'], $favuser['email'], $sitename, $siteemail, $subject, $mailtext, "html");
							}				
						}
					}
					else if($alertson && $newchapter) {
						$pennames[] = $penname;
						$coQuery = dbquery("SELECT "._PENNAMEFIELD." AS penname FROM ".TABLEPREFIX."fanfiction_coauthors AS c LEFT JOIN "._AUTHORTABLE." ON "._UIDFIELD." = c.uid WHERE sid = '$sid'");
						while($c = dbassoc($coQuery)) {
							$pennames[] = $c['penname'];
						}
						$titlequery = dbquery("SELECT title FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid' LIMIT 1");
						list($title) = dbrow($titlequery);
						list($inorder) = dbrow(dbquery("SELECT inorder FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid' ORDER BY inorder DESC LIMIT 1"));
						$subject = _STORYALERT;
						$mailtext = sprintf(_STORYALERTNOTE, $storytitle, implode(", ", $pennames), $sid, $inorder);
						$favorites = dbquery("SELECT "._UIDFIELD." as uid, "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname, alertson FROM ".TABLEPREFIX."fanfiction_favorites as fav, ".TABLEPREFIX."fanfiction_authorprefs as ap, "._AUTHORTABLE." WHERE fav.item = '$sid' AND fav.type = 'ST' AND fav.uid = "._UIDFIELD." AND ap.uid = "._UIDFIELD." AND ap.alertson = '1'");
						while($favuser = dbassoc($favorites)) { 
							sendemail($favuser['penname'], $favuser['email'], $sitename, $siteemail, $subject, $mailtext, "html");
						}
					}
					$update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET validated = '$validated', updated = '" . time() . "' WHERE sid = '$sid'");
					$update2 = dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET validated = 1 WHERE sid = '$sid'");
					$coauths = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '$sid'");
					while($c = dbassoc($coauths)) {
						dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories + 1 WHERE uid = '".$c['uid']."'");
					}
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories + 1 WHERE uid = '$uid'");
					$count =  dbquery("SELECT SUM(wordcount) as totalcount FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid' AND validated = '1'");
					list($totalcount) = dbrow($count);
					if($totalcount) {
						dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET wordcount = '$totalcount' WHERE sid = '$sid'");
					}
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET stories = stories + 1");
				}
				else if($validated == 2) {
					$update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET validated = '$validated' WHERE sid = '$sid'");
					$update2 = dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET validated = 1 WHERE sid = '$sid'");
					$count =  dbquery("SELECT SUM(wordcount) as totalcount FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid' AND validated = '1'");
					list($totalcount) = dbrow($count);
					if($totalcount) {
						dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET wordcount = '$totalcount' WHERE sid = '$sid'");
					}
				}
				$newcats = array_diff($catid, $oldcats);
				foreach($newcats as $cat)  { categoryitems($cat, 1); }
				$delcats = array_diff($oldcats, $catid);
				foreach($delcats as $cat) { categoryitems($cat, -1); }
			}
			else if($admin && !$validated && $oldvalid > 0) {
				foreach($oldcats as $cat) { categoryitems($cat, -1); }
				$update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET validated = '0' WHERE sid = '$sid'");
				$update2 = dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET validated = 0 WHERE sid = '$sid'");
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET stories = stories - 1");
				$coauths = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '$sid'");
				while($c = dbassoc($coauths)) {
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories - 1 WHERE uid = '".$c['uid']."'");
				}
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories - 1 WHERE uid = '$uid'");
				list($chapters, $words) = dbrow(dbquery("SELECT COUNT(chapid), SUM(wordcount) FROM ".TABLEPREFIX."fanfiction_chapters WHERE validated > 0"));
				list($authors) = dbrow(dbquery("SELECT COUNT(uid) FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE stories > 0"));
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats set wordcount = '$words', chapters = '$chapters', authors = '$authors'");
			}
			else if(!$admin) $validated = $oldvalid;
			if(!$admin && $oldfeat != $feat) $feat = $oldfeat;
			// Update the site stats
			list($chapters, $words) = dbrow(dbquery("SELECT COUNT(chapid), SUM(wordcount) FROM ".TABLEPREFIX."fanfiction_chapters WHERE validated > 0"));
			list($authors) = dbrow(dbquery("SELECT COUNT(uid) FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE stories > 0"));
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats set wordcount = '$words', chapters = '$chapters', authors = '$authors'");

			$updatequery = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET title = '".addslashes($title)."', summary = '".addslashes(format_story($summary))."', storynotes = '".addslashes(format_story($storynotes))."', rr = '".($rr ? 1 : 0)."', completed = '".($complete ? 1 : 0)."', validated = '$validated', rid = '$rid', classes = '".(is_array($classes) ? implode(",", $classes) : $classes)."', charid = '".(is_array($charid) ? implode(",", $charid) : $charid)."', catid = '".(is_array($catid) ? implode(",", $catid) : $catid)."', coauthors = '".$coauthors."', featured = '$feat' WHERE sid = '$sid'");
		 
			$clist = array( );
			$coauths = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '$sid'");
			while($c = dbassoc($coauths)) {
				$clist[] = $c['uid'];
			}
			foreach($au AS $a) {
				if($a == $uid) continue;
				if(!in_array($a, $clist)) {
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories + 1 WHERE uid = '$a'");
					dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_coauthors(`sid`, `uid`) VALUES('$sid', '$a')");
				}
			}
			foreach($clist AS $c) {
				if(!in_array($c, $au)) {
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_authorprefs SET stories = stories - 1 WHERE uid = '$c'");
					dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '$sid' AND uid = '$c'");
				}		
			}
			$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'editstory'");

			while($code = dbassoc($codequery)) {
			 
				eval($code['code_text']);
			}
			if($logging && $admin) {
				if(USERUID != $uid) { // If you're editing your own story, don't log it.
					$authorquery = dbquery("SELECT "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
					list($penname) = dbrow($authorquery);
					dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_ADMIN_EDIT, USERPENNAME, USERUID, $title, $sid, $penname, $uid))."', '".USERUID. "', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'ED', " . time() . ")");
				}
			}
			$output .= write_message(_STORYUPDATED."  ".($admin ? _BACK2ADMIN : _BACK2ACCT));
			$tpl->assign( "output", $output );
			$tpl->printToScreen( );
			dbclose( );
			exit( );
		}
	}
	$query = dbquery("SELECT DATE_FORMAT(FROM_UNIXTIME(date), '$dateformat') as date, wordcount, uid FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid' LIMIT 1");
	list($published, $wordcount, $storyuid) = dbrow($query);
	$formbegin = "<div class=\"tblborder\" style=\"margin: 10px auto; padding: 10px;\">
		<form METHOD=\"POST\" name=\"form\" action=\"stories.php?action=editstory".($admin ? "&amp;admin=1" : "")."&amp;sid=$sid\">";

	if(isset($_POST['submit']) && $_POST['submit'] != _PREVIEW) {
		$submit = _PREVIEW;
		$storyquery = dbquery("SELECT title, summary, storynotes,  rr, completed, rid, classes, charid, catid, featured, uid, coauthors, date as date FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid' LIMIT 1");
		$story = dbassoc($storyquery);
		$output .= $formbegin.storyform($story, $preview);
		$output .= "<input type=\"hidden\" name=\"oldcats\" value =\"".$story['catid']."\">";
	}
	else {
		$submit = _PREVIEW;
		$storyinfo = dbquery("SELECT s.*, s.date as date, s.updated as updated, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_stories as s, "._AUTHORTABLE." WHERE "._UIDFIELD." = s.uid AND sid = '$sid' LIMIT 1");
		$stories = dbassoc($storyinfo);
		if($stories['coauthors'] && !isset($_POST['submit'])) {
			$au = array();
			$coauths = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '".$stories['sid']."'");
			while($c = dbassoc($coauths)) {
				$au[] = $c['uid'];
			}
			$stories['coauthors'] = $au;
		}
		if(isset($_POST['submit'])) {
			$stories['title'] = stripslashes($title);
			$stories['summary'] = stripslashes($summary);
			$stories['storynotes'] = stripslashes($storynotes);
			$stories['catid'] = isset($catid) ? is_array($catid) ? implode(",", $catid) : $catid : "";
			$stories['classes'] = isset($classes) && is_array($classes) ? implode(",", $classes) : $classes;
			$stories['charid'] = isset($charid) ? is_array($charid) ? implode(",", $charid) : $charid : "";		
			$stories['coauthors'] = $au;
			$stories['featured'] = $feat;
			$stories['completed'] = $complete;
			$stories['rid'] = $rid;
			$stories['rr'] = $rr;
			$stories['uid'] = $uid;
			$stories['wordcount'] = $wordcount;
		}
		$output .= preview_story($stories);
		$output .= $formbegin.storyform($stories, _PREVIEW);
	}

	$chapquery = dbquery("SELECT chapid, title, inorder, rating, reviews, validated, uid FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid' ORDER BY inorder");
	$chapters = dbnumrows($chapquery);
	$output .= "<p><input type=\"submit\" class=\"button\" value=\"$submit\" name=\"submit\">&nbsp; <input type=\"submit\" class=\"button\" value=\""._ADDSTORY."\" name=\"submit\"></p></form></div>";
	$output .= "<br><table class=\"tblborder\" style=\"margin: 0 auto;  \"><tr><th>"._CHAPTER."</th>".($chapters > 1 ? "<th>"._MOVE."</th>" : "")."<th>"._OPTIONS."</th></tr>";
			while($chapter = dbassoc($chapquery)) {
				$output .="<tr><td class=\"tblborder\"><a href=\"viewstory.php?sid=$sid&amp;chapter=".$chapter['inorder']."\">".$chapter['title']."</a></td>";
				if($chapters > 1) $output .= "<td align=\"center\" class=\"tblborder\">".($chapter['inorder'] == 1 ? "" : "<a href=\"stories.php?action=viewstories&amp;go=up&amp;sid=$sid&amp;chapid=".$chapter['chapid']."&amp;inorder=".$chapter['inorder']."\">$up</a>").
					($chapter['inorder'] == $chapters ? "" : "<a href=\"stories.php?action=viewstories&amp;go=down&amp;sid=$sid&amp;chapid=".$chapter['chapid']."&amp;inorder=".$chapter['inorder']."\">$down</a>")."</td>";
				$output .= "<td class=\"tblborder\" align=\"center\"><a href=\"stories.php?action=editchapter&amp;chapid=".$chapter['chapid'].($admin ? "&amp;admin=1&amp;uid=".$chapter['uid'] : "")."\">"._EDIT."</a>";
				if($chapters > 1) $output .= " - <a href=\"stories.php?action=delete&amp;chapid=".$chapter['chapid']."&amp;sid=$sid".($admin ? "&amp;admin=1&amp;uid=".$chapter['uid'] : "")."\">"._DELETE."</a>";
				$output .= "</td></tr>";
			}
	$output .= "<tr><td class=\"tblborder\" colspan=\"3\" align=\"center\"><a href=\"stories.php?action=newchapter&amp;sid=$sid&amp;inorder=$chapters".($admin ? "&amp;admin=1&amp;uid=".$storyuid : "")."\">"._ADDNEWCHAPTER."</a></td></tr></table>";
	return $output;
}
// end editstory

function delete( ) {
	global $tpl, $store, $storiespath, $admin, $sid, $chapid, $logging;

	$confirmed = isset($_GET['confirmed']) ? $_GET['confirmed'] : false;
	if(!$sid && !$chapid) return write_error(_ERROR);
	$output = "<div id=\"pagetitle\">".($chapid ? _DELETECHAPTERTITLE : _DELETESTORYTITLE)."</div>";
	if($admin) {
		if($chapid) $authorquery = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_chapters WHERE chapid = '$chapid' LIMIT 1");
		else $authorquery = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid' LIMIT 1");
		list($uid) = dbrow($authorquery);
	}
	else $uid = USERUID;
	if($confirmed == "no") {
		$output .= "<center>"._ACTIONCANCELLED."  ".($admin ? _BACK2ADMIN : _BACK2ACCT)."</center>";
	}
	else if($confirmed == "yes") {
		$storyquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid' LIMIT 1");
		$story = dbassoc($storyquery);
		if($chapid) {
			$chapterquery = dbquery("SELECT inorder, chapid, uid FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$sid'");
			$orderquery = dbquery("SELECT inorder FROM ".TABLEPREFIX."fanfiction_chapters WHERE chapid = '$chapid' LIMIT 1");
			list($inorder) = dbrow($orderquery);
			if(empty($inorder)) { // Shouldn't be possible and yet someone managed to do it.
				errorExit();
			}
			$chapters = dbnumrows($chapterquery);
		}
		if(isset($chapters) && $chapters > 1) {
			list($valid) = dbrow(dbquery("SELECT validated FROM ".TABLEPREFIX."fanfiction_chapters where chapid = '$chapid' LIMIT 1"));
			dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_chapters WHERE chapid = '$chapid' LIMIT 1");
			if($valid) dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET chapters = chapters - 1");
			if($store == "files") unlink(STORIESPATH."/$uid/".$chapid.".txt"); 
			if($inorder < $chapters) 
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET inorder = (inorder - 1) WHERE sid = '$sid' AND inorder > $inorder");
			$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'delchapter'");
			while($code = dbassoc($codequery)) {
				eval($code['code_text']);
			}

			if($logging && $admin) {
				$authorquery = dbquery("SELECT "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
				list($penname) = dbrow($authorquery);
				dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_ADMIN_DEL_CHAPTER, USERPENNAME, USERUID, $story['title'], $sid, $penname, $uid, $inorder))."', '".USERUID. "', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'DL', " . time() . ")");
			}
			return "<center>"._ACTIONSUCCESSFUL."</center>".editstory( $sid );
		}
		else {
			include("includes/deletefunctions.php");
			deleteStory($story);
		}
		$output = write_message(_ACTIONSUCCESSFUL."  ".($admin ? _BACK2ADMIN : viewstories( )));	
	}
	else {
		if($chapid) {
			$output .= write_message(_CONFIRMDELETE."<BR><BR>
[ <a href=\"stories.php?action=delete&amp;confirmed=yes&amp;chapid=$chapid&amp;sid=$sid".(!empty($admin) ? "&amp;admin=1&amp;uid=".$uid : "")."\">"._YES."</a> | 
				<a href=\"stories.php?action=delete&amp;confirmed=no\">"._NO."</a> ]");
		}
		else {
			$output .= write_message(_DELETESTORY."<BR><BR>
[ <a href=\"stories.php?action=delete&amp;confirmed=yes&amp;sid=$sid".(!empty($admin) ? "&amp;admin=1&amp;uid=".$uid : "")."\">"._YES."</a> | 
				<a href=\"stories.php?action=delete&amp;confirmed=no\">"._NO."</a> ]");
		}
	}
	return $output;
}
// end delete
	
switch($action) {
	case "newstory":
		$output .= newstory( );
		break;
	case "newchapter":
		$output .= newstory( );
		break;
	case "editchapter":

		$output .= editchapter($chapid);			
		break;
	case "editstory":
		$output .= editstory($sid);
		break;
	case "delete":
		$output .= delete( );
		break;
	default:
		$output .= viewstories( );
		break;
}

	$tpl->assign( "output", $output );
	$tpl->printToScreen();
	dbclose( );
?>
