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
	$output .= "<div class='sectionheader'>"._FAVORITEAUTHORS."</div>";
	
	$add = !empty($_GET['add']) ? $_GET['add'] : "";
	$delete = isset($_GET['delete']) && isNumber($_GET['delete']) ? $_GET['delete'] : false;
	$edit = isset($_GET['edit']) && isNumber($_GET['edit']) ? $_GET['edit'] : false;
	$author = explode(",", $add);
	$author = array_filter($author, "isNumber");
	$array_pennames = array();
	$pennames = '';
	if(($add || $edit || $delete) && !isMEMBER) accessDenied( );

	if(isMEMBER && $uid == USERUID) {
		if($delete) {
			$result = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND item = '$delete' AND type = 'AU' LIMIT 1");
			if($result) $output .= write_message(_ACTIONSUCCESSFUL." "._BACK2ACCT);
			else $output .= write_error(_ERROR);
		}
		if($add && isset($_POST['submit'])) {
			$comment = escapestring(descript(strip_tags(replace_naughty($_POST['comments']), $allowed_tags)));
			foreach($author AS $a) {
				$result = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_favorites(uid, item, type, comments) VALUES('".USERUID."', '$a', 'AU', '$comment') ON DUPLICATE KEY UPDATE comments = '$comment'");
			}
			if($result) $output .= write_message(_ACTIONSUCCESSFUL." "._BACK2ACCT);
			else $output .= write_error(_ERROR);
		}
		if($edit && isset($_POST['submit'])) {
			$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_favorites SET comments = '".escapestring(descript(strip_tags(replace_naughty($_POST['comments']), $allowed_tags)))."' WHERE uid = '".USERUID."' AND item = '$edit' AND type = 'AU'");
			if($result) $output .= write_message(_ACTIONSUCCESSFUL." "._BACK2ACCT);
			else $output .= write_error(_ERROR);
		}
		if(($add || $edit) && !isset($_POST['submit'])) {
			if($add) {
				$check = dbquery("SELECT item FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '".USERUID."' AND ".findclause("item", $author)." AND type = 'AU'");
				if(dbnumrows($check)) {
					while($c = dbassoc($check)) {
						$edit[] = $c['item'];
					}
					$edit = array_diff($author, $edit);
				}
				else {
					$pQuery = dbquery("SELECT "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE ".findclause(_UIDFIELD, $author));
					while($pRes = dbassoc($pQuery)) {
						$array_pennames[] = $pRes['penname'];
					}
					$pennames = implode(", ", $array_pennames);
				}
				$output = "<div class='sectionheader'>"._ADDTOFAVORITES.": ".$pennames."</div>";
			}
			if($edit) {
				$pQuery = dbquery("SELECT fav.*, "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_favorites as fav ON fav.item = "._UIDFIELD." AND fav.type = 'AU' AND fav.uid = '".USERUID."' WHERE "._UIDFIELD." = '$edit' LIMIT 1");
				if(dbnumrows($pQuery)) {
					$info = dbassoc($pQuery);
					$pennames = $info['penname'];
					$output .= "<div class='sectionheader'>"._EDITFAVORITES.": ".$pennames."</div>";
				}
				else {
					$author = array($edit);
					$pQuery = dbquery("SELECT "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE ".findclause(_UIDFIELD, $author));
					while($pRes = dbassoc($pQuery)) {
						$array_pennames[] = $pRes['penname'];
					}
					$pennames = implode(", ", $array_pennames);
					$output .= "<div class='sectionheader'>"._ADDTOFAVORITES.": ".$pennames."</div>";
				}
			}
			$author = implode(",", $author);
			$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"user.php?action=favau&amp;".( $add ? "add=$author" : "edit=$edit")."\">\n
				<div style=\"width: 350px; margin: 0 auto; text-align: left;\"><label for=\"comments\">"._COMMENTS.":</label><br />
				<textarea class=\"textbox\" name=\"comments\" id=\"comments\" cols=\"40\" rows=\"5\">".(isset($info['comments']) ? $info['comments'] : "")."</textarea><br />
				<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></div></form>";
		}
	}
	if(!$add && !$edit) {
		$query = "SELECT "._UIDFIELD." as uid, "._PENNAMEFIELD." as penname, fav.comments as comments FROM ".TABLEPREFIX."fanfiction_favorites as fav, "._AUTHORTABLE." WHERE fav.uid = '$uid' AND fav.type = 'AU' AND fav.item = "._UIDFIELD." ORDER BY "._PENNAMEFIELD;
		$countquery = dbquery("SELECT COUNT(item) FROM ".TABLEPREFIX."fanfiction_favorites WHERE uid = '$uid' AND type = 'AU' GROUP BY uid");
		list($count) = dbrow($countquery);
		$x = 1;
		if($count) {
				$list = dbquery($query."  LIMIT $offset, $itemsperpage");
				while($author = dbassoc($list)) { 
					$output .= "<span class='label'>$x.</span> <a href=\"viewuser.php?uid=".$author['uid']."\">".$author['penname']."</a><br />";
					if(file_exists("./$skindir/favcomment.tpl")) $cmt = new TemplatePower( "./$skindir/favcomment.tpl" );
					else $cmt = new TemplatePower( "./default_tpls/favcomment.tpl" );
					$cmt->prepare( );
					$cmt->newBlock("comment");
					$cmt->assign("comment", format_story($author['comments']));
					if(USERUID == $uid) 
					$cmt->assign("commentoptions", "<div class='adminoptions'><span class='label'>"._OPTIONS.":</span> <a href=\"user.php?action=favau&amp;edit=".$author['uid']."\">"._EDIT."</a> | <a href=\"user.php?action=favau&amp;delete=".$author['uid']."\">"._REMOVEFAV."</a></div>");
					$cmt->assign("oddeven", ($x % 2 ? "odd" : "even"));
					$output .= $cmt->getOutputContent( );
					$x++;
				}
			if($count > $itemsperpage) $output .= build_pagelinks("viewuser.php?action=favau&amp;uid=$uid&amp;", $count, $offset);
		}
		else $output .= write_message(_NORESULTS);
	}
	$tpl->assign("output", $output);
	$tpl->gotoBlock( "_ROOT" );
?>
