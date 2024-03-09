<?php
$current = "challenges";
include ("../../header.php");

//make a new TemplatePower object
if(file_exists( "$skindir/default.tpl")) $tpl = new TemplatePower("$skindir/default.tpl" );
else $tpl = new TemplatePower(_BASEDIR."default_tpls/default.tpl");
$tpl->assignInclude( "header", "$skindir/header.tpl" );
$tpl->assignInclude( "footer", "$skindir/footer.tpl" );
include(_BASEDIR."includes/pagesetup.php");
include_once(_BASEDIR."languages/".$language."_admin.php");
if(file_exists("languages/".$language.".php")) include_once("languages/".$language.".php");
else include_once("languages/en.php");
if(!isADMIN) accessDenied( );
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
$table = dbassoc(dbquery("SHOW TABLES LIKE '".TABLEPREFIX."challenges'"));
if($table) $output .= write_message(_ALREADYINSTALLED);
else  {
if($confirm == "yes") {
	include("version.php");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ( 'include(_BASEDIR.\"modules/challenges/authorof.php\");', 'AO', 'challenges');");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ('include(_BASEDIR.\"modules/challenges/storyblock.php\");', 'storyblock', 'challenges');");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ('include(_BASEDIR.\"modules/challenges/seriesblock.php\");', 'seriesblock', 'challenges');");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ('include(_BASEDIR.\"modules/challenges/seriestitle.php\");', 'seriestitle', 'challenges');");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ('include(_BASEDIR.\"modules/challenges/adminfunctions.php\");', 'delchar', 'challenges');");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ('include(_BASEDIR.\"modules/challenges/adminfunctions.php\");', 'delcategory', 'challenges');");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ('include(_BASEDIR.\"modules/challenges/otherresults.php\");', 'otherresults', 'challenges');");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ( 'include(_BASEDIR.\"modules/challenges/deluser.php\");', 'deluser', 'challenges');");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ( 'include(_BASEDIR.\"modules/challenges/stats.php\");', 'sitestats', 'challenges');");
	dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_modules(`name`, `version`) VALUES('$moduleName', '$moduleVersion')");
	$proquery = dbquery("SELECT count(panel_id) FROM `".TABLEPREFIX."fanfiction_panels` WHERE panel_type = 'P' AND panel_hidden = '0'");
	list($profiles) = dbrow($proquery);
	$profiles++;
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_panels` (`panel_name`, `panel_title`, `panel_url`, `panel_level`, `panel_order`, `panel_hidden`, `panel_type`) VALUES ('challengesby', 'Challenges by {author}', 'modules/challenges/challengesby.php', 0, $profiles, 0, 'P');");
	$userquery = dbquery("SELECT count(panel_id) FROM `".TABLEPREFIX."fanfiction_panels` WHERE panel_type = 'U' AND panel_hidden = '0'");
	list($userpanels) = dbrow($userquery);
	$userpanels++;
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_panels` (`panel_name`, `panel_title`, `panel_url`, `panel_level`, `panel_order`, `panel_hidden`, `panel_type`) VALUES ('challengesby', 'Your Challenges', 'modules/challenges/challengesby.php', 0, $userpanels, 0, 'U');");
	$topquery = dbquery("SELECT count(panel_id) FROM `".TABLEPREFIX."fanfiction_panels` WHERE panel_type = 'L' AND panel_hidden = '0'");
	list($listpanels) = dbrow($topquery);
	$listpanels++;
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_panels` (`panel_name` , `panel_title` , `panel_url` , `panel_level` , `panel_order` , `panel_hidden` , `panel_type` ) VALUES ('challenges', 'Top 10 Challenges', 'modules/challenges/topchallenges.php', '0', $listpanels, 0, 'L');");
	$browsequery = dbquery("SELECT count(panel_id) FROM `".TABLEPREFIX."fanfiction_panels` WHERE panel_type = 'B' AND panel_hidden = '0'");
	list($browse) = dbrow($browsequery);
	$browse++;
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_panels` (`panel_name` , `panel_title` , `panel_url` , `panel_level` , `panel_order` , `panel_hidden` , `panel_type` ) VALUES ('challenges', 'Challenges', 'modules/challenges/browse.php', '0', $browse, 0, 'B');");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_pagelinks` (`link_name`, `link_text`, `link_url`, `link_target`, `link_access`) VALUES ('challenges', 'Challenges', 'browse.php?type=challenges', '0', 0);");
	dbquery("ALTER TABLE `".$settingsprefix."fanfiction_settings` ADD `anonchallenges` TINYINT( 1 ) NOT NULL");
	dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_stories` ADD `challenges` varchar(200) NOT NULL default '0'");
	$series = dbassoc(dbquery("SHOW COLUMNS FROM ".TABLEPREFIX."fanfiction_series LIKE 'challenges'"));
	if(!$series) dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_series` ADD `challenges` varchar(200) NOT NULL default '0'");
	dbquery("CREATE TABLE `".TABLEPREFIX."fanfiction_challenges` (
	  `chalid` int(11) NOT NULL auto_increment,
	  `challenger` varchar(200) NOT NULL default '',
	  `uid` int(11) NOT NULL default '0',
	  `title` varchar(250) NOT NULL default '',	
	  `catid` varchar(200) NOT NULL default '',
	  `characters` varchar(200) NOT NULL default '',
	  `summary` text NOT NULL,
	  `responses` int(11) NOT NULL default '0',
	  PRIMARY KEY  (`chalid`),
	  KEY `title` (`catid`),
	  KEY `uid` (`uid`),
	  KEY `title_2` (`title`),
	  KEY `characters` (`characters`)
	) ENGINE=MyISAM;");
	dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_modules`(`version`, `name`) VALUES('$moduleVersion', '$moduleName')");
	$output = write_message(_ACTIONSUCCESSFUL);
}
else if($confirm == "no") {
	$output = write_message(_ACTIONCANCELLED);
}
else {
	$output = write_message(_CONFIRMINSTALL."<br /><a href='install.php?confirm=yes'>"._YES."</a> "._OR." <a href='install.php?confirm=no'>"._NO."</a>");
}
}
$tpl->assign("output", $output);
$tpl->printToScreen( );
?>