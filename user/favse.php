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
if(empty($favorites)) accessDenied( );

	if(empty($uid)) {
		$uid = USERUID;
		$output .= "<div id='pagetitle'>"._YOURSTATS."</div>";
	}
	$add = isset($_GET['add']) && isNumber($_GET['add']) ? $add = $_GET['add'] : false;
	$edit = isset($_GET['edit']) && isNumber($_GET['edit']) ? $_GET['edit'] : false;
	$author = isset($_GET['author']) && isNumber($_GET['author']) ? $_GET['author'] : false;
	$delete = isset($_GET['delete']) && isNumber($_GET['delete']) ? $_GET['delete'] : false;
	if(($add || $edit || $delete) && !isMEMBER) accessDenied( );

	$output .= "<div class='sectionheader'>"._FAVORITESERIES."</div>";
	if($delete) {
		$result = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND item = '$delete' AND type = 'SE' LIMIT 1");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL);
		else $output .= write_error(_ERROR);
	}
	else if($add && isset($_POST['submit'])) {
		$result = false;
		$check = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND item = '$seriesid' AND type = 'SE' LIMIT 1");
		if(dbnumrows($check) == 0)
			$result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_favorites(uid, item, type, comments) VALUES ('".USERUID."', '$seriesid', 'SE', '".escapestring(descript(strip_tags(replace_naughty($_POST['comments']), $allowed_tags)))."')");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL." "._BACK2ACCT);
	}
	else if($edit && isset($_POST['submit'])) {
		$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_favorites SET comments = '".escapestring(descript(strip_tags(replace_naughty($_POST['comments']), $allowed_tags)))."' WHERE uid = '".USERUID."' AND item = '$edit' AND type = 'SE'");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL." "._BACK2ACCT);
		else $output .= write_error(_ERROR);
	}
	else if(($add || $edit) && !isset($_POST['submit'])) {
		$stories = dbassoc(dbquery("SELECT series.*, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_series as series, "._AUTHORTABLE." WHERE series.uid = "._UIDFIELD." AND seriesid = '".($add ? $add : $edit)."' LIMIT 1"));
		$tpl->newBlock("listings");
		include("includes/seriesblock.php");
		$tpl->gotoBlock("listings");
		
		if($add) {
			$check = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND item = '$add' AND type = 'SE' LIMIT 1");
			if(dbnumrows($check)) {
				$edit = $add;
				$add = false;
			}
		}
		if($edit) {
			$query = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND type = 'SE' AND item = '$edit' LIMIT 1");
			$info = dbassoc($query);
		}			
		$tpl->assign("pagelinks", "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"user.php?action=favse&amp;add=1&amp;seriesid=".($add ? $add : $edit)."\">\n
			<div style=\"width: 350px; margin: 0 auto; text-align: left;\"><label for=\"comments\">"._COMMENTS.":</label><br />
			<textarea class=\"textbox\" name=\"comments\" id=\"comments\" cols=\"40\" rows=\"5\">".(isset($info['comments']) ? $info['comments'] : "")."</textarea><br />
			<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></div></form>");
			$tpl->gotoBlock("_ROOT");

	}
	else {
	$storyquery = "SELECT series.*, "._PENNAMEFIELD." as penname, fav.comments as comments FROM ".TABLEPREFIX."fanfiction_series as series, ".TABLEPREFIX."fanfiction_favorites as fav, "._AUTHORTABLE." WHERE fav.uid = '$uid' AND fav.type = 'SE' AND series.uid = "._UIDFIELD." AND fav.item = series.seriesid ORDER BY series.title";
	$countquery = dbquery("SELECT COUNT(item) FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '$uid' AND type = 'SE' GROUP BY uid");
	list($seriescount) = dbrow($countquery);
	if($seriescount) {
		$tpl->newBlock("listings");
		$list = dbquery($storyquery."  LIMIT $offset, $itemsperpage");
		$count = 0;
		while($stories = dbassoc($list)) { 
			include("includes/seriesblock.php"); 
			if(file_exists("./$skindir/favcomment.tpl")) $cmt = new TemplatePower( "./$skindir/favcomment.tpl" );
			else $cmt = new TemplatePower( "./default_tpls/favcomment.tpl" );
			$cmt->prepare( );
			$cmt->newBlock("comment");
			$cmt->assign("comment", $stories['comments'] ? "<div class='comments'><span class='label'>"._COMMENTS.": </span>".strip_tags($stories['comments'])."</div>" : "");
			if(USERUID == $uid) 
				$cmt->assign("commentoptions", "<div class='adminoptions'><span class='label'>"._OPTIONS.":</span> <a href=\"user.php?action=favse&amp;edit=".$stories['seriesid']."\">"._EDIT."</a> | <a href=\"user.php?action=favse&amp;delete=".$stories['seriesid']."\">"._REMOVEFAV."</a></div>");
			$cmt->assign("oddeven", ($count % 2 ? "odd" : "even"));
			$tpl->assign("comment", $cmt->getOutputContent( ));
			$count++;
		}
		if($seriescount > $itemsperpage) {
			$tpl->gotoBlock("listings");
			$tpl->assign("pagelinks", build_pagelinks(basename($_SERVER['PHP_SELF'])."?action=favse&amp;uid=$uid&amp;", $seriescount, $offset));
		}

	}
	else $output .= write_message(_NORESULTS);
	}
	$tpl->gotoBlock( "_ROOT" );
?>
