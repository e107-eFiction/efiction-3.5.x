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

//Begin basic page setup

$current = "reviews";
include ("header.php");

//make a new TemplatePower object
if(file_exists("$skindir/reviews.tpl")) $tpl = new TemplatePower( "$skindir/reviews.tpl" );
else $tpl = new TemplatePower("default_tpls/reviews.tpl");
if(file_exists("$skindir/reviewblock.tpl")) $tpl->assignInclude("reviewsblock", "$skindir/reviewblock.tpl" );
else $tpl->assignInclude("reviewsblock", "default_tpls/reviewblock.tpl");
$tpl->assignInclude( "header", "./$skindir/header.tpl" );
$tpl->assignInclude( "footer", "./$skindir/footer.tpl" );

//let TemplatePower do its thing, parsing etc.
$tpl->prepare();

include("includes/pagesetup.php");

$reviewid = isset($_REQUEST['reviewid']) ? $_REQUEST['reviewid'] : false;
if(!isNumber($reviewid)) unset($reviewid);
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : false;
$item = isset($_REQUEST['item']) ? $_REQUEST['item'] : false;
if(!isNumber($item)) unset($item);

if($action == "delete") {
	if(isADMIN && uLEVEL <= 3) $notauthor = 1;
	$query = dbquery("SELECT item, chapid, type, review, rating, uid FROM ".TABLEPREFIX."fanfiction_reviews WHERE reviewid = '$reviewid' LIMIT 1");
	list($item, $chapid, $type, $review, $rating, $reviewuid) = dbrow($query);
	if($reviewuid == USERUID) $notauthor = 1;
	if($type == "SE") $query2 = "SELECT uid FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '$item' LIMIT 1";
	if($type == "ST") $query2 = "SELECT uid FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$item' LIMIT 1";
	else {
		$revauthor = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'revauthor'");
		while($code = dbassoc($revauthor)) {
			eval($code['code_text']);
		}
	}
	$authorquery = dbquery($query2);
	list($author) = dbrow($authorquery);
	if($author != USERUID && !$notauthor && ($revdelete < 2 || ($revdelete == 1 && $reviewuid))) accessDenied( );
	$delete = isset($_GET['delete']) ? $_GET['delete'] : false;
	$output .= "<div id=\"pagetitle\">"._DELETEREVIEW."</div>";
	if($delete == "yes") {
		if($type == "ST") {
			$query = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = $item LIMIT 1");
			
			list($author) = dbrow($query);
			if($review != "No Review") {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET reviews = (reviews - 1) WHERE sid = '$item'");
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET reviews = (reviews - 1) WHERE chapid = '$chapid'");
			}
			$count =  dbquery("SELECT AVG(rating) as totalcount FROM ".TABLEPREFIX."fanfiction_reviews WHERE item = '$item' AND type='ST' AND rating != '-1'");
			list($totalcount) = dbrow($count);
			if($totalcount) dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET rating = '".round($totalcount)."' WHERE sid = '$item'");
			$count2 =  dbquery("SELECT AVG(rating) as totalcount FROM ".TABLEPREFIX."fanfiction_reviews WHERE chapid = '$chapid' AND rating != '-1'");
			list($totalcount) = dbrow($count2);
			if($totalcount) dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET rating = '".round($totalcount)."' WHERE chapid = '$chapid'");
			$series = dbquery("SELECT seriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE sid = '$item'");
			if(dbnumrows($series)) {
				while($thisseries = dbassoc($series)) {
					seriesreview($thisseries);
				}
			}
		}
		if($type == "SE") {
			if($review != "No Review") dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET reviews = (reviews - 1) WHERE seriesid = '$item'");
			seriesreview($item);
		}
		else {
			$revdelete = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'revdelete'");
			while($code = dbassoc($revdelete)) {
				eval($code['code_text']);
			}
		}
		if($review != "No Review") {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET reviews = reviews - 1");
			list($count) = dbrow(dbquery("SELECT COUNT(DISTINCT uid) FROM ".TABLEPREFIX."fanfiction_reviews WHERE review != 'No Review' AND uid != 0"));
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET reviewers = '$count'");
		}
		dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_reviews WHERE reviewid = '$reviewid'");
		
		$output .= write_message(_ACTIONSUCCESSFUL.(isset($back) ? $back : ""));
	}
	else if ($delete == "no") {
		$output .= write_message(_ACTIONCANCELLED);
	}
	else {
		$output .= write_message(_CONFIRMDELETE."<BR><BR>
			[ <a href=\"reviews.php?action=delete&amp;delete=yes&amp;reviewid=$reviewid\">"._YES."</a> | <a href=\"reviews.php?action=delete&amp;delete=no&amp;reviewid=$reviewid\">"._NO."</a> ]");
	}
}
else if($action == "edit" || $action == "add") {
	if(isset($_GET['next']) && isNumber($_GET['next'])) $nextchapter = $_GET['next'];
	if(!isMEMBER && !$anonreviews) accessDenied( );
	if(!isset($chapid) && $type == "ST") { 
		$chapquery = dbquery("SELECT chapid FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '$item' AND inorder = '1' LIMIT 1");
		list($chapid) = dbrow($chapquery);
	}
	$tpl->assign("pagetitle", "<div id =\"pagetitle\">"._REVIEW."</div>");
	if(isset($_POST['submit'])) {
		$reviewer = isset($_POST['reviewer']) ? escapestring(descript(strip_tags($_POST['reviewer'], $allowed_tags))) : "";
		$review = format_story(strip_tags(descript($_POST['review']), $allowed_tags));
		$review = escapestring($review);
		$rating = isset($_POST['rating']) && isNumber($_POST['rating']) ? $_POST['rating'] : -1;
		if(!$review) $review = "No Review";
		if(!$reviewer && $action != "edit") $reviewer = _ANONYMOUS;
		if(isMEMBER) $reviewer = USERPENNAME;
		if(!$rateonly && $review == "No Review") $output .= write_error(_MISSINGINFO2);
		else if($captcha && !isMEMBER && !captcha_confirm()) $output .= write_error(_CAPTCHAFAIL);
		else if($review == "No Review" && $_POST['rating'] == "-1") $output .= write_error(_MISSINGINFO);
		else if(find_naughty($reviewer)) $output .= write_error(_NAUGHTYWORDS);
		else if($action == "add" && (!$type || !$item)) $output .= write_error(_ERROR);
		else {
			$output .= write_message(_REVTHANKYOU);
			if($action == "edit") {
				$update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_reviews SET review = '$review', rating = '$rating' WHERE reviewid = '$reviewid' LIMIT 1");
				$info = dbquery("SELECT item, type, chapid FROM ".TABLEPREFIX."fanfiction_reviews WHERE reviewid = '$reviewid' LIMIT 1");
				list($item, $type, $chapid) = dbrow($info);
			}
			else if($type == "ST") {
				if(isset($_POST['chapid']) && isNumber($_POST['chapid'])) $chapid = $_POST['chapid'];
				dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_reviews (item, type, reviewer, review, rating, date, uid, chapid) VALUES ('$item', 'ST', '$reviewer', '$review', '$rating', now(), '".(USERUID && isNumber(USERUID) ? USERUID : 0)."', '$chapid')");
				$count =  dbquery("SELECT AVG(rating) as totalcount FROM ".TABLEPREFIX."fanfiction_reviews WHERE item = '$item' AND type = 'ST' AND rating != '-1'");
				list($totalcount) = dbrow($count);
				if($totalcount) $update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET rating = '".round($totalcount)."' WHERE sid = '$item'");
				unset($totalcount);
				$count2 = dbquery("SELECT AVG(rating) as totalcount FROM ".TABLEPREFIX."fanfiction_reviews WHERE chapid = '$chapid' AND rating != '-1'");
				list($totalcount) = dbrow($count2);
				if($totalcount) $update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET rating = '".round($totalcount)."' WHERE chapid = '$chapid'");
				if($review != "No Review") {
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET reviews = (reviews + 1) WHERE sid = '$item'");
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET reviews = (reviews + 1) WHERE chapid = '$chapid'");
				}
				$series = dbquery("SELECT seriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE sid = '$item'");
				if(dbnumrows($series)) {
					while($s = dbassoc($series)) {
						seriesreview($s['seriesid']);
					}
				}
				$output .= write_message("<a href=\"viewstory.php?sid=$item".(isset($_GET['next']) ? "&amp;chapter=".$_GET['next'] : "")."\">"._BACKTOSTORY."</a></center>");

				$uidquery = dbquery("SELECT uid,title, coauthors FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$item'");
				list($author, $title, $coauthors) = dbrow($uidquery);
				if($coauthors) {
					$array_coauthors = array( );
					$coQuery = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '$item'");
					while($c = dbassoc($coQuery)) {
						$array_coauthors[] = $c['uid'];
					}
				}
			}
			else if($type == "SE"){
				dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_reviews (item, type, reviewer, review, rating, date, uid) VALUES ('$item', 'SE', '$reviewer', '$review', '$rating', now(), '".(USERUID && isNumber(USERUID) ? USERUID : 0)."')");
				$thisseries = $item;
				seriesreview($item);
				$uidquery = dbquery("SELECT uid as uid,title FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '$item'");
				list($author, $title) = dbrow($uidquery);

			}
			else {
				$revadd = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'revadd'");
				while($code = dbassoc($revadd)) {
					eval($code['code_text']);
				}
			}
			if($review != "No Review" && $action == "add") {
				$mailquery= dbquery("SELECT ap.newreviews,"._EMAILFIELD." as email, "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs as ap ON ap.uid = "._UIDFIELD." WHERE "._UIDFIELD." = '$author' ".(!empty($coauthors) ? " OR ".findclause(_UIDFIELD, $array_coauthors) : ""));
				while($mail = dbassoc($mailquery)) {
					if($mail['newreviews']) {
						include_once("includes/emailer.php");
						$subject = _REVEMAIL1.$title;
						$mailtext = sprintf(_REVEMAIL2, "type=$type&amp;item=$item");
						$result = sendemail($mail['penname'], $mail['email'], $sitename, $siteemail, $subject, $mailtext, "html");
					}
				}
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET reviews = reviews + 1");
				list($count) = dbrow(dbquery("SELECT COUNT(DISTINCT uid) FROM ".TABLEPREFIX."fanfiction_reviews WHERE review != 'No Review' AND uid != 0"));
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET reviewers = '$count'");
			}
			if($logging && !USERUID) {
				if($type == "ST") {
					$storyquery = dbquery("SELECT s.title, s.uid, s.rid, s.sid FROM ".TABLEPREFIX."fanfiction_stories as s WHERE s.sid = '$item' LIMIT 1");
					$story = dbassoc($storyquery);
					$title = "<a href=\"viewstory.php?sid=$item\">".stripslashes($story['title'])."</a>";
					$authoruid = $story['uid'];
				}
				else if($type == "SE") {
					$storyquery = dbquery("SELECT title, uid FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '$item' LIMIT 1");
					list($title, $authoruid) = dbrow($storyquery);
					$titletext = $title;
					$title = "<a href=\"viewseries.php?seriesid=$item\">".stripslashes($title)."</a>";	
				}
				else { 
					$titlequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'revtitle'");
					while($code = dbassoc($titlequery)) {
						eval($code['code_text']);
					}
				}
				$reviewname = $reviewer."(".$_SERVER['REMOTE_ADDR'].")";
				if($action == "add") dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_REVIEW, $reviewname, truncate_text($review), $title)). "', '0', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'RE', " . time() . ")");
				else dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_log (`log_action`, `log_uid`, `log_ip`, `log_type`, `log_timestamp`) VALUES('".escapestring(sprintf(_LOG_EDIT_REVIEW, USERPENNAME, USERUID, $title, $reviewid)). "', '0', INET6_ATON('".$_SERVER['REMOTE_ADDR']."'), 'RE', " . time() . ")");
			}
		}
	}
	else {
		if(!$anonreviews && !USERPENNAME) accessDenied( );
		else {
			if($action == "edit") {
				$result = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_reviews WHERE reviewid = '$reviewid' LIMIT 1");
				$review = dbassoc($result);
			}
			include("includes/reviewform.php");
			$output .= $form;
		}
	}
}
else {
	$query = "";
	if(empty($reviewid) && empty($type)) accessDenied( );
	if(!empty($reviewid) && empty($type)) {
		list($type, $item) = dbrow(dbquery("SELECT type, item FROM ".TABLEPREFIX."fanfiction_reviews WHERE reviewid = '$reviewid' LIMIT 1"));
	}
	if($type == "ST") {
		$storyquery = dbquery("SELECT s.title, s.uid, s.rid, s.sid, s.coauthors FROM ".TABLEPREFIX."fanfiction_stories as s WHERE s.sid = '$item' LIMIT 1");
		$story = dbassoc($storyquery);
		$title = title_link($story);
		$authoruid = $story['uid'];
		$array_coauthors = array();
		if(!empty($story['coauthors'])) {
			$colist = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '$item'");
			while($c = dbassoc($colist)) {
				$array_coauthors[] = $c['uid'];
			}
		}
	}
	else if($type == "SE") {
		$storyquery = dbquery("SELECT title, uid FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '$item' LIMIT 1");
		list($title, $authoruid) = dbrow($storyquery);
		$titletext = $title;
		$title = "<a href=\"series.php?seriesid=$item\">".stripslashes($title)."</a>";
	}
	else { 
		$titlequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'revtitle'");
		while($code = dbassoc($titlequery)) {
			eval($code['code_text']);
		}
	}
	$tpl->assign("pagetitle", "<div id=\"pagetitle\">"._REVIEWSFOR." $title</div>");
	if($type == "SE") {
		$jumpmenu = "";
		$stinseries = dbquery("SELECT sid, subseriesid, inorder FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '$item'");
		$scount = dbnumrows($stinseries);
		if($scount) {
			$subs = array( );
			$stories = array( );
			while($i = dbassoc($stinseries)) {
				if($i['subseriesid']) {
					$subs[$i['inorder']] = $i['subseriesid'];
					$serieslist[] = $i['subseriesid'];
				}
				else {
					$stories[$i['inorder']] = $i['sid'];
					$storieslist[] = $i['sid'];
				}
			}
			if(count($subs) > 0) {
				$subsquery = dbquery(_SERIESQUERY." AND FIND_IN_SET(seriesid, '".implode(",", $subs)."') > 0");
				while($sub = dbassoc($subsquery)) {
					$inlist[array_search($sub['seriesid'], $subs)] = $sub;
				}
			}
			if(count($stories)) {
				$seriesstoryquery = dbquery(_STORYQUERY." AND FIND_IN_SET(sid, '".implode(",", $stories)."') > 0");
				while($story = dbassoc($seriesstoryquery)) {
					$inlist[array_search($story['sid'], $stories)] = $story;
				}
			}
			if(is_array($inlist)) ksort($inlist);
			$jumpmenu = "<form name=\"jump\" action=\"\">";
			$jumpmenu .= "<select name=\"sid\" onChange=\"if (this.selectedIndex >0) window.location=this.options[this.selectedIndex].value\">";
			$jumpmenu .= "<option value=\"reviews.php?type=SE&amp;item=$item\">"._VIEWALLREVIEWS."</option>";
			foreach($inlist as $x => $st) {
				$jumpmenu .= "<option value=\"reviews.php?type=".(isset($st['sid']) ? "ST&amp;item=".$st['sid'] : "SE&amp;item=".$st['seriesid'])."\">"._REVIEWSFOR." ".$st['title']."</option>";
				if(!empty($st['sid'])) $storylist[] = $st['sid'];
				else if(!empty($st['subseriesid'])) $serieslist[] = $st['subseriesid'];
			}
			$jumpmenu .= "</select></form>";
		}
		$tpl->assign("jumpmenu", $jumpmenu );
		$query = "SELECT review.reviewid, review.respond, review.review, review.uid, review.reviewer, review.rating, review.date as date, series.title as seriestitle, stories.title as title FROM ".TABLEPREFIX."fanfiction_reviews as review LEFT JOIN ".TABLEPREFIX."fanfiction_series as series ON review.item = series.seriesid AND review.type = 'SE' LEFT JOIN ".TABLEPREFIX."fanfiction_stories as stories ON stories.sid = review.item AND review.type ='ST' WHERE 
			((series.seriesid = '$item' AND series.seriesid = review.item AND review.type = 'SE')".
			(isset($storylist) ? " OR (FIND_IN_SET(review.item, '".(implode(",", $storieslist))."') > 0 AND review.type = 'ST')" : "").
			(isset($serieslist) ? " OR (FIND_IN_SET(item, '".(implode(",", $serieslist))."') > 0 AND type = 'SE')" : "").
			") AND review.review != 'No Review'";
		$count =  "SELECT count(reviewid) FROM ".TABLEPREFIX."fanfiction_reviews WHERE item = '$item' AND type = 'SE' AND review != 'No Review'";

	}
	else if($type == "ST") {
		if(isset($chapid))  {
			$query = "SELECT review.reviewid, review.respond, review.review, review.uid, review.reviewer, review.rating, date as date, chapter.title as title, chapter.inorder as inorder FROM ".TABLEPREFIX."fanfiction_reviews as review, ".TABLEPREFIX."fanfiction_chapters as chapter WHERE chapter.chapid = '$chapid' AND chapter.chapid = review.chapid AND review.review != 'No Review' ";
			$count =  "SELECT count(reviewid) FROM ".TABLEPREFIX."fanfiction_reviews as review WHERE chapid = '$chapid' AND review != 'No Review'";
		}
		else  {
			$query = "SELECT review.reviewid, review.respond, review.review, review.uid, review.reviewer, review.rating, review.date as date, chapter.title as title, chapter.inorder as inorder FROM ".TABLEPREFIX."fanfiction_reviews as review, ".TABLEPREFIX."fanfiction_chapters as chapter WHERE chapter.sid = '$item' AND chapter.chapid = review.chapid AND review.review != 'No Review' AND review.type = 'ST'";
			$count = "SELECT count(reviewid) FROM ".TABLEPREFIX."fanfiction_reviews as review WHERE item = '$item' AND review != 'No Review' AND type = 'ST'";
		}
		$jumpmenu = "<form name=\"jump\" action=\"\">";
		$jumpmenu .= "<select name=\"sid\" onChange=\"window.location=this.options[this.selectedIndex].value\">";
		$jumpmenu .= "<option value=\"reviews.php?type=ST&amp;item=".$story['sid'].(isset($_GET['unresponded']) ? "&amp;unresponded=1" : "")."\"";
		if(!isset($chapid))
			$jumpmenu .= " selected";
		$jumpmenu .= ">"._VIEWALLREVIEWS."</option>";
		$chapquery = dbquery("SELECT inorder, title, chapid, sid FROM ".TABLEPREFIX."fanfiction_chapters WHERE sid = '".$story['sid']."' ORDER BY inorder ASC");
		while($chapters = dbassoc($chapquery)) {
			$jumpmenu .= "<option value=\"reviews.php?chapid=".$chapters['chapid'].(isset($_GET['unresponded']) ? "&amp;unresponded=1" : "")."&amp;type=ST&amp;item=$item\"";
	
			if(isset($chapid) && $chapid == $chapters['chapid'])
				$jumpmenu .= " selected";

			$jumpmenu .= ">"._REVIEWSFOR." ".$chapters['inorder'].". ".$chapters['title']."</option>\n";
		}
		$jumpmenu .= "</select></form>";

		$tpl->assign("jumpmenu", $jumpmenu );
	}
	else {
		$reviewquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'revqueries'");
		while($code = dbassoc($reviewquery)) {
			eval($code['code_text']);
		}
	}
	if(isset($_GET['unresponded'])) {
		$query .= "AND review.respond = '0' ";
		$count .= "AND review.respond = '0' ";
	}
	if(!empty($reviewid)) {
		$query .= " AND review.reviewid = '$reviewid' ";
		$count .= " AND review.reviewid = '$reviewid' ";
	}
	$query .= "ORDER BY review.reviewid DESC LIMIT $offset,$itemsperpage";
	$query = dbquery($query);
	$count = dbquery($count);

	list($numrows)= dbrow($count);
	$counter = 0;
	while($reviews = dbassoc($query))
	{
		$adminlink = "";
		if(isADMIN && uLEVEL < 4) $adminlink = "<span class='label'>"._ADMINOPTIONS.": </span> [<a href=\"reviews.php?action=edit&amp;reviewid=".$reviews['reviewid']."\">"._EDIT."</a>]";
		if( (isADMIN && uLEVEL < 4) || (USERUID && USERUID == $reviews['uid'])) $adminlink .= " [<a href=\"reviews.php?action=delete&amp;reviewid=".$reviews['reviewid']."\">"._DELETE."</a>]";
		if($reviews['uid']) {
			if(USERUID == $authoruid && $revdelete == 2 && !isADMIN) $adminlink .= " [<a href=\"reviews.php?action=delete&amp;reviewid=".$reviews['reviewid']."\">"._DELETE."</a>]";
			$reviewer = "<a href=\"viewuser.php?uid=".$reviews['uid']."\">".$reviews['reviewer']."</a>";
			$member = _SIGNED;
		}
		else {
			if(USERUID == $authoruid && $revdelete && !isADMIN) $adminlink .= " [<a href=\"reviews.php?action=delete&amp;reviewid=".$reviews['reviewid']."\">"._DELETE."</a>]";
			$reviewer = $reviews['reviewer'];
			$member = _ANONYMOUS;
		}
		if(empty($reviews['respond']) && (USERUID == $authoruid || (isset($array_coauthors) && in_array(USERUID, $array_coauthors)))) $adminlink .= " [<a href=\"user.php?action=revres&amp;reviewid=".$reviews['reviewid']."\">"._RESPOND."</a>]";
		$tpl->newBlock("reviewsblock");
		$tpl->assign("reviewer"   , $reviewer );
		$tpl->assign("reportthis", "[<a href=\""._BASEDIR."contact.php?action=report&amp;url=reviews.php?reviewid=".$reviews['reviewid']."\">"._REPORTTHIS."</a>]");
		$tpl->assign("review"   , stripslashes($reviews['review']));
		$tpl->assign("reviewdate", date("$dateformat $timeformat", $reviews['date']) );
		$tpl->assign("rating", ratingpics($reviews['rating']) );
		$tpl->assign("member", $member );
		$tpl->assign("chapter", (!empty($reviews['title']) ? stripslashes($reviews['title']) : (!empty($reviews['seriestitle']) ? stripslashes($reviews['seriestitle']) : _NONE)) );
		if(isset($reviews['inorder'])) $tpl->assign("chapternumber", $reviews['inorder'] );
		if(!empty($adminlink)) $tpl->assign("adminoptions", "<div class=\"adminoptions\">$adminlink</div>");
		$tpl->assign("oddeven", ($counter % 2 ? "odd" : "even"));
		$counter++;
	}
	$tpl->gotoBlock( "_ROOT" );

	if ($numrows > $itemsperpage) 
		$tpl->assign("reviewpagelinks", build_pagelinks("reviews.php?type=$type&amp;item=$item&amp;".(!empty($chapid) ? "chapid=$chapid&amp;" : "").(isset($_GET['unresponded']) ? "unresponded=1&amp;" : ""), $numrows, $offset));
	else if($numrows == 0) $tpl->assign("reviewpagelinks", write_message(_NORESULTS));
	if(isMEMBER || $anonreviews)
		$reviewslink = "<a href=\"reviews.php?action=add&amp;type=$type&amp;item=$item".(isset($chapid) ? "&amp;chapid=".$chapid : "")."\">"._SUBMITREVIEW."</a>";
	else
		$reviewslink = write_message(sprintf(_LOGINTOREVIEW, strtolower($pagelinks['login']['link']), strtolower($pagelinks['register']['link'])));
	$tpl->assign("reviewslink", $reviewslink);
	$form = "";
	if($reviewsallowed) {
		if(isMEMBER || $anonreviews) {
			$item = $item;
			$type = $type;
			include("includes/reviewform.php");
		}
		else $form = write_message(sprintf(_LOGINTOREVIEW, strtolower($pagelinks['login']['link']), strtolower($pagelinks['register']['link'])));
	}
	$tpl->assign("reviewform", $form);

}
$tpl->assign("output", $output);
$tpl->printToScreen( );
?>
