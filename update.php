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
$set_355 = false;
 
$set_355 = do_version_check_355();
 
if($set_355) {
	$output .= write_message("Table <b>" . $set_355 . "</b> needs a update.");

	dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET version = '3.5.5' WHERE sitekey = '" . SITEKEY . "'");

	$settings['version'] = '3.5.5';
}
else {
	$set_356 = do_version_check_356();
 
	if ($set_356)
	{
		$output .= write_message("Table <b>" . $set_356 . "</b> needs a update.");
		dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET version = '3.5.6' WHERE sitekey = '" . SITEKEY . "'");
		$settings['version'] = '3.5.6';
	}
}

$oldVersion = explode(".", $settings['version']);
 
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
 
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

			// List of DB tables (key) and field (value) which need changing to accommodate datetime field
			$date_upgrade = array(
				'fanfiction_authors' => 'date',
				'fanfiction_comments'  => 'time',
				'fanfiction_reviews' => 'date',
				'fanfiction_stories'  => 'date',
				'fanfiction_stories' => 'updated',
				'fanfiction_news' => 'time',
				'fanfiction_poll' => 'poll_start',
				'fanfiction_poll' => 'poll_end',
			);

			// Tables where IP address field needs updating to accommodate IPV6
			// Set to varchar(45) - just in case something uses the IPV4 subnet (see http://en.wikipedia.org/wiki/IPV6#Notation)
			foreach ($date_upgrade as $t => $f)
			{
				if (isTable($t))
				{

					// Check for table - might add some core plugin tables in here
					if ($field_info =  dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . $t . " LIKE '{$f}'")))
					{ 
						if (strtolower($field_info['Type']) == 'datetime')
						{

							$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . $t . " LIKE '{$f}_tmp'"));
							if (!$updated)
							{
								dbquery("ALTER TABLE `" . TABLEPREFIX . $t .  "` ADD `{$f}_tmp` int(10) unsigned NOT NULL default '0' ");
							}

							dbquery("UPDATE `" . TABLEPREFIX . $t .  "` SET `{$f}_tmp` = UNIX_TIMESTAMP( `{$f}` )");
							dbquery("ALTER TABLE `" . TABLEPREFIX . $t .  "` CHANGE `{$f}` `{$f}` INT NOT NULL"); 
							dbquery("UPDATE  `" . TABLEPREFIX . $t .  "` set {$f} = {$f}_tmp");
							dbquery("ALTER TABLE  `" . TABLEPREFIX . $t .  "` DROP `{$f}_tmp`");
						}
					}
				}
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

			$set_355 = false;
			$set_355 = do_version_check_355();
			if($set_355) {
				$output .= write_error(_ERROR);
			}
			else {
				$update = dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET version = '" . $version . "' WHERE sitekey = '" . SITEKEY . "'");
				if ($update) $output .= write_message(_ACTIONSUCCESSFUL);
			}
 
		}
	

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
elseif ($oldVersion[0] == 3 && ($oldVersion[1] < 5 || $oldVersion[2] < 7))  //3.5.6
{
	if ($confirm == "yes")
	{
		if ($oldVersion[0] == 3 &&  $oldVersion[1] == 5 && $oldVersion[2] < 7)
		{
			// List of DB tables (key) and field (value) which need changing to accommodate IPV6 addresses
			$ip_upgrade = array(
				'fanfiction_log' 	=> 'log_ip',
				'fanfiction_online' => 'online_ip'
			);

			// Tables where IP address field needs updating to accommodate IPV6
			// Set to varchar(45) - just in case something uses the IPV4 subnet (see http://en.wikipedia.org/wiki/IPV6#Notation)
			foreach ($ip_upgrade as $t => $f)
			{
				if (isTable($t))
				{
			 
					// Check for table - might add some core plugin tables in here
					if ($field_info =  dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . $t . " LIKE '{$f}'")))
					{
						//print_r($field_info);
						if (strtolower($field_info['Type']) != 'VARBINARY(16)')
						{

							dbquery("ALTER TABLE `" . TABLEPREFIX . $t .  "` MODIFY `$f` varbinary(16) DEFAULT NULL;");
 
						}
					}

				}
			}	 
		}

		$set_356 = false;
		$set_356 = do_version_check_356();
		if ($set_356)
		{
			$output .= write_error(_ERROR);
		}
		else
		{
			$update = dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET version = '" . $version . "' WHERE sitekey = '" . SITEKEY . "'");
			if ($update) $output .= write_message(_ACTIONSUCCESSFUL);
		}
	}
	else if ($confirm == "no")
	{
		$output .= write_message(_ACTIONCANCELLED);
	}
	else
	{
		if ($oldVersion[0] == 3 && ($oldVersion[1] < 4 || $oldVersion[1] == 4 && (!isset($oldVersion[2]) || $oldVersion[2] < 7)))
		$output .= write_message(_CONFIRMUPDATE . "<br /> <a href='update.php?confirm=yes'>" . _YES . "</a> " . _OR . " <a href='update.php?confirm=no'>" . _NO . "</a>");
		else $output .= write_message("Are you ready to update? <a href='update.php?confirm=yes'>" . _YES . "</a> " . _OR . " <a href='update.php?confirm=no'>" . _NO . "</a>");
	}

}
else $output .= write_message(_ALREADYUPDATED);

/* until database is fully fixed, not update efiction version */
function do_version_check_355() {
	global $fanfiction_poll_exists;

	$check_355 = false;

	// List of DB tables (key) and field (value) which need changing date from datetime/timestamp to int
	$date_upgrade = array(
		'fanfiction_authors' => 'date',
		'fanfiction_comments'  => 'time',
		'fanfiction_reviews' => 'date',
		'fanfiction_stories'  => 'date',
		'fanfiction_stories' => 'updated',
		'fanfiction_news' => 'time',
		'fanfiction_poll' => 'poll_start',
		'fanfiction_poll' => 'poll_end',
	);

	foreach ($date_upgrade as $t => $f)
	{

		if (isTable($t))
		{

			// Check for table - might add some core plugin tables in here
			if ($field_info =  dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . $t . " LIKE '{$f}'")))
			{

				if (strtolower($field_info['Type']) != 'int' 
				&& strtolower($field_info['Type']) != 'int(10)'
				&& strtolower($field_info['Type']) != 'int(11)' 
				&& strtolower($field_info['Type']) != 'int unsigned'
				&& strtolower($field_info['Type']) != 'int(10) unsigned')
				{
					// echo "<pre>"; print_r($field_info); echo "</pre>";
					$check_355 = $t . " - field:  " . $f;
					return $check_355;
				}
			}
		}
		
	}

	$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_log LIKE 'log_timestamp'"));

	if ($tmp['Type'] == "timestamp")
	{
		$check_355 = $t . " - field:  " . $f;;
		return $check_355;
	}

	return $check_355;
}

function do_version_check_356()
{
	$check_356 = false;
 
	// List of DB tables (key) and field (value) which need changing to accommodate IPV6 addresses
	$ip_upgrade = array(
		'fanfiction_log' 	=> 'log_ip',
		'fanfiction_online' => 'online_ip'
	);

	foreach ($ip_upgrade as $t => $f)
	{
 
		if (isTable($t))
		{
			// Check for table - might add some core plugin tables in here
			if ($field_info =  dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . $t ." LIKE '{$f}'")) )
			{

			
				if (strtolower($field_info['Type']) != 'varbinary(16)')
				{
					$check_356 = $t . " - field:  " . $f;;
			 
					return $check_356;	 
				}
			}
	 
		}
		
	}
 
	return $check_356;
}

$tpl->assign("output", $output);
$tpl->printToScreen();
dbclose();


// new functions for easier db manipulation 3.6. 
function db_mySQLtableList()
{
	global  $debug, $dbconnect, $dbname, $settingsprefix;

	$table = array();

	if ($res = dbquery("SHOW TABLES FROM " . $dbname . " LIKE '" . $settingsprefix . "%' "))

		$length = strlen($settingsprefix);
	while ($rows = dbrow($res))
	{
		$t = substr($rows[0], $length);
		$table[] = $t;
	}
	return  $table;
}

function isTable($table)
{
	$mySQLtableList = db_mySQLtableList();
	return in_array($table, $mySQLtableList);
}
