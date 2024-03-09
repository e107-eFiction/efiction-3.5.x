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

	$output .= "<div id=\"pagetitle\">"._MANAGEREVIEWS."</div>";
	if(isset($_POST['submit'])) {
		$reviewid = isset($_POST['reviewid']) && isNumber($_POST['reviewid']) ? $_POST['reviewid'] : false;
		$result = dbquery("SELECT review, reviewid, item, type, chapid, uid FROM ".TABLEPREFIX."fanfiction_reviews WHERE reviewid = '".$_POST['rid']."' LIMIT 1");
		list($review, $reviewid, $item, $type, $chapid, $uid) = dbrow($result);
		$updated = escapestring($review . "<br><br><i>"._AUTHORSRESPONSE.": " .replace_naughty(descript($_POST['response'], $allowed_tags))."</i>");
		$success = dbquery("UPDATE ".TABLEPREFIX."fanfiction_reviews SET review = '$updated', respond = '1' WHERE reviewid = '$reviewid'");
		if($uid) {
			$prefsquery = dbquery("SELECT "._UIDFIELD." as uid, "._EMAILFIELD." as email, "._PENNAMEFIELD." as penname, newrespond FROM ".TABLEPREFIX."fanfiction_authorprefs as ap LEFT JOIN "._AUTHORTABLE." ON ap.uid = "._UIDFIELD." WHERE ap.uid = "._UIDFIELD." AND "._UIDFIELD." = '$uid' LIMIT 1");
			$prefs = dbassoc($prefsquery);
			if(isset($prefs['newrespond']) && $prefs['newrespond'] == 1) {
				include("includes/emailer.php");
				sendemail($prefs['penname'], $prefs['email'], $sitename, $siteemail, _RESPONSESUBJECT, preg_replace(array("@\{penname\}@", "@\{review\}@"), array(USERPENNAME, $reviewid), _RESPONSETEXT), "html");
			}
		}
		$back = sprintf(_BACK2REVIEWS, "item=$item&amp;type=$type".(isset($chapid) ? "&amp;chapid=$chapid" : ""));
		if($success) $output .= write_message(_ACTIONSUCCESSFUL."  ".$back);
		else $output .= write_error(_ERROR);
	}
	else {
		$reviewid = isset($_GET['reviewid']) && isNumber($_GET['reviewid']) ? $_GET['reviewid'] : false;
		if(!$reviewid) accessDenied( );
		$result = dbquery("SELECT review.*, review.date as date FROM ".TABLEPREFIX."fanfiction_reviews as review LEFT JOIN ".TABLEPREFIX."fanfiction_authors as member ON member.uid = review.uid WHERE review.reviewid = '$reviewid' LIMIT 1");
		$reviews = dbassoc($result);
		if(!empty($reviews['respond'])) {
			$tpl->assign("output", write_message(_ALREADYRESPONDED));
			$tpl->printToScreen( );
			dbclose( );
			exit( );

		}
		$type = $reviews['type'];
		if($type == "SE") $query2 = "SELECT uid FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '".$reviews['item']."'";
		else if($type == "ST") {
			$query2 = "SELECT uid, coauthors FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '".$reviews['item']."' LIMIT 1";
		}
		else { 
			$codeblock = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'reviewto'");
			while($code = dbassoc($codeblock)) {
				eval($code['code_text']);
			}
		}
		$user = dbassoc(dbquery($query2));
		$array_coauthors = array();
		if(!empty($user['coauthors'])) {
			$coQuery = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '".$reviews['item']."'");
			while($c = dbassoc($coQuery)) {
				$array_coauthors[] = $c['uid'];
			}
		}
		if(USERUID == $user['uid'] || (!empty($user['coauthors']) && in_array(USERUID, $array_coauthors))) {
			if($type == "ST") {
				$storyquery = dbquery("SELECT s.title, s.uid, r.rid, r.rating, r.ratingwarning, r.warningtext, s.sid FROM ".TABLEPREFIX."fanfiction_stories as s, ".TABLEPREFIX."fanfiction_ratings as r WHERE s.sid = '".$reviews['item']."' AND s.rid = r.rating LIMIT 1");
				$story = dbassoc($storyquery);
				$authoruid = $story['uid'];
				$title = title_link($story);
			}
			else if($type == "SE") {
				$storyquery = dbquery("SELECT title, uid FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '$item' LIMIT 1");
				list($title, $authoruid) = dbrow($storyquery);
				$title = stripslashes($title);
				$title = "<a href=\"series.php?seriesid=$item\">$title</a>";
			}
			else { 
				$titlequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'revtitle'");
				while($code = dbassoc($titlequery)) {
					eval($code['code_text']);
				}
			}
			if($reviews['uid']) {
				$reviewer = "<a href=\"viewuser.php?uid=".$reviews['uid']."\">".$reviews['reviewer']."</a>";
				$member = _SIGNED;
			}
			else {
				if(USERUID == $authoruid && $revdelete && !isADMIN) $adminlink .= " [<a href=\"reviews.php?action=delete&amp;reviewid=".$reviews['reviewid']."\">"._DELETE."</a>]";
				$reviewer = $reviews['reviewer'];
				$member = _ANONYMOUS;
			}
			if(file_exists("$skindir/reviewblock.tpl")) $revlist = new TemplatePower( "$skindir/reviewblock.tpl" );
			else $revlist = new TemplatePower("default_tpls/reviewblock.tpl");
			$revlist->prepare( );
			$revlist->newBlock("reviewsblock");
			$revlist->assign("reviewer"   , $reviewer );
			$revlist->assign("review"   , $reviews['review']);
			$revlist->assign("chapter", (isset($chaptitle) ? _CHAPTER." $chapnum: $chaptitle" : ($story['title'] ? $story['title'] : _NONE)) );
			$revlist->assign("reviewdate", date("$dateformat", $reviews['date']) );
			$revlist->assign("rating", ratingpics($reviews['rating']) );
			$revlist->assign("member", $member );
			$revlist->assign("oddeven", "odd");
			$output .= $revlist->getOutputContent( );
			$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"user.php?action=revres\">";
			$output .= "<INPUT type=\"hidden\" name=\"rid\" value=\"$reviewid\">
				<div style=\"text-align: center;\"><label for=\"response\">"._AUTHORSRESPONSE.":</label><span style='clear: left;'>&nbsp;</span></div>
				<div class='shorttextarea'><textarea class=\"textbox\" name=\"response\" id=\"response\" style='width: 100%;' rows=\"5\"></TEXTAREA></div>";
			if($tinyMCE) 
				$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('response');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
			$output .= "<div style=\"text-align: center;\"><INPUT type=\"submit\" class=\"button\" name=\"submit\" id=\"submit\" value=\""._SUBMIT."\"></div></form>";
		}
		else accessDenied( );
	}
?>