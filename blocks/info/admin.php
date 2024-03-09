<?php
if(!defined("_CHARSET")) exit( );

global $language;
$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks WHERE block_name = 'info'");
while($block = dbassoc($blockquery)) {
	if ($block['block_variables'])
	{
		$blocks[$block['block_name']] = unserialize($block['block_variables']);
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	 $blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
include("blocks/info/info.php");
if(file_exists("blocks/info/{$language}.php")) include_once("blocks/info/{$language}.php");
else include_once("blocks/info/en.php");
	if(isset($_POST['submit'])) {
		$blocks['info']['style'] = !empty($_POST['style']) && isNumber($_POST['style']) ? $_POST['style'] : 0;
		if($_POST['template'] != _NARTEXT) $blocks['info']['template'] = $_POST['template'];
		$output .= "<center>"._ACTIONSUCCESSFUL."</center>";
		save_blocks( $blocks );
	}
	else {
		$style = isset($blocks['info']['style']) ? $blocks['info']['style'] : 0;
		if(empty($blocks['info']['template']) && $style == 1) $template = _NARTEXT;
		else if($style == 1) $template = $blocks['info']['template'];
		else $template = "";
		$output .= "<div style='margin: 1em auto; width: 80%;'><b>"._CURRENT.":</b><br /><div class=\"tblborder\" style=\"text-align: left; padding: 4px; margin: 0 auto;\">$content</div><br />";
		$output .= "<form method='POST' enctype='multipart/form-data' name='blockadmin' action='admin.php?action=blocks&admin=info'>
			<label for='template'>"._TEMPLATE.":</label><br /><textarea name='template' rows='5' cols='50' style='width: 100%;'>$template</textarea>";
		if($tinyMCE) $output .= "<div style='display: block; margin: 0; padding: 0;'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('template');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
		$output .= "<label for='style'>"._DISPLAY.": </label><select name='style'class='textbox' ><option value='0'".(!$style ? " selected" : "").">"._CHART."</option>
				<option value='1'".($style == 1 ? " selected" : "").">"._NARRATIVE."</option>
				<option value='2'".($style == 2 ? " selected" : "").">"._VARIABLES."</option></select><br />
			<INPUT type='submit' class='button' name='submit' value='"._SUBMIT."'></form></div>";
	}
?>