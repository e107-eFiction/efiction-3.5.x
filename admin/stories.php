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
function featured( ) {
	global $tableprefix, $dbconnect;

	if($_GET['retire'])
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET featured = 2 WHERE sid = ".$_GET['retire']);
	if($_GET['feature'])
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET featured = 1 WHERE sid = ".$_GET['feature']);
	$fquery = "SELECT stories.*, stories.title as title, author.penname, DATE_FORMAT(FROM_UNIXTIME(stories.updated), '%Y.%m.%d') as updatesort, DATE_FORMAT(stories.date, '$datim') as date, DATE_FORMAT(FROM_UNIXTIME(stories.updated), '$datim') as updated FROM ".TABLEPREFIX."fanfiction_authors as author, ".TABLEPREFIX."fanfiction_stories as stories WHERE stories.featured > 0 AND stories.uid = author.uid ORDER BY stories.featured";
	$fresult = dbquery($fquery) or die(_FATALERROR."Query: ".$fquery."<br />Error: (". $dbconnect->errno.") ". $dbconnect->error);
	$output = "<center><table class=\"tblborder\" cellpadding=\"5\"><tr><th colspan=\"2\" align=\"center\">"._FEATUREDSTORIES."</th></tr>";
	if(!dbnumrows($fresult)) $output .= "<tr><td colspan=\"2\" align=\"center\">"._NOSTORIES."</td><tr>";
	while($story = dbassoc($fresult)) {
		$output .= "<tr><td class=\"tblborder\"><a href=\"viewstory.php?sid=".$story['sid']."\">$story[title]</a> "._BY." <a href=\"viewuser.php?uid=".$story['uid']."\">".$story['penname']."</a></td><td class=\"tblborder\" align=\"center\">".($story['featured'] == 1 ? "<a href=\"admin.php?action=featured&retire=".$story['sid']."\">"._CURRENT."</a>" : "<b>"._RETIRED."</b>")."</td></tr>";
	}
	$output .= "</table>"._FEATUREDNOTE;
	return $output;
}
// end featured

function submitted()
{
	global $tableprefix, $admincats, $allowed_tags, $dbconnect;
		$output = "<center><h4>"._SUBMITTED."</h4></center>";
		$query = "SELECT story.title as storytitle, chapter.uid, chapter.sid, story.catid, chapter.chapid, chapter.inorder, chapter.title, author.penname FROM (".TABLEPREFIX."fanfiction_chapters as chapter, ".TABLEPREFIX."fanfiction_authors as author) LEFT JOIN ".TABLEPREFIX."fanfiction_stories as story ON story.sid = chapter.sid WHERE chapter.validated = '0' AND chapter.uid = author.uid ORDER BY story.title ASC, chapter.inorder ASC";
		$result = dbquery($query) or die(_FATALERROR."Query: ".$query."<br />Error: (". $dbconnect->errno.") ". $dbconnect->error);
		$output .= "<table class=\"tblborder\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\"><tr class=\"tblborder\"><th>"._TITLE."</th><th>"._AUTHOR."</th><th>"._CATEGORY."</th><th>"._OPTIONS."</th></tr>";
		$array = explode(",", $admincats);
		while ($story = dbassoc($result))
		{
			unset($catstring);
			$result3 = dbquery("SELECT catid, category FROM ".TABLEPREFIX."fanfiction_categories WHERE FIND_IN_SET(catid, '".$story['catid']."')");
			while($cats = dbassoc($result3)) {
				$catstring[] = "<a href=\"categories.php?catid=".$cats['catid']."\">".$cats['category']."</a>";
			}
			if(!$admincats || sizeof(array_intersect(explode(",", $story['catid']), explode(",", $admincats)))) {
				$output .= "<tr class=\"tblborder\">";
				$output .= "<td class=\"tblborder\"><a href=\"viewstory.php?sid=".$story['sid']."\">".$story['storytitle']."</a>";
				if($story['title']) $output .= " <b>:</b> <a href=\"viewstory.php?sid=".$story['sid']."&amp;chapter=".$story['inorder']."\">".$story['title']."</a>";
				$output .= "<td class=\"tblborder\"><a href=\"viewuser.php?uid=".$story['uid']."\">".$story['penname']."</a></td>";
				$output .= "<td class=\"tblborder\">".(is_array($catstring) ? implode(", ", $catstring) : "")."</td>";
			$output .= "<td class=\"tblborder\"><a href=\"admin.php?action=validate&amp;chapid=".$story['chapid']."\">"._VALIDATE."</a> | "._DELETE.": <a href=\"stories.php?action=delete&amp;chapid=".$story['chapid']."&amp;sid=".$story['sid']."&amp;admin=1&amp;uid=".$story['uid']."\">"._CHAPTER."</a> "._OR." <a href=\"stories.php?action=delete&amp;sid=".$story['sid']."&amp;admin=1\">"._STORY."</a> | <a href=\"javascript:pop('admin.php?action=yesletter&uid=".$story['uid']."&chapid=".$story['chapid']."', 500, 400, 'yes')\">"._YESLETTER."</a> | <a href=\"javascript:pop('admin.php?action=noletter&uid=".$story['uid']."&chapid=".$story['chapid']."', 500, 400, 'yes')\">"._NOLETTER."</a></td></tr>";
			}
		}
		$output .= "</table>";

	return $output;
}
// end submitted function

function validate( ) {
	global $tableprefix, $level, $store, $storiespath, $allowed_tags, $admincats, $sitename, $siteemail, $url, $alertson;

	$output  = "<center><h4>"._VIEWSUBMITTED."</h4></center>";
	if($_GET['validate'] == "yes") {
		$storyquery = dbquery("SELECT story.validated, story.catid, story.sid, story.title, story.summary, story.uid, author.penname, chapter.inorder FROM ".TABLEPREFIX."fanfiction_stories as story, ".TABLEPREFIX."fanfiction_chapters  as chapter, ".TABLEPREFIX."fanfiction_authors as author WHERE author.uid = story.uid AND chapter.sid = story.sid AND chapter.chapid ='".$_GET['chapid']."' LIMIT 1");
		list($validated, $catid, $sid, $title, $summary, $authoruid, $author, $inorder) = dbassoc($storyquery);
		if($admincats == "0" || sizeof(array_intersect(explode(",", $catid), explode(",", $admincats)))) {
			include("includes/emailer.php");
			if($validated != "1") {
				dbquery("UPDATE ".TABLEPREFIX. "fanfiction_stories SET validated = '1', updated = '".time()."' WHERE sid = '".$_GET['sid']."'");
				$categories = explode(",", $catid);
				include("functions.php");
				foreach($categories as $cat) {
					categoryitems($cat, 1);
				}
				if($alertson) {
					$subject = _NEWSTORYAT;
					$mailtext = sprintf(_AUTHORALERTNOTE, $title, $author, $summary, $sid);
					$favorites = dbquery("SELECT author.uid, email, penname FROM ".TABLEPREFIX."fanfiction_favauth as fav, ".TABLEPREFIX."fanfiction_authors as author WHERE fav.favuid = $authoruid AND fav.uid = author.uid");
					while($favuser = dbassoc($favorites)) { 
						sendemail($favuser['penname'], $favuser['email'], $sitename, $siteemail, $subject, $mailtext, "html");
					}				
				}
			}
			else if($alertson) {
				$subject = _STORYALERT;
				$mailtext = sprintf(_STORYALERTNOTE, $title, $author, $sid, $inorder);
				$favorites = dbquery("SELECT author.uid, penname, email FROM ".TABLEPREFIX."fanfiction_favstor as fav, ".TABLEPREFIX."fanfiction_authors as author WHERE sid = '$sid' AND fav.uid = author.uid");
				while($favuser = dbassoc($favorites)) { 
					sendemail($favuser['penname'], $favuser['email'], $sitename, $siteemail, $subject, $mailtext, "html");
				}
			}
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET validated = '1' WHERE chapid = '".$_GET['chapid']."'");
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET updated = '" . time() . "' WHERE sid = '$sid'");
			$output .= "<center><b>"._STORYVALIDATED."</b></center>";
		}
		else
			$output .= "<br /><br /><center>"._NOTAUTHORIZEDADMIN."  "._TRYAGAIN."</center><br /><br />";
	}
	else {
			$result = dbquery("SELECT story.title as storytitle, story.sid, story.catid, story.rid, story.gid, story.charid, story.wid, story.summary as storysummary, chapter.chapid, chapter.title, chapter.storytext, author.penname, chapter.uid as uid FROM ".TABLEPREFIX."fanfiction_authors as author, ".TABLEPREFIX."fanfiction_stories as story, ".TABLEPREFIX."fanfiction_chapters as chapter WHERE chapter.chapid = '".$_GET['chapid']."' AND chapter.sid = story.sid AND chapter.uid = author.uid");
			$story = dbassoc($result);
			$output .= "<b>"._AUTHOR.":</b> ".$story['penname']."<br />";
			$output .= "<b>"._TITLE.":</b> ".$story['storytitle'].": ".$story['title'];
			$output .= "<br />";
			$result3 = dbquery("SELECT catid, category FROM ".TABLEPREFIX."fanfiction_categories WHERE FIND_IN_SET(catid, '".$story['catid']."')");
			$catstring = "";
			$count = 0;
			while($cats = dbassoc($result3)) {
				if($count > 0) $catstring .= ", ";
				$catstring .= "<a href=\"categories.php?catid=".$cats['catid']."\">".$cats['category']."</a>";
				$categories[] = $cats['catid'];
				$count++;
			}
			$output .= "<b>"._CATEGORY.":</b> $catstring<br />";

			$output .= "<b>"._GENRES.":</b> ".(isset($story['gid']) ? preg_replace("/,/", ", ", $story['gid']) : _NONE);
			$output .= "<br />";
			$output .= "<b>"._RATING.":</b> ".$story['rid']."<br />";
			if($story['wid'] != "")
			{
				$output .= "<b>"._WARNINGS.":</b> ".(isset($story['wid']) ? preg_replace("/,/", ", ", $story['wid']) : _NONE);
				$output .= "<br />";
			}
			$output .= "<b>"._CHARACTERS.":</b> ".$story['charid'];
			$output .= "<br />";
			$output .= "<b>"._SUMMARY.":</b> ".$story['storysummary']."<br />";
			$output .= "<a href=\"admin.php?action=validate&amp;sid=".$story['sid']."&amp;chapid=".$story['chapid']."&amp;validate=yes\">"._VALIDATE."</a> | <a href=\"stories.php?action=editstory&amp;sid=$story[sid]&amp;admin=1\">"._EDIT." - "._STORY."</a> | <a href=\"stories.php?action=delete&amp;sid=$story[sid]\">"._DELETE." - "._STORY."</a> | <a href=\"javascript:pop('admin.php?action=yesletter&amp;uid=$story[uid]&amp;chapid=$story[chapid]', 400, 300, 'yes')\">"._YESLETTER."</a> | <a href=\"javascript:pop('admin.php?action=noletter&amp;uid=$story[uid]&amp;chapid=$story[chapid]',400, 300, 'yes')\">"._NOLETTER."</a><br />";
			if($store == "files")
			{
				$file = "$storiespath/".$story['uid']."/".$story['chapid'].".txt";
				$log_file = fopen($file, "r");
				$file_contents = fread($log_file, filesize($file));
				$storytext .= $file_contents;
				fclose($log_file);
			}
			else if($store == "mysql")
			{
				$storytext .= $story['storytext'];
			}
			if(strpos($story, "<br>") === false && strpos($story, "<p>") === false && strpos($story, "<br />") === false) $storytext = nl2br($storytext);
			$storytext = format_story($storytext, $allowed_tags);
			$output .= $storytext;
			$output .= "<br /><br />";
			$output .= "<a href=\"admin.php?action=validate&amp;sid=".$story['sid']."&amp;chapid=".$story['chapid']."&amp;validate=yes\">"._VALIDATE."</a> | <a href=\"stories.php?action=editchapter&amp;chapid=".$story['chapid']."&amp;admin=1\">"._EDIT." - "._CHAPTER."</a> | <a href=\"stories.php?action=delete&amp;chapid=".$story['chapid']."&amp;sid=".$story['sid']."&amp;admin=1&amp;uid=".$story['uid']."\">"._DELETE." - "._CHAPTER."</a> | <a href=\"javascript:pop('admin.php?action=yesletter&amp;uid=".$story['uid']."&amp;chapid=".$story['chapid']."', 400, 300, 'yes')\">"._YESLETTER."</a> | <a href=\"javascript:pop('admin.php?action=noletter&amp;uid=".$story['uid']."&amp;chapid=".$story['chapid']."',400, 300, 'yes')\">"._NOLETTER."</a><br />";
		}
	return $output;
}
// end validate

function yesletter( ) {
	global $tableprefix, $level, $adminemail, $sitename, $siteemail, $allowed_tags;
	
	if($_POST['submit']) {

		if($adminemail)
			$ademail = $adminemail;
		else 
			$ademail = $siteemail;
		$subject = stripinput($_POST['subject']);
		$letter = nl2br(stripinput($_POST['letter']));
			
		include("includes/emailer.php");
		$result = sendemail($_POST['email'], $_POST['email'], $adminname, $ademail, $subject, $letter, "html");

		if($result) echo "<div style='text-align: center;'>"._EMAILSENT."</div>";
		else echo "<div style='text-align: center;'>"._ERROR."</div>";
	}
	else {
			$authorquery = dbquery("SELECT email,penname FROM ".TABLEPREFIX."fanfiction_authors WHERE uid = '".$_GET['uid']."' LIMIT 1");
			$author = dbassoc($authorquery);
			$storyquery = dbquery("SELECT story.title, chapter.title as chapter FROM ".TABLEPREFIX."fanfiction_stories as story, ".TABLEPREFIX."fanfiction_chapters as chapter WHERE chapter.chapid = '".$_GET['chapid']."' AND chapter.sid = story.sid LIMIT 1");
			$story = dbassoc($storyquery);
			$letter = file_get_contents("messages/thankyou.txt");
			echo "<body>";
			echo "<table><tr><td>Story:</td><td>".$story['title'].": ".$story['chapter']."</td></tr>";
			echo "<tr><td>"._BY.":</td><td>".$author['penname']."</td></tr>";
			echo "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=yesletter\">
				<tr><td>To:</td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"email\" value=\"".$author['email']."\"></td></tr>
				<tr><td>From:</td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"ademail\" value=\"$adminemail\"></td></tr>
				<tr><td>Subject:</td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"subject\" value=\"Your Submission to $sitename\"></td></tr>
				<tr><td colspan=\"2\"><textarea class=\"textbox\" name=\"letter\" cols=\"50\" rows=\"8\">$letter</TEXTAREA></td></tr><tr><td colspan=\"2\"><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></form></td></tr></table></body></html>";
	}
	exit( );
}
// end yesletter

function noletter( ) {
	global $tableprefix, $level, $adminemail, $sitename, $siteemail, $allowed_tags, $adminloggedin;
	if($_POST['submit'])
		{
		if($adminemail)
			$ademail = $adminemail;
		else 
			$ademail = $siteemail;
		$subject = stripinput($_POST['subject']);
		$letter = nl2br(stripinput($_POST['letter']));
			
		include("includes/emailer.php");
		$result = sendemail($_POST['email'], $_POST['email'], $adminname, $ademail, $subject, $letter, "html");

		if($result) echo "<div style='text-align: center;'>"._EMAILSENT."</div>";
		else echo "<div style='text-align: center;'>"._ERROR."</div>";
	}
	else
	{
		$authorquery = dbquery("SELECT email,penname FROM ".TABLEPREFIX."fanfiction_authors WHERE uid = '".$_GET['uid']."' LIMIT 1");
		$author = dbassoc($authorquery);
		$storyquery = dbquery("SELECT story.title, chapter.title as chapter FROM ".TABLEPREFIX."fanfiction_stories as story, ".TABLEPREFIX."fanfiction_chapters as chapter WHERE chapter.chapid = '".$_GET['chapid']."' AND chapter.sid = story.sid LIMIT 1");
		$story = dbassoc($storyquery);
		$letter = file_get_contents("messages/nothankyou.txt");
		echo "<body>";
		echo "<table><tr><td>Story:</td><td>".$story['title'].": ".$story['chapter']."</td></tr>";
		echo "<tr><td>"._BY.":</td><td>".$author['penname']."</td></tr>";
		echo "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=noletter\">
			<tr><td>To:</td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"email\" value=\"".$author[email]."\"></td></tr>
			<tr><td>From:</td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"ademail\" value=\"$adminemail\"></td></tr>
			<tr><td>Subject:</td><td><INPUT type=\"text\" class=\"textbox=\"  name=\"subject\" value=\"Your Submission to $sitename\"></td></tr>
			<tr><td colspan=\"2\"><textarea class=\"textbox\" name=\"letter\" cols=\"50\" rows=\"8\">$letter</TEXTAREA></td></tr><tr><td colspan=\"2\"><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></form></td></tr></table></body></html>";
	}
	exit( );
}
// end noletter

?>