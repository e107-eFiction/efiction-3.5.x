<?php
define("_BASEDIR", "../");
include("../config.php");
include("../includes/dbfunctions.php");
list($tableprefix, $lang) = dbrow(dbquery("SELECT tableprefix, language FROM ".$settingsprefix."fanfiction_settings WHERE sitekey = '$sitekey'"));
define("TABLEPREFIX", $tableprefix);
include(_BASEDIR."languages/$lang.php");
//header("Content-Type: text/html; charset=".CHARSET,true);
include("../includes/queries.php");
// Checks that the given $num is actually a number.  Used to help prevent XSS attacks.
function isNumber($num) {
	if(empty($num)) return false;
	return (preg_match("/^[0-9]+$/", $num) || $num == "-1" ? true : false);
}

$catid = isset($_GET['catid']) ? explode(",", $_GET['catid']) : array(-1);
$catid = array_filter($catid, "isNumber");
$characters = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_characters WHERE ".(count($catid) > 1 ? "FIND_IN_SET(catid, '".implode(",", $catid)."') > 0" : "catid = '".$catid[0]."'")." ORDER BY charname");
echo "var element = '".$_GET['element']."';\n";
$x = 0;
$find = array ('"', chr(150), chr(147), chr(148), chr(146));
$replace = array ('\"', "-", "\"", "\"", "'");
while($char = dbassoc($characters)) {
	echo "characters[$x] = new Array(".$char['charid'].", \"".urlencode(str_replace($find, $replace, stripslashes($char['charname'])))."\");\r\n";
	$x++;
}
?>