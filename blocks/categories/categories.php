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



	if($multiplecats) {
		$displaycolumns = $displaycolumns ? $displaycolumns : 1;
		$query = "SELECT * FROM ".TABLEPREFIX."fanfiction_categories WHERE parentcatid = '-1' ORDER BY displayorder";
		$result4 = dbquery($query) or die(_FATALERROR."Query: ".$query."<br />Error: (". $dbconnect->errno.") ". $dbconnect->error);
		$total = dbnumrows($result4);
		$count = 0;
		$collist = array( );
		if($total) {
			if(!empty($blocks['categories']['tpl'])) {
				while($categories = dbassoc($result4)) {
					$list = floor($total / $displaycolumns);
					if($total % $displaycolumns != 0) $list++;
					$tpl->newBlock("categoriesblock");
					if($categories['image'] && file_exists("$skindir/images/".$categories['image'])) $tpl->assign("categoryimage", "$skindir/images/".$categories['image']);
					$tpl->assign("categorytitle", "<a href=\"browse.php?type=categories&amp;catid=".$categories['catid']."\">".$categories['category']."</a>");
					$tpl->assign("numstories", "(".$categories['numitems'].")");
					$tpl->assign("categorydescription", stripslashes($categories['description']));
					$list--;
					if($count % $displaycolumns == 0) {
						$tpl->assign("categorycolumn", "</tr> ".($count < $list ? "<tr>" : ""));
					}
				}
			}
			else {
				if(isset($blocks['categories']['columns']) && empty($blocks['categories']['columns'])) $catcolumns = 1;
				else $catcolumns = $displaycolumns;
				$colcount = floor(($total / $catcolumns) + ($total % $catcolumns ? 1 : 0));
				$mod = $total % $catcolumns;
				$col = 0;
				$count = 0;
				$template = (!empty($blocks['categories']['template']) ? stripslashes($blocks['categories']['template']) : "{image} {link} [{count}] {description}"); 
				$search = array("@\{image\}@", "@\{link\}@", "@\{count\}@", "@\{description\}@");
				while($categories = dbassoc($result4)) {
					unset($catinfo);
					$count++;
					$replace = array(
						(!empty($categories['image']) && file_exists("$skindir/images/".$categories['image']) ? "<img src=\"$skindir/images/".$categories['image']."\" alt=\"".$categories['category']."\" title=\"".$categories['category']."\">" : ""),
						"<a href=\"browse.php?type=categories&amp;catid=".$categories['catid']."\">".$categories['category']."</a>", $categories['numitems'], stripslashes($categories['description']));
					$catinfo = preg_replace($search, $replace, $template);
					if($catcolumns == 1) $collist[] = $catinfo;
					else $collist[$col][] = $catinfo;
					if(($mod && $col < $mod && $count == $colcount) || ($mod && $col >= $mod && $count >= ($colcount - 1)) || (!$mod && $count == $colcount)) {
						$col++;
						$count = 0;
					}
				}
				if($catcolumns > 1) {
					$content .= "<div id=\"categoryblock\"><div><div id=\"browseblock\">";
					$col = 0;
					for($a = 0; $a < $colcount; $a++) {
						$content .= "<div class=\"row\">";
						foreach($collist as $c) {
							if(isset($c[$a])) $content .= "<div class=\"column\">".$c[$a]."</div>";
						}
						$content .= "<div class=\"cleaner\">&nbsp;</div></div>";
					}
					$content .= "</div><div>&nbsp;</div></div></div>";
				}
				else {
					$content .= "<div id=\"categoryblock\">";
					foreach($collist as $c) {
						$content .= "<div class=\"row\">".$c."</div>";
					}
					$content .= "</div>";
				}
			}
		}
	}
?>