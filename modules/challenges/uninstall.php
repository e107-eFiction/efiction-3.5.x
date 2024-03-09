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
if(!isADMIN) accessDenied( );
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
if($confirm == "yes") {
	dbquery("DELETE FROM `".TABLEPREFIX."fanfiction_codeblocks` WHERE code_module = 'challenges'");
	dbquery("DELETE FROM `".TABLEPREFIX."fanfiction_panels` WHERE panel_name LIKE 'challenges%'");
	dbquery("DELETE FROM `".TABLEPREFIX."fanfiction_pagelinks` WHERE link_name LIKE 'challenges%'");
	dbquery("ALTER TABLE `".$settingsprefix."fanfiction_settings` DROP `anonchallenges`");
	dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_stories` DROP `challenges`");
	dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_series` DROP `challenges`");
	dbquery("DROP TABLE `".TABLEPREFIX."fanfiction_challenges`");
	include("version.php");
	dbquery("DELETE FROM `".TABLEPREFIX."fanfiction_modules` WHERE name = '$moduleName'");	
	$output = write_message(_ACTIONSUCCESSFUL);
}
else if($confirm == "no") {
	$output = write_message(_ACTIONCANCELLED);
}
else {
	$chalquery = dbquery("SHOW COLUMNS FROM ".$settingsprefix."fanfiction_settings LIKE 'anonchallenges'");
	if(dbnumrows($chalquery)) $output = write_message(_CONFIRMUNINSTALL." "._UNINSTALLWARNING."<br /><a href='uninstall.php?confirm=yes'>"._YES."</a> "._OR." <a href='uninstall.php?confirm=no'>"._NO."</a>");
	else $output .= write_message(_MODNOTINSTALLED);
}
$tpl->assign("output", $output);
$tpl->printToScreen( );
?>