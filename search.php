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
if(!is_string($searchtype) || (isset($_REQUEST['searchterm']) && !is_string($_REQUEST['searchterm']))) errorExit( );
$rawterm = (isset($_REQUEST['searchterm']) ? $_REQUEST['searchterm'] : "");
// %, _ and ! are matched literally (3.5.82): every LIKE below uses ESCAPE '!'
$searchterm = $rawterm !== "" ? ef_like_term($rawterm) : false;

if(isset($_POST['submit']) || isset($_GET['offset'])) {
	$output .= "<div id=\"pagetitle\">"._RESULTS."</div>";
	$query = array();
	$countquery = array();
	$scountquery = array();	
	// old bookmarks and links have offset= but no searchtype: search by title; any other value must be one of the list
	if(!isset($_REQUEST['searchtype']) || $searchtype === "") $searchtype = "title";
	if(!in_array($searchtype, array("title", "summary", "penname", "advanced", "fulltext"), true)) errorExit( );
	if($searchtype == "fulltext" && $store != "mysql") errorExit( );
	if(($searchtype != "advanced" && trim($rawterm) === "") || ($searchterm !== false && strlen($rawterm) < 3)) {
		errorExit(_SEARCHTERMTOOSHORT);
	}
	if($searchtype == "fulltext") {
		// Only validated chapters count; EXISTS lists each story once and keeps the totals right
		// when several chapters match, and the common filters below still apply.
		$fulltext = "EXISTS (SELECT 1 FROM ".TABLEPREFIX."fanfiction_chapters AS chapter WHERE chapter.sid = stories.sid AND chapter.validated > 0 AND chapter.storytext LIKE '%$searchterm%' ESCAPE '!')";
		$query[] = $fulltext;
		$countquery[] = $fulltext;
		$scountquery[] = "1 = 0"; // series have no chapter text
	}
	if($searchterm !== false) {
		if($searchtype == "title") {
			$query[] = "stories.title LIKE '%$searchterm%' ESCAPE '!'";
			$countquery[] = "stories.title LIKE '%$searchterm%' ESCAPE '!'";
			$scountquery[] = "series.title LIKE '%$searchterm%' ESCAPE '!'";
		}
		if($searchtype == "summary") {
			$query[] = "stories.summary LIKE '%$searchterm%' ESCAPE '!'";
			$countquery[] = "stories.summary LIKE '%$searchterm%' ESCAPE '!'";
			$scountquery[] = "series.summary LIKE '%$searchterm%' ESCAPE '!'";
		}
		if($searchtype == "penname") {
			$authorquery = dbquery(_MEMBERLIST." WHERE "._PENNAMEFIELD." LIKE '%$searchterm%' ESCAPE '!' GROUP BY "._UIDFIELD);
			$authorlist = array();
			while($auth = dbassoc($authorquery)) {
				$authorlist[] = (int)$auth['uid'];
			}
			if(count($authorlist) > 0) {
				$authors = implode(",",$authorlist);
				// a story matches through its author or a co-author; EXISTS keeps one row per story
				// (the co-author names are still filled in by includes/storyblock.php)
				$penmatch = "(FIND_IN_SET(stories.uid, '$authors') > 0 OR EXISTS (SELECT 1 FROM ".TABLEPREFIX."fanfiction_coauthors AS coauth WHERE coauth.sid = stories.sid AND FIND_IN_SET(coauth.uid, '$authors') > 0))";
				$query[] = $penmatch;
				$countquery[] = $penmatch;
				$scountquery[] = "FIND_IN_SET(series.uid, '$authors') > 0";
			}
			else {
				$query[] = "1 = 0";
				$countquery[] = "1 = 0";
				$scountquery[] = "1 = 0";
			}
		}
		if($searchtype == "advanced") {
			$query[] = "(stories.summary LIKE '%$searchterm%' ESCAPE '!' OR stories.title LIKE '%$searchterm%' ESCAPE '!') ";
			$countquery[] = "(stories.summary LIKE '%$searchterm%' ESCAPE '!' OR stories.title LIKE '%$searchterm%' ESCAPE '!') ";
			$scountquery[] = "(summary LIKE '%$searchterm%' ESCAPE '!' OR title LIKE '%$searchterm%' ESCAPE '!') ";
		}
	}
	if(isset($_REQUEST['authors'])) {
		$authors = is_array($_REQUEST['authors']) ? $_REQUEST['authors'] : explode(",", (string)$_REQUEST['authors']);
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
		if(!empty($charstories)) {
			$query[] = "(".implode(" AND ", $charstories).")";
			$countquery[] = "(".implode(" AND ", $charstories).")";
		}
		if(!empty($charseries)) $scountquery[] = "(".implode(" AND ", $charseries).")";
	}

	if(isset($_REQUEST['rid'])) $rid = is_array($_REQUEST['rid']) ? $_REQUEST['rid'] : explode(",", $_REQUEST['rid']);
	else $rid = array();
	// "All" (-1) in the ratings list means no rating filter (before 3.5.77 it matched nothing)
	$rid = array_filter((array)$rid, function($r) { return is_string($r) && isNumber($r); });
	if(count($rid) > 0) {
		$query[] = findclause('rid', $rid);
		$countquery[] = findclause('rid', $rid);
	}
	if(isset($_REQUEST['complete']) || isset($_REQUEST['completed'])) {   // the advanced form's checkbox is "completed"
		$query[] = "stories.completed = '1'";
		$countquery[] = "stories.completed = '1'";
	}
	$classin = array( );
	$classex = array( );
	foreach($classtypelist as $id => $vars) {
		$opts = array( );
		$exopts = array( );
		// form lists and comma lists from continuation pages; a malformed list is refused
		if(isset($_REQUEST["class_".$id])) $opts = ef_search_ids($_REQUEST["class_".$id]);
		else if(isset($_REQUEST[$vars["name"]])) $opts = ef_search_ids($_REQUEST[$vars["name"]]);
		$classin = array_merge($classin, $opts);
		if(isset($_REQUEST["exclass_".$id])) $exopts = ef_search_ids($_REQUEST["exclass_".$id]);
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
		if(!empty($wordcount)) $wordcount .= " AND stories.wordcount < ".$wordhigh;
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
	// the conditions above are semi-joins and can't repeat a story; DISTINCT keeps it that way
	search(preg_replace('/^SELECT /i', 'SELECT DISTINCT ', _STORYQUERY).$query, str_ireplace('count(sid)', 'COUNT(DISTINCT stories.sid)', _STORYCOUNT).$countquery);
	$otherresults = array( );
	$termArray = array_merge($_GET, $_POST);
	$termsList = array();
	foreach($termArray as $term => $value) {
		// the related-series link keeps the search mode and the filters (3.5.83)
		if($term == "submit" || $term == "offset" || empty($value) || $term == "formname") continue;
		$termsList[$term] = ef_term($term, $value);
	}
	$terms = implode("&amp;", $termsList);
	if(!empty($scountquery)) {
		$seriesresults = dbquery(_SERIESCOUNT.$scountquery);
		list($scount) = dbrow($seriesresults);
		if($scount > 0) $otherresults[] = "<a href='browse.php?type=series".($terms !== "" ? "&amp;".$terms : "")."'>$scount "._SERIES."</a>";
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
