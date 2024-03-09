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
$current = "challenges";
if(isset($_GET['action']) && ($_GET['action'] == "add" || $_GET['action'] == "edit")) $displayform = 1;

if(file_exists(_BASEDIR."languages/{$language}_admin.php")) include_once(_BASEDIR."languages/{$language}_admin.php");
else include_once(_BASEDIR."languages/en_admin.php");
if(file_exists(_BASEDIR."modules/challenges/languages/{$language}.php")) include_once(_BASEDIR."modules/challenges/languages/{$language}.php");
else include_once(_BASEDIR."modules/challenges/languages/en.php");
$output = "<div id='pagetitle'>"._CHALLENGEADMIN."</div>";
if(!isADMIN) accessDenied( );
if(isset($_GET['task']) && $_GET['task'] == "recalc") {
	$challenges = dbquery("SELECT chalid, characters FROM ".TABLEPREFIX."fanfiction_challenges");
	while($c = dbassoc($challenges)) {
		unset($newchars, $count, $count1, $count2);
		$stories = dbquery("SELECT COUNT(sid) AS count FROM ".TABLEPREFIX."fanfiction_stories WHERE FIND_IN_SET('".$c['chalid']."', challenges) > 0");
		list($count1) = dbrow($stories);
		$series = dbquery("SELECT COUNT(seriesid) FROM ".TABLEPREFIX."fanfiction_series WHERE FIND_IN_SET('".$c['chalid']."', challenges) > 0");
		list($count2) = dbrow($series);
		$count = $count1 + $count2;
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_challenges SET  responses = '$count' WHERE chalid = $c[chalid]");
	}
	$output .= write_message(_ACTIONSUCCESSFUL);
}
if(isset($_POST['submit'])) {
	$anonchallenges = $_POST['anonchallenges'] == 1 ? 1 : 0;
	$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET anonchallenges = '$anonchallenges' WHERE sitekey = '".SITEKEY."'");
	if($result) $output .= write_message(_ACTIONSUCCESSFUL);
	else $output .= write_error(_ERROR);
}
else {
	$output .= "<div  id=\"settingsform\"><form method=\"POST\" style=\"margin: 1em auto;\" enctype=\"multipart/form-data\" action=\"admin.php?action=modules&amp;admin=true&amp;module=challenges\">";
	$output .= "<label for=\"anonchallenges\">"._ANONCHALLENGES.": </label><select name=\"anonchallenges\">
				<option value=\"1\"".($anonchallenges == "1" ? " selected" : "").">"._YES."</option>
				<option value=\"0\"".($anonchallenges == "0" ? " selected" : "").">"._NO."</option>
			</select><br />";
	$output .= "<div id='submitdiv'><INPUT type=\"submit\" id=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></div></form></form></div>";
	$output .= write_message("<a href='admin.php?action=modules&amp;admin=true&amp;module=challenges&amp;task=recalc'>"._RECALCCHAL."</a>");
}
?>
