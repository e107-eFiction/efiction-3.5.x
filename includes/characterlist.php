<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(-1);

define("_BASEDIR", "../");
define("_CHARSET", "utf-8");

include("../config.php");
include("../includes/dbfunctions.php");
list($tableprefix, $lang) = dbrow(dbquery("SELECT tableprefix, language FROM ".$settingsprefix."fanfiction_settings WHERE sitekey = '$sitekey'"));
define("TABLEPREFIX", $tableprefix);
if(file_exists(_BASEDIR."languages/{$lang}.php")) include (_BASEDIR."languages/{$lang}.php");
else include (_BASEDIR."languages/en.php");
include("../includes/queries.php");

header("Content-Type: text/javascript; charset="._CHARSET);
header("X-Content-Type-Options: nosniff");
// Checks that the given $num is actually a number.  Used to help prevent XSS attacks.
function isNumber($num) {
	if(empty($num)) return false;
	return (preg_match("/^[0-9]+$/", $num) || $num == "-1" ? true : false);
}

// Character list for the category picker (includes/javascript.js evals this reply).
// Every value is JSON-encoded so nothing from the request or a character name can break out.
$catid = isset($_GET['catid']) && is_string($_GET['catid']) ? explode(",", $_GET['catid']) : array(-1);
$catid = array_values(array_filter($catid, "isNumber"));
if(!count($catid)) $catid = array(-1);
$element = isset($_GET['element']) && is_string($_GET['element']) && preg_match('/^[A-Za-z0-9_]{1,40}$/', $_GET['element']) ? $_GET['element'] : '';
$flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE;
$characters = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_characters WHERE ".(count($catid) > 1 ? "FIND_IN_SET(catid, '".implode(",", $catid)."') > 0" : "catid = '".$catid[0]."'")." ORDER BY charname");
echo "var element = ".json_encode($element, $flags).";\n";
$x = 0;
while($char = dbassoc($characters)) {
	echo "characters[".$x."] = new Array(".(int)$char['charid'].", ".json_encode((string)stripslashes($char['charname']), $flags).");\r\n";
	$x++;
}
?>
