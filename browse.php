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
// ---------------------------------------------------------------------

$current = "browse";
$displayform = 1;

include ("header.php");

if(file_exists("$skindir/browse.tpl")) $tpl = new TemplatePower( "$skindir/browse.tpl" );
else $tpl = new TemplatePower("default_tpls/browse.tpl");
if(file_exists("$skindir/listings.tpl")) $tpl->assignInclude("listings", "./$skindir/listings.tpl");
else $tpl->assignInclude( "listings", "./default_tpls/listings.tpl" );
$tpl->assignInclude( "header", "./$skindir/header.tpl" );
$tpl->assignInclude( "footer", "./$skindir/footer.tpl" );

include("includes/pagesetup.php");
if(isset($_GET['type'])) $type = descript($_GET['type']);
else $type = false;

if($type) {
	$query = array();
	$countquery = array();
	$scountquery = array();
	$searchtype = (isset($_REQUEST['searchtype']) ? $_REQUEST['searchtype'] : "simple");
	$searchterm =  (isset($_REQUEST['searchterm']) ? descript($_REQUEST['searchterm']) : false);
	$disablesorts = array( );
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
				$query[] = "FIND_IN_SET(stories.uid, '".implode(",",$authorlist)."') > 0";
				$countquery[] = "FIND_IN_SET(stories.uid, '".implode(",",$authorlist)."') > 0";
				$scountquery[] = "FIND_IN_SET(series.uid, '".implode(",",$authorlist)."') > 0";
			}
		}
		if($searchtype == "advanced" || !$searchtype) {
			$query[] = "(stories.summary LIKE '%$searchterm%' OR stories.title LIKE '%$searchterm%') ";
			$countquery[] = "(stories.summary LIKE '%$searchterm%' OR stories.title LIKE '%$searchterm%') ";
			$scountquery[] = "(summary LIKE '%$searchterm%' OR title LIKE '%$searchterm%') ";
		}
	}
	if(isset($_REQUEST['authors'])) {
		$query[] = "FIND_IN_SET(stories.uid, '".implode(",",$searchVars['authors'])."') > 0";
		$countquery[] = "FIND_IN_SET(stories.uid, '".implode(",",$searchVars['authors'])."') > 0";
		$scountquery[] = "FIND_IN_SET(series.uid, '".implode(",",$searchVars['authors'])."') > 0";
	}
	if(isset($_REQUEST['catid'])) {
		$catid = is_array($_REQUEST['catid']) ? $_REQUEST['catid'] : explode(",", $_REQUEST['catid']);
		$catid = array_filter($catid, "isNumber");
	}
	if(!isset($catid)) $catid = array();
	if($type == "categories" && isset($_GET['id']) && isNumber($_GET['id'])) {
		$catid[] =  $_GET['id'];
	}
	$categories = array( );
	// Get the recursive list.
	if($type == "categories") $categories = $catid;
	else {
		foreach($catid as $cat) {
			if($cat == "false" || empty($cat)) continue;
			$categories = array_merge($categories, recurseCategories($cat));
		}
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
	if(empty($charid)) $charid = array( );
	if(isset($_REQUEST['charid'])) {
		$charid = is_array($_REQUEST['charid']) ? $_REQUEST['charid'] : explode(",", $_REQUEST['charid']);
		$charid = array_filter($charid, "isNumber");
	}
	if(!empty($_REQUEST['charlist1']) && !in_array($_REQUEST['charlist1'], $charid)) $charid[] = $_REQUEST['charlist1'];
	if(!empty($_REQUEST['charlist2']) && !in_array($_REQUEST['charlist2'], $charid)) $charid[] = $_REQUEST['charlist2'];
	if($charid) {
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
	$rid = array( ); $rating = array( );
	if(!empty($_REQUEST['rid'])) $rid = is_array($_REQUEST['rid']) ? implode(",", $_REQUEST['rid']) : array($_REQUEST['rid']);
	if(!empty($_REQUEST['rating'])) $rating = is_array($_REQUEST['rating']) ? implode(",", $_REQUEST['rating']) : array($_REQUEST['rating']);
	$rid = array_merge($rid, $rating);
	if(!empty($rid)) {
		$query[] = "FIND_IN_SET(stories.rid, '".(implode(",", $rid))."') > 0";
		$countquery[]  = "FIND_IN_SET(stories.rid, '".(implode(",", $rid))."') > 0";
	}
	$complete = "all";
	if(isset($_REQUEST['complete'])) {
		if($_REQUEST['complete'] == 1) {
			$query[] = "stories.completed = '1'";
			$countquery[] = "stories.completed = '1'";
			$complete = 1;
		} 
		else if(empty($_REQUEST['complete'])) {
			$query[] = "stories.completed = '0'";
			$countquery[] = "stories.completed = '0'";
			$complete = 0;
		} 
	}
	if(isset($_REQUEST['classin'])) $classin = is_array($_REQUEST['classin']) ? $_REQUEST['classin'] : array($_REQUEST['classin']);
	else $classin = array( );
	if(isset($_REQUEST['classex'])) $classex = is_array($_REQUEST['classex']) ? $_REQUEST['classex'] : array($_REQUEST['classex']);
	else $classex = array( );
	$opts = array();
	$exopts = array( );
	foreach($classtypelist as $id => $vars) {
		if(!empty($_POST[$vars['name']])) $opts[] = $_POST[$vars['name']];
		else if(isset($_GET["class_".$id])) {
			$opts = !empty($opts) ? array_merge($opts, explode(",", $_GET["class_".$id])) : array($_GET["class_".$id]);
		}
		else if(!empty($_REQUEST[$vars["name"]])) $opts = is_array($_REQUEST[$vars["name"]]) ? $_REQUEST[$vars["name"]] : array($_REQUEST[$vars["name"]]);
		if(!empty($opts)) $classin = array_merge($classin, $opts);
		if(isset($_POST["exclass_".$id])) $exopts = $_POST["exclass_".$id];
		else if(isset($_GET["exclass_".$id])) {
			$exopts = !empty($exopts) ? array_merge($exopts, explode(",", $_GET["exclass_".$id])) : array($_GET["exclass_".$id]);
		}
		if(!empty($exopts)) $classex = array_merge($classex, $exopts);
		unset($opts, $exopts);
	}
	$classin = array_filter($classin, "isNumber");
	$classex = array_filter($classex, "isNumber");
	if($classin) {
		foreach($classin as $class) {
			if(empty($class)) continue;
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
	$wordlow = isset($_REQUEST['wordlow']) ? $_REQUEST['wordlow'] : "-500";
	$wordhigh = isset($_REQUEST['wordhigh']) ? $_REQUEST['wordhigh'] : "1000000";
	if($wordlow != "-500") $wordcount = "stories.wordcount > ".$wordlow;
	if($wordhigh != "1000000") 
	{
		if($wordcount) $wordcount .= " AND stories.wordcount < ".$wordhigh;
		else $wordcount = "stories.wordcount < ".$wordhigh;
	}
	if($wordhigh < $wordlow) $wordcount = "stories.wordcount < ".$wordlow;
	if($wordlow != "-500" && $wordhigh != "1000000" && isset($_POST['submit'])) {
		$query[] = "($wordcount)";
		$countquery[] = "($wordcount)";
	}
	// End wordcount
	$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'browseterms'");
	while($code = dbassoc($codeblocks)) {
		eval($code['code_text']);
	}
	$storyquery = count($query) > 0 ? " AND ".implode(" AND ", $query) : "";
	$countquery = count($countquery) > 0 ? " AND ".implode(" AND ", $countquery) : "";
	$seriesquery = count($scountquery) > 0 ? implode(" AND ", $scountquery) : false;
	$termArray = array_merge($_GET, $_POST);
	$termsList = array();
	foreach($termArray as $term => $value) {
		if($term == "submit" || $term == "go" || $term == "offset" || $term == "type" || ($term != "complete" && empty($value))) continue;
		$termsList[$term] = "$term=".(is_array($value) ? implode(",", $value) : $value);
	}
	$terms = "type=$type".(count($termsList) ? "&amp;" : "").implode("&amp;", $termsList);
// End query strings
	$panelquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_name = '$type' AND panel_type = 'B' LIMIT 1");
	if(dbnumrows($panelquery)) {
		$numrows = 0;
		$panel = dbassoc($panelquery);
		if($panel['panel_url'] && file_exists(_BASEDIR.$panel['panel_url'])) include($panel['panel_url']);
		else if(file_exists("browse/{$type}.php")) include("browse/{$type}.php");
		else $output .= write_error(_ERROR);
	}
	else $output .= write_error(_ERROR);
	$terms = implode("&amp;", $termsList);
// Other results
	$otherresults = array( );
	$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'otherresults'");
	while($code = dbassoc($codeblocks)) {
		eval($code['code_text']);
	}
	if($type != "series" && !empty($seriesquery)) {
		$seriesresults = dbquery(_SERIESCOUNT." WHERE ".$seriesquery);
		list($scount) = dbrow($seriesresults);
		if($scount > 0) $otherresults[] = "<a href='browse.php?type=series&amp;$terms'>$scount "._SERIES."</a>";
	}
	if(count($otherresults) > 0 && $type != "titles") $tpl->assign("otherresults", "<div id='otherresults'><span class='label'>"._OTHERRESULTS.":</span> ".implode(", ", $otherresults)."</div>");
// build our sort menus. if there aren't any stories don't bother with these since they won't be used
	if(!empty($numrows) || isset($_POST['go'])) {
		$tpl->newBlock("sortform");
		$tpl->assign("sortbegin", "<form style=\"margin:0\" method=\"POST\" id=\"form\" enctype=\"multipart/form-data\" action=\"browse.php?type=$type&amp;$terms\">");
		if($catlist && !in_array("categories", $disablesorts)) {
			if(count($catid) > 0) $thiscat = $catid[0];
			else $thiscat = -1;
			$catmenu = "<select class=\"textbox\" name=\"catid\" id=\"catid\" onChange=\"browseCategories('catid')\"><option value=\"-1\">".($thiscat > 0 ? _BACK2CATS : _CATEGORIES)."</option>\n";
			foreach($catlist as $cat => $info) {
				if($info['pid'] == $thiscat || $cat == $thiscat) $catmenu .= "<option value=\"$cat\"".($thiscat == $cat ? " selected" : "").">".$info['name']."</option>\n";
			}
			$catmenu .= "</select>\n";
			$tpl->assign("categorymenu", $catmenu);
		}
		if(count($charlist) > 0 && !in_array("characters", $disablesorts)) {
			$charactermenu1 = "<select class=\"textbox\" name=\"charlist1\" id=\"charlist1\">\n";
			$charactermenu1 .= "<option value=\"0\">"._CHARACTERS."</option>\n";
			$charactermenu2 = "<select class=\"textbox\" name=\"charlist2\" id=\"charlist2\">\n";
			$charactermenu2 .= "<option value=\"0\">"._CHARACTERS."</option>\n";
			$categories[] = -1;
			$categories = array_merge($categories, $catid);
			foreach($charlist as $char => $info) {
				if(is_array($categories) && in_array($info['catid'], $categories)) {
					$charactermenu1 .= "<option value=\"$char\"";
					if(isset($charid[0]) && $charid[0] == $char)
						$charactermenu1 .= " selected";
					$charactermenu1 .= ">".$info['name']."</option>\n";
					$charactermenu2 .= "<option value=\"$char\"";
					if(isset($charid[1]) && $charid[1] == $char)
						$charactermenu2 .= " selected";
					$charactermenu2 .= ">".$info['name']."</option>\n";
				}
			}
			$charactermenu1 .= "</select>";
			$charactermenu2 .= "</select>";
			if($type != "characters") $tpl->assign("charactermenu1"   , $charactermenu1 );
			$tpl->assign("charactermenu2"   , $charactermenu2 );
		}
		// To avoid throwing warnings we need to define $classopts and tell it how many elements it should have.
		if(!in_array("classes", $disablesorts)) {
			$classopts = array();
			foreach($classlist as $id => $vars) {
				if(empty($classopts[$vars['type']])) $classopts[$vars['type']] = "";
				$classopts[$vars['type']] = $classopts[$vars['type']]."<option value=\"$id\"".(isset($classin) && is_array($classin) && in_array($id, $classin) ? " selected" : "").">".$vars['name']."</option>\n";
			}
			$allclasses = "";
			foreach($classopts as $type => $opts) {
				if(empty($type) || in_array($classtypelist[$type]['name'], $disablesorts)) continue; // Because of the way we defined $classopts we need to skip the empty first element.
				$opts = "<option value=\"\">".$classtypelist[$type]['title']."</option>$opts";
				$tpl->assign($classtypelist[$type]['name']."menu", "<select name=\"".$classtypelist["$type"]['name']."\">\n$opts</select>\n");
				$allclasses .= "<select class=\"textbox\" name=\"".$classtypelist["$type"]['name']."\">\n$opts</select>\n ";
			}
			$tpl->assign("classmenu", $allclasses);
		}
		if(!in_array("ratings", $disablesorts)) {
			$ratingmenu = "<select class=\"textbox\" name=\"rating\">\n";
			$ratingmenu .= "<option value=\"0\">"._RATINGS."</option>\n";
			if(!isset($ratingslist)) $ratingslist = array( );
			foreach($ratingslist as $r => $rinfo) {
				$ratingmenu .= "<option value=\"".$r."\"";
				if(isset($rid) && in_array($r, $rid))
					$ratingmenu .= " selected";
				$ratingmenu .= ">".$rinfo['name']."</option>\n";
			}
			$ratingmenu .= "</select>\n";
			$tpl->assign("ratingmenu"   , $ratingmenu );
		}
		if(!in_array("sorts", $disablesorts)) $tpl->assign("sortmenu", "<select class=\"textbox\" name=\"sort\">\n<option value=''>"._SORT."</option><option value=\"alpha\"".(!$defaultsort ? " selected" : "").">"._ALPHA."</option>\n<option value=\"update\"".($defaultsort == 1 ? " selected" : "").">"._MOSTRECENT."</option>\n</select>\n");
		if(!in_array("complete", $disablesorts)) $tpl->assign("completemenu", "<select class=\"textbox\" name=\"complete\">\n<option value=\"all\"".($complete == "all" ? " selected" : "").">"._ALLSTORIES."</option>\n<option value=\"1\"".($complete == 1 ? " selected" : "").">"._COMPLETEONLY."</option>\n<option value=\"0\"".($complete && $complete != "all" && $complete != 1 ? " selected" : "").">"._WIP."</option>\n</select>\n");
		$codeblocks = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'browsesorts'");
		while($code = dbassoc($codeblocks)) {
			eval($code['code_text']);
		}
		$tpl->assign("sortend"   , "<INPUT type=\"submit\" class=\"button\" name=\"go\" value=\""._GO."\"></form>");
		$tpl->gotoBlock("_ROOT");
	}
}
else  {
	$output = "<div id='pagetitle'>"._BROWSE."</div>";
	$panelquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_hidden != '1' AND panel_level ".(isMEMBER ? " < 2" : "= '0'")." AND panel_type = 'B' ORDER BY panel_type DESC, panel_order ASC, panel_title ASC");
	while($panel = dbassoc($panelquery)) {
		$browsetypes[$panel['panel_title']] =  "<a href=\"browse.php?type=".$panel['panel_name']."\">".$panel['panel_title']."</a><br />\n";
	}
	
	foreach($classtypelist as $type => $info) {
		$browsetypes[$info['title']] = "<a href='browse.php?type=class&amp;type_id=$type'>".$info['title']."</a><br />\n";
	}
	ksort($browsetypes);
	$total = count($browsetypes);
	$count = 0;
	$column = 1;
	$list = floor($total / $displaycolumns);
	if($total % $displaycolumns != 0) $list++;
	$output .= "<div id=\"columncontainer\"><div id=\"browseblock\">".($displaycolumns ? "<div class=\"column\">" : "");
	foreach($browsetypes as $link) {
		$count++;
		$output .= $link;
		if( $count >= $list && $column != $displaycolumns) {
			$output .= "</div><div class=\"column\">";
			if($total % $displaycolumns == $column) $list--;
			$column++;
			$count = 0;
		}
	}
	$output .= "</div>".($displaycolumns ? "</div>" : "")."<div class='cleaner'>&nbsp;</div></div>";
}
$tpl->assign("output", $output);
$tpl->printToScreen( );
?>
