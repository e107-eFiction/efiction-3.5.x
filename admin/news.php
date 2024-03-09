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

if (!defined("_CHARSET")) exit();
if (uLEVEL > 3) accessDenied();
$output .= "<div id=\"pagetitle\">" . _NEWS . "</div>";
if (isset($_POST['submit']))
{
	$title = addslashes(strip_tags(descript($_POST['title']), $allowed_tags));
	$author = addslashes(strip_tags(descript($_POST['author']), $allowed_tags));
	$story = addslashes(strip_tags(descript($_POST['story']), $allowed_tags));
	$time = time();
	if ($_GET['form'] == "new") $result = dbquery("INSERT INTO " . TABLEPREFIX . "fanfiction_news (title, author, story, time) VALUES ('$title', '$author', '$story', '$time')");
	else $result = dbquery("UPDATE " . TABLEPREFIX . "fanfiction_news 
	SET title = '$title', author = '$author', story = '$story' WHERE nid = '$_POST[nid]'");
	if ($result)
	{
		$output .= write_message(_ACTIONSUCCESSFUL);
		unset($_GET['form']);
	}
	else $output .= write_error(_ERROR . " " . TRYAGAIN);
}
if (isset($_GET['delete']) && isNumber($_GET['delete']))
{
	if (isset($_GET['confirm']))
	{
		if ($_GET['confirm'] == "yes")
		{
			dbquery("DELETE FROM " . TABLEPREFIX . "fanfiction_news where nid = '" . $_GET['delete'] . "'");
			$output .= write_message(_ACTIONSUCCESSFUL);
		}
		else if ($_GET['confirm'] == "no") $output .= write_message(_ACTIONCANCELLED);
	}
	else
	{
		$output .= write_message(_CONFIRMDELETE . "<br /><br />
	[ <a href=\"admin.php?action=news&confirm=yes&delete=$_GET[delete]\">" . _YES . "</a> | <a href=\"admin.php?action=news&confirm=no&delete=$_GET[delete]\">" . _NO . "</a> ]");
	}
}
else if (isset($_GET["form"]))
{
	if ($_GET["form"] != "new" && isNumber($_GET["form"]))
	{
		$result = dbquery("SELECT * FROM " . TABLEPREFIX . "fanfiction_news WHERE nid = '" . $_GET['form'] . "' LIMIT 1");
		$newsitem = dbassoc($result);
	}
	else $newsitem = array("title" => "", "author" => "", "story" => "");
	$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=news&form=$_GET[form]\">
				<table align=\"center\"><tr><td colspan=\"2\" align=\"center\"><b>" . ($_GET["form"] == "new" ? _ADDNEWS : _EDITNEWS) . "</b></td></tr>
			<tr><td>" . _AUTHOR . ": </td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"author\" value=\"" . stripslashes($newsitem['author']) . "\"></td></tr>
			<tr><td>" . _TITLE . ": </td><td><INPUT  type=\"text\" class=\"textbox=\" name=\"title\" value=\"" . stripslashes($newsitem['title']) . "\"></td></tr>
			<tr><td>" . _TEXT . ": </td><td><textarea class=\"textbox\" name=\"story\" id=\"story\" COLS=\"45\" ROWS=\"6\">" . stripslashes($newsitem['story']) . "</TEXTAREA>";
	if ($tinyMCE)
		$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('story');\" checked><label for='toggle'>" . _TINYMCETOGGLE . "</label></div>";

	$output .= "</td></tr><tr><td colspan=\"2\" align=\"center\">" . ($_GET["form"] != "new" ? "<INPUT type=\"hidden\" name=\"nid\" value=\"" . $_GET['form'] . "\">" : "") . "<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\"" . _SUBMIT . "\"></td></tr></table></form>";
}
else
{
	$output .= "<p align=\"center\"><a href=\"admin.php?action=news&form=new\">" . _ADDNEWS . "</a></p>";
	$count = dbquery("SELECT COUNT(nid) FROM " . TABLEPREFIX . "fanfiction_news");
	list($numrows) = dbrow($count);

	$result = dbquery("SELECT n.*, n.time as date, count(c.cid) as comments FROM " . TABLEPREFIX . "fanfiction_news as n LEFT JOIN " . TABLEPREFIX . "fanfiction_comments as c ON  n.nid = c.nid GROUP BY n.nid ORDER BY time DESC LIMIT $offset, $itemsperpage");
	if (file_exists("$skindir/newsbox.tpl")) $news = new TemplatePower("$skindir/newsbox.tpl");
	else $news = new TemplatePower("default_tpls/newsbox.tpl");
	$news->prepare();
	$count = 0;
	while ($stories = dbassoc($result))
	{
		$news->newBlock("newsbox");

		//assign values
		$news->assign("newstitle", $stories['title']);
		$news->assign("newsstory", $stories['story']);
		$news->assign("newsauthor", $stories['author']);
		$news->assign("newsdate", date("$dateformat $timeformat", $stories['date']));
		$news->assign("oddeven", ($count % 2 ? "even" : "odd"));
		if ($newscomments == "1")
			$news->assign("newscomments", "<a href=\"news.php?action=newsstory&amp;nid=" . $stories['nid'] . "\">" . $stories['comments'] . " " . _COMMENTS . "</a>");
		if (isADMIN)
			$news->assign("adminoptions", "<a href=\"admin.php?action=news&amp;form=" . $stories['nid'] . "\">" . _EDIT . "</a> | <a href=\"admin.php?action=news&amp;delete=" . $stories['nid'] . "\">" . _DELETE . "</a>");
		$count++;
	}
	$news->gotoBlock("_ROOT");
	$output .= $news->getOutputContent();
	if ($numrows > $itemsperpage) $output .= build_pagelinks("admin.php?action=news&amp;", $numrows, $offset);
}
