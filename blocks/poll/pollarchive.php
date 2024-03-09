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

$current = "pollarchive";

include ("../../header.php");

if(file_exists(_BASEDIR."blocks/poll/{$language}.php")) include(_BASEDIR."blocks/poll/{$language}.php");
else include(_BASEDIR."blocks/poll/en.php");
if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
else $tpl = new TemplatePower(_BASEDIR."default_tpls/default.tpl");
$tpl->assignInclude( "header", "./$skindir/header.tpl" );
$tpl->assignInclude( "footer", "./$skindir/footer.tpl" );
//let TemplatePower do its thing, parsing etc.
$tpl->prepare();

include("../../includes/pagesetup.php");

$poll = !empty($_GET['poll']) && isNumber($_GET['poll']) ? $_GET['poll'] : false;

if($poll) {
	$pollquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_poll WHERE poll_id = '$poll' ORDER BY poll_id DESC LIMIT 1");
	$currentpoll = dbassoc($pollquery);
	$opts = explode("|#|", $currentpoll['poll_opts']);
	$votes = explode("#", $currentpoll['poll_results']);
	$output .= "<div id='pagetitle'>"._POLLARCHIVE.": ".$currentpoll['poll_question']."</div><br />";
	if(isset($blocks['poll']['bar']) && file_exists($blocks['poll']['bar'])) $bar = $blocks['poll']['bar'];
	else $bar = _BASEDIR."blocks/poll/bar.jpg";
	$totalvotes = 0;
	$results = array( );
	foreach($votes as $opt => $count) {
		$results[$opt] = $count;
		 $totalvotes = $count ? $count + $totalvotes : $totalvotes;
	}
	$output .= "<div id='story'>";
	foreach($opts as $num => $opt) {
		unset($percent);
		if(!empty($results[$num]) && $results[$num] > 0) $percent = floor(($results[$num] / $totalvotes) * 100);
		else $percent = 0;
		$output .= "<div>$opt <br /> <img src='$bar' alt='".strip_tags($opt)."' height='12' class='poll' width='".$percent."'> $percent%</div>";	
	}
	$output .= "</diV>";
}
else {
	$output .= "<div id='pagetitle'>"._POLLARCHIVE."</div>";
	$listquery = dbquery("SELECT poll_id, poll_question, poll_start as start, poll_end as end FROM ".TABLEPREFIX."fanfiction_poll WHERE poll_end IS NOT NULL OR poll_end != 0 ORDER BY poll_id DESC");
	if(dbnumrows($listquery) > 0) {
		$output .= "<table class='tblborder' style='margin: 1em auto;'><tr><th class='tblborder'>"._POLLARCHQUESTION."</th><th class='tblborder'>"._START."</th><th class='tblborder'>"._END."</th></tr>";
		while($poll = dbassoc($listquery)) {
			$output .= "<tr><td class='tblborder'><a href='pollarchive.php?poll=".$poll['poll_id']."'>".$poll['poll_question']."</a></td><td class='tblborder'>".date("$dateformat", $poll['start'])."</td><td class='tblborder'>".date("$dateformat", $poll['end'])."</td></tr>";
		}
		$output .= "</table>";
	}
}
$tpl->assign("output", $output);
$tpl->printToScreen();

?>