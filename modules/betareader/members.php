<?php 
if(!defined("_CHARSET")) exit( );
list($field_id, $field_title) = dbrow(dbquery("SELECT field_id, field_title FROM ".TABLEPREFIX."fanfiction_authorfields WHERE field_name = 'betareader'"));
$listOpts .= "<option value=\"authors.php?".($let ? "let=$let&amp;" : "")."list=beta\"".($list == "beta" ? " selected" : "").">$field_title</option>";
if($list == "beta") {
	$countquery = "SELECT COUNT(DISTINCT ai.uid) FROM ".TABLEPREFIX."fanfiction_authorinfo as ai, "._AUTHORTABLE." WHERE ai.field = '$field_id' AND ai.info = '"._YES."' AND "._UIDFIELD." = ai.uid".(isset($letter) ? " AND $letter" : "");
	$authorquery = "SELECT ap.stories as stories, "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM ".TABLEPREFIX."fanfiction_authorinfo as ai, "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs AS ap ON "._UIDFIELD." = ap.uid WHERE ai.field = '$field_id' AND ai.info = '"._YES."' AND "._UIDFIELD." = ai.uid ".(isset($letter) ? " AND $letter" : "")." GROUP BY "._UIDFIELD;
	$pagetitle .= $field_title;
}

?>