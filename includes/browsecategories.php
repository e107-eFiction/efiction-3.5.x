<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(-1);

define("_BASEDIR", "../");
define("_CHARSET", "utf-8");

include("../config.php");
include("../includes/dbfunctions.php");
list($tableprefix) = dbrow(dbquery("SELECT tableprefix FROM ".$settingsprefix."fanfiction_settings WHERE sitekey = '$sitekey'"));
define("TABLEPREFIX", $tableprefix);

header("Content-Type: text/javascript; charset="._CHARSET, true);
header("X-Content-Type-Options: nosniff");
include("../includes/queries.php");

// Category and character lists for the browse menus (includes/javascript.js evals this reply).
// Every value is JSON-encoded or cast to a number so nothing from the request or a name can break out
// (the same treatment as includes/categorylist.php and includes/characterlist.php).
$catid = isset($_GET['catid']) && is_string($_GET['catid']) && preg_match("/^[0-9]+$/", $_GET['catid']) ? (int)$_GET['catid'] : -1;
$element = isset($_GET['element']) && is_string($_GET['element']) && preg_match('/^[A-Za-z0-9_]{1,40}$/', $_GET['element']) ? $_GET['element'] : '';
$flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE;
$cats = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = '$catid' OR catid = '$catid' ORDER BY leveldown, displayorder");
echo "var el = ".json_encode($element, $flags).";\n";
$x = 0;
while($category = dbassoc($cats)) {
	echo "categories[$x] = new category(".(int)$category['parentcatid'].", ".(int)$category['catid'].", ".json_encode((string)stripslashes((string)$category['category']), $flags).", ".(int)$category['locked'].", ".(int)$category['displayorder'].");\r\n";
	$x++;
}
$characters = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_characters WHERE catid = '$catid' OR catid = '-1' ORDER BY charname");
$x = 0;
while($char = dbassoc($characters)) {
	echo "characters[$x] = new Array(".(int)$char['charid'].", ".json_encode((string)stripslashes((string)$char['charname']), $flags).");\r\n";
	$x++;
}
?>