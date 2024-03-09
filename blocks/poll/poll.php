<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
// http://efiction.hugosnebula.com
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

global $language;

if(file_exists(_BASEDIR."blocks/poll/{$language}.php")) include_once(_BASEDIR."blocks/poll/{$language}.php");
else include_once(_BASEDIR."blocks/poll/en.php");
$content = "";
$pollopts = array();
$pollquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_poll WHERE poll_end IS NULL OR poll_end = 0 ORDER BY poll_id DESC LIMIT 1");
if($pollquery) $currentpoll = dbassoc($pollquery);
 
if(isset($_POST['cast_vote'])) {
	$poll = isset($_POST['poll_id']) && isNumber($_POST['poll_id']) ? $_POST['poll_id'] : false;
	$opt = isset($_POST['opt']) && isNumber($_POST['opt']) ? $_POST['opt'] : false;
	if(!$poll || !$opt) accessDenied( ); // could only be a hack attempt.
	$voted = dbquery("SELECT vote_id FROM ".TABLEPREFIX."fanfiction_poll_votes WHERE vote_poll = '".$currentpoll['poll_id']."' AND vote_user = '".USERUID."'");
	list($voter) = dbrow($voted);
        if(!$voter) $cast = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_poll_votes(`vote_user`, `vote_opt`, `vote_poll`) VALUES('".USERUID."', '$opt', '$poll')");
	if(!empty($cast)) $content .= write_message(_VOTECAST);
	else $content .= write_message(_ALREADYVOTED);
}

if(!$currentpoll) $content = "<div style='text-align: center;'>"._NOPOLL."</div>";
else  {
	   $pollopts = explode("|#|", $currentpoll['poll_opts']);
	   $content .= "<div id='poll_question'>".$currentpoll['poll_question']."</div><br />";
	   if(isMEMBER) {
		$voted = dbquery("SELECT vote_id FROM ".TABLEPREFIX."fanfiction_poll_votes WHERE vote_poll = '".$currentpoll['poll_id']."' AND vote_user = '".USERUID."'");
		list($voter) = dbrow($voted);
           }
	   if(empty($voter) && isMEMBER) {
		$content .= "<form name='poll' id='poll' method='POST' action='".$_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING']  : "")."'>
			<input type='hidden' name='poll_id' value='".$currentpoll['poll_id']."'>";
		   $x = 1;
		   foreach($pollopts as $opt) {
		       $content .= "<input type='radio' name='opt' value='$x'> $opt<br />";
		       $x++;
		   }
		   $content .= "<div style='text-align: center;'><input type='submit' id='cast_vote' name='cast_vote' value='"._VOTE."' class='button' style='font-size: 11px;'></div></form>";
		$content .= write_message("<br /><a href='"._BASEDIR."blocks/poll/pollarchive.php?poll=".$currentpoll['poll_id']."'>"._POLLRESULTS."</a><br /><br />");

	  }
	  else { 
		$votes = dbquery("SELECT COUNT(vote_opt) as count, vote_opt FROM ".TABLEPREFIX."fanfiction_poll_votes WHERE vote_poll = '".$currentpoll['poll_id']."' GROUP BY vote_opt  ORDER BY vote_opt");

		if(isset($blocks['poll']['bar']) && file_exists($blocks['poll']['bar'])) $bar = $blocks['poll']['bar'];
		else $bar = "blocks/poll/bar.jpg";
		$totalvotes = 0;
		while($optvotes = dbassoc($votes)) {
			$results[$optvotes['vote_opt']] = $optvotes['count'];
			 $totalvotes = $optvotes['count'] ? $optvotes['count'] + $totalvotes : $totalvotes;
		}
		foreach($pollopts as $num => $opt) {
			unset($percent);
			if(!empty($results[($num + 1)])) $percent = floor(($results[($num + 1)] / $totalvotes) * 100);
			else $percent = 0;
			$content .= "<div>$opt <br /> <img src='$bar' alt='".strip_tags($opt)."' height='12' width='".$percent."' class='poll'> $percent%</div>";	
		}
	  }
}
	$content .= "<div style='text-align: center;'><a href='"._BASEDIR."blocks/poll/pollarchive.php'>"._POLLARCHIVE."</a></div>";
?>