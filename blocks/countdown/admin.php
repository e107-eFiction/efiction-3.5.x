<?php
if(!defined("_CHARSET")) exit( );

global $language, $numupdated;
$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks WHERE block_name = 'countdown'");
while($block = dbassoc($blockquery)) {
	if ($block['block_variables'])
	{
		$blocks[$block['block_name']] = unserialize($block['block_variables']);
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	 $blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
include("blocks/countdown/block.php");
	if(isset($_POST['submit'])) {
		if(!empty($_POST['target'])) $blocks['countdown']['target'] = escapestring($_POST['target']);
		else unset($blocks['countdown']['target']);
		if(!empty($_POST['CDformat'])) $blocks['countdown']['CDformat'] = escapestring($_POST['CDformat']);
		else unset($blocks['countdown']['CDformat']);
		if(!empty($_POST['finish'])) $blocks['countdown']['CDfinal'] = escapestring($_POST['finish']);
		else unset($blocks['countdown']['CDfinal']);
		$output .= "<div style='text-align: center;'>"._ACTIONSUCCESSFUL."</div>";
		save_blocks( $blocks );
	}
	else  {
		$output .= "<div style='text-align: center;'><b>"._CURRENT.":</b><br /><div class=\"tblborder\" style=\"width: 80%; margin: 0 auto; text-align: left;\">".$content."</div><br /></div>";
		$output .= "<div id='settingsform'><form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks&amp;admin=countdown\">
			<div><label for=\"target\">"._TARGETDATE.":</label><input type=\"text\" class=\"textbox\" name=\"target\" id=\"target\" value=\"".(!empty($blocks['countdown']['target']) ? $blocks['countdown']['target'] : date("m/d/Y G:H"))."\"></div>
			<div><label for=\"CDformat\">"._FORMATCOUNT.":</label><input type=\"text\" class=\"textbox\" name=\"CDformat\" id=\"CDformat\" size=\"40\" value=\"".(empty($blocks['countdown']['CDformat']) ? _COUNTDOWNFORMAT : $blocks['countdown']['CDformat'])."\"></div>
			<div><label for=\"finish\">"._FINISHMESSAGE.":</label><input type=\"text\" class=\"textbox\" name=\"finish\" id=\"finish\" size=\"40\" value=\"".(empty($blocks['countdown']['finish']) ? _COUNTDOWNOVER : $blocks['countdown']['finish'])."\"></div>
			<INPUT type=\"submit\" name=\"submit\" class=\"button\" id=\"submit\" value=\""._SUBMIT."\"></form></div><div style='text-align: center;'>"._DATENOTE."</div>";
	}
?>