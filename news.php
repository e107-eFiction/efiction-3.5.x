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

$current = "news";

include ("header.php");

if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
else $tpl = new TemplatePower("default_tpls/default.tpl");

include("includes/pagesetup.php");


if(isset($_REQUEST['nid'])) $nid = $_REQUEST['nid'];
if(!isset($nid) || !is_string($nid) || !isNumber($nid)) $nid = false; else $nid = (int)$nid;
$cid = isset($_REQUEST['cid']) && is_string($_REQUEST['cid']) && isNumber($_REQUEST['cid']) ? (int)$_REQUEST['cid'] : false;

if($nid) {

	$output .= "<div id=\"pagetitle\">"._NEWS."</div>";
	// Comments: members may post when comments are on; only staff (the ones shown the Edit link) may change one.
	// (Before 3.5.77 anyone, even signed out, could rewrite any comment by sending its number.)
	if(isset($_POST['submit']) && isset($_POST['comment']) && is_string($_POST['comment']))
	{
		$comment = escapestring(format_story(replace_naughty(strip_tags(descript($_POST['comment']), $allowed_tags))));
		$ef_news_ok = (bool)dbrow(dbquery("SELECT nid FROM ".TABLEPREFIX."fanfiction_news WHERE nid = '$nid' LIMIT 1"));
		if(!$cid && USERUID && $newscomments && $ef_news_ok && trim($comment) !== "") {
			$insert = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_comments (nid, uid, comment, time) VALUES ('$nid', '".(int)USERUID."', '$comment', '" . time() . "')");
			if($insert) dbquery("UPDATE ".TABLEPREFIX."fanfiction_news SET comments = comments + 1 WHERE nid = '$nid' LIMIT 1");
		}
		else if($cid && isADMIN && uLEVEL < 4) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_comments SET comment = '$comment' WHERE cid = '$cid' AND nid = '$nid'");
		}
		else accessDenied( );
		unset($comment);
	}
	if(isset($_GET['del']) && isADMIN && uLEVEL < 4 && !empty($cid)) {
		dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_comments WHERE cid = '$cid' AND nid = '$nid'");
		if($GLOBALS['dbconnect']->affected_rows > 0) dbquery("UPDATE ".TABLEPREFIX."fanfiction_news SET comments = GREATEST(comments - 1, 0) WHERE nid = '$nid' LIMIT 1");
	}
	$newsquery = dbquery("SELECT n.*, n.time as date FROM ".TABLEPREFIX."fanfiction_news as n WHERE n.nid = '$nid' LIMIT 1");
	$stories = dbassoc($newsquery);
	if(file_exists("./$skindir/newsbox.tpl"))
		$news = new TemplatePower( "./$skindir/newsbox.tpl" );
	else $news = new TemplatePower( "./default_tpls/newsbox.tpl" );
	$news->prepare( );	
	//create a new number_row block
	$news->newBlock("newsbox");

	//assign values
	$news->assign("newstitle"   , stripslashes($stories['title'] ?? '') );
	$news->assign("newsstory"   , format_story($stories['story']) );
	$news->assign("newsauthor", stripslashes($stories['author'] ?? '') );
	$news->assign("newsid", $stories['nid']);
	$news->assign("oddeven", "odd");
	$news->assign("skindir", $skindir);
	$news->assign("newsdate", date("$dateformat $timeformat", $stories['date']) );
	if($newscomments)
		$news->assign("newscomments", "<a href=\"news.php?action=newsstory&amp;nid=".$stories['nid']."\">".$stories['comments']." "._COMMENTS."</a>");
	if(isADMIN && uLEVEL < 4) 
		$news->assign("adminoptions", "<a href=\"admin.php?action=news&amp;form=".$stories['nid']."\">"._EDIT."</a> | <a href=\"admin.php?action=news&amp;delete=".$stories['nid']."\">"._DELETE."</a>");
	$output .= $news->getOutputContent( );

	$cquery = dbquery("SELECT COUNT(cid) FROM ".TABLEPREFIX."fanfiction_comments WHERE nid = '$nid'");
	list($ccount) = dbrow($cquery);
	if($ccount) {
		$query2 = dbquery("SELECT c.*, "._PENNAMEFIELD." as penname, c.time as date FROM ".TABLEPREFIX."fanfiction_comments as c LEFT JOIN "._AUTHORTABLE." ON c.uid = "._UIDFIELD." WHERE c.nid = '$nid' ORDER BY time LIMIT $offset, $itemsperpage");
		$output .= "<div class=\"sectionheader\">"._COMMENTS."</div>";
		if(file_exists("$skindir/comments.tpl")) $c = new TemplatePower( "$skindir/comments.tpl" );
		else $c = new TemplatePower( "default_tpls/comments.tpl" );
		$c->prepare( );
		$count = 0;
		while($comments = dbassoc($query2)) {
			$c->newBlock("commentbox");
			$c->assign("comment", format_story($comments['comment']));
			$c->assign("uname", $comments['penname']);
			$c->assign("date", date("$dateformat $timeformat", $comments['date']));
			if(isADMIN && uLEVEL < 4)
				$c->assign("adminoptions", "<div class='adminoptions'><span class='label'>"._ADMINOPTIONS.":</span> [<a href=\"news.php?action=newsstory&amp;edit=".$comments['cid']."&amp;nid=$nid\">"._EDIT."</a>] [<a href=\"news.php?action=newsstory&amp;cid=".$comments['cid']."&amp;del=1&amp;nid=$nid\">"._DELETE."</a>]</div>");
			$c->assign("oddeven", ($count % 2 ? "odd" : "even"));
			$count++;
		}
		$output .= $c->getOutputContent( );
		if($ccount > $itemsperpage) $output .= build_pagelinks("news.php?nid=$nid&amp;", $ccount, $offset);
	}
	if(isMEMBER) {
		$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"news.php?action=newsstory&amp;nid=$nid\">";
		$comment = "";
		if(isset($_GET['edit']) && is_string($_GET['edit']) && isNumber($_GET['edit']) && isADMIN && uLEVEL < 4) {
			$select = dbquery("SELECT cid, comment FROM ".TABLEPREFIX."fanfiction_comments WHERE cid = '".(int)$_GET['edit']."' AND nid = '$nid' LIMIT 1");
			if($ef_c = dbassoc($select)) {
				$comment = htmlspecialchars($ef_c['comment'], ENT_QUOTES);
				$output .= "<input type=\"hidden\" name=\"cid\" value=\"".(int)$ef_c['cid']."\">";
			}
		}
		else $comment = "";
		$output .= "<table align=\"center\"><tr><td><strong>"._PENNAME.":</strong></td><td>".USERPENNAME."<INPUT type=\"hidden\" name=\"uname\" value=\"".USERPENNAME."\"></td></tr>
		<tr><td><b>"._COMMENTS.":</b></td><td><TEXTAREA name=\"comment\" cols=\"35\" rows=\"6\">$comment</TEXTAREA></td></tr>
		<tr><td><INPUT type=\"hidden\" name=\"nid\" value=\"$nid\"><INPUT name=\"submit\" id=\"submit\" type=\"submit\" value=\""._SUBMIT."\"></td></tr></table></form>";
	}
	$tpl->assign("output", $output);
}
else {
	$output .= "<div id=\"pagetitle\">"._NEWS."</div>";
	if(file_exists("./$skindir/newsbox.tpl"))
		$news = new TemplatePower( "./$skindir/newsbox.tpl" );
	else $news = new TemplatePower( "./default_tpls/newsbox.tpl" );
	$news->prepare( );
	$cquery = dbquery("SELECT count(nid) FROM ".TABLEPREFIX."fanfiction_news");
	list($count) = dbrow($cquery);
	$newsquery = dbquery("SELECT n.*, n.time as date FROM ".TABLEPREFIX."fanfiction_news as n ORDER BY n.time DESC LIMIT $offset, $itemsperpage");
	$counter = 0;
	while($stories = dbassoc($newsquery)) {

		//create a new number_row block
		$news->newBlock("newsbox");
		
		//assign values
		$news->assign("newstitle"   , $stories['title']);
		$news->assign("newsstory"   , nl2br($stories['story']) );
		$news->assign("newsauthor", $stories['author']);
		$news->assign("newsdate", date("$dateformat $timeformat", $stories['date']) );
		$news->assign("newsid", $stories['nid']);
		$news->assign("skindir", $skindir);
		if($newscomments)
			$news->assign("newscomments", "<a href=\"news.php?action=newsstory&amp;nid=".$stories['nid']."\">".$stories['comments']." "._COMMENTS."</a>");
		if(isADMIN && uLEVEL < 4) 
			$news->assign("adminoptions", "<a href=\"admin.php?action=news&amp;form=".$stories['nid']."\">"._EDIT."</a> | <a href=\"admin.php?action=news&amp;delete=".$stories['nid']."\">"._DELETE."</a>");
		$news->assign("oddeven", ($counter % 2 ? "even" : "odd"));
		$counter++;
	}
	$output .= $news->getOutputContent( );
	if ($count > $itemsperpage) $output .= build_pagelinks("news.php?", $count, $offset);
	$tpl->assign("output", $output);
}

$tpl->printToScreen();
dbclose( );
?>
