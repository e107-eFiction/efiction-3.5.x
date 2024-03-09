<?php
if(!defined("_CHARSET")) exit( );
$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks WHERE block_name = 'featured'");
while($block = dbassoc($blockquery)) {
	if ($block['block_variables'])
	{
		$blocks[$block['block_name']] = unserialize($block['block_variables']);
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	 $blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
include("blocks/".$blocks['featured']['file']);
if($use_tpl) $tpl->gotoBlock("_ROOT");
	if(isset($_POST['submit'])) {
		if($_POST['tpl']) $blocks['featured']['tpl'] = 1;
		else unset($blocks['featured']['tpl']);
		if($_POST['allowtags']) $blocks['featured']['allowtags'] = 1;
		else unset($blocks['featured']['allowtags']);
		if($_POST['sumlength'] && isNumber($_POST['sumlength'])) $blocks['featured']['sumlength'] = $_POST['sumlength'];
		else unset($blocks['featured']['sumlength']);
		$output .= "<div style='text-align: center;'>"._ACTIONSUCCESSFUL."</div>";
		save_blocks( $blocks );
		unset($admin);
	}
	else  {
		$output .= "<div style='text-align: center;'><span class='label'>"._CURRENT.":</span><br /><div class=\"tblborder\" style=\"width: 80%; text-align: left; margin: 1ex auto;\">".($use_tpl ? _NATPL : $content)."</div><br /></div>";
		$output .= "<div id='settingsform'><form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks&admin=featured\">
			<label for=\"tpl\">"._FORMAT.":</label><select name=\"tpl\" class=\"textbox\" id=\"tpl\"><option value=\"0\"".(!isset($blocks['featured']['tpl']) || !$blocks['featured']['tpl'] ? " selected" : "").">"._DEFAULT."</option>
					<option value=\"1\"".(isset($blocks['featured']['tpl']) && $blocks['featured']['tpl'] ? " selected" : "").">"._USETPL."</option></select><br />
			<label for=\"allowtags\">"._TAGS.":</label><select class=\"textbox\" name=\"allowtags\" id=\"allowtags\"><option value=\"0\"".(!isset($blocks['featured']['allowtags']) || !$blocks['featured']['allowtags'] ? " selected" : "").">"._STRIPTAGS."</option>
					<option value=\"1\"".(isset($blocks['featured']['allowtags']) && $blocks['featured']['allowtags'] ? " selected" : "").">"._ALLOWTAGS."</option></select><br />
			<label for=\"sumlength\">"._SUMLENGTH.":</label><input type=\"text\" class=\"textbox\" name=\"sumlength\" id=\"sumlength\" size=\"4\" value=\"".(isset($blocks['featured']['sumlength']) ? $blocks['featured']['sumlength'] : "")."\"><br />
			<INPUT type=\"submit\" class=\"button\" name=\"submit\" id=\"submit\" value=\""._SUBMIT."\"></form></div><div style='text-align: center;'>"._SUMNOTE."</div>";
	}
?>