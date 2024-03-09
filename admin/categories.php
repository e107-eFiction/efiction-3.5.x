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

function build_cat_row($thiscat, $thiscatinfo) {
	global $catlist, $oddeven, $onpic, $down, $up;
	$space = "";
	if($thiscatinfo['leveldown'] % 2) $class = "odd";
	else $class = "even";
	for($count = 0; $count < $thiscatinfo['leveldown']; $count++) {
		$space .= "&nbsp;&nbsp;&nbsp;";
	}
	$output = "<tr id='catid_".$thiscat."' ".($thiscatinfo['leveldown'] > 0 ? "style='display: none;'" : "")." class='$class'>\n\t<td class='tblborder'>".$space;
	$subs = ""; $downlink = "&nbsp;"; $uplink = "&nbsp;";
	$$thiscat = $catlist;
	foreach($$thiscat as $cat => $catinfo) {
		if($catinfo['pid'] == $thiscatinfo['pid'] && $thiscatinfo['order'] > $catinfo['order']) $uplink = "<a href=\"admin.php?action=categories&go=up&displayorder=".$thiscatinfo['order']."&parentcatid=".$thiscatinfo['pid']."\">$up</a>";
		if($catinfo['pid'] == $thiscatinfo['pid'] && $thiscatinfo['order'] < $catinfo['order']) $downlink = "<a href=\"admin.php?action=categories&go=down&displayorder=".$thiscatinfo['order']."&parentcatid=".$thiscatinfo['pid']."\">$down</a>";
		if($catinfo['pid'] == $thiscat) $subs .= build_cat_row($cat, $catinfo);
	}
	if($subs) $output .= "<img onclick=\"javascript:displayCatRows('$thiscat')\" name='c_$thiscat' alt='on' src='".($onpic ? $onpic : "images/row_on.gif")."'> ";
	$output .= $thiscatinfo['name']."</td>\n
	<td class='tblborder'>$downlink</td>\n
	<td class='tblborder' width=\"13\">$uplink</td>\n
	<td class='tblborder'><a href=\"admin.php?action=categories&cat=$thiscat\">"._EDIT."</a> | <a href=\"admin.php?action=categories&delete=$thiscat\">"._DELETE."</a> 
			| <span class='label'>"._ADD.":</span> $space <a href=\"admin.php?action=categories&cat=new&subof=$thiscat\">"._SUBCATEGORY."</a>, <a href=\"admin.php?action=characters&do=addform&catid=$thiscat\">"._CHARACTERS."</a>,  <a href=\"admin.php?action=admins&category=$thiscat\">"._ADMINS."</a></td>\r\n</tr>$subs";
	return $output;
}

function relevelcategory($cat, $leveldown) {
	global $action;

	$subs = dbquery("SELECT catid FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = '$cat'");
	if(dbnumrows($subs)) {
		while($sub = dbassoc($subs)) {relevelcategory($sub[catid], $leveldown + 1); }
	}
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET leveldown = $leveldown WHERE catid = '$cat'");
}


	$showlist = 1;
	$output .= "<div id=\"pagetitle\">"._CATEGORIES." </div>";
	if(isset($_GET['catcounts'])) 
		$output .= catcounts( );
	if(isset($_GET["delete"]) && isNumber($_GET["delete"])) {
		$catid = $_GET["delete"];
		$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;
		if($confirm == "yes") {
			$result = dbquery("SELECT displayorder,leveldown, parentcatid FROM ".TABLEPREFIX."fanfiction_categories WHERE catid = '$catid'");
			$cat = dbassoc($result);
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = (displayorder - '1') WHERE displayorder > '$cat[displayorder]' AND parentcatid = '$cat[parentcatid]'");
			dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_categories WHERE catid = '$catid'");
			dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = '$catid'");
			$stories = dbquery("SELECT title,sid, catid FROM ".TABLEPREFIX."fanfiction_stories WHERE FIND_IN_SET(catid, '$catid')");
			while($story = dbassoc($stories)) {
				$cats = explode(",", $story[catid]);
				if(count($cats) == 1) $newcat = "-1";
				else $newcat = implode(",", array_dif($cats, array($catid)));
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_stories SET catid = '$newcat' WHERE sid = '$story[sid]' LIMIT 1");					
			}
			$code = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'delcategory'");
			while($c = dbassoc($code)) {
				eval($c['code_text']);
			}
			$output .= write_message(_ACTIONSUCCESSFUL);
		}
		else if ($confirm == "no") {
			$output .= write_message(_ACTIONCANCELLED);
		}
		else {
			$showlist = 0;
			$output .= write_message(_CONFIRMDELETE."<br /><br />
[ <a href=\"admin.php?action=categories&delete=$catid&confirm=yes\">"._YES."</a> | <a href=\"admin.php?action=categories&delete=$catid&confirm=no\">"._NO."</a> ]");
		}
	}
	if (isset($_POST["submit"]) && !isset($_GET['catcounts'])) {
		if(isset($_POST['catid']) && $_POST['parentcatid'] == $_POST['catid']) {
			$output .= write_error(_ACTIONCANCELLED." "._CATERROR);
			$tpl->assign( "output", $output );
			$tpl->printToScreen();
			dbclose( );
			exit( );
		}
		if($_POST['parentcatid'] != "-1") 	{
			$parentquery = dbquery("SELECT leveldown FROM ".TABLEPREFIX."fanfiction_categories WHERE catid = '$_POST[parentcatid]'");
			$parentresult = dbassoc($parentquery);
			$leveldown = $parentresult['leveldown'] + 1;
		}
		else $leveldown = 0;
		$locked = (isset($_POST['locked']) && $_POST['locked'] == "on" ? "1" : "0");

		$displayorder = (isNumber($_POST['orderafter']) ? $_POST['orderafter'] : 0) + 1;
		if($_GET["cat"] == "new") { 
			$query = dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = (displayorder + 1) WHERE displayorder > '".$_POST['orderafter']."' AND leveldown = '$leveldown' AND parentcatid ='".$_POST['parentcatid']."'");
			$catresult = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_categories (category, parentcatid, description, locked, image, leveldown, displayorder) VALUES ('".addslashes(descript(strip_tags(trim($_POST['category']))))."', '".$_POST['parentcatid']."', '".addslashes(descript($_POST['description']))."', '$locked', '".$_POST['image']."', '$leveldown', '$displayorder')");
		}
		else {
			$oldinfo = dbquery("SELECT displayorder, parentcatid, leveldown FROM ".TABLEPREFIX."fanfiction_categories WHERE catid = '$_GET[cat]' LIMIT 1");
			list($oldorder, $oldparent, $oldleveldown) = dbrow($oldinfo);
			$catresult = "UPDATE ".TABLEPREFIX."fanfiction_categories SET category = '".addslashes(descript($_POST['category']))."', description = '".addslashes(descript($_POST['description']))."', locked = '$locked', parentcatid = '".$_POST['parentcatid']."', image = '".$_POST['image']."', leveldown = '$leveldown', displayorder = '$displayorder' WHERE catid = '".$_POST['catid']."'";
			if($oldparent == $_POST['parentcatid']) {
				if($oldorder && $oldorder < $displayorder) {
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = displayorder - 1 WHERE displayorder > $oldorder AND displayorder < $displayorder");
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = $displayorder - 1 WHERE catid = '".$_GET['cat']."'");
				}
				if($oldorder > $displayorder) {
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = displayorder + 1 WHERE displayorder >= $displayorder AND displayorder < $oldorder");
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = $displayorder WHERE catid = '".$_GET['cat']."'");
				}
			} 
			else {
				if($oldleveldown != $leveldown) relevelcategory($_GET['cat'], $leveldown);
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = displayorder -1 WHERE displayorder > $oldorder AND parentcatid = '$oldparent'");
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = displayorder + 1 WHERE displayorder > '".$_POST['orderafter']."' AND parentcatid = '".$_POST['parentcatid']."'");
			}
			$success = dbquery($catresult);
		}
		$selectA = "SELECT category, catid FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = -1 ORDER BY displayorder";
		$resultA = dbquery($selectA);
		$countA = 1;
		while($cat = dbassoc($resultA)) {
			$count = 1;
			if($cat['parentcatid'] = -1) {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = $countA WHERE catid = $cat[catid]");
				$countA++;
			}
			$selectB = "SELECT category, catid FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = '$cat[catid]' ORDER BY displayorder";
			$resultB = dbquery($selectB);
			while($sub = dbassoc($resultB)) {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = $count WHERE catid = $sub[catid]");
				$count++;
			}
		}
		$showlist = false;
		$output .= write_message(_ACTIONSUCCESSFUL."<br /><br />"."<a href='admin.php?action=categories'>"._CONTINUE."</a>");
	}
	else {
		if(isset($_GET["go"])) {
			$displayorder = $_GET["displayorder"];
			$oneabove =  ($_GET["go"] == "up" ?  $displayorder -1 : $displayorder + 1);
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = '-1' WHERE displayorder = '$displayorder' AND parentcatid = '".$_GET['parentcatid']."'");
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = '$displayorder' WHERE displayorder = '$oneabove' AND parentcatid = '".$_GET['parentcatid']."'");
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET displayorder = '$oneabove' WHERE displayorder = '-1' AND parentcatid = '".$_GET['parentcatid']."'");
			$catlist = array( );
			$catresults = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_categories ORDER BY leveldown, displayorder");
			while($cat = dbassoc($catresults)) {
				$catlist[$cat['catid']] = array("name" => stripslashes($cat['category']), "pid" => $cat['parentcatid'], "order" => $cat['displayorder'], "locked" => $cat['locked'], "leveldown" => $cat['leveldown']);
			}
		}

		if(isset($_GET["cat"])) {
			$subof = isset($_GET['subof']) ? $_GET['subof'] : -1;
			$after = isset($_GET['after']) ? $_GET['after'] : false;
			$cat = isset($_GET['cat']) ? $_GET['cat'] : "new";
			$new = isNumber($cat) ? false : true;
			if(isNumber($cat)) {
				$query = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_categories WHERE catid='$cat' LIMIT 1");
				$cat1 = dbassoc($query);
				$subof  = $cat1['parentcatid'];
				$after = $cat1['displayorder'] -1;
			}
			$showlist = 0;	
			$output .= "<div style='width: 100%;'><div  id=\"settingsform\"><form method='POST' enctype='multipart/form-data' name='form' action='admin.php?action=categories".($cat ? "&cat=$cat" : "")."'>
				<div class='sectionheader'>".(!$new ? _EDITCAT : _NEWCAT)."</div>
				<div><label for='parentcatid'>"._CATLEVEL.": </label>
				<select name='parentcatid' id='parentcatid' onchange=\"setCategoryForm(this);\"><option value='-1'>"._TOPLEVEL."</option>";
			if(count($catlist) > 0) {
				$opt2 = "";
				foreach($catlist as $c=> $cinfo) {
					if($subof == $cinfo['pid'] || $subof == $c) 
						$output .= "<option value='$c'".($subof == $c ? " selected" : "").">".$cinfo['name']."</option>";
					if($subof == $cinfo['pid'] || (!$subof && $cinfo['pid'] == -1))
						$opt2 .= "<option value='".$cinfo['order']."'".($after == $cinfo['order'] ? " selected" : "").">".$cinfo['name']."</option>";
				}
			}
			$output .= "</select> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_CATLEVEL."</span></A></div>";
			$output .= "<div><label for='orderafter'>"._ORDERAFTER.":</label> 
					<select name='orderafter' id='orderafter'>";
			$output .= "<option value='0'>"._MOVETOP."</option>".(isset($opt2) ? $opt2 : "")."</select> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_ORDERAFTER."</span></A></div>
				<div><label for='category'>"._CATNAME.": </label>
				<INPUT  type='text' class='textbox=' name='category'".($new ? ">" : "value='$cat1[category]'><input type='hidden' name='catid' value='$cat1[catid]'>")." <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_CATNAME."</span></A></div>
				<div><label for='description'>"._DESC.": </label><A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_CATDESC."</span></A>
				<div style='margin-left: 30%; padding-left: 1em;'><textarea class='textbox' id='description' name='description' id='description' cols='35' rows='4'>".(!$new ? $cat1['description'] : "")."</textarea></div>";
			if($tinyMCE) 
				$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('description');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
			
			$output.= "</div>
				<div><label for='locked'>"._LOCKED.": </label>
				<INPUT type='checkbox' class='checkbox' name='locked'".(!$new && $cat1['locked'] == 1 ? "checked" : "")."> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_LOCKED."</span></A></div>
				<div><label for='image'>"._IMAGE.": </label>
				<INPUT  type='text' class='textbox='  name='image'".(!$new && $cat1['image'] != "" ? "value='$cat1[image]'": "")."> <A HREF=\"#\" class=\"pophelp\">[?]<span>"._HELP_CATIMAGE."</span></A></div>
				<INPUT type='submit' class='button' id='submit' value='"._SUBMIT."' name='submit'>
				</form></div><div style='clear: both;'>&nbsp;</div></div>";
		}
	}

	if($showlist) {
		// Rebuild the category list to reflect any changes we just did.
			$catlist = array( );
			$catresults = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_categories ORDER BY leveldown, displayorder");
			while($cat = dbassoc($catresults)) {
				$catlist[$cat['catid']] = array("name" => stripslashes($cat['category']), "pid" => $cat['parentcatid'], "order" => $cat['displayorder'], "locked" => $cat['locked'], "leveldown" => $cat['leveldown']);
			}
		$output .= "
<table class=\"tblborder\" align=\"center\" cellspacing=\"0\" cellpadding=\"3\" style='margin: 0 auto;' width=\"90%\">
		<tr><th class=\"tblborder\"><b>"._CATEGORY."</b></th><th class=\"tblborder\" colspan=\"2\"><b>"._MOVE."</b></th><th class=\"tblborder\"><b>"._OPTIONS."</b></th></tr>";
		foreach($catlist as $cat => $catinfo) {
				if($catinfo['pid'] == -1) $output .= build_cat_row($cat, $catinfo);
		}
		$output .= "<tr><td class=\"tblborder\" colspan=\"4\" align=\"center\"><a href=\"admin.php?action=categories&cat=new\">"._NEWMAIN."</a></td></tr></table>";
	}	

?>