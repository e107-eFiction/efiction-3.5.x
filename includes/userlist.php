<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(-1);

define("_BASEDIR", "../");
define("_CHARSET", "utf-8");

include("../config.php");
include("../includes/dbfunctions.php");
list($tableprefix, $language) = dbrow(dbquery("SELECT tableprefix, language FROM ".$settingsprefix."fanfiction_settings WHERE sitekey = '$sitekey'"));
define("TABLEPREFIX", $tableprefix);
$sitename = $url = "";
if(file_exists(_BASEDIR."languages/{$language}.php")) include (_BASEDIR."languages/{$language}.php");
else include (_BASEDIR."languages/en.php");
include("../includes/queries.php");

header("Content-Type: text/javascript; charset="._CHARSET);
header("X-Content-Type-Options: nosniff");

// Name lookup for the co-author picker (includes/userselect.js evals this reply).
// Every value is JSON-encoded so nothing from the request or a penname can break out.
$str = isset($_REQUEST['str']) && is_string($_REQUEST['str']) ? substr($_REQUEST['str'], 0, 50) : '';
$element = isset($_REQUEST['element']) && is_string($_REQUEST['element']) && preg_match('/^[A-Za-z0-9_]{1,40}$/', $_REQUEST['element']) ? $_REQUEST['element'] : '';
$flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE;
echo "var element = ".json_encode($element, $flags).";\n";
if($str === '' || $element === '') exit( );
$like = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), strtolower($str));
$users = dbquery("SELECT "._UIDFIELD." as uid, "._PENNAMEFIELD." as username FROM "._AUTHORTABLE." WHERE LOWER(".
	_PENNAMEFIELD.") LIKE '".escapestring($like)."%' ORDER BY username ASC limit 10");
$x = 0;
while($u = dbassoc($users)) {
	echo "userList[".$x."] = new Array(".json_encode((string)$u['uid'], $flags).", ".json_encode((string)$u['username'], $flags).");\n";
	$x++;
}
?>