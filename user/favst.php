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
if(empty($favorites)) accessDenied( );

	if(empty($uid)) {
		$uid = USERUID;
		$output .= "<div id='pagetitle'>"._YOURSTATS."</div>";
	}
	$add = isset($_GET['add']) ? $_GET['add'] : false;
	$edit = isset($_GET['edit']) ? $_GET['edit'] : false;
	$delete = isset($_GET['delete']) ? $_GET['delete'] : false;
	if($add) $output .= "<div class='sectionheader'>"._ADDTOFAVORITES."</div>";
	else if($edit) $output .= "<div class='sectionheader'>"._EDITFAVORITES."</div>";
	else $output .= "<div class='sectionheader'>"._FAVORITESTORIES."</div>";
	if(($add || $edit || $delete) && !isMEMBER) accessDenied( );

	if($delete && isNumber($delete)) {
		$result = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND item = '$delete' AND type = 'ST' LIMIT 1");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL." "._BACK2ACCT);
		else $output .= write_error(_ERROR);
	}
	if($add && isset($_POST['submit'])) {
		$check = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND item = '$sid' AND type = 'ST' LIMIT 1");
		if(!dbnumrows($check))
			$result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_favorites(uid, item, type, comments) VALUES ('".USERUID."', '$sid', 'ST', '".escapestring(descript(strip_tags(replace_naughty($_POST['comments']), $allowed_tags)))."')");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL." "._BACK2ACCT);
	}
	if($edit && isset($_POST['submit'])) {
		$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_favorites SET comments = '".escapestring(descript(strip_tags(replace_naughty($_POST['comments']), $allowed_tags)))."' WHERE uid = '$uid' AND item = '$edit' AND type = 'ST'");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL." "._BACK2ACCT);
		else $output .= write_error(_ERROR);
	}
	if(($add || $edit) && !isset($_POST['submit'])) {
		if($add) {
			$check = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND item = '$sid' AND type = 'ST' LIMIT 1");
			if(dbnumrows($check)) {
				$edit = $sid;
				$add = false;
			}
		}
		if($edit) {
			$query = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND type = 'ST' AND item = '$edit' LIMIT 1");
			$info = dbassoc($query);
		}
		$storyinfo = dbquery(_STORYQUERY." AND sid = '".($edit? $edit : $sid)."'");
		$stories = dbassoc($storyinfo);
		if(!$stories) $output .= write_error(_ERROR);
		else {
			$tpl->newBlock("listings");
			$tpl->newBlock("storyblock");
			include("includes/storyblock.php");
			$tpl->gotoBlock("listings");
			$tpl->assign("pagelinks", "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"user.php?action=favst&amp;".($edit? "edit=$edit" : "add=1")."&amp;sid=".(isset($sid) ? $sid : $edit)."\">\n
				<div style=\"width: 350px; margin: 0 auto; text-align: left;\"><label for=\"comments\">"._COMMENTS.":</label><br />
				<textarea class=\"textbox\" name=\"comments\" id=\"comments\" cols=\"40\" rows=\"5\">".(isset($info['comments']) ? $info['comments'] : "")."</textarea><br />
				<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></div></form>");
			$tpl->gotoBlock("_ROOT");
		}
	}
	else if(!isset($_POST['submit'])) {
		$storyquery = "SELECT stories.*, "._PENNAMEFIELD." as penname, fav.comments as comments,  stories.date as date, stories.updated as updated FROM ".TABLEPREFIX."fanfiction_stories as stories, ".TABLEPREFIX."fanfiction_favorites as fav, "._AUTHORTABLE." WHERE fav.uid = '$uid' AND fav.type = 'ST' AND stories.uid = "._UIDFIELD." AND fav.item = stories.sid "._ORDERBY;
		$countquery = dbquery("SELECT COUNT(item) FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '$uid' AND type = 'ST' GROUP BY uid");
		list($storycount) = dbrow($countquery);
		if($storycount) {
			$list = dbquery($storyquery."  LIMIT $offset, $itemsperpage");
			$tpl->newBlock("listings");
			$count = 0;
			while($stories = dbassoc($list)) { 
				$tpl->newBlock("storyblock");
				include("includes/storyblock.php");
				if(!empty($stories['comments']) || USERUID == $uid || isADMIN) {
				if(file_exists("./$skindir/favcomment.tpl")) $cmt = new TemplatePower( "./$skindir/favcomment.tpl" );
				else $cmt = new TemplatePower( "./default_tpls/favcomment.tpl" );
				$cmt->prepare( );
				$cmt->newBlock("comment");
				$cmt->assign("comment", $stories['comments'] ? "<div class='comments'><span class='label'>"._COMMENTS.": </span>".strip_tags($stories['comments'])."</div>" : "");
				if(USERUID == $uid) 
				$cmt->assign("commentoptions", "<div class='adminoptions'><span class='label'>"._OPTIONS.":</span> <a href=\"user.php?action=favst&amp;edit=".$stories['sid']."\">"._EDIT."</a> | <a href=\"user.php?action=favst&amp;delete=".$stories['sid']."\">"._REMOVEFAV."</a></div>");
				$cmt->assign("oddeven", ($count % 2 ? "odd" : "even"));
				$tpl->assign("comment", $cmt->getOutputContent( ));
				$tpl->gotoBlock( "listings" );
				}
			}	
			if($storycount > $itemsperpage) {
				$tpl->gotoBlock("listings");
				$tpl->assign("pagelinks", build_pagelinks(basename($_SERVER['PHP_SELF'])."?action=favst&amp;uid=$uid&amp;", $storycount, $offset));
			}
		}
		else $output .= write_message(_NORESULTS);
		$tpl->gotoBlock( "_ROOT" );
	}
?>
