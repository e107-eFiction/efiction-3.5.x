<?php
// ----------------------------------------------------------------------
// eFiction 3.2
// Copyright (c) 2007 by Tammy Keefer
// Valid HTML 4.01 Transitional
// Based on eFiction 1.1
// Copyright (C) 2003 by Rebecca Smallwood.
// http://efiction.sourceforge.net/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------

if(!defined("_CHARSET")) exit( );

	if(!$uid) $output .= write_error(_ERROR);
	else {
		$author = dbquery("SELECT "._PENNAMEFIELD." as penname, "._EMAILFIELD." as email FROM "._AUTHORTABLE." WHERE "._UIDFIELD." = '$uid' LIMIT 1");
		list($penname, $email) = dbrow($author);
		$output .= "<div class='sectionheader'>"._CONTACTAUTHOR."</div>";
		if(isset($_POST['submit'])) {
			if($captcha && !isMEMBER && !captcha_confirm()) $output .= write_error(_CAPTCHAFAIL);
			else {
				include_once(_BASEDIR."includes/emailer.php");
				if(!$email) $output .= write_error(_ERROR);
				else $result = sendemail($penname, $email, $_POST['email'], $siteemail , descript(strip_tags($_POST['subject'])), descript($_POST['comments'])."<br /><br />".(isMEMBER ? sprintf(_SITESIG2, "<a href='".$url."/viewuser.php?uid=".USERUID."'>".USERPENNAME."</a>") : _SITESIG), "html");
				if($result)  $output .= write_message(_ACTIONSUCCESSFUL);
				else $output .= write_error(_ERROR);
			}
		}
		else {
			$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" style=\"width: 400px; margin: 0 auto;\" action=\"viewuser.php?action=contact&amp;uid=$uid\">
				<label for=\"email\">"._YOUREMAIL.":</label> <INPUT type=\"text\" class=\"textbox\" name=\"email\"><br />
				<label for=\"subject\">"._SUBJECT.":</label> <INPUT  type=\"text\" class=\"textbox\" name=\"subject\"><br />
				<label for=\"comments\">"._COMMENTS.":</label> <TEXTAREA  class=\"textbox\" name=\"comments\" cols=\"50\" rows=\"6\"></TEXTAREA><br />";
			if(!USERUID && !empty($captcha)) $output .= "<div><span class=\"label\">"._CAPTCHANOTE."</span><input MAXLENGTH=5 SIZE=5 name=\"userdigit\" type=\"text\" value=\"\"><br /><img width=120 height=30 src=\""._BASEDIR."includes/button.php\" style=\"border: 1px solid #111;\"></div>";
			$output .= "<div style=\"text-align: center;\"><INPUT name=\"submit\" class=\"button\" type=\"submit\" value=\""._SUBMIT."\"></div></form>";
			$output .= write_message(_REQUIREDFIELDS._RESPECTNOTE);
		}
	}
	$tpl->assign( "output", $output );	
?>