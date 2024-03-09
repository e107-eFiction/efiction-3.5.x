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

		$output .= "<div id=\"pagetitle\">"._MANAGEREVIEWS."</div>";
		$count = dbquery("SELECT count(s.sid) FROM ".TABLEPREFIX."fanfiction_stories AS s LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors AS c ON s.sid = c.sid WHERE s.uid = '".USERUID."' OR c.uid = '".USERUID."'");
		list($numrows)= dbrow($count);
		$count2 = dbquery("SELECT count(seriesid) FROM ".TABLEPREFIX."fanfiction_series WHERE uid = '".USERUID."'");
		list($numrows2)= dbrow($count2);
		$output .= "<table class=\"tblborder\" cellspacing=\"0\" cellpadding=\"4\" style='width: 90%; margin: 0 auto;'><tr><th>"._SERIES."</th><th>"._REVIEWS."</th><th>"._UNRESPONDED."</th></tr>";
		$x = 0;
		if($numrows2 > 0) {
			$query = dbquery("SELECT series.seriesid, series.title, series.reviews, sum(reviews.respond) as respond  FROM ".TABLEPREFIX."fanfiction_series as series, ".TABLEPREFIX."fanfiction_reviews as reviews WHERE series.uid = '".USERUID."' AND series.seriesid = reviews.item AND reviews.type = 'SE' GROUP BY reviews.item ORDER BY series.title");
			while($story = dbassoc($query)) {
				if($story['reviews'] > 0) {
					$output .= "<tr><td class=\"tblborder\"><a href=\"reviews.php?type=SE&amp;item=".$story['seriesid']."\">".$story['title']."</a></td><td class=\"tblborder\" align=\"center\">".$story['reviews']."</td><td class=\"tblborder\" align=\"center\">".($story['reviews'] - $story['respond'])."</td></tr>";
					$x++;
				}
			}
		}
		if($x == 0) $output .= "<tr><td class=\"tblborder\" colspan=\"3\" align=\"center\">"._NORESULTS."</td></tr>";
		$output .= "<tr><th>"._STORIES."</th><th>"._REVIEWS."</th><th>"._UNRESPONDED."</th></tr>";
		$x = 0;
		if($numrows > 0) {
			$query = dbquery("SELECT story.sid, story.title, story.reviews, sum(reviews.respond) as respond FROM ".TABLEPREFIX."fanfiction_stories as story JOIN ".TABLEPREFIX."fanfiction_reviews as reviews ON story.sid = reviews.item AND reviews.type = 'ST' LEFT JOIN ".TABLEPREFIX."fanfiction_coauthors AS c ON c.sid = story.sid WHERE (story.uid = '".USERUID."' OR c.uid = '".USERUID."') GROUP BY reviews.item ORDER BY story.title");
			while($story = dbassoc($query)) {
				if($story['reviews'] > 0) {
				        $output .= "<tr><td class=\"tblborder\">".$story['title']."</td><td class=\"tblborder\" align=\"center\"><a href=\"reviews.php?type=ST&amp;item="
			                  .$story['sid']."\">".$story['reviews']."</a></td><td class=\"tblborder\" align=\"center\"><a href=\"reviews.php?type=ST&amp;item="
			                  .$story['sid']."&amp;unresponded=1\">".($story['reviews'] - $story['respond'])."</a></td></tr>";

					$x++;
				}
			}
		}
		if($x == 0) $output .= "<tr><td class=\"tblborder\" colspan=\"3\" align=\"center\">"._NORESULTS."</td></tr>";
		// For other items that might receive reviews such as fan art.
		$code = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_codeblocks WHERE code_type = 'revreceived'");
		while($c = dbassoc($code)) {
			eval($c['code_text']);
		}
		$output .= "</table>";
?>