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

function warnings( ) {
	global $allowed_tags, $dbconnect;

	$output = "<center><h4>"._WARNINGS."</h4></center>";
	if($_GET["delete"]) {
		$wid = $_GET["delete"];
		if($_GET["confirm"] == "yes")
		{
			$result5 = dbquery("SELECT warning FROM ".TABLEPREFIX."fanfiction_warnings WHERE wid = '$wid'");
			$warnings = dbassoc($result5);
			$newquery5 = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_stories WHERE FIND_IN_SET($wid, wid) > 1");
				while($warningresult = dbassoc($newquery5))
				{
					$tok = strtok($warningresult['wid'], ", ");// tokenize the old list of names
					$newString = "";// the new list of good names
					while($tok)
					{
						if( $tok != $warnings['warning'] )// oldname is the thing that is going away
						{
							// It's a keeper, so decide if it is first or not for comma-age, then add it in
							if( $newString != "" )
								$newString .= ", ";
							$newString .= $tok;
						}
						$tok = strtok(", "); //advance to the next token
					}
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET wid = '$newString' WHERE sid = '".$warningresult['sid']."'");
				}
			dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_warnings where wid = '$wid'");
			$output .= "<center>"._ACTIONSUCCESSFUL."</center>";
		}
		else if ($_GET["confirm"] == "no")
		{
			$output .= "<center>"._ACTIONCANCELLED."</center>";
		}
		else
		{
			$output .= "<center>"._CONFIRMDELETE."<br /><br />";
			$output .= "[ <a href=\"admin.php?action=warnings&delete=$wid&confirm=yes\">"._YES."</a> | <a href=\"admin.php?action=warnings&delete=$wid&confirm=no\">"._NO."</a> ]</center>";
			return $output;
		}
	}
	if ($_POST['submit']) {
		if($_GET['warning'] == "new") dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_warnings (warning) VALUES ('".addslashes(stripinput(strip_tags($_POST['warning'])))."')") or die(_FATALERROR."Query: INSERT INTO ".TABLEPREFIX."fanfiction_warnings (warning) VALUES ('".addslashes(strip_tags($_POST['warning']))."')<br />Error: (". $dbconnect->errno.") ".$dbconnect->error);
		else dbquery("UPDATE ".TABLEPREFIX."fanfiction_warnings set warning = '".addslashes(stripinput(strip_tags($_POST['warning'])))."' WHERE wid = '".$_GET['warning']."'") or die(_FATALERROR."Query: UPDATE ".TABLEPREFIX."fanfiction_warnings set warning = '".addslashes(strip_tags($_POST['warnings']))."' WHERE wid = '".$_GET['warning']."'<br />Error: (". $dbconnect->errno.") ". $dbconnect->error);
			$output .= "<center>"._ACTIONSUCCESSFUL."</center>";
	}
	else {
		if(isset($_GET['warning'])) {
			if($_GET['warning'] != "new") {
				$warnquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_warnings WHERE wid = '".$_GET['warning']."' LIMIT 1") or die(_FATALERROR."Query: SELECT * FROM ".TABLEPREFIX."fanfiction_warnings WHERE wid = '".$_GET['warning']."' LIMIT 1<br />Error: (". $dbconnect->errno.") ". $dbconnect->error);
				$warning = dbassoc($warnquery);
			}
			$output .=  "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=warnings&warning=".$_GET['warning']."\">
			<table align=\"center\">	<tr><td colspan=\"2\">	<b>".($_GET['warning'] == "new" ? _NEWWARNING : _EDITWARNING)."</b></td></tr>
			<tr><td>"._WARNING.": <A HREF=\"javascript:pop('docs/adminhints.htm#warnings');\">[?]</A>
			</td><td><INPUT  type=\"text\" class=\"textbox=\"  name=\"warning\" value=\"".$warning['warning']."\"></td></tr>
			<tr><td colspan=\"2\"><INPUT type=\"submit\" class=\"button\" value=\""._SUBMIT."\" name=\"submit\"></td></tr></table></form>";
		}
		else {

			$result = dbquery("SELECT * from ".TABLEPREFIX."fanfiction_warnings");

			//List of current warnings

			$output .= "<table class=\"tblborder\" cellpadding=\"3\" cellspacing=\"0\" align=\"center\">
			<tr><th colspan=\"2\" align=\"center\">"._WARNINGS."</th></tr>
			<tr><td colspan=\"2\" align=\"center\" class=\"tblborder\"><a href=\"admin.php?action=warnings&warning=new\">"._ADDWARNING."</a></td></tr>";
			while ($warningresults = dbassoc($result))
			{
				$output .=  "<tr><td class=\"tblborder\">".$warningresults['warning'];
				$output .=  "</td><td class=\"tblborder\"><a href=\"admin.php?action=warnings&warning=".$warningresults['wid']."\">"._EDIT."</a> | <a href=\"admin.php?action=warnings&delete=".$warningresults['wid']."\">"._DELETE."</td></tr>";
			}
			$output .= "</table>";

		}
	}
	return $output;
}
// end warnings function

?>