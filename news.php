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
if(!isset($nid) || !isNumber($nid)) $nid = false;
$cid = isset($_REQUEST['cid']) && isNumber($_REQUEST['cid']) ? $_REQUEST['cid'] : false;

if($nid) {

	$output .= "<div id=\"pagetitle\">"._NEWS."</div>";
	if(isset($_POST['submit']))
	{
		$comment = escapestring(format_story(replace_naughty(strip_tags($_POST['comment'], $allowed_tags))));
		if(!$cid && USERUID) {
			$insert = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_comments (nid, uid, comment, time) VALUES ('$nid', '".(USERUID ? USERUID : 0)."', '$comment', '" . time() . "')");
			if($insert) dbquery("UPDATE ".TABLEPREFIX."fanfiction_news SET comments = comments + 1 WHERE nid = '$nid' LIMIT 1");
		}
		else if($cid) {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_comments SET comment = '$comment' WHERE cid = '$cid'");
		}
		unset($comment);
	}
	if(isset($_GET['del']) && isADMIN && uLEVEL < 4 && !empty($cid)) {
		$insert = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_comments WHERE cid = '$cid'");
		if($insert) dbquery("UPDATE ".TABLEPREFIX."fanfiction_news SET comments = comments - 1 WHERE nid = '$nid' LIMIT 1");
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
	$news->assign("newstitle"   , stripslashes($stories['title']) );
	$news->assign("newsstory"   , format_story($stories['story']) );
	$news->assign("newsauthor", stripslashes($stories['author']) );
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
		if(isset($_GET['edit']) && isNumber($_GET['edit'])) {
			
			$select = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_comments WHERE cid = '".$_GET['edit']."' LIMIT 1");
			list($cid, $nid, $uname, $comment, $time) = dbrow($select);
			$output .= "<input type=\"hidden\" name=\"cid\" value=\"$cid\">";
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
