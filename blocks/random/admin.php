<?php
if(!defined("_CHARSET")) exit( );

global $language;
$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks WHERE block_name = 'random'");
while($block = dbassoc($blockquery)) {
	if ($block['block_variables'])
	{
		$blocks[$block['block_name']] = unserialize($block['block_variables']);
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	 $blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
include("blocks/".$blocks['random']['file']);
if(!empty($blocks['random']['tpl'])) $tpl->gotoBlock("_ROOT");
	if(isset($_POST['submit'])) {
		if($_POST['tpl']) $blocks['random']['tpl'] = 1;
		else unset($blocks['random']['tpl']);
		if($_POST['allowtags']) $blocks['random']['allowtags'] = 1;
		else unset($blocks['random']['allowtags']);
		if(isset($_POST['sumlength']) && isNumber($_POST['sumlength'])) $blocks['random']['sumlength'] = $_POST['sumlength'];
		else unset($blocks['random']['sumlength']);
		$output .= "<div style='text-align: center;'>"._ACTIONSUCCESSFUL."</div>";
		save_blocks( $blocks );
	}
	else  {
		$output .= "<div style='text-align: center;'><b>"._CURRENT.":</b><br /><div class=\"tblborder\" style=\"width: 80%; margin: 1ex auto; text-align: left;\">".(!empty($blocks['random']['tpl']) ? _NATPL : $content)."</div><br /></div>";
		$output .= "<div id='settingsform'><form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks&admin=random\">
			<label for=\"tpl\">"._BLOCKTYPE.":</label><select class=\"textbox\" name=\"tpl\" id=\"tpl\"><option value=\"0\"".(empty($blocks['random']['tpl']) ? " selected" : "").">"._DEFAULT."</option>
					<option value=\"1\"".(!empty($blocks['random']['tpl']) ? " selected" : "").">"._USETPL."</option></select><br />
			<label for=\"allowtags\">"._TAGS.":</label><select class=\"textbox\" name=\"allowtags\" id=\"allowtags\"><option value=\"0\"".(empty($blocks['random']['allowtags']) ? " selected" : "").">"._STRIPTAGS."</option>
					<option value=\"1\"".(!empty($blocks['random']['allowtags']) ? " selected" : "").">"._ALLOWTAGS."</option></select><br />
			<label for=\"sumlength\">"._SUMLENGTH.":</label><input type=\"text\" class=\"textbox\" name=\"sumlength\" id=\"sumlength\" size=\"4\" value=\"".(!empty($blocks['random']['sumlength']) ? $blocks['random']['sumlength'] : "")."\"><br />
			<INPUT type=\"submit\" name=\"submit\" id=\"submit\" class=\"button\" value=\""._SUBMIT."\"></form></div><div style='text-align: center;'>"._SUMNOTE."</div>";
	}
?>