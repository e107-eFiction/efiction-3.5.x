<?php
if(!defined("_CHARSET")) exit( );
	$content = "<form method=\"POST\" id=\"searchblock\" enctype=\"multipart/form-data\" action=\""._BASEDIR."search.php?action=advanced\">
			<INPUT type=\"text\" class=\"textbox\" name=\"searchterm\" id=\"searchterm\" size=\"15\"> 
			<INPUT type=\"hidden\" name=\"searchtype\" value=\"advanced\">
			<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SEARCH."\"></form>";

?>