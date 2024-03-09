<?php
if(!defined("_CHARSET")) exit( );
$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks WHERE block_name = 'login'");
while($block = dbassoc($blockquery)) {
	if ($block['block_variables'])
	{
		$blocks[$block['block_name']] = unserialize($block['block_variables']);
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	 $blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
global $language;
// Set $loggedin to 0 to see the block
include("blocks/login/login.php");
// Then set it back to 1. 
if(file_exists("blocks/login/$language.php")) include("blocks/login/$language.php");
else include("blocks/login/en.php");
	if(isset($_POST['submit'])) {
		$blocks['login']['acctlink'] = $_POST['acctlink'];
		if(empty($_POST['form'])) unset($blocks['login']['form']);
		else $blocks['login']['form'] = $_POST['form'];
		$blocks['login']['template'] = addslashes(descript($_POST['template']));
		save_blocks( $blocks );
		unset($_GET['admin']);
		$output .= write_message(_ACTIONSUCCESSFUL);
	}
	else {
		if(empty($blocks['login']['template'])) $template = "";
		else $template = $blocks['login']['template'];
		$output .= "<div style='text-align: center;'><span class='label'>"._CURRENT.":</span><br /><div class=\"tblborder\" style=\"width: 80%; text-align: left; margin: 1ex auto;\">$content</div></div><br />";
		$output .= _LOGINNOTE."<div id='settingsform'><form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks&admin=login\">
			<div><label for=\"template\">"._TEMPLATE.":</label><span style='clear: left;'>&nbsp;</span></div>
			<div class=\"shorttextarea\"><textarea name=\"template\" rows=\"5\" style=\"width: 100%;\" cols=\"40\">".stripslashes($template)."</textarea>";
		if($tinyMCE) 
			$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('template');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
		$output .= "</div>
			<label for=\"form\">"._DEFAULTOPTS.":</label> <select name=\"form\" id=\"form\" class=\"textbox\" ><option value=\"0\"".(empty($blocks['login']['form']) ? " selected" : "").">"._SHORT."</option>
					<option value=\"1\"".(!empty($blocks['login']['form']) ? " selected" : "").">"._LONG."</option>
			</select><br />
			<label for=\"acctlink\">"._ACCTLINK."</label> <select name=\"acctlink\" id=\"acctlink\" class=\"textbox\" ><option value=\"0\"".(empty($blocks['login']['acctlink']) ? " selected" : "").">"._NO."</option>
					<option value=\"1\"".(!empty($blocks['login']['acctlink']) ? " selected" : "").">"._YES."</option>
			</select><br />
			<INPUT type=\"submit\" class=\"button\" id=\"submit\" name=\"submit\" value=\""._SUBMIT."\"></form></div>";
	}
?>