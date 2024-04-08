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


// Page Setup
// Set the current page id.  Because search does multiple things there are multiple possibilities
if(isset($_POST['submit']) || isset($_GET['offset'])) $current = "searchresults";
else $current = "search";
if(isset($_GET['action']) && $_GET['action'] == "advanced") $displayform = 1;
// Now set up the page

$displayform = 1;


include ("header.php");

if(file_exists("$skindir/browse.tpl")) $tpl = new TemplatePower( "$skindir/browse.tpl" );
else $tpl = new TemplatePower("default_tpls/browse.tpl");
if(file_exists("$skindir/listings.tpl")) $tpl->assignInclude( "listings", "./$skindir/listings.tpl" );
else $tpl->assignInclude( "listings", "./default_tpls/listings.tpl" );

include("includes/pagesetup.php");


$searchtype = (isset($_REQUEST['searchtype']) ? $_REQUEST['searchtype'] : "simple");
$searchterm =  (isset($_REQUEST['searchterm']) ? escapestring($_REQUEST['searchterm']) : false);

if(isset($_POST['submit']) || isset($_GET['offset'])) {
	$output .= "<div id=\"pagetitle\">"._RESULTS."</div>";
	$query = array();
	$countquery = array();
	$scountquery = array();	
	if($searchtype == "fulltext") {
		$query = "SELECT stories.*, "._PENNAMEFIELD." as penname, stories.date as date, stories.updated as updated FROM "._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_stories as stories, ".TABLEPREFIX."fanfiction_chapters as chapter WHERE chapter.storytext LIKE '%$searchterm%' AND chapter.sid = stories.sid AND stories.uid = "._UIDFIELD;
		$countquery = "SELECT count(stories.sid) FROM ".TABLEPREFIX."fanfiction_stories as stories, ".TABLEPREFIX."fanfiction_chapters as chapter WHERE chapter.storytext LIKE '%$searchterm%' AND chapter.sid = stories.sid GROUP BY stories.sid";
		$query .= " "._ORDERBY;
		search($query, $countquery);
		$tpl->assign("output", $output);
		$tpl->printToScreen();
		dbclose( );			
		exit( );
	}
	if($searchterm && strlen($searchterm) < 3) {
		errorExit(_SEARCHTERMTOOSHORT);
	}
	if($searchterm) {
		if($searchtype == "title") {
			$query[] = "stories.title LIKE '%$searchterm%'";
			$countquery[] = "stories.title LIKE '%$searchterm%'";
			$scountquery[] = "series.title LIKE '%$searchterm%'";
		}
		if($searchtype == "summary") {
			$query[] = "stories.summary LIKE '%$searchterm%'";
			$countquery[] = "stories.summary LIKE '%$searchterm%'";
			$scountquery[] = "series.summary LIKE '%$searchterm%'";
		}
		if($searchtype == "penname") {
			$authorquery = dbquery(_MEMBERLIST." WHERE "._PENNAMEFIELD." LIKE '%$searchterm%' GROUP BY "._UIDFIELD);
			$authorlist = array();
			while($auth = dbassoc($authorquery)) {
				$authorlist[] = $auth['uid'];
			}
			if(count($authorlist) > 0) {
				$authors = implode(",",$authorlist);
				$query = "SELECT stories.*, "._PENNAMEFIELD." as penname, stories.date as date, stories.updated as updated FROM ("._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_stories as stories) LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors as coauth ON coauth.sid = stories.sid WHERE "._UIDFIELD." = stories.uid AND stories.validated > 0 AND (FIND_IN_SET(stories.uid, '$authors') > 0 OR FIND_IN_SET(coauth.uid, '$authors') > 0) ";
				$countquery = "SELECT COUNT(stories.sid) FROM ".TABLEPREFIX."fanfiction_stories as stories LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors as coauth ON coauth.sid = stories.sid WHERE stories.validated > 0 AND (FIND_IN_SET(stories.uid, '$authors') > 0 OR FIND_IN_SET(coauth.uid, '$authors') > 0)";
				search($query, $countquery);
				$tpl->assign("output", $output);
				$tpl->printToScreen();
				dbclose( );			
				exit( );
			}
			else {
				$query[] = "1 = 0";
				$countquery[] = "1 = 0";
				$scountquery[] = "1 = 0";
			}
		}
		if($searchtype == "advanced") {
			$query[] = "(stories.summary LIKE '%$searchterm%' OR stories.title LIKE '%$searchterm%') ";
			$countquery[] = "(stories.summary LIKE '%$searchterm%' OR stories.title LIKE '%$searchterm%') ";
			$scountquery[] = "(summary LIKE '%$searchterm%' OR title LIKE '%$searchterm%') ";
		}
	}
	if(isset($_REQUEST['authors'])) {
		$authors = isarray($_REQUEST['authors']) ? $_REQUEST['authors'] : explode(",", $_REQUEST['authors']);
		$authors = array_filter($authors, "isNumber");
		if($authors) {
			$authors = implode(",", $authors);
			$query[] = "FIND_IN_SET(stories.uid, '$authors') > 0";
			$countquery[] = "FIND_IN_SET(stories.uid, '$authors') > 0";
			$scountquery[] = "FIND_IN_SET(series.uid, '$authors') > 0";
		}
		else {
			$query[] = "1 = 0";
			$countquery[] = "1 = 0";
			$scountquery[] = "1 = 0";
		}
	}
	if(isset($_REQUEST['catid'])) {
		$catid = is_array($_REQUEST['catid']) ? $_REQUEST['catid'] : explode(",", $_REQUEST['catid']);
		$catid = array_filter($catid, "isNumber");
	}
	if(!isset($catid)) $catid = array("false");
	$categories = array( );
	// Get the recursive list.
	foreach($catid as $cat) {
		if($cat == "false" || empty($cat)) continue;
		$categories = array_merge($categories, recurseCategories($cat));
		}
	// Now format the SQL
	foreach($categories as $cat) {
		$catstories[] = "FIND_IN_SET($cat, stories.catid) > 0 ";
		$catseries[] = "FIND_IN_SET($cat, series.catid) > 0 ";
	}
	// Now implode the SQL list
	if(!empty($catstories)) {
		$query[] = "(".implode(" OR ", $catstories).")";
		$countquery[] = "(".implode(" OR ", $catstories).")";
		$scountquery[] = "(".implode(" OR ", $catseries).")";
	}
	if(isset($_REQUEST['charid'])) {
		$charid = is_array($_REQUEST['charid']) ? $_REQUEST['charid'] : explode(",", $_REQUEST['charid']);
	}
	else $charid = array( );
	if(isset($_REQUEST['charlist1'])) $charid[] = $_REQUEST['charlist1'];
	if(isset($_REQUEST['charlist2'])) $charid[] = $_REQUEST['charlist2'];
	$charid = array_filter($charid, "isNumber");
	if(count($charid) > 0) {
		foreach($charid as $c) {
			if(!empty($c)) {
				$charstories[] = "FIND_IN_SET($c, stories.charid) > 0 ";
				$charseries[] = "FIND_IN_SET($c, series.characters) > 0 ";
			}
		}
		if(count($charstories) > 0) {
			$query[] = "(".implode(" AND ", $charstories).")";
			$countquery[] = "(".implode(" AND ", $charstories).")";
		}
		if(count($charseries) > 0) $scountquery[] = "(".implode(" AND ", $charseries).")";
	}

	if(isset($_REQUEST['rid'])) $rid = is_array($_REQUEST['rid']) ? $_REQUEST['rid'] : explode(",", $_REQUEST['rid']);
	else $rid = array();
	if(count($rid) > 0) {
		$rid = array_filter($rid, "isNumber");
		$query[] = findclause('rid', $rid);
		$countquery[] = findclause('rid', $rid);
	}
	if(isset($_REQUEST['complete'])) {
		$query[] = "stories.completed = '1'";
		$countquery[] = "stories.completed = '1'";
	}
	$classin = array( );
	$classex = array( );
	foreach($classtypelist as $id => $vars) {
		$opts = array( );
		$exopts = array( );
		if(isset($_POST["class_".$id])) $opts = array_filter($_POST["class_".$id], "isNumber");
		else if(isset($_GET["class_".$id])) {
			$opts = array_merge($opts, explode(",", $_GET["class_".$id]));
			$opts = array_filter($opts, "isNumber");
		}
		else if(isset($_REQUEST[$vars["name"]])) $opts[] = array($_REQUEST[$vars["name"]]);
		$classin = array_merge($classin, $opts);
		if(isset($_POST["exclass_".$id])) $exopts = array_filter($_POST["exclass_".$id], "isNumber");
		else if(isset($_GET["exclass_".$id])) {
			$exopts = array_merge($exopts, explode(",", $_GET["exclass_".$id]));
			$exopts = array_filter($exopts, "isNumber");
		}
		$classex = array_merge($classex, $exopts);
		unset($opts, $exopts);
	}
	if($classin) {
		foreach($classin as $class) {
			$query[] = "FIND_IN_SET($class, stories.classes) > 0";
			$countquery[] = "FIND_IN_SET($class, stories.classes) > 0";
			$scountquery[] = "FIND_IN_SET($class, series.classes) > 0";
		}
	}
	if($classex) {
		foreach($classex as $class) {
			$query[] = "FIND_IN_SET($class, stories.classes) = 0";
			$countquery[] = "FIND_IN_SET($class, stories.classes) = 0";
			$scountquery[] = "FIND_IN_SET($class, series.classes) = 0";
		}
	}
	// Begin story length
	unset($wordcount);
	$wordlow = isset($_REQUEST['wordlow']) && isNumber($_REQUEST['wordlow']) ? $_REQUEST['wordlow'] : "-500";
	$wordhigh = isset($_REQUEST['wordhigh']) && isNumber($_REQUEST['wordhigh']) ? $_REQUEST['wordhigh'] : "1000000";
	if($wordlow != "-500") $wordcount = "stories.wordcount > ".$wordlow;
	if($wordhigh != "1000000") 
	{
		if($wordcount) $wordcount .= " AND stories.wordcount < ".$wordhigh;
		else $wordcount = "stories.wordcount < ".$wordhigh;
	}
	if($wordhigh < $wordlow) $wordcount = "stories.wordcount < ".$wordlow;
	if(!empty($wordcount)) {
		$query[] = "($wordcount)";
		$countquery[] = "($wordcount)";
	}
	// End wordcount

	$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'browseterms'");
	while($code = dbassoc($codeblocks)) {
		eval($code['code_text']);
	}

	$query = (count($query) > 0 ? " AND ".implode(" AND ", $query) : "")." "._ORDERBY;
	$countquery = count($countquery) > 0 ? " AND ".implode(" AND ", $countquery) : "";
	if(count($scountquery) > 0) $scountquery = " WHERE ".implode(" AND ", $scountquery);
	search(_STORYQUERY.$query, _STORYCOUNT.$countquery);
	$otherresults = array( );
	$termArray = array_merge($_GET, $_POST);
	$termsList = array();
	foreach($termArray as $term => $value) {
		if($term == "submit" || $term == "wordhigh" || $term == "wordlow" || empty($value) || $term == "formname" || $term == "searchtype") continue;
		$termsList[$term] = "$term=".(is_array($value) ? implode(",", $value) : $value);
	}
	$terms = implode("&amp;", $termsList);
	if(!empty($scountquery)) {
		$seriesresults = dbquery(_SERIESCOUNT.$scountquery);
		list($scount) = dbrow($seriesresults);
		if($scount > 0) $otherresults[] = "<a href='browse.php?type=series$terms'>$scount "._SERIES."</a>";
	}
	$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'otherresults'");
	while($code = dbassoc($codeblocks)) {
		eval($code['code_text']);
	}
	if(count($otherresults) > 0) $tpl->assign("otherresults", "<div id='otherresults'><span class='label'>"._OTHERRESULTS.":</span> ".implode(", ", $otherresults)."</div>");
}
else {
	if($searchtype == "simple") {
		$output .= "<div id=\"pagetitle\">"._SIMPLE."</div><div style='text-align: center;'><form method=\"post\" enctype=\"multipart/form-data\" action=\"search.php\">
		<div class=\"tblborder\" style=\"width: 320px; padding: 5px; margin: 0 auto;\">
		<select name=\"searchtype\">
		<option value=\"penname\">"._PENNAME."</option>
		<option value=\"title\">"._TITLE."</option>
		<option value=\"summary\">"._SUMMARY."</option>";
		if($store == "mysql")
			$output .= "<option value=\"fulltext\">"._FULLTEXT."</option>";
		$output .= "</select> <INPUT type=\"text\" class=\"textbox\" name=\"searchterm\" size=\"20\"> ";
		$output .= "<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\" size=\"20\">
		<div style=\"font-size: 8pt; text-align: right;\"><a href=\"search.php?searchtype=advanced\">"._ADVANCED."</a></div></div></form></div>";
	}
	else {
		$output .= "<div id=\"pagetitle\">"._ADVANCED. "</div><div>
			<form method=\"POST\" name=\"form\" enctype=\"multipart/form-data\" action=\"search.php?searchtype=advanced\">
			<div class=\"tblborder\" style=\"width: 90%; margin: 0 auto; padding: 10px;\">";
		if($multiplecats) {
			include("includes/categories.php");
			$output .= "<input type=\"hidden\" name=\"formname\" value=\"search\">";
		}
		$output .= "<div style='float: left; width: 99%;'>";
		if(count($charlist) > 0) {
			$output .= "<div style=\"float: left; width: 49%;\"><label for=\"charname\">"._CHARACTERS.": </label><br />\n";
			$output .= "<select name=\"charid[]\"  style=\"width: 90%;\" multiple size=\"5\" id=\"charid\">";
			foreach($charlist as $charid => $info) {
				if($info['catid'] != -1) continue;
				$output .= "<option value=\"".$charid."\">" .stripslashes($info['name'] ). "</option>\n";
			}
			$output .= "</select></div>";
		}
		$result = dbquery("SELECT rid, rating FROM ".TABLEPREFIX."fanfiction_ratings ORDER BY rating");
		if(dbnumrows($result)) {
			$output .= "<div style=\"float: left; width: 49%;\"><label for=\"rid\">"._RATINGS.":</label><br /><select id=\"rid\" style=\"width: 90%;\" multiple size=\"5\"name=\"rid[]\"><option value=\"-1\">"._ALL."</option>";
			while ($ratingresults = dbassoc($result))
			{
				$output .= "<option value=\"".$ratingresults['rid']."\">".$ratingresults['rating']."</option>\n";
			}
			$output .= "</select></div>";
		}
		$output .= "<div style=\"clear: both;\">&nbsp;</div>";
		$result = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classtypes ORDER BY classtype_name");
		while($type = dbassoc($result)) {
			$result2 = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classes WHERE class_type = '".$type['classtype_id']."' ORDER BY class_name");
			$select = "";
			while ($class = dbassoc($result2)) {
 				$select .= "<option value=\"".$class['class_id']."\">".$class['class_name']."</option>";
			}
			$output .= "<div>
				<div style=\"float: left; width: 49%; margin-bottom: 1em;\"><label for=\"class_".$type['classtype_id']."\">".$type['classtype_title']." "._INCLUDE.":</label><br />
					 <select name=\"class_".$type['classtype_id']."[]\" id=\"class_".$type['classtype_id']."\"  style=\"width: 95%;\" multiple size=\"5\">$select</select></div>
				<div style=\"float: left; width: 49%; margin-bottom: 1em;\"><label for=\"exclass_".$type['classtype_id']."\">".$type['classtype_title']." "._EXCLUDE.":</label><br />
					 <select name=\"exclass_".$type['classtype_id']."[]\"  id=\"exclass_".$type['classtype_id']."\"  style=\"width: 95%;\" multiple size=\"5\">$select</select></div>
				</div>";
		}
		$output .= "</div><label for=\"completed\">"._COMPLETEONLY.":</label> <input type=\"checkbox\" class=\"checkbox\" id=\"completed\" name=\"completed\" value=\"ON\"><label for=\"wordlow\">"._WORDCOUNT.":</label> <select size=\"1\" id=\"wordlow\" name=\"wordlow\">
  <option value='-500'>&lt; 500</option>
  <option>1000</option>
  <option>5000</option>
  <option>10000</option>
  <option>50000</option>
  <option>100000</option>
  <option value='1000000'>100000+</option>
  </select> - <select size=\"1\" name=\"wordhigh\">
  <option value='1000000'>100000+</option>
  <option>100000</option>
  <option>50000</option>
  <option>10000</option>
  <option>5000</option>
  <option>1000</option>
  <option value='-500'>&lt; 500</option>
  </select><br /><br /><label for=\"searchterm\">"._SEARCHTERM.": </label><INPUT type=\"text\" class=\"textbox\" name=\"searchterm\" id=\"searchterm\" size=\"20\">
  
  <label for=\"sortorder\">"._SORT.":</label> <select name=\"sortorder\" id=\"sortorder\"><option value=\"alpha\">"._ALPHA."</option><option value=\"update\">"._MOSTRECENT."</option></select>";
	$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'searchform'");
	while($code = dbassoc($codequery)) {
		eval($code['code_text']);
	}
$output .= "<div id='submitdiv'><input name=\"submit\" id=\"submit\" value=\""._SUBMIT."\" type=\"submit\" class=\"button\"></div></div></form></div>";
	}
}
$tpl->assign("output", $output);
$tpl->printToScreen();
dbclose( );
?>
