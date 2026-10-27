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

$current = "adminarea";

if(isset($_GET['action']) && ($_GET['action'] == "categories" || $_GET['action'] == "admins")) $displayform = 1;

$disableTiny = true;

include ("header.php");



//make a new TemplatePower object
if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
else $tpl = new TemplatePower("default_tpls/default.tpl");
include("includes/pagesetup.php");
if(file_exists("languages/".$language."_admin.php")) include_once("languages/".$language."_admin.php");
else include_once("languages/en_admin.php");
// end basic page setup

// check that user has permissions to perform this action before going further.  Otherwise kick 'em
	if(!isADMIN) accessDenied( );
	// The admin panels put these values straight into SQL. They are all ids, so anything
	// else (other than the few words the panels use) is refused here once for all panels.
	$ef_idkeys = array('uid', 'sid', 'rid', 'chapid', 'catid', 'parentcatid', 'charid', 'class', 'type_id', 'subof', 'field', 'displayorder',
		'after', 'orderafter', 'revoke', 'lock', 'unlock', 'validate', 'release', 'retire', 'remove', 'feature', 'level', 'adlevel', 'category',
		'cat', 'delete', 'edit', 'warning', 'reviewid', 'seriesid', 'inorder', 'message_id', 'link_id', 'nid', 'cid', 'panel_level');
	// "type" is used by admin/classifications.php (delete=type)
	$ef_words = array('', 'new', 'class', 'type', 'yes', 'no', 'all', '-1', 'on');
	// these are posted as text (category name in admin/categories.php, warning text in admin/warnings.php)
	$ef_posttext = array('category', 'warning');
	foreach(array('_GET' => $_GET, '_POST' => $_POST) as $ef_src => $ef_vals) {
		foreach($ef_idkeys as $ef_k) {
			if(!isset($ef_vals[$ef_k])) continue;
			if($ef_src == '_POST' && in_array($ef_k, $ef_posttext, true)) continue;
			foreach((array)$ef_vals[$ef_k] as $ef_v) {
				if(is_array($ef_v) || (!in_array((string)$ef_v, $ef_words, true) && !preg_match('/^-?[0-9]+(,-?[0-9]+)*$/', (string)$ef_v))) accessDenied(_ERROR." (".$ef_k.")");
			}
		}
	}
	unset($ef_idkeys, $ef_words, $ef_posttext, $ef_src, $ef_vals, $ef_k, $ef_v);
	$adminquery = dbquery("SELECT categories FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE uid = '".USERUID."' LIMIT 1");
	list($admincats) = dbrow($adminquery);
	if(empty($admincats)) $admincats = "0";
	$output = "<div style='text-align: center; margin: 1em;'>";
	$panelquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_hidden != '1' AND panel_type = 'A' AND panel_level >= ".uLEVEL." ORDER BY panel_level DESC, panel_order ASC, panel_title ASC");
	if(!dbnumrows($panelquery)) $output .= _FATALERROR;
	$panellist = array();
	while($panel = dbassoc($panelquery)) {
		if(!$panel['panel_url']) $panellist[$panel['panel_level']][]= "<a href=\"admin.php?action=".$panel['panel_name']."\">".$panel['panel_title']."</a>";
		else $panellist[$panel['panel_level']][] = "<a href=\"".$panel['panel_url']."\">".$panel['panel_title']."</a>";
	}
	foreach($panellist as $accesslevel => $row) {
		$output .= implode(" | ", $row)."<br />";
	}
	$output .= "</div>\n";
	if($action) {
		$panelquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_name = '$action' AND panel_type = 'A' LIMIT 1");
		if(dbnumrows($panelquery)) {
			$panel = dbassoc($panelquery);
			if((isset($panel['panel_level']) ? $panel['panel_level'] : 0) >= uLEVEL) {
				if($panel['panel_url'] && file_exists(_BASEDIR.$panel['panel_url'])) require_once(_BASEDIR.$panel['panel_url']);
				else if (file_exists("admin/{$action}.php")) require_once("admin/{$action}.php");
			}
			else accessDenied( );
		}
	}
	else {
		if (file_exists("install"))
			$output .= write_error(_SECURITYDELETE);
		$adminnotices = "";
		$countquery = dbquery("SELECT COUNT(DISTINCT chapid) FROM ".TABLEPREFIX."fanfiction_chapters WHERE validated < 1");
		list($count) = dbrow($countquery);
		if($count) $adminnotices .= write_message(sprintf(_QUEUECOUNT, $count));
		$codequery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'adminnotices'");
		while($code = dbassoc($codequery)) {
			eval($code['code_text']);
		}

		$output .= write_message("Actual time: ". time() );
		$output .= write_message("Date format: " . $dateformat);
		$output .= write_message(date("$dateformat", time())); 	
 
		$output .= write_message($adminnotices);
		$output .= write_message(_RUNNINGVERSION);
	}	
	$tpl->assign( "output", $output );
	$tpl->printToScreen();
	dbclose( );
?>