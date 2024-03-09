<?php
// ----------------------------------------------------------------------
// eFiction 3.0
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
$current = "update";

include("header.php");

$blocks['news']['status'] = 0;
$blocks['info']['status'] = 0;

//make a new TemplatePower object
if (file_exists("$skindir/default.tpl")) $tpl = new TemplatePower("$skindir/default.tpl");
else $tpl = new TemplatePower("default_tpls/default.tpl");
include("includes/pagesetup.php");
if (file_exists("languages/" . $language . "_admin.php")) include_once("languages/" . $language . "_admin.php");
else include_once("languages/en_admin.php");
// end basic page setup

if (!isADMIN)
{
	$output .= "<script language=\"javascript\" type=\"text/javascript\">
location = \"maintenance.php\";
</script>";
	$tpl->assign("output", $output);
	$tpl->printToScreen();
	dbclose();
	exit();
}
$oldVersion = explode(".", $settings['version']);

if ($oldVersion[0] <= 3 && $oldVersion[1] <= 5 && $oldVersion[2] < 5)
{
	header("Location: update355.php");
	exit();
}

$fanfiction_poll_exists = 0;  /* poll tables maybe be not installed */
$fanfiction_poll_table = TABLEPREFIX . "fanfiction_poll";
$fanfiction_poll_exists =  dbassoc(dbquery("
			SELECT *
			FROM information_schema.tables
			WHERE table_schema = '{$dbname}'
				AND table_name = '{$fanfiction_poll_table}'
			;"));

$set_355 = do_version_check_355();
if($set_355) {
	dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET version = '3.5.5' WHERE sitekey = '" . SITEKEY . "'");
	$settings['version'] = '3.5.5';
}
$oldVersion = explode(".", $settings['version']);

$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
$oldconfirm = isset($_GET['oldconfirm']) ? $_GET['oldconfirm'] : false;

if ($oldVersion[0] == 3 && ($oldVersion[1] < 5 || $oldVersion[2] < 3))
{
	write_message("This version is working only for 3.5.5 database. If you have only database, there is way how to do it.");
	exit;
}



if ($oldVersion[0] == 3 && ($oldVersion[1] < 5 || $oldVersion[2] < 6))  //3.5.5
{
	if ($confirm == "yes")
	{
		if ($oldVersion[0] == 3 &&  $oldVersion[1] == 5 && $oldVersion[2] < 6)
		{
			/****************************************************************************************************************/
			$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_authors LIKE 'date'"));
			if ($tmp['Type'] == "datetime")
			{ 
				$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_authors LIKE 'date_tmp'"));
				if (!$updated)
				{
					dbquery("ALTER TABLE 	`" . TABLEPREFIX . "fanfiction_authors` ADD `date_tmp` int(10) unsigned NOT NULL default '0' ");
				}

				dbquery("UPDATE 		`" . TABLEPREFIX . "fanfiction_authors` SET `date_tmp` = UNIX_TIMESTAMP( `date` )");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_authors` CHANGE `date` `date` INT NOT NULL");
				dbquery("UPDATE `" . TABLEPREFIX . "fanfiction_authors` set date = date_tmp");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_authors` DROP `date_tmp`");
			}
			/****************************************************************************************************************/
			$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_log LIKE 'log_timestamp'"));

			if ($tmp['Type'] == "timestamp")
			{ 
				$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_log LIKE 'log_timestamp_tmp'"));
				if (!$updated)
				{
					dbquery("ALTER TABLE 	`" . TABLEPREFIX . "fanfiction_log` ADD `log_timestamp_tmp` int(10) unsigned NOT NULL default '0' ");
				}

				dbquery("UPDATE 		`" . TABLEPREFIX . "fanfiction_log` SET `log_timestamp_tmp` = UNIX_TIMESTAMP( `log_timestamp` )");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_log` CHANGE `log_timestamp` `log_timestamp` INT NOT NULL");
				dbquery("UPDATE `" . TABLEPREFIX . "fanfiction_log` set log_timestamp = log_timestamp_tmp");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_log` DROP `log_timestamp_tmp`");
			}
			/****************************************************************************************************************/
			/* fanfiction_comments   `time` datetime NOT NULL default '0000-00-00 00:00:00'  */
			$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_comments LIKE 'time'"));
			if ($tmp['Type'] == "datetime")
			{
				$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_comments LIKE 'time_tmp'"));
				if (!$updated)
				{
					dbquery("ALTER TABLE 	`" . TABLEPREFIX . "fanfiction_comments` ADD `time_tmp` int(10) unsigned NOT NULL default '0' ");
				}

				dbquery("UPDATE 		`" . TABLEPREFIX . "fanfiction_comments` SET `time_tmp` = UNIX_TIMESTAMP( `time` )");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_comments` CHANGE `time` `time` INT NOT NULL");
				dbquery("UPDATE `" . TABLEPREFIX . "fanfiction_comments` set time = time_tmp");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_comments` DROP `time_tmp`");
			}
			/****************************************************************************************************************/
			/* fanfiction_reviews   `date` datetime NOT NULL default '0000-00-00 00:00:00', */
			$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_reviews LIKE 'date'"));
			if ($tmp['Type'] == "datetime")
			{
				$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_reviews LIKE 'date_tmp'"));
				if (!$updated)
				{
					dbquery("ALTER TABLE 	`" . TABLEPREFIX . "fanfiction_reviews` ADD `date_tmp` int(10) unsigned NOT NULL default '0' ");
				}

				dbquery("UPDATE 		`" . TABLEPREFIX . "fanfiction_reviews` SET `date_tmp` = UNIX_TIMESTAMP( `date` )");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_reviews` CHANGE `date` `date` INT NOT NULL");
				dbquery("UPDATE `" . TABLEPREFIX . "fanfiction_reviews` set date = date_tmp");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_reviews` DROP `date_tmp`");
			}
			/****************************************************************************************************************/
			/* fanfiction_stories `date` datetime NOT NULL default '0000-00-00 00:00:00',   */
			$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_stories LIKE 'date'"));
			if($tmp['Type'] == "datetime") {
				$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_stories LIKE 'date_tmp'"));
				if (!$updated)
				{
					dbquery("ALTER TABLE 	`" . TABLEPREFIX . "fanfiction_stories` ADD `date_tmp` int(10) unsigned NOT NULL default '0' ");
				}

				dbquery("UPDATE 		`" . TABLEPREFIX . "fanfiction_stories` SET `date_tmp` = UNIX_TIMESTAMP( `date` )");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_stories` CHANGE `date` `date` INT NOT NULL");
				dbquery("UPDATE `" . TABLEPREFIX . "fanfiction_stories` set date = date_tmp");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_stories` DROP `date_tmp`");
			}

			/****************************************************************************************************************/
			/* fanfiction_stories `updated` datetime NOT NULL default '0000-00-00 00:00:00', */
			$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_stories LIKE 'updated'"));
			if ($tmp['Type'] == "datetime")
			{				
				$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_stories LIKE 'updated_tmp'"));
				if (!$updated)
				{
					dbquery("ALTER TABLE 	`" . TABLEPREFIX . "fanfiction_stories` ADD `updated_tmp` int(10) unsigned NOT NULL default '0' ");
				}

				dbquery("UPDATE 		`" . TABLEPREFIX . "fanfiction_stories` SET `updated_tmp` = UNIX_TIMESTAMP( `updated` )");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_stories` CHANGE `updated` `updated` INT NOT NULL");
				dbquery("UPDATE `" . TABLEPREFIX . "fanfiction_stories` set updated = updated_tmp");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_stories` DROP `updated_tmp`");
			}

			/****************************************************************************************************************/
			/* fanfiction_news `time` datetime default NULL,  */
			$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_news LIKE 'time'"));
			
			if ($tmp['Type'] == "datetime")
			{		
				$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_news LIKE 'time_tmp'"));
				if (!$updated)
				{
					dbquery("ALTER TABLE 	`" . TABLEPREFIX . "fanfiction_news` ADD `time_tmp` int(10) unsigned NOT NULL default '0' ");
				}

				dbquery("UPDATE 		`" . TABLEPREFIX . "fanfiction_news` SET `time_tmp` = UNIX_TIMESTAMP( `time` )");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_news` CHANGE `time` `time` INT NOT NULL");
				dbquery("UPDATE `" . TABLEPREFIX . "fanfiction_news` set time = time_tmp");
				dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_news` DROP `time_tmp`");
			}

			/****************************************************************************************************************/
			if ($fanfiction_poll_exists)
			{
				$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_poll LIKE 'poll_start'"));

				if ($tmp['Type'] == "datetime")
				{
					$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_poll LIKE 'poll_start_tmp'"));
					if (!$updated)
					{
						dbquery("ALTER TABLE 	`" . TABLEPREFIX . "fanfiction_poll` ADD `poll_start_tmp` int(10) unsigned NOT NULL default '0' ");
					}

					dbquery("UPDATE 		`" . TABLEPREFIX . "fanfiction_poll` SET `poll_start_tmp` = UNIX_TIMESTAMP( `poll_start` )");
					dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_poll` CHANGE `poll_start` `poll_start` INT NOT NULL");
					dbquery("UPDATE `" . TABLEPREFIX . "fanfiction_poll` set poll_start = poll_start_tmp");
					dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_poll` DROP `poll_start_tmp`");
				}

				$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_poll LIKE 'poll_end'"));

				if ($tmp['Type'] == "datetime")
				{
					$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_poll LIKE 'poll_end_tmp'"));
					if (!$updated)
					{
						dbquery("ALTER TABLE 	`" . TABLEPREFIX . "fanfiction_poll` ADD `poll_end_tmp` int(10) unsigned NOT NULL default '0' ");
					}

					dbquery("UPDATE 		`" . TABLEPREFIX . "fanfiction_poll` SET `poll_end_tmp` = UNIX_TIMESTAMP( `poll_end` )");
					dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_poll` CHANGE `poll_end` `poll_end` INT NOT NULL");
					dbquery("UPDATE `" . TABLEPREFIX . "fanfiction_poll` set poll_end = poll_end_tmp");
					dbquery("ALTER TABLE `" . TABLEPREFIX . "fanfiction_poll` DROP `poll_end_tmp`");
				}

			}	

		}
	
		$update = dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET version = '" . $version . "' WHERE sitekey = '" . SITEKEY . "'");
		if ($update) $output .= write_message(_ACTIONSUCCESSFUL);
		else $output .= write_error(_ERROR);
	}
	else if ($confirm == "no")
	{
		$output .= write_message(_ACTIONCANCELLED);
	}
	else
	{
		if ($oldVersion[0] == 3 && ($oldVersion[1] < 4 || $oldVersion[1] == 4 && (!isset($oldVersion[2]) || $oldVersion[2] < 6)))
			$output .= write_message(_CONFIRMUPDATE . "<br /> <a href='update.php?confirm=yes'>" . _YES . "</a> " . _OR . " <a href='update.php?confirm=no'>" . _NO . "</a>");
		else $output .= write_message("Are you ready to update? <a href='update.php?confirm=yes'>" . _YES . "</a> " . _OR . " <a href='update.php?confirm=no'>" . _NO . "</a>");
	}
}
else $output .= write_message(_ALREADYUPDATED);

/* until database is fully fixed, not update efiction version */
function do_version_check_355() {
	global $fanfiction_poll_exists;

	$check_355 = false;
 
	/* do double check or return version back if it is needed */
	$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_authors LIKE 'date'"));
	if ($tmp['Type'] == "datetime")
	{
		$check_355 = true;
		return $check_355;
	}
	$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_log LIKE 'log_timestamp'"));

	if ($tmp['Type'] == "timestamp")
	{
		$check_355 = true;
		return $check_355;
	}

	$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_comments LIKE 'time'"));
	if ($tmp['Type'] == "datetime")
	{
		$check_355 = true;
		return $check_355;
	}

	$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_reviews LIKE 'date'"));
	if ($tmp['Type'] == "datetime")
	{
		$check_355 = true;
		return $check_355;
	}

	$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_stories LIKE 'date'"));
	if ($tmp['Type'] == "datetime")
	{
		$check_355 = true;
		return $check_355;
	}

	$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_stories LIKE 'updated'"));
	if ($tmp['Type'] == "datetime")
	{
		$check_355 = true;
		return $check_355;
	}

	$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_news LIKE 'time'"));

	if ($tmp['Type'] == "datetime")
	{
			$check_355 = true;
			return $check_355;
	}

	if ($fanfiction_poll_exists)
	{
		$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_poll LIKE 'poll_start'"));

		if ($tmp['Type'] == "datetime")
		{
			$update = dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET version = '3.5.5' WHERE sitekey = '" . SITEKEY . "'");
		}


		$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_poll LIKE 'poll_end'"));

		if ($tmp['Type'] == "datetime")
		{
			$update = dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET version = '3.5.5' WHERE sitekey = '" . SITEKEY . "'");
		}
	}
	

	return $check_355;
}
 


$tpl->assign("output", $output);
$tpl->printToScreen();
dbclose();
