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


$disablesorts = array("categories");

// Get variables
	$catid = isset($_GET['catid']) && isNumber($_GET['catid']) ? $_GET['catid'] : -1;

// End variables
	if($catid == -1) $output .= "<div id=\"pagetitle\">"._CATEGORIES."</div>";
	else $output .= "<div id=\"pagetitle\">".catlist($catid)."</div>";
	$subs = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = '$catid' ORDER BY displayorder ASC");
	$total = dbnumrows($subs);
	$list = floor($total / $displaycolumns);
	if($total % $displaycolumns != 0) $list++;
	$colwidth = (100/$displaycolumns) -1;
	$count = 0;
	$column = 1;
	$content = "";
	if($total) {
		$collist = array();
		$colcount = floor(($total / $displaycolumns) + ($total % $displaycolumns ? 1 : 0));
		if(!$colcount) $colcount = 1;
		$mod = $total % $displaycolumns;
		$col = 0;
		$count = 0;
		while($cats = dbassoc($subs)) {
			unset($catinfo);
			$count++;
			if(file_exists("$skindir/categories.tpl")) $cat = new TemplatePower( "$skindir/categories.tpl" );
			else $cat = new TemplatePower("default_tpls/categories.tpl");
			$cat->prepare( );
			$cat->newBlock("categoryblock");
			$cat->assign("image", ($cats['image'] && file_exists("$skindir/images/".$cats['image']) ? "<img src=\"$skindir/images/".$cats['image']."\" alt=\"".$cats['category']."\" title=\"".$cats['category']."\">" : ""));
			$cat->assign("link", "<a href=\"browse.php?type=categories&amp;catid=".$cats['catid']."\">".$cats['category']."</a>");
			$cat->assign("count", "[".$cats['numitems']."]");
			$cat->assign("description", stripslashes($cats['description']));
			if($displaycolumns == 1) $content .= "<div>".$cat->getOutputContent( )."</div>";
			else $collist[$col][] = "<div>".$cat->getOutputContent( )."</div>";
			if(($mod && $col < $mod && $count == $colcount) || ($mod && $col >= $mod && $count >= ($colcount - 1)) || (!$mod && $count == $colcount)) {
				$col++;
				$count = 0;
			}
		}
		if($displaycolumns > 1) {
			$output .= "<div id=\"columncontainer\"><div><div id=\"browseblock\">";
			$col = 0;
			for($a = 0; $a < $colcount; $a++) {
				$output .= "<div class=\"row\">";
				foreach($collist as $c) {
					if(isset($c[$a])) $output .= "<div class=\"column\">".$c[$a]."</div>";
				}
				$output .= "<div class=\"cleaner\">&nbsp;</div></div>";
			}
			$output .= "</div><div>&nbsp;</div></div></div>";
		}
		else {
			$output .= "<div id=\"columncontainer\">";
			$output .= $content;
			$output .= "</div>";
		}
	}
	if($catid > 0) {
		$storyquery .= _ORDERBY;
		$numrows = search(_STORYQUERY.$storyquery, _STORYCOUNT.$countquery, "browse.php?");
	}
	$catid = array($catid);
?>