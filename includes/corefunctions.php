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

if(!defined("_CHARSET")) exit( );

// Validates emails
function validEmail($str) {
	return (bool) preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*@(?:(?![-.])[-a-z0=9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', $str);
}

// Function used to add and remove stories from category counts
function categoryitems($catid, $value)
{
	//add or subtract one to the current categories
	//find out current category's parent
	if(is_array($catid)) $cats = $catid;
	else $cats = array($catid);
	$cats = array_values(array_filter(array_map("strval", $cats), "isNumber"));
	if(!$cats) return;
	$value = (int)$value;
	$catquery = dbquery("SELECT catid, parentcatid, leveldown FROM ".TABLEPREFIX."fanfiction_categories WHERE FIND_IN_SET(catid, '".implode(",", $cats)."') GROUP BY catid");
	$seen = array( );
	while(isset($catquery)) {
		$pcats = array( );
		while($cat = dbassoc($catquery)) {
			$seen[$cat['catid']] = true;
			if($cat['leveldown'] > 0 && !isset($seen[$cat['parentcatid']])) $pcats[] = (int)$cat['parentcatid'];
			if(!in_array($cat['catid'], $cats)) $cats[] = $cat['catid'];
		}
		if(count($pcats) > 0) $catquery = dbquery("SELECT catid, parentcatid, leveldown FROM ".TABLEPREFIX."fanfiction_categories WHERE FIND_IN_SET(catid, '".implode(",", array_unique($pcats))."') GROUP BY catid");
		else unset($catquery);
	}
	dbquery("UPDATE ".TABLEPREFIX."fanfiction_categories SET numitems = (numitems + $value) WHERE FIND_IN_SET(catid, '".implode(",", $cats)."')");
}

// Function to recurse through categories to build a list of the category and all it's sub-categories.
function recurseCategories($catid) {
	global $catlist;
	$$catid = $catlist;
	$categorylist[] = $catid;
	foreach($$catid as $cat => $info) {
		if($info['pid'] == $catid) {
			$categorylist = array_merge($categorylist, recurseCategories($cat));
		}
	}
	return $categorylist;
}

// Captcha script validation
function captcha_confirm() {
	if(empty($_SESSION[SITEKEY.'_digit'])) return false;
	$digit = $_SESSION[SITEKEY.'_digit'];
	$userdigit = $_POST['userdigit'];
	unset($_SESSION[SITEKEY.'_digit']);
	if($digit == md5(SITEKEY.$userdigit) && $userdigit > 1) return true;
	return false;
}

// Sanitizes user input to help prevent XSS attacks 
function descript($text) {
	// Convert problematic ascii characters to their true values
	$search = array("40","41","58","65","66","67","68","69","70",
		"71","72","73","74","75","76","77","78","79","80","81",
		"82","83","84","85","86","87","88","89","90","97","98",
		"99","100","101","102","103","104","105","106","107",
		"108","109","110","111","112","113","114","115","116",
		"117","118","119","120","121","122");
	
	$replace = array("(",")",":","a","b","c","d","e","f","g","h",
		"i","j","k","l","m","n","o","p","q","r","s","t","u",
		"v","w","x","y","z","a","b","c","d","e","f","g","h",
		"i","j","k","l","m","n","o","p","q","r","s","t","u",
		"v","w","x","y","z");

	$entities = count($search);
	
	for ($i=0;$i < $entities;$i++) $text = preg_replace("#(&\#)(0*".$search[$i]."+);*#si", $replace[$i], $text);

	// the following is based on code from bitflux (http://blog.bitflux.ch/wiki/)	
	// Kill hexadecimal characters completely
	$text = preg_replace('#(&\#x)([0-9A-F]+);*#si', "", $text);

	// remove any attribute starting with "on" or xmlns

	$text = preg_replace('#(<[^>]+[\\"\'\s])(onmouseover|onmousedown|onmouseup|onmouseout|onmousemove|onclick|ondblclick|onload|xmlns)[^>]*>#iU', ">", $text);

	// 3.5.78: the list above missed most event handlers (onerror, onfocus...) and handlers
	// written without a space before them, so every tag is now rebuilt from its attributes:
	// event handlers, style with expression(), and script/data links are dropped.
	// A quote that is never closed and a tag cut off at the end of the text are rebuilt too
	// (the browser would otherwise read them on into the page around the text).
	$cleaned = preg_replace_callback('#<(/?)([a-zA-Z][a-zA-Z0-9]*)((?>[^>"\']+|"[^"]*"|\'[^\']*\'|["\'])*)(>|\z)#', 'ef_clean_tag', (string)$text);
	// if the regex gives up (backtrack limit on a huge text) no tag may pass uncleaned
	$text = $cleaned !== null ? $cleaned : str_replace("<", "&lt;", (string)$text);

	// remove javascript: and vbscript: protocol
	
	$text = preg_replace('#([a-z]*)=([\`\'\"]*)script:#iU', '$1=$2nojscript...', $text);
	$text = preg_replace('#([a-z]*)=([\`\'\"]*)javascript:#iU', '$1=$2nojavascript...', $text);
	$text = preg_replace('#([a-z]*)=([\'\"]*)vbscript:#iU', '$1=$2novbscript...', $text);

	//<span style="width: expression(alert('Ping!'));"></span> (only affects ie...)
	$text = preg_replace('#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU', "$1>", $text);
	$text = preg_replace('#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU', "$1>", $text);
	return $text;
}

// Rebuild one HTML tag keeping only safe attributes (used by descript()).
function ef_clean_tag($m) {
	$closed = isset($m[4]) && $m[4] === ">";
	if($m[1] === "/") return "</".$m[2].($closed ? ">" : "");
	$out = "<".$m[2];
	$selfclose = preg_match('#/\s*$#', $m[3]);
	preg_match_all('#([^\s"\'/=<>]+)(?:\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+))?#', $m[3], $attrs, PREG_SET_ORDER);
	foreach($attrs as $a) {
		$name = strtolower($a[1]);
		if(!preg_match('#^[a-z][a-z0-9_:.-]*$#', $name) || strpos($name, "on") === 0 || $name == "xmlns" || $name == "formaction" || $name == "srcdoc") continue;
		if(!isset($a[2])) { $out .= " ".$name; continue; }
		$v = $a[2];
		if(strlen($v) > 1 && ($v[0] == '"' || $v[0] == "'") && substr($v, -1) == $v[0]) $v = substr($v, 1, -1);
		// ENT_HTML5 so that &colon; &Tab; &NewLine; are decoded the way the browser decodes them
		$plain = strtolower(preg_replace('#[\x00-\x20]+#', '', html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
		$plain = preg_replace('#&\#?[a-z0-9]+;?#', '', $plain);
		if(preg_match('#^(java|vb|live)script:#', $plain) || (strpos($plain, "data:") === 0 && !preg_match('#^data:image/(png|gif|jpe?g|webp);#', $plain))) continue;
		if($name == "style" && preg_match('#expression\(|behaviou?r\s*:|javascript:|url\s*\(\s*[\'"]?\s*(java|vb)script#i', $plain)) continue;
		$out .= " ".$name.'="'.str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $v).'"';
	}
	if(!$closed) return $out;
	return $out.($selfclose ? " />" : ">");
}

// Call this function when the user tries to do something they shouldn't have access to.
function accessDenied($str = ""){
	global $tpl, $output;

	if(!empty($str)) $output = write_error($str);
	else $output = write_error(_NOTAUTHORIZED);
	if(!empty($tpl)) {
		$tpl->assign("output", $output);
		$tpl->printToScreen( );
		dbclose( );
	}
	else echo $output;
	exit( );
}

// Call this function when something causes an error and the script has to die.
function errorExit( $msg = ""){
	global $tpl, $output;

	$output .= write_error(_ERROR.(!empty($msg) ? " " : "").$msg);
	$tpl->assign("output", $output);
	$tpl->printToScreen( );
	dbclose( );
	exit( );
}
// The next three functions are used to calculate the series reviews and rating

function storiesInSeries($thisseries) {
	$storylist = array( );
	if(is_int($thisseries)) $thisseries = (string)$thisseries;
	if(!isNumber($thisseries)) return $storylist;
	$serieslist = array( );
	$stinseries = dbquery("SELECT sid, subseriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '$thisseries'");
	while($st = dbassoc($stinseries)) { 
		if(!empty($st['sid'])) $storylist[] = $st['sid'];
		else if(!empty($st['subseriesid'])) $serieslist[] = $st['subseriesid'];
	}
	if($serieslist) {
		foreach($serieslist as $s) {
			$storylist = array_merge($storylist, storiesInSeries($s));
		}
	}
	return $storylist;
}
function subseriesList($thisseries) {
	$serieslist = array( );
	if(is_int($thisseries)) $thisseries = (string)$thisseries;
	if(!isNumber($thisseries)) return $serieslist;
	$stinseries = dbquery("SELECT subseriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE seriesid = '$thisseries'");
	while($st = dbassoc($stinseries)) {
		$serieslist[] = $st['subseriesid']; 
		$serieslist = array_merge($serieslist, subseriesList($st['subseriesid']));
	}
	return $serieslist;
}

function seriesreview($thisseries) {

	global $ratings;

	if(is_int($thisseries)) $thisseries = (string)$thisseries;
	if(!isNumber($thisseries)) return;
	$storylist = storiesInSeries($thisseries);
	$serieslist = subseriesList($thisseries);
	if($ratings == 3) {
		$newrating = dbquery("SELECT COUNT(rating) as totalreviews FROM " . TABLEPREFIX . "fanfiction_reviews 
		WHERE ((item = '$thisseries' AND type = 'SE')" .
		(count($storylist) > 0 ? " OR (FIND_IN_SET(item, '" . (implode(",", $storylist)) . "') > 0 AND type = 'ST')" : "") .
			(count($serieslist) > 0 ? " OR (FIND_IN_SET(item, '" . (implode(",", $serieslist)) . "') > 0 AND type = 'SE')" : "") .
			") AND rating != '-1'");
	}
    else {
		$newrating = dbquery("SELECT AVG(rating) as totalreviews FROM " . TABLEPREFIX . "fanfiction_reviews 
	WHERE ((item = '$thisseries' AND type = 'SE')" .
		(count($storylist) > 0 ? " OR (FIND_IN_SET(item, '" . (implode(",", $storylist)) . "') > 0 AND type = 'ST')" : "") .
		(count($serieslist) > 0 ? " OR (FIND_IN_SET(item, '" . (implode(",", $serieslist)) . "') > 0 AND type = 'SE')" : "") .
		") AND rating != '-1'");
	}

list($totalreviews) = dbrow($newrating);
$newcount = dbquery("SELECT count(reviewid) as totalcount FROM ".TABLEPREFIX."fanfiction_reviews 
	WHERE ((item = '$thisseries' AND type = 'SE')".
	(count($storylist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $storylist))."') > 0 AND type = 'ST')" : "").
	(count($serieslist) > 0 ? " OR (FIND_IN_SET(item, '".(implode(",", $serieslist))."') > 0 AND type = 'SE')" : "").
	") AND review != 'No Review'");
list($totalcount) = dbrow($newcount);
$update = dbquery("UPDATE ".TABLEPREFIX."fanfiction_series SET rating = '".($totalcount ? round((float)$totalreviews) : 0)."', reviews = '".(int)$totalcount."', numstories = '".count($storylist)."' WHERE seriesid = '$thisseries'");
$parentq = dbquery("SELECT seriesid FROM ".TABLEPREFIX."fanfiction_inseries WHERE subseriesid = '$thisseries' AND seriesid != '$thisseries'");
while($parent2 = dbassoc($parentq)) { seriesreview($parent2['seriesid']); }

}

// Per a suggestion from jrabbit, we'll use this function to optimize some queries 
function findclause($field,$set) {
  if (empty($set)) {
    return "1 = 0";
  }
  if(is_array($set)) $set = implode(",", $set);
  if (strpos($set,',')>0) {
    return "FIND_IN_SET($field,'$set') > 0";
  }
  return "$field='$set'";
}

// Added 3.3
function nl2br2($string) {
	$string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
	return $string;
}

// Formats the text of the story when displayed on screen.
function format_story($text) {
      // issue #4: several call sites hand a row array here (the (string)
      // cast below only converts the warning into "Array"); join the
      // values so the formatter receives the text it was meant to see.
      if(is_array($text)) $text = implode("\n", $text);
      $text = trim((string)$text);
      if(strpos($text, "<br>") === false && strpos($text, "<p>") === false && strpos($text, "<br />") === false) $text = nl2br2($text);
      if(_CHARSET != "ISO-8859-1" && _CHARSET != "US-ASCII") return stripslashes($text);
      $badwordchars = array(chr(212), chr(213), chr(210), chr(211), chr(209), chr(208), chr(201), chr(145), chr(146), chr(147), chr(148), chr(151), chr(150), chr(133));
      $fixedwordchars = array('&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8212;', '&#8211;', '&#8230;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8212;', '&#8211;',  '&#8230;' );
      $text = str_replace($badwordchars,$fixedwordchars,stripslashes($text));
      return $text;
}

// Function to Spam-protect emails.  Is called for the IM fields in the user's profile.
function format_email($text) {
	$search = array('@', '.');
	$replace = array(" [AT] ", " [DOT] ");
	return str_replace($search, $replace, $text);
}

// One "name=value" pair for a link that repeats the visitor's search or browse terms.
// Names and values are URL-encoded and the result is safe inside an HTML attribute; arrays become a comma list.
function ef_term($name, $value) {
	if(is_array($value)) $value = implode(",", array_map(function($v) { return is_array($v) ? "" : (string)$v; }, $value));
	return htmlspecialchars(rawurlencode((string)$name)."=".rawurlencode((string)$value), ENT_QUOTES);
}

// The current query string, minus the given keys, as "&amp;a=b&amp;c=d" (empty when nothing is left).
function ef_query_terms($skip = array()) {
	$out = "";
	foreach($_GET as $k => $v) if(!in_array((string)$k, $skip, true)) $out .= "&amp;".ef_term($k, $v);
	return $out;
}

// Function to format a URL into a clickable link
// Only http://, https:// and mailto: are links; a bare host (example.org/page) gets https://.
// Anything else (javascript:, data:...) is shown as text. Both the address and the title are escaped.
function format_link($text, $title = "", $target = 0) {
	$text = trim((string)$text);
	if(empty($title)) $title = $text;
	$title = htmlspecialchars((string)$title, ENT_QUOTES, _CHARSET);
	if(!preg_match('#^(https?://|mailto:)#i', $text)) {
		if(!preg_match('#^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*\.[a-z]{2,}([:/?\#]|$)#i', $text)) return $title;
		$text = "https://".$text;
	}
	$text = "<a href='".htmlspecialchars($text, ENT_QUOTES, _CHARSET)."'".($target ? " target='_blank'" : "").">$title</a>";
	return $text;
}

// Because this is used in places other than the listings of stories, we're setting it up as a function to be called as needed.
function title_link($stories) {
	global $ratingslist, $ageconsent, $disablepopups;

	$rating = $stories['rid'];
	$warningtext = !empty($ratingslist[$rating]['warningtext']) ? addslashes(strip_tags($ratingslist[$rating]['warningtext'])) : "";
		if(empty($ratingslist[$rating]['ratingwarning']))
			$title = "<a href=\""._BASEDIR."viewstory.php?sid=".$stories['sid']."\">".$stories['title']."</a>";
		else {
			$warning = "";
			$warninglevel = sprintf("%03b", $ratingslist[$rating]['ratingwarning']);
			if($warninglevel[2] && !isset($_SESSION[SITEKEY."_warned"][$rating])) {
				$location = "viewstory.php?sid=".$stories['sid']."&amp;warning=$rating";
				$warning = $warningtext;
			}
			if($warninglevel[1] && !$ageconsent && empty($_SESSION[SITEKEY.'_ageconsent'])) {
				$location = "viewstory.php?sid=".$stories['sid']."&amp;ageconsent=ok&amp;warning=$rating";
				$warning = _AGECHECK." - "._AGECONSENT." ".$warningtext." -- 1";
			}
			if($warninglevel[0] && !isMEMBER) {
				$location = "user.php?action=login&amp;sid=".$stories['sid'];
				$warning = _RUSERSONLY." - $warningtext";		
			}
			if(!empty($warning)) {
				$warning = preg_replace("@'@", "\'", $warning);
				$title = "<a href=\"javascript:if(confirm('".$warning."')) location = '"._BASEDIR."$location'\">".$stories['title']."</a>";
			}
			else $title = "<a href=\""._BASEDIR."viewstory.php?sid=".$stories['sid']."\">".$stories['title']."</a>";
		}
	return $title;
}

// Same with the author list
function author_link($stories) {
	
	$authlink[] = "<a href=\"" . _BASEDIR . "viewuser.php?uid=" . $stories['uid'] . "\">" . $stories['penname'] . "</a>";

	// issue #13: coauthors_array is not always set on the row; reading it
	// unguarded raised "Undefined array key" on PHP 8.
	if(!empty($stories['coauthors']) && !empty($stories['coauthors_array']))  {
		//not needed, why is it there?
		//$coauth = dbquery("SELECT "._PENNAMEFIELD." as penname, co.uid FROM ".TABLEPREFIX."fanfiction_coauthors AS co 
		//LEFT JOIN "._AUTHORTABLE." ON co.uid = "._UIDFIELD." WHERE co.sid = '".$stories['sid']."'");

		foreach($stories['coauthors_array'] AS $k => $v) {
			$authlink[] = "<a href=\""._BASEDIR."viewuser.php?uid=".$k."\">".$v."</a>";
		}
	}
 
	return  implode(", ", $authlink) ;
}

// Used to truncate text (summaries in blocks for example) to a set length.  An improvement on the old version as this keeps words intact
function truncate_text($str, $n = 75, $delim='...') { 
   $len = strlen($str);
   if($len > $n) {
        $pos = strpos($str, " ", $n);
	if($pos) $str = trim(substr($str, 0, $pos), "\n\t\.,"). $delim;
  }
  return closetags($str);
} 

// A helper function for the truncate_text function.  This will close all open tags
function closetags($html){

  $donotclose=array('br','img','input', 'hr');
  preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU",$html,$result);
  $openedtags=$result[1];

  preg_match_all("#</([a-z]+)>#iU",$html,$result);
  $closedtags=$result[1];
  $len_opened = count($openedtags);
  if(count($closedtags) == $len_opened){
	  return $html;
  }

  $openedtags = array_reverse($openedtags);

  for($i=0;$i < $len_opened;$i++) {

    if (!in_array($openedtags[$i],$closedtags) && !in_array($openedtags[$i], $donotclose)){
      $html .= '</'.$openedtags[$i].'>';
    } 
    else {
      unset($closedtags[array_search($openedtags[$i],$closedtags)]);
    }
  }
  return $html;
}

//  Builds the naughty word list for the site's censor
function build_word($myword) {
	$letters =preg_split('//',$myword, -1, PREG_SPLIT_NO_EMPTY);
	$word = "";
	foreach($letters as $letter) { 
		if($word != "") $word .= "\W+";
		$word .= $letter;
	}
	return $word;

}

// Finds forbidden words in text.  Returns true if a forbidden word is found
function find_naughty($text) {
	global $words;
	if(!count($words)) return false;
	$naughty = 0;
	for($i = 0; $i < sizeof($words); $i++) {
		if(strpos($words[$i], "*") === false) {
			// check for whole word
			if(preg_match('/(\s|^)+(('.$words[$i].')|('.build_word($words[$i]).')+(s|es)?)+(\W|$)+/i', $text, $match)) {
				echo $words[$i] ." 0=".$match[0];
				$naughty = 1; 
			}
		}
		if(strpos($words[$i], "*") !== false && strpos($words[$i], "*") == 0 && !$naughty) {
			// remove the * from the beginning of the word
			$word = substr($words[$i], 1, strlen($words[$i]));
			if(strrpos($word, "*") == strlen($word) - 1) $word = substr($word, 0, strlen($word) - 1);
			// check for whole word plus word as suffix
			if(preg_match('/[\s|^]+\w*('.$word.'|\W'.build_word($word).')+(s|es)?(\W|$)/i', $text, $match)) $naughty = 1; 
		}
		if(strrpos($words[$i], "*") == strlen($words[$i]) - 1 && !$naughty) {
			// remove the * from the end of the word
			$word = substr($words[$i], 0, strlen($words[$i]) - 1);
			if(strpos($word, "*") !== false && strpos($word, "*") == 0) $word = substr($word, 1, strlen($word));
			// check for whole word plus word as prefix
			if(preg_match('/(\s|^)('.$word.'|'.build_word($word).'\W)+\w*(s|es)?(\W|$)/i', $text, $match)) $naughty = 1; 
		}
		if($naughty) break;  // no sense continuing down the list and this will make it run faster
	}
	return $naughty;
}

// Replaces forbidden words in text.  The forbidden word is replaced with the first letter and trailing ***
function replace_naughty($text) {
	global $words;

	if(!count($words)) return $text;
	$i = 0;
	for($j = 0; $j < sizeof($words); $j++) {
		if(strpos($words[$j], "*") === false) {
			$replace[$i] = str_pad($words[$j][0], strlen($words[$j]), "*");
			$naughtywords[$i] = '/\b('.$words[$j].'\b)|('.build_word($words[$j]).')\b/i';
			$i++;
		}

		if(strpos($words[$j], "*") !== false && strpos($words[$j], "*") == 0) {
			$word = substr($words[$j], 1, strlen($words[$j]));
			if(strrpos($word, "*") == strlen($word) - 1) $word = substr($word, 0, strlen($word) - 1);
			$naughtywords[$i] = '/(\s|^)+(\w*)+('.$word.'|'.build_word($word).')/i';
			$replace[$i] = "$1$2".str_pad(substr($word, 0, 1), strlen($word), "*");
			$i++;
		}
		if(strrpos($words[$j], "*") == strlen($words[$j]) - 1) {
			$word = substr($words[$j], 0, strlen($words[$j]) - 1);
			if(strpos($word, "*") !== false && strpos($word, "*") == 0) $word = substr($word, 1, strlen($word));
			$replace[$i] = '$1'.str_pad(substr($word, 0, 1), strlen($word), "*").'$3';
			$naughtywords[$i] = '/(.*)('.$word.'|'.build_word($word).')(\s|\w)/i';
			$i++;
		}
	}
	$text = preg_replace($naughtywords, $replace, $text);
	return $text;
}

// Format for messages sent back from various forms and actions 
function write_message($str) {
	return "<div style='text-align: center; margin: 1em;'>$str</div>";
}

// Formats error messages sent back from various forms and actions
function write_error($str) {
	return "<div style='text-align: center; margin: 1em;' class='errortext'>$str</div>";
}

// Checks that the given $num is actually a number.  Used to help prevent XSS attacks.
function isNumber($num) {
	if(empty($num)) return false;
	if(!is_string($num)) return false;
	return preg_match("/^[0-9]+$/", $num);
}

// May be needed for sites that have bridged the authors table
function check_prefs($uid) {
	$test = dbquery("SELECT uid FROM ".TABLEPREFIX."fanfiction_authorprefs WHERE uid = '$uid'");
	if(dbnumrows($test)) return true;
	else return false;
}

// Passwords (3.5.7): stored with password_hash(). Old unsalted MD5 hashes still verify and are
// replaced at the next login, but only once the password column is wide enough for the new hash
// (update.php widens it to varchar(255)); until then MD5 is kept so nobody is locked out.

// Can the authors table hold a password_hash() result? Checked once per request.
// $table defaults to the table in _AUTHORTABLE (the installer passes its own).
function ef_password_column_ok($table = "") {
	static $ok = array( );
	if($table === "" && defined("_AUTHORTABLE")) $table = trim(substr(_AUTHORTABLE, 0, strpos(_AUTHORTABLE, " as author")));
	if($table === "") return false;
	if(!isset($ok[$table])) {
		$result = dbquery("SHOW COLUMNS FROM ".$table." LIKE 'password'");
		$col = $result ? dbassoc($result) : false;
		$ok[$table] = $col && preg_match("/^varchar\((\d+)\)/i", $col['Type'], $m) && (int)$m[1] >= 255;
	}
	return $ok[$table];
}

// Hash a new password for storage.
function ef_hash_password($plain, $table = "") {
	if(!ef_password_column_ok($table)) return md5($plain);
	return password_hash($plain, PASSWORD_DEFAULT);
}

// True while $stored is still a legacy MD5 hash (or anything that is not a password_hash() result).
function ef_password_is_legacy($stored) {
	return !(is_string($stored) && strlen($stored) > 32 && $stored[0] === '$');
}

// Is $stored a real password hash: a 32-character hex MD5 or a password_hash() result? Anything
// else ('0' or '' on accounts an administrator created and never released, '!' placeholders set by
// update.php) must never authenticate, by form login, cookie or session.
function ef_password_is_hash($stored) {
	if(!is_string($stored) || $stored === "") return false;
	if(preg_match("/^[0-9a-f]{32}$/i", $stored)) return true;
	$info = password_get_info($stored);
	return !empty($info['algo']);
}

// Check a password against the stored hash (password_hash() or legacy MD5).
function ef_verify_password($plain, $stored) {
	if(!is_string($plain) || !is_string($stored) || $stored === "") return false;
	if(!ef_password_is_legacy($stored)) return password_verify($plain, $stored);
	return hash_equals($stored, md5($plain));
}

// Function builds the alphabet links on various pages.
function build_alphalinks($url, $let) {
	global $alphabet;

	$alpha = "<div id=\"alphabet\">";
	foreach( $alphabet as $link ) {
		// Build a link that calls a function with ($link and 1 (page number) )
		$alpha .= "<a href=\"{$url}let=$link\"".($let == $link ? " id='currentletter'" : "").">$link</a> \n";
	}
	$alpha .= "</div>";
	return $alpha;
}

// Function builds the pagination links
function build_pagelinks($url, $total, $offset = 0, $columns = 1) {
	global $itemsperpage, $linkstyle, $linkrange;
	$pages = "";
	$itemsperpage = $itemsperpage * $columns;

	if($itemsperpage >= $total) return;

	if(empty($linkrange)) $linkrange = 4;

	$totpages = floor($total/$itemsperpage) + ($total % $itemsperpage ? 1 : 0);
	$curpage = floor($offset/$itemsperpage) + 1;
	if(!$linkstyle) $startrange = $curpage;
	else {
		if($totpages <= $linkrange || $curpage == 1) $startrange = 1;
		else if($curpage >= $totpages - floor($linkrange / 2) + 1) $startrange = $totpages - $linkrange;
		else $startrange = $curpage - floor($linkrange / 2) > 0 ? $curpage - floor($linkrange / 2) : 1;
	}
	if($startrange >= $totpages - $linkrange ) $startrange = $totpages - $linkrange > 0 ? $totpages - $linkrange : 1;
	$stoprange = $totpages > $startrange + $linkrange ? $startrange + $linkrange : $totpages + 1;
	if($curpage > 1 && $linkstyle != 1) $pages .= "<a href='".$url."offset=".( $offset - $itemsperpage)."' id='plprev'>["._PREVIOUS."]</a> ";
	if($startrange > 1 && $linkstyle > 0) $pages .= "<a href='".$url."offset=0'>1</a><span class='ellipses'>...</span>";
	for($x = $startrange; $x < $stoprange; $x++) {
		$pages .= "<a href='".$url."offset=".(($x - 1) * $itemsperpage)."'".($x == $curpage ? "id='currentpage'" : "").">".$x."</a> \n";
	}
	if($stoprange < $totpages && $linkstyle > 0) $pages .= "<span class='ellipses'>...</span> <a href='".$url."offset=".(($totpages - 1) * $itemsperpage)."'>$totpages</a>\n";
	if ($curpage < $totpages && $linkstyle != 1) $pages .=  " <a href='".$url."offset=".($offset+$itemsperpage)."' id='plnext'>["._NEXT."]</a>";
	return "<div id=\"pagelinks\">$pages</div>";
}

// Function that returns the ratings picks 
function ratingpics($rating) {
	global $ratings, $like, $dislike, $star, $halfstar;
	$ratingpics = "";

	if($ratings == "3") { 
		$ratingpics = "<span title=\""._LIKES_NUMBER. "\"><b>" . $rating . "</b></span>";
	}	
	elseif($ratings == "2") {
		if($rating >= 0.5)
			$ratingpics = ($like ? $like : "<img src=\""._BASEDIR."images/like.gif\" alt=\""._LIKED."\">");
		else if(($rating < 0.5) && ($rating > 0))
			$ratingpics = ($dislike ? $dislike :"<img src=\""._BASEDIR."images/dislike.gif\" alt=\""._DISLIKED."\">");
		else $ratingpics = "";
	}
	if($ratings == "1") {
		global $star, $halfstar;
		if($rating > 0) {
			for($x = 0; $x < ($rating / 2) - .5; $x++) {
				$ratingpics .= ($star ? $star  : "<img src=\""._BASEDIR."images/star.gif\" alt=\"star\">");
			}
			if($rating % 2 != 0) $ratingpics .= ($halfstar ? $halfstar  : "<img src=\""._BASEDIR."images/starhalf.gif\" alt=\"half-star\">");
		}
		else $ratingpics = "";
	}
	if(!empty($ratingpics)) return "<span style='white-space: nowrap;'>$ratingpics</span>"; // the no-wrap style will keep the stars together
	else return;
}

// This function builds the list of category links (including the breadcrumb depending on settings)
function catlist($catid) {
	global $extendcats, $catlist, $action;

	if(!is_array($catid)) $catid = explode(",", $catid);
	$categorylinks = array();
	foreach($catid as $cat) {
		if(empty($catlist[$cat])) continue;
		if($extendcats) {
			unset($link);
			$thiscat = $cat;
			while(isset($thiscat)) {
				if(isset($link)) $link = " > ".$link;
				else $link = "";
				if($action != "printable") $link = "<a href='"._BASEDIR."browse.php?type=categories&amp;catid=$thiscat'>".$catlist[$thiscat]['name']."</a>".$link;
				else $link = $catlist[$thiscat]['name'].$link;
				if($catlist[$thiscat]['pid'] == -1) unset($thiscat);
				else $thiscat = $catlist[$thiscat]['pid'];
			}
			$categorylinks[] = $link;
		}
		else $categorylinks[] = "<a href='"._BASEDIR."browse.php?type=categories&amp;catid=$cat'>".$catlist[$cat]['name']."</a>";
	}
	return implode(", ", $categorylinks);
}

// This function builds the list of character links
function charlist($characters) {
	global $charlist, $action;

	if(!is_array($characters)) $characters = explode(",", $characters);
	$charlinks = array( );
	foreach($characters as $c) {
		if(empty($charlist[$c]['name'])) continue;
		if($action != "printable") $charlinks[] = "<a href='"._BASEDIR."browse.php?type=characters&amp;charid=$c'>".$charlist[$c]['name']."</a>";
		else $charlinks[] = $charlist[$c]['name'];
	}
	return implode(", ", $charlinks);
}

// Most of the pages that list stories and series use this fuction.  This handles showing the series and stories and pagination of the two together when needed
function search($storyquery, $countquery, $pagelink = "search.php?", $pagetitle = 0) {
	global $tpl, $new, $ratingslist, $itemsperpage, $reviewsallowed, $output, $dateformat, $current, $featured, $favorites, $retired, $ageconsent, $classtypelist, $classlist, $offset, $recentdays;
     
	$count = dbquery($countquery);
	list($numrows) = dbrow($count);
	if($numrows) {
		$tpl->assign("output", ($pagetitle ? "<div id=\"pagetitle\">$pagetitle</div>" : ""));
		$tpl->newBlock("listings");
		if(!$ratingslist) {
			$ratlist = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_ratings");
			while($rate = dbassoc($ratlist)) {
				$ratings[$rate['rid']] = array("rating" => $rate['rating'], "ratingwarning" => $rate['ratingwarning'], "warningtext" => $rate['warningtext']);
			}
		}
		$tpl->newBlock("listings");
		$tpl->gotoBlock("listings");
		$tpl->assign("stories",  "<div class=\"sectionheader\">"._STORIES."</div>");
		$storyquery .= " LIMIT $offset, $itemsperpage";
		$result3 = dbquery($storyquery);     
		$count = 0;                     
		while($stories = dbassoc($result3)) {       
			$tpl->newBlock("storyblock");
			include(_BASEDIR."includes/storyblock.php"); 
		}
		$tpl->gotoBlock("_ROOT");		
	}
	else {
		$tpl->newBlock("listings");
		$tpl->assign("pagelinks", write_message(_NORESULTS));
	}
	if($numrows > $itemsperpage) {
		$termArray = array_merge($_GET, $_POST);
		$terms = array();
		foreach($termArray as $term => $value) {
			if($term == "submit" || $term == "go" || $term == "offset" || ($term != "complete" &&empty($value))) continue;
			$terms[] = ef_term($term, $value);
		}
		$terms = implode("&amp;", $terms);
		$terms .= "&amp;";
		$tpl->gotoBlock("listings");
		$tpl->assign( "pagelinks", build_pagelinks($pagelink.$terms, $numrows, $offset));
	}
	$tpl->gotoBlock("_ROOT");
	return $numrows;
}


// This function replaces escaped newlines with html <br /> tags
function fixup_newlines($string)
{
	$string = str_replace(array("\\r\\n", "\\n\\r", "\\r", "\\n"), "<br />", $string);
	return $string;
}
