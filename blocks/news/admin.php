<?php
if(!defined("_CHARSET")) exit( );
 
$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks WHERE block_name = 'news'");
while($block = dbassoc($blockquery)) {
	if ($block['block_variables'])
	{
		$blocks[$block['block_name']] = unserialize($block['block_variables']);
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	 $blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
global $language, $numupdated;

if (file_exists(_BASEDIR . "blocks/news/" . $language . ".php")) include(_BASEDIR . "blocks/news/" . $language . ".php");
else include(_BASEDIR . "blocks/news/en.php");

include("blocks/".$blocks['news']['file']);
	if(isset($_POST['submit'])) {
		if(isset($_POST['num']) && isNumber($_POST['num'])) $blocks['news']['num'] = $_POST['num'];
		else $blocks['news']['num'] = 1;
		if(isset($_POST['sumlength']) && isNumber($_POST['sumlength'])) $blocks['news']['sumlength'] = $_POST['sumlength'];
		else unset($blocks['news']['sumlength']);
		$output .= "<div style='text-align: center;'>"._ACTIONSUCCESSFUL."</div>";
		save_blocks( $blocks );
	}
	else  {
		if(!isset($blocks['news']['sumlength'])) $blocks['news']['sumlength'] = "";
		$output .= "<div style='text-align: center;'><b>"._CURRENT.":</b><br /><div class=\"tblborder\" style=\"width: 80%; margin: 0 auto; text-align: left;\">$content</div><br /></div>";
		$output .= "<div><div id='settingsform'><form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks&admin=news\">
			<div><label for=\"num\">"._NUMNEWS.":</label><input type=\"text\" class=\"textbox\" name=\"num\" id=\"num\" size=\"4\" value=\"".$blocks['news']['num']."\"></div>
		<div><label for=\"num\">"._SUMLENGTH.":</label><input type=\"text\" class=\"textbox\" name=\"sumlength\" id=\"sumlength\" size=\"6\" value=\"".$blocks['news']['sumlength']."\"></div>
			<INPUT type=\"submit\" name=\"submit\" class=\"button\" id=\"submit\" value=\""._SUBMIT."\"></form></div><div style='clear: both;'></div></div>";
	}
?>