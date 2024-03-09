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


if(!defined("_CHARSET")) exit( );

// Delete characters
if($action == "characters") {
	$chalquery = dbquery("SELECT characters, chalid FROM ".TABLEPREFIX."fanfiction_challenges WHERE FIND_IN_SET('$charid', characters) > 0");
	while($chal = dbassoc($chalquery)) {
		$newcharlist = array_diff(explode(",", $chal['characters']), array($charid));
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_challenges SET characters = '".($newcharlist ? implode(",", $newcharlist) : "")."' WHERE chalid = '$chal[chalid]'");
	}
}

// Delete categories 
if($action == "categories") {
	$chalquery = dbquery("SELECT catid, chalid FROM ".TABLEPREFIX."fanfiction_challenges WHERE FIND_IN_SET('$catid', catid) > 0");
	while($chal = dbassoc($chalquery)) {
		$newcatlist = array_diff(explode(",", $chal['catid']), array($catid));
		dbquery("UPDATE ".TABLEPREFIX."fanfiction_challenges SET catid = '".($newcatlist ? implode(",", $newcatlist) : "")."' WHERE chalid = '$chal[chalid]'");
	}
}

?>