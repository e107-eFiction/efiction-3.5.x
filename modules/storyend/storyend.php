<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
// Also Like Module developed for eFiction 3.0
// // http://efiction.hugosnebula.com/
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
if(file_exists(_BASEDIR."modules/storyend/languages/{$language}.php")) include_once(_BASEDIR."modules/storyend/languages/{$language}.php");
else include_once(_BASEDIR."modules/storyend/languages/en.php");

if(file_exists("./$skindir/storyend.tpl")) $storyend = new TemplatePower("./$skindir/storyend.tpl");
else $storyend = new TemplatePower("./modules/storyend/default_tpls/storyend.tpl");
$storyend->prepare( );
$storyend->assign("theend", !empty($storyinfo['completed']) ? _THEEND : _THEENDMAYBE);

$authors[] = $storyinfo['uid'];
if(!empty($storyinfo['coauthors'])) {
	$coauth = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_coauthors WHERE sid = '".$storyinfo['sid']."'");
	while($c = dbassoc($coauth)) {
		$authors[] = $c['uid'];
	}
}
if(count($authors) > 1) $cond = "FIND_IN_SET(ap.uid, '".implode(",", $authors)."') > 0";
else $cond = "ap.uid = '".$authors[0]."'";
$aquery = dbquery("SELECT "._PENNAMEFIELD." as penname, ap.uid, ap.stories FROM "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs AS ap on "._UIDFIELD." = ap.uid WHERE $cond");
while($a2 = dbassoc($aquery)) {
	$alist[] = sprintf(_AUTHORCOUNT, "<a href='viewuser.php?uid=".$a2['uid']."'>".$a2['penname']."</a>", ($a2['stories'] - 1));
}
$storyend->assign("authorcount", implode("<br />", $alist));
$favquery = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_favorites WHERE item = '".$storyinfo['sid']."' AND type = 'ST'");
$favcount = dbnumrows($favquery);
$authlist = array( );
if($favcount > 0) {
	while($favau = dbassoc($favquery)) {
		$authlist[] = $favau['uid'];
	}
	$authlist = implode(",", $authlist);
	$storyend->assign("favof", sprintf(_SE_FAVOF, $favcount));
	$other = dbquery("SELECT item FROM ".TABLEPREFIX."fanfiction_favorites WHERE type = 'ST' AND FIND_IN_SET(uid, '$authlist') > 0 AND item != '".$storyinfo['sid']."' GROUP BY item");
	$othercount = dbnumrows($other);
	$storyend->assign("alsolike", sprintf(_SE_ALSOLIKE, title_link($storyinfo), $storyinfo['sid'], $othercount));
}
/*
// If we've got series links for this story, let's give them links to the previous and next stories in the series.
$seriesquery = "SELECT series.title, series.seriesid, list.inorder FROM ".TABLEPREFIX."fanfiction_inseries as list, ".TABLEPREFIX."fanfiction_series as series WHERE list.sid = '".$storyinfo['sid']."' AND series.seriesid = list.seriesid";
$seriesresults = dbquery($seriesquery);
$seSeries = array( );
 while($series = dbassoc($seriesresults)) {
	$thisSeries = sprintf(_SE_INSERIES, "<a href='"._BASEDIR."viewseries.php?seriesid=".$series['seriesid']."'>".stripslashes($series['title'])."</a>");
	$others = dbquery("SELECT subseriesid, sid, inorder FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '".$series['seriesid']."' AND confirmed = 1 AND (inorder = ".($series['inorder'] - 1)." OR inorder = ".($series['inorder'] + 1).")"); 
 	while($o = dbassoc($others)) {
		unset($story, $sub);
		if(!empty($o['sid'])) $story = dbassoc(dbquery(_STORYQUERY." AND sid = ".$o['sid']." LIMIT 1"));
		elseif(!empty($o['subseriesid'])) $sub = dbassoc(dbquery(_SERIESQUERY." AND seriesid = ".$o['seriesid']));
		
		if($o['inorder'] < $series['inorder']) {
			if($story) $thisSeries .= " ".sprintf(_SE_SERIES_PREVST, title_link($story));
			elseif($sub) $thisSeries .= " ".sprintf(_SE_SERIES_PREVSE, "<a href='"._BASEDIR."viewseries.php?seriesid=".$sub['seriesid']."'>".stripslashes($sub['title'])."</a>");
		}
		elseif ($o['inorder'] > $series['inorder']) {
			if($story) $thisSeries .= " ".sprintf(_SE_SERIES_NEXTST, title_link($story));
			elseif($sub) $thisSeries .= " ".sprintf(_SE_SERIES_NEXTSE, "<a href='"._BASEDIR."viewseries.php?seriesid=".$sub['seriesid']."'>".stripslashes($sub['title'])."</a>");
		}
		
	}  
	$seSeries[] = $thisSeries;
}
if(count($seSeries) > 0) $storyend->assign("seriesinfo", implode("<br />", $seSeries));
*/
$tpl->assign("storyend", $storyend->getOutputContent( ));

?>