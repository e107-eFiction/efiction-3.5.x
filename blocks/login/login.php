<?php
if(!defined("_CHARSET")) exit( );

	$longform = "{penname} {password} {rememberme} {go} <div id='loginlinks'>{register} | {lostpwd}</div>";
	$shortform = "{penname} {password} {rememberme} {go}";
if(!isMEMBER) {
	$content = "<form method=\"POST\" id=\"loginblock\" enctype=\"multipart/form-data\" action=\""._BASEDIR."user.php?action=login\">";
	$replace = array("<label for=\"penname=\">"._PENNAME.":</label><INPUT type=\"text\" class=\"textbox\" name=\"penname\" id=\"penname\" size=\"15\">", 
			"<label for=\"password\">"._PASSWORD.":</label><INPUT type=\"password\" class=\"textbox\" name=\"password\" id=\"password\" size=\"15\">",
			"<span id='rememberme'><INPUT type=\"checkbox\" class=\"checkbox\" name=\"cookiecheck\" id=\"cookiecheck\" value=\"1\"><label for=\"cookiecheck=\">"._REMEMBERME."</label></span>",
			(!empty($pagelinks['register']['link']) ? $pagelinks['register']['link'] : ""), $pagelinks['lostpassword']['link'],
			"<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._GO."\">");
	$search = array("@\{penname\}@", "@\{password\}@", "@\{rememberme\}@", "@\{register\}@", "@\{lostpwd\}@", "@\{go\}@");
	if(!empty($blocks['login']['template'])) $content .= preg_replace($search, $replace, stripslashes($blocks['login']['template']));
	else $content .= preg_replace( $search, $replace , (!empty($blocks['login']['form']) ? $longform : $shortform));		
	$content .= "</form>";
}
else if(!empty($blocks['login']['acctlink'])) $content = $pagelinks['login']['link'];
else $content = "";

?>