<?php
if(!defined("_CHARSET")) exit( );

global $language, $numupdated;
$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks WHERE block_name = 'recent'");
while($block = dbassoc($blockquery)) {
	if ($block['block_variables'])
	{
		$blocks[$block['block_name']] = unserialize($block['block_variables']);
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	 $blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
if(empty($blocks['recent']['tpl'])) include("blocks/".$blocks['recent']['file']);
if(file_exists("blocks/recent/".$language.".php")) include("blocks/recent/".$language.".php");
else include("blocks/recent/en.php");
	if(isset($_POST['submit'])) {
		if(!empty($_POST['tpl'])) $blocks['recent']['tpl'] = 1;
		else unset($blocks['recent']['tpl']);
		if(!empty($_POST['allowtags'])) $blocks['recent']['allowtags'] = 1;
		else unset($blocks['recent']['allowtags']);
		if(!empty($_POST['sumlength']) && isNumber($_POST['sumlength'])) $blocks['recent']['sumlength'] = $_POST['sumlength'];
		else unset($blocks['recent']['sumlength']);
		if(!empty($_POST['num']) && isNumber($_POST['num'])) $blocks['recent']['num'] = $_POST['num'];
		else $blocks['recent']['num'] = 1;
		$output .= "<div style='text-align: center;'>"._ACTIONSUCCESSFUL."</div>";
		save_blocks( $blocks );
	}
	else  {
		$output .= "<div style='text-align: center;'><b>"._CURRENT.":</b><br /><div class=\"tblborder\" style=\"width: 80%; margin: 0 auto; text-align: left;\">".(!empty($blocks['recent']['tpl']) ? _NATPL : $content)."</div><br /></div>";
		$output .= "<div id='settingsform'><form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks&amp;admin=recent\">
			<div><label for=\"tpl\">"._BLOCKTYPE.":</label><select name=\"tpl\" class=\"textbox\" id=\"tpl\"><option value=\"0\"".(empty($blocks['recent']['tpl']) ? " selected" : "").">"._DEFAULT."</option>
					<option value=\"1\"".(!empty($blocks['recent']['tpl']) ? " selected" : "").">"._USETPL."</option></select></div>
			<div><label for=\"allowtags\">"._TAGS.":</label><select name=\"allowtags\" class=\"textbox\" id=\"allowtags\"><option value=\"0\"".(empty($blocks['recent']['allowtags']) ? " selected" : "").">"._STRIPTAGS."</option>
					<option value=\"1\"".(!empty($blocks['recent']['allowtags']) ? " selected" : "").">"._ALLOWTAGS."</option></select></div>
			<div><label for=\"sumlength\">"._SUMLENGTH.":</label><input type=\"text\" class=\"textbox\" name=\"sumlength\" id=\"sumlength\" size=\"4\" value=\"".(!empty($blocks['recent']['sumlength']) ? $blocks['recent']['sumlength'] : "")."\"></div>
			<div><label for=\"num\">"._NUMUPDATED.":</label><input type=\"text\" class=\"textbox\" name=\"num\" id=\"num\" size=\"4\" value=\"".$blocks['recent']['num']."\"></div>
			<INPUT type=\"submit\" name=\"submit\" class=\"button\" id=\"submit\" value=\""._SUBMIT."\"></form></div><div style='text-align: center;'>"._SUMNOTE."</div>";
	}
?>