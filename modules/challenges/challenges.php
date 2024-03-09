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
include ("../../header.php");

//make a new TemplatePower object
if(file_exists( "$skindir/default.tpl")) $tpl = new TemplatePower("$skindir/default.tpl" );
else $tpl = new TemplatePower(_BASEDIR."default_tpls/default.tpl");
if(file_exists("$skindir/listings.tpl")) $tpl->assignInclude( "listings", "$skindir/listings.tpl" );
else $tpl->assignInclude( "listings", _BASEDIR."default_tpls/listings.tpl" );
$tpl->assignInclude( "header", "$skindir/header.tpl" );
$tpl->assignInclude( "footer", "$skindir/footer.tpl" );
include(_BASEDIR."includes/pagesetup.php");

$chalid = isset($_GET['chalid']) ? $_GET['chalid'] : false;
if($chalid && !isNumber($chalid)) unset($chalid, $action);

// security check
	$admin = 0;
	if(!isset($anonchallenges)) accessDenied( );
	if(($action && ($action != "add" || !$anonchallenges)) && !isMEMBER) accessDenied( );
	if(isADMIN && uLEVEL < 3) $admin = 1;
	if(isADMIN && uLEVEL == 3) {
		if(isset($chalid)) {
			$challenge = dbquery("SELECT uid, catid from ".TABLEPREFIX."fanfiction_challenges WHERE chalid='$chalid' LIMIT 1");
			list($chaluid, $catid) = dbrow($challenge);
			if(uLEVEL == 3 && $admincats != 0) {
				$seriescats = explode(",", $catid);
				$adcats = explode(",", $admincats);
				foreach($seriescats as $cat) {
					if(in_array($cat, $adcats)) $admin = 1;
				}
			}
			if($chaluid != USERUID &&  $admin != 1 && uLEVEL > 2) accessDenied( );
		}
	}

// end security check

if($action == "remove") {
	if(!$chalid || (empty($seriesid) && empty($sid))) {
		$output .= write_error(_ERROR);
	}
	else {
		$output .= "<div id='pagetitle'>"._CHALLENGES."</div>";
		if(isset($seriesid)) {
			$chalinfo =dbquery("SELECT challenges FROM ".TABLEPREFIX."fanfiction_series WHERE seriesid = '$seriesid'");
			list($challenges) = dbrow($chalinfo);
			$challenges = explode(",", $challenges);
			$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET challenges = '".array_diff($challenges, explode(",", $chalid))."' WHERE seriesid = '$seriesid'");
		}
		else {
			$chalinfo = dbquery("SELECT challenges FROM ".TABLEPREFIX."fanfiction_stories WHERE sid = '$sid'");
			list($challenges) = dbrow($chalinfo);
			$challenges = explode(",", $challenges);
			$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET challenges = '".implode(",", array_diff($challenges, explode(",", $chalid)))."' WHERE sid = '$sid'");
		}
		if($result) dbquery("UPDATE ".TABLEPREFIX."fanfiction_challenges SET responses = responses - 1 WHERE chalid = '$chalid'");
		$output .= write_message(_ACTIONSUCCESSFUL);
	}
}

else if($action == "add" || $action == "edit") {

	if(!empty($_GET['cat']) && isNumber($_GET['cat'])) $cat = $_GET["cat"];
	else $cat = -1;
	$catid = isset($_POST['catid']) && isNumber($_POST['catid']) ? $_POST['catid'] : "";

	$output .= "<div id=\"pagetitle\">".($action == "edit" ? _EDITCHALLENGE : _ADDCHALLENGE)."</div>";
	if(isset($_POST['submit'])) {
		$title = escapestring(descript($_POST['title'], $allowed_tags));
		$summary = escapestring(replace_naughty(strip_tags(descript($_POST['summary']), $allowed_tags)));
		$challenger = escapestring(strip_tags(descript($_POST['challenger']), $allowed_tags));
		if(!$challenger) $challenger = _ANONYMOUS;
		$challengeruid = isset($_POST['challengeruid']) && isNumber($_POST['challengeruid']) ? $_POST['challengeruid'] : 0;
		$catid = isset($_POST['catid']) ? array_filter(explode(",", $_POST['catid']), "isNumber") : array( );
		$category = implode(",", $catid);
		if(!$category) $category = -1;
		$characters = isset($_POST['charid']) ? array_filter($_POST['charid'], "isNumber") : array( );
		$charid = implode(",", $characters);
		if(empty($title) || empty($summary)) {
			$output .= write_error(_REQUIREDINFO);
		}
		else if(find_naughty($title) || find_naughty($challenger)) {
			$output .= write_error(_NAUGHTYWORDS);
		}
		else if(!isMEMBER && $captcha && !captcha_confirm()) {
			$output .= write_error(_CAPTCHAFAIL);
			$tpl->assign("output", $output);
			$tpl->printToScreen( );
			exit( );
		}
		else {
			if($action != "edit") 
				$insert = "INSERT INTO ".TABLEPREFIX."fanfiction_challenges (title, summary, catid, characters, challenger, uid) VALUES('$title', '$summary', '$category', '$charid', '$challenger', '$challengeruid')";
			else 
				$insert = "UPDATE ".TABLEPREFIX."fanfiction_challenges SET title = '$title', summary = '$summary', challenger = '$challenger', catid = '$category', characters = '$charid' WHERE chalid = '$chalid'";
			dbquery($insert);
			$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'addchallenge'");
			while($code = dbassoc($codequery)) {
				eval($code['code_text']);
			}
			$output .= write_message(_ACTIONSUCCESSFUL."  <a href=\""._BASEDIR."browse.php?type=challenges\">"._BACK2PREVIOUS."</a>");
			$tpl->assign("output", $output);
			$tpl->printToScreen( );
			exit( );
		}		
	}
	if($action == "edit" && $chalid) {
		$challenge = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_challenges WHERE chalid='$chalid' LIMIT 1");
		list($chalid, $challenger, $chaluid, $chaltitle, $categories, $characters, $summary) = dbrow($challenge);
		$cat = -1;
		$catid = explode(",", $categories);
		$charids = explode(",", $characters);
	}
	else {
		$chaltitle = "";
		$summary = "";
		$categories = "";
	}
	$challenger = $action == "edit" ? $challenger : USERPENNAME;
	$chaluid = $action == "edit" ? $chaluid : USERUID;
	$output .= "<form METHOD=\"POST\" name=\"form\" action=\"challenges.php?action=$action".(isset($chalid) ? "&amp;chalid=$chalid" : "")."\">";
	$output .= "<div class=\"tblborder\" style=\"width: 550px; margin: 0 auto; padding: 5px;\">";
	$output .= "<label for=\"challenger\">"._NAME.":</label> ";
	if(isMEMBER) $output .= $challenger."<input type=\"hidden\" id=\"challenger\" name=\"challenger\" value=\"$challenger\"><input type=\"hidden\" name=\"challengeruid\" value=\"$chaluid\"><br />";
	else $output .= "<input  type=\"text\" class=\"textbox=\" name=\"challenger\" maxlength=\"200\" size=\"30\"> <font color=\"red\">*</font><br />";
	$output .= "<label for=\"title\">"._TITLE.":</label> <span class=\"required\">*</span> <input  type=\"text\" class=\"textbox=\" name=\"title\" id=\"title\" maxlength=\"200\" value=\"".htmlentities(stripslashes($chaltitle))."\" size=\"50\"><br />
		<label for=\"summary\">"._SUMMARY.":</label> <span class=\"required\">*</span><br /><textarea class=\"textbox\" rows=\"6\" id=\"summary\" name=\"summary\" cols=\"58\">".stripslashes($summary)."</textarea>";
	if($tinyMCE) 
		$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('summary');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
	if(!$multiplecats) $output .= "<input type=\"hidden\" name=\"catid\" id=\"catid\" value=\"1\">";
	else {
		include(_BASEDIR."includes/categories.php");
		$output .= "<input type=\"hidden\" name=\"formname\" value=\"challenges\">";
	}
	$output .= "<label for=\"charid\">"._CHARACTERS.":</label> <br /><select class=\"textbox\" size=\"8\" style=\"width: 100%;\" name=\"charid[]\" id=\"charid\" multiple>";
	foreach($charlist as $char => $info) {
			if($info['catid'] == -1) $output .= "<option value=\"$char\"".(isset($charids) && in_array($char, $charids) ? " selected" : "").">".$info['name']."</option>";
	}		
	$output .= "</select>";
	$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'challengeform'");
	while($code = dbassoc($codequery)) {
		eval($code['code_text']);
	}
	if(!isMEMBER && $captcha) $output .= "<div><span class=\"label\">"._CAPTCHANOTE."</span><input MAXLENGTH=5 SIZE=5 name=\"userdigit\" type=\"text\" value=\"\"><br /><img width=120 height=30 src=\""._BASEDIR."includes/button.php\" style=\"border: 1px solid #111;\">";
	$output .= "<div style=\"text-align: center; margin: 1em;\"><input type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></div></div></form>";
}

else if($action == "delete") {

	if($chalid)
	{

		$confirmed = isset($_GET["confirmed"]) ? $_GET["confirmed"] : false;
	
		$output .= "<div id=\"pagetitle\">"._DELETECHALLENGE."</div>";
		if(!$admin) {
			list($uid) = dbrow(dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_challenges WHERE chalid = '$chalid' LIMIT 1"));
			if(USERUID != $uid) $output .=  write_error(_NOTAUTHORIZED);
		}	
		if($confirmed == "no") {
			$output .= write_message(_ACTIONCANCELLED."  <a href=\""._BASEDIR."browse.php?type=challenges\">"._BACK2PREVIOUS."</a>");
		}
		else if($confirmed == "yes" && $chalid) {
			dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_challenges WHERE chalid = '$chalid'");
			$responses = dbquery("SELECT challenges, sid FROM  ".TABLEPREFIX."fanfiction_stories WHERE FIND_IN_SET($chalid, challenges) > 0");
			while($response = dbassoc($responses)) {
				$challenges = explode(",", $response[challenges]);
				if(is_array($challenges)) 
					foreach($challenges as $key=>$value) { if($value == $chalid) unset($challenges[$key]); }
				dbquery("UPDATE  ".TABLEPREFIX."fanfiction_stories SET challenges = '".implode(",", $challenges)."' WHERE sid = $response[sid] LIMIT 1");
			}
			$output .=  write_message(_ACTIONSUCCESSFUL."  <a href=\"challenges.php\">"._BACK2PREVIOUS."</a>");
		}
		else {
			$output .= write_message(_CONFIRMDELETE."<br /><br />[ <a href=\"challenges.php?action=delete&amp;confirmed=yes&amp;chalid=$chalid\">"._YES."</a> | 
				<a href=\"challenges.php?action=delete&amp;confirmed=no\">"._NO."</a> ]");
		}
	}
}

else if($action == "respond") {

	if(!isset($chalid)) {
		$output .= write_error(_ERROR);
	}
	if(isset($_POST['submit'])) {
		$count = 0;
		if(isset($_POST["sid"]) && is_array($_POST["sid"])) {
			foreach($_POST["sid"] as $story) {
				if(isNumber($story))
				{
					$result = dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET challenges = CONCAT('$chalid', ',', challenges) WHERE sid = '$story'");
					$count++;
				}
			}
		}
		if(isset($_POST['seriesid']) && is_array($_POST["seriesid"])) {
			foreach($_POST["seriesid"] as $series) {
				if(isNumber($series))
				{
					$result2 = dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET challenges = CONCAT(challenges, ',' '$chalid') WHERE seriesid = '$series'");
					$count++;
				}
			}
		}
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_challenges SET responses = responses + $count WHERE chalid = '$chalid'");
		if($count) $output .= write_message(_ACTIONSUCCESSFUL." <a href='"._BASEDIR."browse.php?type=challenges'>"._BACK2CHALLENGES."</a>");
	}
	else {
		$challenge = dbquery("SELECT title, challenger, uid FROM ".TABLEPREFIX."fanfiction_challenges WHERE chalid = '$chalid' LIMIT 1");
		list($title, $challenger, $challengeruid) = dbrow($challenge);
		$output .= "<div id=\"pagetitle\">$title "._BY." ".($challengeruid ? "<a href=\""._BASEDIR."viewuser.php?uid=$challengeruid\">$challenger</a>" : "$challenger")."</div>";
		$output .= "<form METHOD=\"POST\" name=\"form\" action=\"challenges.php?action=respond&amp;chalid=$chalid\">";
		$output .= "<input type=\"hidden\" name=\"chalid\" value=\"$chalid\">";
		if(($admin) && isset($_GET['stories']) && $_GET['stories'] == "others") {
			if($let == _OTHER) $letter = _PENNAMEFIELD." REGEXP '^[^a-z]'";	
			else if($let) $letter = _PENNAMEFIELD." LIKE '$let%'";
			$pagelink = _BASEDIR."modules/challenges/challenges.php?action=respond&amp;stories=others&amp;chalid=$chalid&amp;".($let ? "let=$let&amp;" : "");
			$authorlink = "<a href=\""._BASEDIR."modules/challenges/challenges.php?action=respond&amp;chalid=$chalid&amp;stories=";
			$countquery = "SELECT count(distinct uid) FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE stories > 0 ".(isset($letter) ? " AND $letter" : "");
			$authorquery = "SELECT ap.stories, "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM "._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_authorprefs as ap WHERE ap.uid = author.uid AND ap.stories > 0 ".(isset($letter) ? " AND $letter" : "");
			include(_BASEDIR."includes/members_list.php");
		}
		else {
			$authuid = isset($_GET["stories"]) && isNumber($_GET['stories']) ? $_GET["stories"] : USERUID;
			$stories = dbquery("SELECT stories.title, stories.sid FROM ".TABLEPREFIX."fanfiction_stories as stories LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors as coauth ON coauth.sid = stories.sid WHERE stories.validated > 0 AND (stories.uid = '$authuid' OR coauth.uid = '$authuid')");

			if($admin) $output .= "<div style=\"text-align: center; margin-bottom: 1em;\"><a href=\"challenges.php?action=respond&amp;stories=others&amp;chalid=$chalid\">"._CHOOSEAUTHOR."</a></div>";
			$output .= "<table class=\"tblborder\" style=\"width: 500px; margin: 0 auto;\"><tr><th class=\"tblborder\">"._STORIES."</th></tr>";
			$numstories = 0;
			while($story = dbassoc($stories)) {
				$output .= "<tr><td class=\"tblborder\"><input type=\"checkbox\" class=\"checkbox\" value=\"$story[sid]\" name=\"sid[]\">".stripslashes($story['title'])."</td></tr>";
				$numstories++;
			}
			if($numstories == 0) $output .= "<tr><td align=\"center\">"._NORESULTS."</td></tr>";
			$series2 = dbquery("SELECT title, seriesid FROM ".TABLEPREFIX."fanfiction_series WHERE uid = '$authuid' AND FIND_IN_SET($chalid, challenges) < 1 ORDER BY title ASC");
			$output .= "<tr><th class=\"tblborder\">"._SERIES."</th></tr>";
			$numseries = 0;
			while($series = dbassoc($series2)) {
				$output .= "<tr><td><input type=\"checkbox\" class=\"checkbox\" value=\"$series[seriesid]\" name=\"seriesid[]\">".stripslashes($series['title'])."</td></tr>";
				$numseries++;
			}
			if($numseries < 1) $output .= "<tr><td align=\"center\">"._NORESULTS."</td></tr>";
			$output .="</table><div style=\"text-align: center; margin: 1em;\"><input type=\"submit\" class=\"button\" name=\"submit\" value=\"submit\"></div></form>";
		}
	}
}
else {

	$catid = isset($_GET['catid']) ? $_GET['catid'] : false;	
	if($chalid) {
		if(file_exists("./$skindir/challenges_title.tpl")) $challenges = new TemplatePower("./$skindir/challenges_title.tpl");
		else $challenges = new TemplatePower(_BASEDIR."modules/challenges/default_tpls/challenges_title.tpl");
		$challenges->prepare( );

		$challenge = dbquery("SELECT * FROM  ".TABLEPREFIX."fanfiction_challenges WHERE chalid = '$chalid' LIMIT 1");
		list($chalid, $challenger, $uid, $title, $cat, $chars, $summary, $responses) = dbrow($challenge);
		$challenger = stripslashes($challenger);
		$title = stripslashes($title);
		$summary= stripslashes($summary);

		$challenges->newBlock("titleblock");
		if(isADMIN || USERUID == $uid) $challenges->assign("adminoptions", "<div class=\"adminoptions\">".($admin ? _ADMINOPTIONS : _OPTIONS).": [<a href=\"challenges.php?action=edit&amp;chalid=$chalid\">"._EDIT."</a>] [<a href=\"challenges.php?action=delete&amp;chalid=$chalid\">"._DELETE."</a>]</div>");
		$challenges->assign("author", ($uid ? "<a href=\""._BASEDIR."viewuser.php?uid=$uid\">$challenger</a>" : "$challenger"));
		$challenges->assign("title", $title);
		$challenges->assign("summary",$summary);
		$challenges->assign("skindir", $skindir);
		$challenges->assign("characters", $chars ? charlist($chars) : _NONE);
		$challenges->assign("category", $cat > 0 ? catlist($cat) : _NONE);
		$challenges->assign("respond", "<div class=\"respond\"><a href=\"challenges.php?action=respond&amp;chalid=$chalid\">"._RESPOND2CHALLENGE."</a></div>");
		$challenges->assign("reportthis", "[<a href=\""._BASEDIR."contact.php?action=report&amp;url=modules/challenges/challenges.php?chalid=".$chalid."\">"._REPORTTHIS."</a>]");
		$output .= $challenges->getOutputContent( );
		$tpl->gotoBlock( "_ROOT" );
		$tpl->newBlock("listings");
		list($scount) = dbrow(dbquery(_SERIESCOUNT." WHERE FIND_IN_SET($chalid, challenges) > 0"));
		$count = 0;
		if($scount > $offset) {
			$seriesquery = dbquery(_SERIESQUERY." AND FIND_IN_SET($chalid, series.challenges) > 0 LIMIT $offset, $itemsperpage");
			while($stories = dbassoc($seriesquery)) {
				include(_BASEDIR."includes/seriesblock.php");
			}
		}
		if($scount - $offset < $itemsperpage) {
			$storyoffset = $offset - $scount > 0 ? $offset - $scount : 0;
			$remainder = $itemsperpage - $count;
			list($scount2) = dbrow(dbquery(_STORYCOUNT." AND FIND_IN_SET($chalid, challenges) > 0"));
			if($scount2 > 0) {
				$storyquery = _STORYQUERY." AND FIND_IN_SET($chalid, stories.challenges) > 0";
				$storyquery .= " LIMIT $offset, $remainder";
				$storyresults = dbquery($storyquery);
				while($stories = dbassoc($storyresults)) {
					$tpl->newBlock("storyblock");
					include(_BASEDIR."includes/storyblock.php"); 
				}
			}
		}
		if($scount + $scount2 > $itemsperpage) {
			$tpl->gotoBlock( "listings" );
			$tpl->assign( "pagelinks", build_pagelinks("challenges.php?chalid=$chalid&amp;", $scount + $scount2, $offset));
		}
		$tpl->gotoBlock( "_ROOT" );
	}
}

$tpl->assign("output", $output);
$tpl->printToScreen( );
?>