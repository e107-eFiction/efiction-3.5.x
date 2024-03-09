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
include("version.php");
list($currentVersion) = dbrow(dbquery("SELECT version FROM ".TABLEPREFIX."fanfiction_modules WHERE name = 'Challenges' LIMIT 1"));
$currentVersion = explode(".", $currentVersion);
if(empty($currentVersion ) || $currentVersion[1] < 4) {
if($confirm == "yes") {
	if(empty($currentVersion) || $currentVersion[1] == 0) {
		dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks` (`code_text`, `code_type`, `code_module`) VALUES ( 'include(_BASEDIR.\"modules/challenges/deluser.php\");', 'deluser', 'challenges');");
	}
	else if($currentVersion[1] < 1) {
		dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks` (`code_text`, `code_type`, `code_module`) VALUES('include(_BASEDIR.\"modules/challenges/stats.php\");', 'sitestats', 'challenges');");
	}
	else if($currentVersion[1] < 3) {
		dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_name = 'challenges' AND panel_type = 'A' LIMIT 1");
	}
	$statsOkay = dbquery("SELECT code_id FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_module = 'challenges' AND code_type = 'stats'");
	if(dbnumrows($statsOkay) == 0) dbquery("INSERT INTO `".TABLEPREFIX."fanfiction_codeblocks`(`code_text`, `code_type`, `code_module`) VALUES ( 'include(_BASEDIR.\"modules/challenges/stats.php\");', 'sitestats', 'challenges');");
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_modules SET version = '1.4' WHERE name = 'Challenges' LIMIT 1");
	$output = write_message(_ACTIONSUCCESSFUL);
}
else if($confirm == "no") {
	$output = write_message(_ACTIONCANCELLED);
}
else {
	$output = write_message(_CONFIRMUPDATE."<br /><a href='update.php?confirm=yes'>"._YES."</a> "._OR." <a href='update.php?confirm=no'>"._NO."</a>");
}
}
else $output .= write_message(_ALREADYUPDATED);
$tpl->assign("output", $output);
$tpl->printToScreen( );
?>