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

list($field_id, $field_title) = dbrow(dbquery("SELECT field_id, field_title FROM ".TABLEPREFIX."fanfiction_authorfields WHERE field_name = 'betareader'"));
$query = dbquery("SELECT count(uid) FROM ".TABLEPREFIX."fanfiction_authorinfo WHERE field = '$field_id' AND info = '"._YES."'");
list($count) = dbrow($query);
if(!empty($blocks['info']['style']) && $blocks['info']['style'] == 2) {
	$tpl->assignGlobal("totalbetas", $count);
}
else if(!empty($blocks['info']['style']) && $blocks["info"]["style"] == 1) {
	$content = preg_replace("@\{totalbetas\}@", $count, $content);
}
else $content .= "<div><span class='label'>".$field_title.": </span>".$count."</div>";
?>