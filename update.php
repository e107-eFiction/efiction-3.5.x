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
 
	else {
		$set_359 = do_version_check_359();
	
		if ($set_359)
		{
			$output .= write_message("Table <b>" . $set_359 . "</b> needs a update.");
			dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET version = '3.5.8' WHERE sitekey = '" . SITEKEY . "'");
			$settings['version'] = '3.5.8';
		}
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
			// List of DB tables (key) and field (value) which need changing date from datetime/timestamp to int
			$date_upgrade = array(
				array('table' => 'fanfiction_authors', 'field' => 'date'),
				array(
					'table' =>
					'fanfiction_comments', 'field'  => 'time'
				),
				array(
					'table' =>
					'fanfiction_reviews', 'field' => 'date'
				),
				array(
					'table' =>
					'fanfiction_stories', 'field'  => 'date'
				),
				array(
					'table' =>
					'fanfiction_stories', 'field' => 'updated'
				),
				array(
					'table' =>
					'fanfiction_news', 'field' => 'time'
				),
				array(
					'table' =>
					'fanfiction_poll', 'field' => 'poll_start'
				),
				array(
					'table' =>
					'fanfiction_poll', 'field' => 'poll_end'
				),
			);

			// Tables where IP address field needs updating to accommodate IPV6
			// Set to varchar(45) - just in case something uses the IPV4 subnet (see http://en.wikipedia.org/wiki/IPV6#Notation)
			foreach ($date_upgrade as $tmp)
			{

				$t = $tmp['table'];
				$f = $tmp['field'];
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

							$updated = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . $t . " LIKE '{$f}_backup'"));
							if (!$updated)
							{
								dbquery("ALTER TABLE `" . TABLEPREFIX . $t .  "` ADD `{$f}_backup` datetime  ");
							}

							dbquery("UPDATE `" . TABLEPREFIX . $t .  "` SET `{$f}_backup` =  `{$f}`  ");
							dbquery("UPDATE `" . TABLEPREFIX . $t .  "` SET `{$f}_tmp` = UNIX_TIMESTAMP( `{$f}` )");
							dbquery("UPDATE `" . TABLEPREFIX . $t .  "` SET `{$f}` =  '' ");
						//	dbquery("ALTER TABLE `" . TABLEPREFIX . $t .  "` CHANGE `{$f}` `{$f}` DATETIME NOT NULL)");  this is needed on some servers
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
elseif ($oldVersion[0] == 3 && ($oldVersion[1] < 5 || $oldVersion[2] < 8))  //3.5.6 + 3.5.7
{
	if ($confirm == "yes")
	{
		$ip_errors = 0;
		if ($oldVersion[0] == 3 &&  $oldVersion[1] == 5 && $oldVersion[2] < 7)
		{
			// List of DB tables (key) and field (value) which need changing to accommodate IPV6 addresses
			$ip_upgrade = array(
				'fanfiction_log' 	=> 'log_ip',
				'fanfiction_online' => 'online_ip'
			);

			// PHP 8.1+ makes mysqli throw, and dbquery() then stops the page. Switch that off for this
			// step only, so a failed query returns false and is reported with its table and column.
			$mysqli_driver = new mysqli_driver();
			$report_mode = $mysqli_driver->report_mode;
			mysqli_report(MYSQLI_REPORT_OFF);

			// IP addresses are stored as INET6_ATON() binaries in varbinary(16)
			foreach ($ip_upgrade as $t => $f)
			{
				if (isTable($t)) $ip_errors += ef_upgrade_ip_field($t, $f);
			}

			mysqli_report($report_mode);
		}

		$set_356 = false;
		$set_356 = do_version_check_356();
		if ($set_356 || $ip_errors)
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

elseif ($oldVersion[0] == 3 && $oldVersion[1] == 5 && $oldVersion[2] < 9)  // 3.5.8 -> 3.5.9
{
	if ($confirm == "yes")
	{
		if (!dbassoc(dbquery("SHOW COLUMNS FROM ".$settingsprefix."fanfiction_settings LIKE 'smtp_port'")))
			dbquery("ALTER TABLE `".$settingsprefix."fanfiction_settings` ADD `smtp_port` varchar(5) NOT NULL DEFAULT ''");
		if (!dbassoc(dbquery("SHOW COLUMNS FROM ".$settingsprefix."fanfiction_settings LIKE 'smtp_secure'")))
			dbquery("ALTER TABLE `".$settingsprefix."fanfiction_settings` ADD `smtp_secure` varchar(3) NOT NULL DEFAULT ''");
		// 3.5.7: password_hash() needs more than the old varchar(40); MD5 hashes keep working and are replaced at login
		$field_info = dbassoc(dbquery("SHOW COLUMNS FROM ".TABLEPREFIX."fanfiction_authors LIKE 'password'"));
		if ($field_info && strtolower($field_info['Type']) != 'varchar(255)')
			dbquery("ALTER TABLE `".TABLEPREFIX."fanfiction_authors` MODIFY `password` varchar(255) NOT NULL DEFAULT ''");
		// older settings pages turned "https://site" into "http://https://site"
		dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET url = SUBSTRING(url, 8) WHERE url LIKE 'http://https://%' OR url LIKE 'http://http://%'");

		$set_359 = do_version_check_359();
		if ($set_359)
		{
			$output .= write_error(_ERROR);   // migration not confirmed -> DO NOT bump version
		}
		else
		{
			// Accounts an administrator created and never released have '0' or '' as password, which made the
			// cookie salt md5(email . hash) guessable. Give them a random value that is no hash, so it never
			// authenticates (see ef_password_is_hash()). Rows with a hash or a '!' value are left alone.
			$unusablechars = '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
			$unreleased = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_authors WHERE password = '0' OR password = ''");
			while ($row = dbassoc($unreleased))
			{
				$unusable = "!";
				for ($i = 0; $i < 40; $i++) $unusable .= $unusablechars[random_int(0, strlen($unusablechars) - 1)];
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_authors SET password = '".escapestring($unusable)."' WHERE uid = '".(int)$row['uid']."' AND (password = '0' OR password = '') LIMIT 1");
			}
			// 3.5.78: clean HTML stored before the descript() rewrite (safe to run again)
			ef_clean_stored_html();
			$update = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET version = '".$version."' WHERE sitekey = '".SITEKEY."'");
			if ($update) $output .= write_message(_ACTIONSUCCESSFUL);
		}
	}
	else if ($confirm == "no") $output .= write_message(_ACTIONCANCELLED);
	else $output .= write_message("Are you ready to update? <a href='update.php?confirm=yes'>"._YES."</a> "._OR." <a href='update.php?confirm=no'>"._NO."</a>");
}

else $output .= write_message(_ALREADYUPDATED);

/* until database is fully fixed, not update efiction version */
function do_version_check_355() {
	global $fanfiction_poll_exists;

	$check_355 = false;

	// List of DB tables (key) and field (value) which need changing date from datetime/timestamp to int
	$date_upgrade = array(	
		array('table'=>'fanfiction_authors', 'field' => 'date'),
		array(
			'table' =>
			'fanfiction_comments', 'field'  => 'time'),
			array(
			'table' =>
			'fanfiction_reviews', 'field' => 'date'),
				array(
			'table' =>
			'fanfiction_stories', 'field'  => 'date'),
					array(
			'table' =>
			'fanfiction_stories', 'field' => 'updated'),
						array(
			'table' =>
			'fanfiction_news', 'field' => 'time'),
							array(
			'table' =>
			'fanfiction_poll', 'field' => 'poll_start'),
								array(
			'table' =>
			'fanfiction_poll', 'field' => 'poll_end'),
	);

	foreach ($date_upgrade as $tmp)
	{

		$t = $tmp['table'];
		$f = $tmp['field'];
		if (isTable($t))
		{

			// Check for table - might add some core plugin tables in here
			if ($field_info =  dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . $t . " LIKE '{$f}'")))
			{
				// echo "<pre>";
				// print_r($t);
				// print_r($field_info);
				// echo "</pre>";
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

			
				if (strtolower($field_info['Type']) != 'varbinary(16)' || $field_info['Null'] != 'YES')
				{
					$check_356 = $t . " - field:  " . $f;;
			 
					return $check_356;	 
				}
			}
	 
		}
		
	}
 
	return $check_356;
}

/* 3.5.6 -> 3.5.8: move one IP column to varbinary(16) holding INET6_ATON() values.
   - integer column (INET_ATON() values): fill <field>_tmp, then rename <field> to <field>_backup
     and <field>_tmp to <field> in one ALTER TABLE. A <field>_tmp left by an earlier run is reused.
   - varbinary(16) column: make it NULL-able and convert decimal text left by the old in-place
     MODIFY (e.g. '3232235777') to the binary value.
   Safe to run again. Failed queries are added to $output; returns the number of failures. */
function ef_upgrade_ip_field($t, $f)
{
	global $output;

	$table = TABLEPREFIX . $t;
	$field_info = ef_show_column($t, $f);
	if (!$field_info) return 0;
	$type = strtolower($field_info['Type']);

	if (preg_match('/^(tiny|small|medium|big)?int(\(\d+\))?( unsigned)?( zerofill)?$/', $type))
	{
		if (ef_show_column($t, "{$f}_backup"))
		{
			$output .= write_error("Table <b>" . $table . "</b>: column <b>{$f}</b> is still " . $type . ", but <b>{$f}_backup</b> already exists. Rename or drop <b>{$f}_backup</b> and run the update again.");
			return 1;
		}

		$tmp_info = ef_show_column($t, "{$f}_tmp");
		if (!$tmp_info)
		{
			if (!ef_ip_query("ALTER TABLE `" . $table . "` ADD `{$f}_tmp` varbinary(16) DEFAULT NULL", $table, "{$f}_tmp")) return 1;
		}
		else if (strtolower($tmp_info['Type']) != 'varbinary(16)' || $tmp_info['Null'] != 'YES')
		{
			if (!ef_ip_query("ALTER TABLE `" . $table . "` MODIFY `{$f}_tmp` varbinary(16) DEFAULT NULL", $table, "{$f}_tmp")) return 1;
		}

		// 0, negative (signed column) or empty means no address
		if (!ef_ip_query("UPDATE `" . $table . "` SET `{$f}_tmp` = IF(`{$f}` > 0, INET6_ATON(INET_NTOA(`{$f}`)), NULL)", $table, "{$f}_tmp")) return 1;

		// both renames in one statement, so the table never ends up with only one of them done
		if (!ef_ip_query("ALTER TABLE `" . $table . "` CHANGE `{$f}` `{$f}_backup` " . $type . " NULL DEFAULT NULL, CHANGE `{$f}_tmp` `{$f}` varbinary(16) DEFAULT NULL", $table, $f)) return 1;

		$output .= write_message("Table <b>" . $table . "</b>: column <b>{$f}</b> converted to varbinary(16), the old values are kept in <b>{$f}_backup</b>.");
		return 0;
	}

	if ($type != 'varbinary(16)' || $field_info['Null'] != 'YES')
	{
		if (!ef_ip_query("ALTER TABLE `" . $table . "` MODIFY `{$f}` varbinary(16) DEFAULT NULL", $table, $f)) return 1;
	}

	// Decimal text from the old in-place MODIFY: only digits, and not 4 or 16 bytes long. A 4-byte
	// binary can consist of digit characters only (49.50.51.52 is stored as '1234'), so 4 digits
	// stay as they are; 16 digits are larger than any IPv4 number anyway.
	$numeric = "`{$f}` IS NOT NULL AND LENGTH(`{$f}`) NOT IN (4, 16) AND HEX(`{$f}`) REGEXP '^(3[0-9])+$'";
	$found = dbquery("SELECT 1 FROM `" . $table . "` WHERE " . $numeric . " LIMIT 1");
	if (!$found)
	{
		$output .= write_error("Table <b>" . $table . "</b>, column <b>{$f}</b>: " . htmlspecialchars(dberror()));
		return 1;
	}
	if (!dbnumrows($found)) return 0;

	global $dbconnect;
	$batch = 1000;
	$converted = 0;
	do
	{
		// converted rows are 4 bytes long (or NULL) afterwards, so the next batch skips them
		if (!ef_ip_query("UPDATE `" . $table . "` SET `{$f}` = IF(CAST(`{$f}` AS UNSIGNED) BETWEEN 1 AND 4294967295, INET6_ATON(INET_NTOA(CAST(`{$f}` AS UNSIGNED))), NULL) WHERE " . $numeric . " LIMIT " . $batch, $table, $f)) return 1;
		$count = $dbconnect->affected_rows;
		$converted += $count;
	}
	while ($count == $batch);

	$output .= write_message("Table <b>" . $table . "</b>: " . $converted . " IP addresses in column <b>{$f}</b> converted from text to binary.");
	return 0;
}

// SHOW COLUMNS row for one column of a data table, false if it does not exist or the query failed
function ef_show_column($t, $f)
{
	$result = dbquery("SHOW COLUMNS FROM `" . TABLEPREFIX . $t . "` LIKE '{$f}'");
	if (!$result) return false;
	$row = dbassoc($result);
	return $row ? $row : false;
}

// Runs an ALTER/UPDATE of the IP migration; on failure adds the MySQL error to $output
function ef_ip_query($query, $table, $field)
{
	global $output;

	if (dbquery($query)) return true;
	$output .= write_error("Table <b>" . $table . "</b>, column <b>" . $field . "</b>: " . htmlspecialchars(dberror()) . "<br />Query: " . htmlspecialchars($query));
	return false;
}

function do_version_check_359()
{
	global $settingsprefix;

	$check_359 = false;

	$required = array('smtp_port', 'smtp_secure');
	foreach ($required as $f)
	{
		if (!dbassoc(dbquery("SHOW COLUMNS FROM " . $settingsprefix . "fanfiction_settings LIKE '{$f}'")))
		{
			$check_359 = "fanfiction_settings - field: " . $f;
			return $check_359;
		}
	}

	// the column exists at varchar(40) on older sites, so check its type, not only that it is there
	$field_info = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_authors LIKE 'password'"));
	if (!$field_info || strtolower($field_info['Type']) != 'varchar(255)')
	{
		$check_359 = "fanfiction_authors - field: password";
		return $check_359;
	}

	return $check_359;
}

/* 3.5.78: reviews, news comments and challenges saved before the descript() rewrite could keep
   script in an attribute (<img onerror=...>). Runs the stored HTML through descript() again.
   Called from the 3.5.8 -> 3.5.9 step; safe to run more than once (rows that are already clean
   are not written). Returns the number of rows changed per table. */
function ef_clean_stored_html($batch = 200)
{
	global $dbconnect;

	$batch = max(1, (int)$batch);
	$changed = array();
	$jobs = array(
		array('fanfiction_reviews', 'reviewid', array('review')),
		array('fanfiction_comments', 'cid', array('comment')),
		array('fanfiction_challenges', 'chalid', array('title', 'summary', 'challenger')),
	);

	// The site connects with SET NAMES UTF8 (3-byte utf8); read and write 4-byte characters
	// (emoji) in utf8mb4 columns unchanged. A server without utf8mb4 cannot hold them anyway.
	$mb4 = ($dbconnect instanceof mysqli);
	if ($mb4)
	{
		// PHP 8.1+ throws here instead of returning false
		try { @$dbconnect->set_charset('utf8mb4'); }
		catch (Exception $e) { }
	}

	foreach ($jobs as $job)
	{
		list($table, $key, $cols) = $job;
		$changed[$table] = 0;
		if (!dbnumrows(dbquery("SHOW TABLES LIKE '" . escapestring(TABLEPREFIX . $table) . "'"))) continue;
		$where = array();
		foreach ($cols as $c) $where[] = "`$c` LIKE '%<%'";
		$last = 0;
		do
		{
			$rows = dbquery("SELECT `$key`, `" . implode("`, `", $cols) . "` FROM " . TABLEPREFIX . $table . " WHERE `$key` > '" . (int)$last . "' AND (" . implode(" OR ", $where) . ") ORDER BY `$key` LIMIT " . $batch);
			$count = 0;
			while ($row = dbassoc($rows))
			{
				$count++;
				$last = (int)$row[$key];
				$set = array();
				foreach ($cols as $c)
				{
					$old = (string)$row[$c];
					$new = (string)descript($old);
					if ($new !== $old) $set[] = "`$c` = '" . escapestring($new) . "'";
				}
				if ($set)
				{
					dbquery("UPDATE " . TABLEPREFIX . $table . " SET " . implode(", ", $set) . " WHERE `$key` = '" . (int)$last . "' LIMIT 1");
					$changed[$table]++;
				}
			}
		}
		while ($count == $batch);
	}

	if ($mb4)
	{
		// back to what includes/mysqli_functions.php sets (SET NAMES UTF8)
		try { @$dbconnect->set_charset('utf8'); }
		catch (Exception $e) { }
	}
	return $changed;
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
