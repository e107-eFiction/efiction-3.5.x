<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
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

function genres( ) {
	global $allowed_tags;

	$output .= "<center><h4>"._GENRES."</h4></center>";
	if($_GET["delete"]) {
		$gid = $_GET["delete"];
		if($_GET["confirm"] == "yes")
		{
			$result5 = dbquery("SELECT genre FROM ".TABLEPREFIX."fanfiction_genres WHERE gid = '$gid'") or die(_FATALERROR."Query: SELECT genre FROM ".TABLEPREFIX."fanfiction_genres WHERE gid = '$gid'<br />Error: (".mysql_errno( ).") ".mysql_error( ));
			$genres = dbassoc($result5);
			$newquery5 = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_stories WHERE gid LIKE '%$genres[genre]%'") or die(_FATALERROR."Query: SELECT * FROM ".TABLEPREFIX."fanfiction_stories WHERE gid LIKE '%$genres[genre]%'<br />Error: (".mysql_errno( ).") ".mysql_error( ));
				while($genreresult = dbassoc($newquery5))
				{
					$tok = strtok($genreresult[gid], ", ");// tokenize the old list of names
					$newString = "";// the new list of good names
					while($tok)
					{
						if( $tok != $genres[genre] )// oldname is the thing that is going away
						{
							// It's a keeper, so decide if it is first or not for comma-age, then add it in
							if( $newString != "" )
								$newString .= ", ";
							$newString .= $tok;
						}
						$tok = strtok(", "); //advance to the next token
					}
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET gid = '$newString' WHERE sid = '$genreresult[sid]'")  or die(_FATALERROR."Query: UPDATE ".TABLEPREFIX."fanfiction_stories SET gid = '$newString' WHERE sid = '$genreresult[sid]'<br />Error: (".mysql_errno( ).") ".mysql_error( ));
				}
			dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_genres where gid = '$gid'") or die(_FATALERROR."Query: DELETE FROM ".TABLEPREFIX."fanfiction_genres where gid = '$gid'<br />Error: (".mysql_errno( ).") ".mysql_error( ));
			$output .= "<center>"._ACTIONSUCCESSFUL."</center>";
		}
		else if ($_GET["confirm"] == "no")
		{
			$output .= "<center>"._ACTIONCANCELLED."</center>";
		}
		else
		{
			$output .= "<center>"._CONFIRMDELETE."<br /><br />";
			$output .= "[ <a href=\"admin.php?action=genres&amp;delete=$gid&amp;confirm=yes\">"._YES."</a> | <a href=\"admin.php?action=genres&delete=$gid&confirm=no\">"._NO."</a> ]</center>";
			return $output;
		}
	}
	if ($_POST[submit]) {
		if($_GET[genre] == "new") dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_genres (genre) VALUES ('".addslashes(stripinput(strip_tags($_POST[genre])))."')") or die(_FATALERROR."<br />Error: (".mysql_errno( ).") ".mysql_error( ));
		else dbquery("UPDATE ".TABLEPREFIX."fanfiction_genres set genre = '".addslashes(stripinput(strip_tags($_POST[genre])))."' WHERE gid = '$_GET[genre]'") or die(_FATALERROR."<br />Error: (".mysql_errno( ).") ".mysql_error( ));
			$output .= "<center>"._ACTIONSUCCESSFUL."</center>";
	}
	else {
		if(isset($_GET[genre])) {
			if($_GET[genre] != "new") {
				$genrequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_genres WHERE gid = '$_GET[genre]' LIMIT 1") or die(_FATALERROR."<br />Error: (".mysql_errno( ).") ".mysql_error( ));
				$genre = dbassoc($genrequery);
			}
			$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=genres&genre=$_GET[genre]\">
			<table align=\"center\"><tr><td colspan=\"2\" align=\"center\"><b>".($_GET[genre] == "new" ? _NEWGENRE : _EDITGENRE)."</b></td></tr>
			<tr><td>"._GENRE.": <A HREF=\"javascript:pop('docs/adminhints.htm#genres');\">[?]</A></td>
			<td><INPUT type=\"text\" class=\"textbox=\"  name=\"genre\"".($_GET[genre] != "new" ? "value=\"$genre[genre]\"" : "")."></td></tr>
			<tr><td colspan=\"2\" align=\"right\"><INPUT type=\"submit\" class=\"button\" value=\""._SUBMIT."\" name=\"submit\"></td></tr></table></form>";
		}
		else {

			$result = dbquery("SELECT * from ".TABLEPREFIX."fanfiction_genres");

			//List of current genres

			$output .= "<table class=\"tblborder\" cellpadding=\"3\" cellspacing=\"0\" align=\"center\">
			<tr><th colspan=\"2\" align=\"center\">"._GENRES."</th></tr>
			<tr><td colspan=\"2\" align=\"center\" class=\"tblborder\"><a href=\"admin.php?action=genres&genre=new\">"._ADDGENRE."</a></td></tr>";
			while ($genreresults = dbassoc($result))
			{
				$output .= "<tr><td class=\"tblborder\">$genreresults[genre]";
				$output .= "</td><td class=\"tblborder\"><a href=\"admin.php?action=genres&genre=$genreresults[gid]\">"._EDIT."</a> | <a href=\"admin.php?action=genres&delete=$genreresults[gid]\">"._DELETE."</td></tr>";
			}
			$output .= "</table>";

		}
	}
	return $output;
}
// end genres function

?>