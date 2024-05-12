<?php
// ----------------------------------------------------------------------
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


$current = "viewstory";

include ("header.php");
if(isset($_GET['action'])) $action = $_GET['action'];
else $action = false;
if(isset($_GET['textsize']) && isNumber($_GET['textsize'])) $textsize = $_GET['textsize'];
else $textsize = 0;
if(empty($chapter)) $chapter = isset($_GET['chapter']) && isNumber($_GET['chapter']) ? $_GET['chapter'] : false;

	// Get the story information
	$storyquery = dbquery("SELECT "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid, story.*, story.date as date, story.updated as updated, story.validated as valid FROM ".TABLEPREFIX."fanfiction_stories as story, "._AUTHORTABLE." WHERE story.sid = '".$sid."' AND story.uid = "._UIDFIELD);
	$storyinfo = dbassoc($storyquery);
 
	if(!$storyinfo) {
		$current = "storyerror";
		// load our template files to set up the page.
		if (file_exists("$skindir/default.tpl")) $tpl = new TemplatePower("$skindir/default.tpl");
		else $tpl = new TemplatePower("default_tpls/default.tpl");
		$title = "Story was not found";
		$text  = "Story with ID " . $sid . " is not in database"; 
		include("includes/pagesetup.php");
		$tpl->assign("output", "<div id='pagetitle'>" . $title . "</div>" . write_error($text));
		$tpl->printToScreen();
		dbclose();
		exit();
	}
	 
	if($storyinfo['coauthors'] > 0) {
	$array_coauthors = array();
		$coauth = dbquery("SELECT "._PENNAMEFIELD." as penname, co.uid FROM ".TABLEPREFIX."fanfiction_coauthors AS co LEFT JOIN "._AUTHORTABLE." ON co.uid = "._UIDFIELD." WHERE co.sid = '".$sid."'");
		while($c = dbassoc($coauth)) {
		$array_coauthors[$c['uid']] = $c['penname'];
		}
		$storyinfo['coauthors_array'] = $array_coauthors;
		unset($array_coauthors);
	}
	else $storyinfo['coauthors_array'] = array();
	 

	//  Check that the story is valid and that the visitor has permissions to read this story
	$warning = "";
	if(!$storyinfo) $warning = _INVALIDSTORY;
	if(!$storyinfo['valid'] && !isADMIN && ($storyinfo['uid'] != USERUID || !in_array(USERUID, $storyinfo['coauthors_array']))) $warning = _ACCESSDENIED;
	if ($storyinfo['rid']  != 0 ) {
		$ratingquery = dbquery("SELECT ratingwarning, warningtext FROM " . TABLEPREFIX . "fanfiction_ratings WHERE rid = '" . $storyinfo['rid'] . "' LIMIT 1");
		$rating = dbassoc($ratingquery);
		$warninglevel = sprintf("%03b", $rating['ratingwarning']);
	}
	else {
		/* fix me, set default rating */
		$warninglevel[0] = '';
		$warninglevel[1] = '';
		$warninglevel[2] = '';
	}
 
	$title = $storyinfo['title'];
 	if($warninglevel[0] && !isMEMBER) $warning = _RUSERSONLY."<br />";
	else if($warninglevel[1] && empty($_SESSION[SITEKEY."_ageconsent"]) && !$ageconsent) $warning = _AGECHECK."<br /><a href='viewstory.php?sid=".$storyinfo['sid']."&amp;ageconsent=ok&amp;warning=".$storyinfo['rid']."'>".$rating['warningtext']."</a>";
	else if($warninglevel[2] && empty($_SESSION[SITEKEY."_warned"][$storyinfo['rid']])) {
		$warning = $rating['warningtext']."<br /><a href='viewstory.php?sid=".$storyinfo['sid']."&amp;warning=".$storyinfo['rid']."'>"._CONTINUE."</a>";
	}

	// if the above checks came back with a warning, output an error page.
	if(!empty($warning)) {
		$current = "storyerror";
		// load our template files to set up the page.
		if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
		else $tpl = new TemplatePower("default_tpls/default.tpl");
		include("includes/pagesetup.php");
		$tpl->assign("output", "<div id='pagetitle'>".$title."</div>".write_error($warning));
		$tpl->printToScreen( );
		dbclose( );
		exit( ); 
	}
	// End 

$reviews = ""; $numreviews = ""; $reviewslink = ""; $form = ""; $rr = "";

if($action == "printable") {
	$settingsresults = dbquery("SELECT store, storiespath FROM ".$settingsprefix."fanfiction_settings WHERE sitekey = '".SITEKEY."'");
	list($store, $storiespath) = dbrow($settingsresults);
	if(file_exists("$skindir/printstory.tpl")) $tpl = new TemplatePower( "$skindir/printstory.tpl" );
	else $tpl = new TemplatePower("default_tpls/printstory.tpl");
	include("includes/pagesetup.php");
	$tpl->assign("title", stripslashes($storyinfo['title']));
	$tpl->assign("author", author_link($storyinfo));
	if(empty($chapter)) $chapter = "all"; // shouldn't happen but just in case
	$stories = $storyinfo;
	include("includes/storyblock.php");
	unset($stories);
	if($chapter == "all") {
		if($storyinfo['storynotes']) {
			$tpl->newBlock("storynotes");
			$tpl->assign( "storynotes", format_story($storyinfo['storynotes']));
			$tpl->gotoBlock("_ROOT");
		}
		$chapterinfo = dbquery("SELECT *, "._PENNAMEFIELD." as penname FROM (".TABLEPREFIX."fanfiction_chapters as c, "._AUTHORTABLE.") WHERE sid = '$sid' AND c.uid = "._UIDFIELD." AND c.validated > 0 ORDER BY inorder");
		while($c = dbassoc($chapterinfo)) {
			$tpl->newBlock("storyindexblock");
			$tpl->assign("chapternumber", $c['inorder']);
			$tpl->assign("title", "<a href='#".$c['inorder']."'>".$c['title']."</a>");
			$tpl->assign("author", $c['penname']);
			$tpl->gotoBlock("_ROOT");
			$tpl->newBlock("chapterblock");
			$tpl->assign("chaptertitle", "<a name='".$c['inorder']."'></a>".$c['title']);
			$tpl->assign("chapterauthor", $c['penname']);
			if(!empty($c['notes'])) {
				$tpl->newBlock("notes");
				$tpl->assign( "notes", format_story($c['notes']));
				$tpl->gotoBlock("chapterblock");
			}
			if($store == "files") {
				//shouldn't happen, but somehow has on occasion. :(
				if(!$c['uid']) {
					errorExit( );
				}
				$file = STORIESPATH."/".$c['uid']."/".$c['chapid'].".txt";
				$log_file = @fopen($file, "r");
				$file_contents = @fread($log_file, filesize($file));
				$story = $file_contents;
				@fclose($log_file);
			}
			else $story = $c['storytext'];
			// The following lines cleans up problems between pre-2.0 stories and 2.0 stories.  If there's html, don't send it through nl2br and then clean up smart quotes.
			$story = format_story($story);
			$tpl->assign("chaptertext", $story);	
			$tpl->assign("back2top", "<a href='#1'>"._BACK2INDEX."</a>");
			if(!empty($c['endnotes'])) {
				$tpl->newBlock("endnotes");
				$tpl->assign( "endnotes", format_story($c['endnotes']));
				$tpl->gotoBlock("chapterblock");
			}
		}
	}
	else {
		$chapterinfo = dbquery("SELECT *, "._PENNAMEFIELD." as penname FROM (".TABLEPREFIX."fanfiction_chapters as c, "._AUTHORTABLE.") WHERE sid = '$sid' AND inorder = '$chapter' AND c.uid = "._UIDFIELD." LIMIT 1");
		$c = dbassoc($chapterinfo);
		// if the *CHAPTER* hasn't been validated and the viewer isn't an admin or the author throw them a warning.  
		if(empty($c['validated']) && !isADMIN && USERUID != $c['uid'] && !in_array(USERUID, $stories['coauthors_array'])) {
			$warning = write_error(_ACCESSDENIED);
			$tpl->assign("archivedat", $warning);
			$tpl->printToScreen( );
			dbclose( );
			exit( );
		}

		if($c['inorder'] == 1 && !empty($storyinfo['storynotes'])) {
			$tpl->newBlock("storynotes");
			$tpl->assign( "storynotes", format_story($storyinfo['storynotes']));
			$tpl->gotoBlock("_ROOT");
		}

		$tpl->newBlock("chapterblock");
		$tpl->assign("chaptertitle", $c['title']);
		$tpl->assign("chapternumber", $c['inorder']);
		$tpl->assign("chapterauthor", $c['penname']);
		if(!empty($c['notes'])) {
			$tpl->newBlock("notes");
			$tpl->assign( "notes", format_story($c['notes']));
			$tpl->gotoBlock("chapterblock");
		}
		//shouldn't happen, but somehow has on occasion. :(
		if($store == "files") {
			if(!$c['uid']) {
				errorExit( );
			}
			$file = STORIESPATH."/".$c['uid']."/".$c['chapid'].".txt";
			$log_file = @fopen($file, "r");
			$file_contents = @fread($log_file, filesize($file));
			$story = $file_contents;
			@fclose($log_file);
		}
		else $story = $c['storytext'];
		// The following lines cleans up problems between pre-2.0 stories and 2.0 stories.  If there's html, don't send it through nl2br and then clean up smart quotes.
		$story = format_story($story);
		$tpl->assign("chaptertext", $story);	
			if(!empty($c['endnotes'])) {
				$tpl->newBlock("endnotes");
				$tpl->assign( "endnotes", format_story($c['endnotes']));
				$tpl->gotoBlock("chapterblock");
			}
	}
	$tpl->gotoBlock("_ROOT");
	// Hook for adding content to the printed version of the story.
	$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'printstory'");
	while($code = dbassoc($codeblocks)) {
		eval($code['code_text']);
	}	
	$tpl->assign("archivedat", _ARCHIVEDAT." <a href=\"$url/viewstory.php?sid=$sid\">$url/viewstory.php?sid=$sid</a>");
	$copyquery = dbquery("SELECT message_text FROM ".TABLEPREFIX."fanfiction_messages WHERE message_name = 'printercopyright' LIMIT 1");
	list($copyright) = dbrow($copyquery);
	$tpl->assign("copyright", $copyright);
}
else if(($displayindex && empty($chapter)) || !empty($_GET['index'])) {
	if(file_exists("$skindir/storyindex.tpl")) $tpl = new TemplatePower( "$skindir/storyindex.tpl" );
	else $tpl = new TemplatePower("default_tpls/storyindex.tpl");
	include("includes/pagesetup.php");
	$stories = $storyinfo;
	// Hook for adding content to only the index of the story.
	$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'storyindex'");
	while($code = dbassoc($codeblocks)) {
		eval($code['code_text']);
	}
	include("includes/storyblock.php");
	$printicon = "<a href=\"viewstory.php?action=printable&amp;textsize=$textsize&amp;sid=$sid&amp;chapter=all\" target=\"_blank\"><img src='".(isset($printer) ? $printer : "images/print.gif")."' border='0' alt='"._PRINTER."'></a>";
	if($reviewsallowed && (isMEMBER || $anonreviews))
			$reviewslink = "<a href=\"reviews.php?action=add&amp;item=$sid&amp;next=2&amptype=ST\">"._SUBMITREVIEW."</a>";
	$tpl->assign( "reviewslink", $reviewslink );
	if($storyinfo['rr']) {
		$tpl->assign("roundrobin", "[<a href=\"stories.php?action=newchapter&amp;sid=".$sid."\">"._CONTRIBUTE2RR."</a>]");
	}
	$tpl->assign( "printicon", $printicon );
	$tpl->assign( "reviewform", $form);
	$tpl->assign( "output", $output );
	if( $storyinfo['storynotes']) {
		$tpl->newBlock("storynotes");
		$tpl->assign( "storynotes", format_story($storyinfo['storynotes']));
		$tpl->gotoBlock("_ROOT");
	}
	$chapterinfo = dbquery("SELECT chap.*, "._PENNAMEFIELD." as penname FROM (".TABLEPREFIX."fanfiction_chapters as chap, "._AUTHORTABLE.") WHERE sid = '$sid' AND chap.uid = "._UIDFIELD." AND chap.validated > 0 ORDER BY inorder");
	$count = 0;
	while($chap = dbassoc($chapterinfo)) {
		$tpl->newBlock("storyindexblock");
		$tpl->assign("chapternumber", $chap['inorder']);
		$tpl->assign("title", "<a href=\"viewstory.php?sid=$sid&amp;chapter=".$chap['inorder']."\">".$chap['title']."</a>");
		$tpl->assign("author", "<a href='viewuser.php?uid=".$chap['uid']."'>".$chap['penname']."</a>");
		$tpl->assign("printicon", "<a href=\"viewstory.php?action=printable&amp;textsize=$textsize&amp;sid=$sid&amp;chapter=".$chap['inorder']."\" target=\"_blank\"><img src='".(isset($printer) ? $printer : "images/print.gif")."' border='0' alt='"._PRINTER."'></a>");
		$tpl->assign("ratingpics", ratingpics($chap['rating']));
		if($reviewsallowed) {
			$tpl->assign("reviews", "<a href=\"reviews.php?type=ST&amp;item=$sid&amp;chapid=".$chap['chapid']."\">"._REVIEWS."</a>");
			$tpl->assign("numreviews", "<a href=\"reviews.php?type=ST&amp;item=$sid&amp;chapid=".$chap['chapid']."\">".$chap['reviews']."</a>");
		}
		if(isADMIN) 
			$tpl->assign("adminoptions", "<div class=\"adminoptions\"><span class='label'>"._ADMINOPTIONS.":</span> <a href=\"stories.php?action=editchapter&amp;sid=$sid&amp;chapid=".$chap['chapid']."&amp;admin=1\">"._EDIT."</a> | <a href=\"stories.php?action=delete&amp;sid=$sid&amp;chapid=".$chap['chapid']."&amp;admin=1\">"._DELETE."</a> </div>");
		$tpl->assign("wordcount", $chap['wordcount']);
		$tpl->assign("chapternotes", format_story($chap['notes']));
		$tpl->assign("count", ($chap['count'] ? $chap['count'] : 0) );
		$tpl->assign("oddeven",  ($count % 2 ? "odd" : "even"));
		$tpl->gotoBlock("_ROOT");
		$count++;
	}
}
else {
	if(file_exists("$skindir/viewstory.tpl")) $tpl = new TemplatePower( "$skindir/viewstory.tpl" );
	else $tpl = new TemplatePower("default_tpls/viewstory.tpl");
	include("includes/pagesetup.php");
	$jumpmenu = "";
	$jumpmenu2 = "";
	if(empty($chapter) || !$chapter) $chapter = 1;
	// get information about the story's chapter(s)
	$chapterinfo = dbquery("SELECT chap.*, "._PENNAMEFIELD." as penname FROM (".TABLEPREFIX."fanfiction_chapters as chap, "._AUTHORTABLE.") WHERE sid = '$sid' AND chap.uid = "._UIDFIELD." ORDER BY inorder");
	$chapters = dbnumrows($chapterinfo);
	if($chapters > 1) {
		$printicon = "<img src='".(isset($printer) ? $printer : "images/print.gif")."' border='0' alt='"._PRINTER."'> <a href=\"viewstory.php?action=printable&amp;textsize=$textsize&amp;sid=$sid&amp;chapter=$chapter\" target=\"_blank\">"._CHAPTER."</a> "._OR." <a href=\"viewstory.php?action=printable&amp;textsize=$textsize&amp;sid=$sid&amp;chapter=all\" target=\"_blank\">"._STORY."</a>";
		$jumpmenu .= "<form name=\"jump\" action=\"\">";
		if($chapter > 1) 
			$prev = "<a href=\"viewstory.php?sid=$sid&amp;".($textsize ? "textsize=$textsize&amp;" : "")."chapter=".($chapter-1)."\" class=\"prev\">"._PREVIOUS."</a> ";
		$jumpmenu .= "<select class=\"textbox\" name=\"chapter\" onchange=\"if(document.jump.chapter.selectedIndex.value != $chapter) document.location = 'viewstory.php?sid=$sid&amp;textsize=$textsize&amp;chapter=' + document.jump.chapter.options[document.jump.chapter.selectedIndex].value\">";
		while($chap = dbassoc($chapterinfo)) {
			if($chap['validated']) $jumpmenu .= "<option value='".$chap['inorder']."'".(isset($chapter) && $chap['inorder'] == $chapter ? " selected" : "").">".$chap['inorder'].". ".stripslashes($chap['title'])."</option>";
			if($chap['inorder'] == $chapter) {
				$inorder = $chapter;
				$notes = isset($chap['notes']) ? format_story($chap['notes']) : false;
				$endnotes = isset($chap['endnotes']) ? format_story($chap['endnotes']) : false;
				$chapid = $chap['chapid'];
				$story = stripslashes($chap['storytext']);
				$chapterauthor = $chap['uid'];
				$chapterpenname = $chap['penname'];
				$valid = $chap['validated'];
				$chaptertitle = stripslashes($chap['title']);
			}
		}
		$jumpmenu .= "</select>";
		if($chapter < $chapters) {
			$nextchapter = $chapter + 1;
			$next = "<a href=\"viewstory.php?sid=$sid&amp;textsize=$textsize&amp;chapter=".($chapter+1)."\" class=\"next\">"._NEXT."</a>";
		}
		else {
			$nextchapter = "";
			// Hook for adding content to only the end of the story.
			$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'storyend'");
			while($code = dbassoc($codeblocks)) {
				eval($code['code_text']);
			}
		}
		$jumpmenu .= "</form>";
	}
	// if the story has only one chapter this is what happens
	else {
		$chapter = dbassoc($chapterinfo);

		if (!$chapter)
		{
			$current = "chaptererror";
			// load our template files to set up the page.
			if (file_exists("$skindir/default.tpl")) $tpl = new TemplatePower("$skindir/default.tpl");
			else $tpl = new TemplatePower("default_tpls/default.tpl");
			$title = "Chapter was not found";
			$text  = "Missing chapter";
			include("includes/pagesetup.php");
			$tpl->assign("output", "<div id='pagetitle'>" . $title . "</div>" . write_error($text));
			$tpl->printToScreen();
			dbclose();
			exit();
		}

		$chapterauthor = $chapter['uid'];
		$chapterpenname = $chapter['penname'];
		$chaptertitle = $chapter['title'];
		$chapid = $chapter['chapid'];
		$title = stripslashes((string) $chapter['title']);
		$inorder = $chapter['inorder']; 
		$notes = format_story($chapter['notes']);
		$endnotes = format_story($chapter['endnotes']);
		$story = $chapter['storytext'];
		$valid = $chapter['validated'];
		$nextchapter = "";
		$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'storyend'");
		while($code = dbassoc($codeblocks)) {
			eval($code['code_text']);
		}
		$printicon = "<a href=\"viewstory.php?action=printable&amp;sid=$sid&amp;textsize=$textsize&amp;chapter=1\" target=\"_blank\"><img src='".(isset($printer) ? $priner : "images/print.gif")."' border='0' alt='"._PRINTER."'></a>";
	}
	// if the *CHAPTER* hasn't been validated and the viewer isn't an admin or the author throw them a warning.  
	if(!$valid && !isADMIN && USERUID != $chapterauthor && !in_array($chapterauthor, $storyinfo['coauthors_array'])) {
		$warning = accessDenied( );
	}
	$stories = $storyinfo;
	$tpl->gotoBlock("_ROOT");
	$jumpmenu2 = ""; 
	include("includes/storyblock.php");
	unset($adminlinks);
	if(isADMIN && uLEVEL < 3) 
		$adminlinks = "<div class=\"adminoptions\"><span class='label'>"._ADMINOPTIONS.":</span> "._EDIT." - <a href=\"stories.php?action=editstory&amp;sid=$sid&amp;admin=1\">"._STORY."</a> "._OR." <a href=\"stories.php?action=editchapter&amp;chapid=$chapid&amp;admin=1\">"._CHAPTER."</a> | "._DELETE." - <a href=\"stories.php?action=delete&amp;sid=$sid&amp;admin=1\">"._STORY."</a> "._OR." <a href=\"stories.php?action=delete&amp;chapid=$chapid&amp;sid=$sid&amp;admin=1\">"._CHAPTER."</a></div>";
	if(isMEMBER && $favorites) {
		$jumpmenu2 .= "<option value=\"user.php?action=favst&amp;add=1&amp;sid=$sid\">"._ADDSTORY2FAVES."</option><option value=\"user.php?action=favau&amp;add=1&amp;author=".$stories['uid'].(count($storyinfo['coauthors_array']) ? ",".implode(",", array_keys($storyinfo['coauthors_array'])) : "")."\">"._ADDAUTHOR2FAVES."</option>";
	}
	if($reviewsallowed ) {
		if(isMEMBER || $anonreviews) {
			$reviewslink = "<a href=\"reviews.php?action=add&amp;type=ST&amp;item=$sid&amp;chapid=$chapid&amp;next=$nextchapter\">"._SUBMITREVIEW."</a>";
			$jumpmenu2 .= "<option value=\"reviews.php?action=add&amp;type=ST&amp;item=$sid&amp;chapid=$chapid&amp;next=$nextchapter\">"._SUBMITREVIEW."</option>";
		}
		else $reviewslink = write_message(sprintf(_LOGINTOREVIEW, strtolower($pagelinks['login']['link']), strtolower($pagelinks['register']['link'])));
	}
	$tpl->assign("reportthis", "[<a href=\""._BASEDIR."contact.php?action=report&amp;url=viewstory.php?chapid=$chapid\">"._REPORTTHIS."</a>]");
	$jumpmenu2 .= "<option value=\"contact.php?action=report&amp;url=viewstory.php?chapid=$chapid\">"._REPORTTHIS."</option>";
	if($stories['rr']) {
		$rr = "[<a href=\"stories.php?action=newchapter&amp;sid=".$sid."\">"._CONTRIBUTE2RR."</a>]";
		$jumpmenu2 .= "<option value=\"stories.php?action=newchapter&amp;sid=".$sid."\">"._CONTRIBUTE2RR."</option>";
	}
	if(isset($jumpmenu2)) $jumpmenu2 = "<form name=\"jump2\" action=\"\"><select class=\"textbox\" name=\"jump2\" onchange=\"if(this.selectedIndex.value != 'false') document.location = document.jump2.jump2.options[document.jump2.jump2.selectedIndex].value\"><option value=\"false\">"._OPTIONS."</option>".$jumpmenu2."</select></form>";
	if($reviewsallowed) {
		if(isMEMBER || $anonreviews) {
			$item = $sid;
			$type = "ST";
			include("includes/reviewform.php");
		}
		else $form = write_message(sprintf(_LOGINTOREVIEW, strtolower($pagelinks['login']['link']), strtolower($pagelinks['register']['link'])));
	}
	$textsizer = "<a href=\"viewstory.php?sid=$sid".($inorder ? "&amp;chapter=$inorder" : "")."&amp;textsize=".($textsize - 1)."\">-</a> <strong>". _TEXTSIZE. "</strong> <a href=\"viewstory.php?sid=$sid".($inorder ? "&amp;chapter=$inorder" : "")."&amp;textsize=".($textsize + 1)."\">+</a> ";
	// okay now that we know they can see the story and the chapter add 1 to the story and chapter counts;
	if(empty($viewed) || (is_array($viewed) && !in_array($sid, $viewed))) {
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET count = count + 1 WHERE sid = '$sid'  LIMIT 1");
		$viewed[] = $sid;
		$_SESSION[SITEKEY."_viewed"] = $viewed;
	}
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_chapters SET count = count + 1 WHERE chapid = '$chapid' LIMIT 1");
	// end counters
	// if the text of the chapters is being stored in files we need to get that text here
	if($store == "files") {
		//shouldn't happen, but somehow has on occasion. :(
		if(!$chapterauthor) {
			errorExit(_ERROR);
		}
		$file = STORIESPATH."/$chapterauthor/$chapid.txt";
		$log_file = @fopen($file, "r");
		$file_contents = @fread($log_file, filesize($file));
		$story = $file_contents;
		@fclose($log_file);
	}
	$story = format_story($story);
	if(isset($adminlinks)) $tpl->assign( "adminlinks", $adminlinks );
	$tpl->assign( "printicon", $printicon );
	$tpl->assign( "output", $output );
	if($inorder == 1 && !empty($storyinfo['storynotes'])) {
		$tpl->gotoBlock("_ROOT");
		$tpl->newBlock("storynotes");
		$tpl->assign( "storynotes", stripslashes($storyinfo['storynotes']));
		$tpl->gotoBlock("_ROOT");
	}
	if(!empty($notes)) {
		$tpl->newBlock("notes");
		$tpl->assign( "notes", $notes);
		$tpl->gotoBlock("_ROOT");
	}
	if(!empty($endnotes)) {
		$tpl->newBlock("endnotes");
		$tpl->assign( "endnotes", $endnotes);
		$tpl->gotoBlock("_ROOT");
	}
	$tpl->gotoBlock("_ROOT");
	$tpl->assign("chaptertitle", $chaptertitle);
	$tpl->assign("chapternumber", $inorder);
	$tpl->assign( "story", "<span style=\"font-size: ".(100 + ($textsize * 20))."%;\">$story</span>" );
	$tpl->assign("textsizer", $textsizer);
	if(isset($jumpmenu)) $tpl->assign( "jumpmenu", $jumpmenu);
	if(isset($jumpmenu2)) $tpl->assign( "jumpmenu2", $jumpmenu2);
	$tpl->assign( "roundrobin", $rr );
	$tpl->assign( "reviewform", $form);
	if(isset($addtofaves)) $tpl->assign( "addtofaves", $addtofaves );
	if(isset($next)) $tpl->assign( "next", $next );
	if(isset($prev)) $tpl->assign( "prev", $prev );

}
// Hook for adding content to view story pages.
$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'viewstory'");
while($code = dbassoc($codeblocks)) {
	eval($code['code_text']);
}
$tpl->printToScreen();
dbclose( );

?>
