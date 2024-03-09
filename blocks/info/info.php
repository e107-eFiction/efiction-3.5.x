<?php
if(!defined("_CHARSET")) exit( );

if(file_exists(_BASEDIR."blocks/info/{$language}.php")) include_once(_BASEDIR."blocks/info/{$language}.php");
else include_once(_BASEDIR."blocks/info/en.php");
	global $noskin, $tpl;

	if(_AUTHORTABLE != TABLEPREFIX."fanfiction_authors") {
		list($members) = dbrow(dbquery("SELECT COUNT("._UIDFIELD.") as members FROM "._AUTHORTABLE));
		list($newest) = dbrow(dbquery("SELECT "._UIDFIELD." as uid FROM "._AUTHORTABLE." ORDER BY "._UIDFIELD." DESC LIMIT 1"));
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_stats SET members = $members, newestmember = '$newest' WHERE sitekey = '".SITEKEY."' LIMIT 1");
	}
	$stats = dbassoc(dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_stats WHERE sitekey = '".SITEKEY."' LIMIT 1"));
	list($newmember) = dbrow(dbquery("SELECT "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '".$stats['newestmember']."' LIMIT 1"));
	$adminnotices = "";
	if(isADMIN) {
		if(file_exists(_BASEDIR."languages/".$language."_admin.php")) include_once(_BASEDIR."languages/".$language."_admin.php");
		else include_once(_BASEDIR."languages/en_admin.php");
		$countquery = dbquery("SELECT COUNT(DISTINCT chapid) FROM ".TABLEPREFIX."fanfiction_chapters WHERE validated = '0'");
		list($count) = dbrow($countquery);
		if($count) $adminnotices = sprintf(_QUEUECOUNT, $count);
		$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'adminnotices'");
		while($code = dbassoc($codequery)) {
				eval($code['code_text']);
		}
	}
	if(!empty($blocks['info']['style']) && $blocks['info']['style'] == 2) {
		$tpl->assignGlobal("totalstories", $stats['stories']);
		$tpl->assignGlobal("totalauthors", $stats['authors']);
		$tpl->assignGlobal("totalmembers", $stats['members']);
		$tpl->assignGlobal("totalreviews", $stats['reviews']);
		$tpl->assignGlobal("totalreviewers", $stats['reviewers']);	
		$tpl->assignGlobal("totalwords", $stats['wordcount']);
		$tpl->assignGlobal("totalchapters", $stats['chapters']);
		$tpl->assignGlobal("totalseries", $stats['series']);
		$tpl->assignGlobal("newestmember", "<a href=\""._BASEDIR."viewuser.php?uid=".$stats['newestmember']."\">$newmember</a>");
		if(isMEMBER) $tpl->assignGlobal("loggedinas", _LOGGEDINAS." ".USERPENNAME.". ".($noskin ? " "._NOSKIN : ""));
		$tpl->assignGlobal("submissions", $adminnotices);
	}
	else {
		if(isMEMBER) $loggedinas = _LOGGEDINAS." ".USERPENNAME.". ".($noskin ? " "._NOSKIN : "");
		else $loggedinas = "";
		if(empty($blocks['info']['style'])) {
			$content = "<div id='infoblock'>
<div><span class='label'>"._MEMBERS.": </span>".$stats['members']."</div>
<div><span class='label'>"._SERIES.": </span>".$stats['series']."</div>
<div><span class='label'>"._STORIES.": </span>".$stats['stories']."</div>
<div><span class='label'>"._CHAPTERS.": </span>".$stats['chapters']."</div>
<div><span class='label'>"._WORDCOUNT.": </span>".$stats['wordcount']."</div>
<div><span class='label'>"._AUTHORS.": </span>".$stats['authors']."</div>
<div><span class='label'>"._REVIEWS.": </span>".$stats['reviews']."</div>
<div><span class='label'>"._REVIEWERS.": </span>".$stats['reviewers']."</div>
<div><span class='label'>"._NEWESTMEMBER.": </span><a href=\""._BASEDIR."viewuser.php?uid=".$stats['newestmember']."\">$newmember</a></div>";
		}
		else if($blocks["info"]["style"] == 1) {
			$replace = array($stats['authors'], $stats['members'], $stats['reviews'], $stats['reviewers'], $stats['wordcount'], $stats['chapters'], $stats['stories'], $stats['series'],"<a href=\""._BASEDIR."viewuser.php?uid=".$stats['newestmember']."\">$newmember</a>", $loggedinas, $adminnotices);
			$search = array("@\{authors\}@", "@\{members\}@", "@\{reviews\}@", "@{reviewers\}@", "@\{totalwords\}@","@\{chapters\}@", "@\{stories\}@", "@\{series\}@", "@\{newest\}@", "@\{loggedinas\}@", "@\{submissions\}@");
			$content = preg_replace($search, $replace, (!empty($blocks['info']['template']) ? stripslashes($blocks['info']['template']) : _NARTEXT));
		}
	}
	$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'sitestats'");
	while($code = dbassoc($codequery)) {
			eval($code['code_text']);
	}
	if(empty($blocks['info']['style'])) $content .= "<div>$adminnotices</div><div class='cleaner'>&nbsp;</div></div>";
?>