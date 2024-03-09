<?php
// ----------------------------------------------------------------------
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

$current = "contactus";

include ("header.php");

	//make a new TemplatePower object
if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
else $tpl = new TemplatePower("default_tpls/default.tpl");
$tpl->assignInclude( "header", "./$skindir/header.tpl" );
$tpl->assignInclude( "footer", "./$skindir/footer.tpl" );
include("includes/pagesetup.php");

	$output .= "<h1>"._CONTACTUS."</h1>";

	if(isset($_POST['submit'])) {
		if($captcha && !isMEMBER && !captcha_confirm()) $output .= write_error(_CAPTCHAFAIL);
		else {
			include("includes/emailer.php");
			$result = sendemail($sitename, $siteemail, $_POST['email'], $_POST['email'], (!empty($_POST['reportpage']) ? _REPORT.": " : "").descript(strip_tags($_POST['subject'])), format_story(descript($_POST['comments'])).(!empty($_POST['reportpage']) ? "<br /><br /><a href='$url/".$_POST['reportpage']."'>$url/".$_POST['reportpage']."</a>" : "").(isMEMBER ? sprintf(_SITESIG2, "<a href='".$url."/viewuser.php?uid=".USERUID."'>".USERPENNAME."</a>") : _SITESIG), "html");
			if($result) $output .= write_message(_EMAILSENT);
			else $output .= write_error(_EMAILFAILED);
		}
	}
	else
	{
		$output .= "<form method='POST' enctype='multipart/form-data' action='contact.php'>
		<table class='acp'><tr><td><label for='email'>"._YOUREMAIL.":</label></td><td><INPUT type='text' class='textbox' name='email'></td></tr>";
		if(!$action) $output .= "<tr><td><label for='subject'>"._SUBJECT.":</label></td><td><INPUT  type='text' class='textbox' name='subject'></td></tr>";
		else if($action == "report") $output .= "<tr><td><label for='subject'>"._REPORT.":</label></td><td><select class='textbox' name='subject'>
			<option>"._RULESVIOLATION."</option>
			<option>"._BUGREPORT."</option>
			<option>"._MISSING."</option>
		</select>
		<input type='hidden' name='reportpage' value='".descript($_GET['url'])."'></td></tr>";
		$output .= "<tr><td><label for='comments'>"._COMMENTS.":</label></td><td> <TEXTAREA  class='textbox' name='comments' cols='50' rows='6'></TEXTAREA></td></tr>";
		if(!USERUID && !empty($captcha)) $output .= "<tr><td><span class='label'>"._CAPTCHANOTE."</span></td><td><img width=120 height=30 src='"._BASEDIR."includes/button.php' alt='CAPTCHA image'><br /><br /><input MAXLENGTH=5 SIZE=5 name='userdigit' type='text' value=''></td></tr>";
		$output .= "<tr><td colspan='2'><div style='text-align: center;'><INPUT name='submit' class='button' type='submit' value='"._SUBMIT."'></div></td></tr></table></form>";
	}

	$tpl->assign( "output", $output );	
	$tpl->printToScreen();
	dbclose( );
	?>