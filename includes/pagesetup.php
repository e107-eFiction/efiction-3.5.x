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


/* 
This file does some of the setup of the common elements of the pages within your site. 
It also checks the common $_GET variables and cleans them up to prevent hacking and attacks.
*/

if(!defined("_CHARSET")) exit( );

$favtypes = array("SE" => "series", "ST" => "stories", "AU" =>"authors");
$revtypes = array("SE" => "series", "ST" => "stories");

$catlist = array( );
$catresults = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_categories ORDER BY leveldown, displayorder");
while($cat = dbassoc($catresults)) {
	$catlist[$cat['catid']] = array("name" => stripslashes($cat['category']), "pid" => $cat['parentcatid'], "order" => $cat['displayorder'], "locked" => $cat['locked'], "leveldown" => $cat['leveldown']);
}
$charlist = array( );
$result = dbquery("SELECT charname, catid, charid FROM ".TABLEPREFIX."fanfiction_characters ORDER BY charname");
while($char = dbassoc($result)) {
	$charlist[$char['charid']] = array("name" => stripslashes($char['charname']), "catid" => $char['catid']);
}
$classlist = array( );
$classresults = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classes ORDER BY class_name");
while($class = dbassoc($classresults)) {
	$classlist[$class['class_id']] = array("type" => $class['class_type'], "name" => stripslashes($class['class_name']));
}
$classtypelist = array( );
$classtyperesults = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_classtypes ORDER BY classtype_name");
while($type = dbassoc($classtyperesults)) {
	$classtypelist[$type['classtype_id']] = array("name" => $type['classtype_name'], "title" => stripslashes($type['classtype_title']));
}
$ratlist = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_ratings");
while($rate = dbassoc($ratlist)) {
	$ratingslist[$rate['rid']] = array("name" => $rate['rating'], "ratingwarning" => $rate['ratingwarning'], "warningtext" => $rate['warningtext']);
}
unset($catresult, $result, $classresults, $classtyperesults, $ratlist, $type, $rate, $class, $char, $cat);
$action = escapestring($action);
// Set up the page template

if($action != "printable") {
	$tpl->assignInclude( "header", "./$skindir/header.tpl" );
	$tpl->assignInclude( "footer", "./$skindir/footer.tpl" );
}
$tpl->prepare( );
// End page template setup

if(file_exists("$skindir/variables.php")) include("$skindir/variables.php");

// If they weren't set in variables.php, set the defaults for these 
if(!isset($up)) $up = "<img src=\"images/arrowup.gif\" border=\"0\" width=\"13\" height=\"18\" align=\"left\" alt=\""._UP."\">";
if(!isset($down)) $down = "<img src=\"images/arrowdown.gif\" border=\"0\" width=\"13\" height=\"18\" align=\"right\" alt=\""._DOWN."\">";

$linkquery = dbquery("SELECT * from ".TABLEPREFIX."fanfiction_pagelinks ORDER BY link_access ASC");
if(!isset($current)) $current = "";

while($link = dbassoc($linkquery)) {
	if($link['link_access'] && !isMEMBER) continue;
	if($link['link_access'] == 2 && uLEVEL < 1) continue;
	if($link['link_name'] == "register" && isMEMBER) continue;
	if(strpos($link['link_url'], "http://") === false && strpos($link['link_url'], "https://") === false) $link['link_url'] = _BASEDIR.$link['link_url'];
	$tpl->assignGlobal($link['link_name'], "<a href=\"".$link['link_url']."\" title=\"".$link['link_text']."\"".($link['link_target'] ? " target=\"_blank\"" : "").(!empty($link['link_key']) ? " accesskey='".$link['link_key']."'" : "").($current == $link['link_name'] ? " id=\"current\"" : "").">".$link['link_text']."</a>");
	$pagelinks[$link['link_name']] = array("id" => $link['link_id'], "text" => $link['link_text'], "url" => $link['link_url'], "key" => $link['link_key'], "link" => "<a href=\"".$link['link_url']."\" title=\"".$link['link_text']."\"".(!empty($link['link_key']) ? " accesskey='".$link['link_key']."'" : "").($link['link_target'] ? " target=\"_blank\"" : "").($current == $link['link_name'] ? " id=\"current\"" : "").">".$link['link_text']."</a>");
}

if($action != "printable") $tpl->newBlock("header");
$tpl->assignGlobal("sitename", $sitename);
$tpl->assignGlobal("slogan", $slogan);
$tpl->assignGlobal("page_id", $current);
$tpl->assignGlobal("basedir", _BASEDIR);
$tpl->assignGlobal("skindir", $skindir);	
$tpl->assignGlobal("rss", "<a href='"._BASEDIR.$pagelinks['rss']['url']."'><img src='"._BASEDIR."images/xml.gif' alt='RSS' title = 'RSS' border='0'></a>");
if(isset($pagelinks['rss'])) $tpl->assignGlobal("columns", $displaycolumns);
if($action != "printable") {
	$tpl->newBlock("footer");
	$copy = dbquery("SELECT message_text FROM ".TABLEPREFIX."fanfiction_messages WHERE message_name = 'copyright' LIMIT 1");
	if($copy) list($copyright) = dbrow($copy);
	$tpl->assign( "footer", $copyright);
	$tpl->gotoBlock( "_ROOT" );
}
foreach($blocks as $block=>$value) {
	if(empty($value['status']) || ($value['status'] == 2 && $current != "home")) continue;
	if(empty($value['file'])) continue;
	if($value['status'] && file_exists(_BASEDIR."blocks/".$value['file'])) {
		$content = "";
		$tpl->assignGlobal($block."_title", !empty($value['title']) ? stripslashes($value['title']) : "");
		if(file_exists(_BASEDIR."blocks/".$value['file'])) include(_BASEDIR."blocks/".$value['file']);
		$tpl->assignGlobal($block."_content", $content);
	}
}

$tpl->gotoBlock( "_ROOT" );
?>