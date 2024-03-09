<?php
if(!defined("_CHARSET")) exit( );
$version = "3.5.6";

/* shortcut 
$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_stories LIKE 'date'"));
if($tmp['Type'] == "datetime") {
    dbquery("UPDATE " . TABLEPREFIX . "fanfiction_settings SET version = '3.5.5' WHERE sitekey = '" . SITEKEY . "'");
}


$tmp = dbassoc(dbquery("SHOW COLUMNS FROM " . TABLEPREFIX . "fanfiction_news LIKE 'time'"));
 
if ($tmp['Type'] == "datetime")
{
    dbquery("UPDATE " . TABLEPREFIX . "fanfiction_settings SET version = '3.5.5' WHERE sitekey = '" . SITEKEY . "'");
}

*/