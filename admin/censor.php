<?php
// ----------------------------------------------------------------------
// eFiction 3.0
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
	$output .= "<div id=\"pagetitle\">"._CENSOR."</div>";
	if(isset($_POST['submit'])) {
		if($_POST["words"]) {
			$wordlist = explode(",", trim(strip_tags($_POST['words'])));
			foreach($wordlist as $word) {
				$word = trim($word);
				if(strlen($word) > 0) $newwords[] = $word;
			}
		}
		$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET words = '".(is_array($newwords) ? implode(", ", $newwords) : "")."' WHERE sitekey = '".SITEKEY."'");
		if($result) $output .= write_message(_ACTIONSUCCESSFUL);
		else $output .= write_error(_ERROR);
	}
	else {
		if(is_array($words)) $wordlist = implode(", ", $words);
		else $wordlist = "";
		$output .= "<div style=\"text-align: center; margin: 1em; \">"._CENSORDIRS."</div>";
		$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=censor\" style=\"width: 500px; margin: 0 auto;\">
			<textarea name=\"words\" rows=\"7\" cols=\"60\">$wordlist</textarea><br /><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></form>";
	}

?>