<?php
define("_BASEDIR", "../");
include("../config.php");
include("../includes/dbfunctions.php");
list($tableprefix, $lang) = dbrow(dbquery("SELECT tableprefix, language FROM ".$settingsprefix."fanfiction_settings WHERE sitekey = '$sitekey'"));
define("TABLEPREFIX", $tableprefix);
include(_BASEDIR."languages/$lang.php");
header("Content-Type: text/javascript; charset="._CHARSET,true);
include("../includes/queries.php");

$catid = isset($_GET['catid']) && preg_match("/^[0-9]+$/", $_GET['catid']) ? $_GET['catid'] : -1;
$cats = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = '$catid' OR catid = '$catid' ORDER BY leveldown, displayorder");
echo "var el = '".$_GET['element']."';\n";
$x = 0;
$find = array ('"', chr(150), chr(147), chr(148), chr(146));
$replace = array ('\"', "-", "\"", "\"", "'");
while($category = dbassoc($cats)) {
	echo "categories[$x] = new category(".$category['parentcatid'].", ".$category['catid'].", \"".urlencode(str_replace($find, $replace, stripslashes($category['category'])))."\", ".$category['locked'].", ".$category['displayorder'].");\r\n";
	$x++;
}
?>