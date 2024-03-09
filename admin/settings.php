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

function updatePanelOrder( ) {
	
	$ptypes = dbquery("SELECT panel_type FROM ".TABLEPREFIX."fanfiction_panels GROUP BY panel_type");
	while($ptype = dbassoc($ptypes)) {
		if($ptype['panel_type'] == "A") {
			for($x = 1; $x < 5; $x++) {
				$count = 1;
				$plist = dbquery("SELECT panel_name, panel_id FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_hidden = '0' AND panel_type = '".$ptype['panel_type']."' AND panel_level = '$x' ORDER BY panel_level, panel_order");
				while($p = dbassoc($plist)) {
					dbquery("UPDATE ".TABLEPREFIX."fanfiction_panels SET panel_order = '$count' WHERE panel_id = '".$p['panel_id']."' LIMIT 1");
					$count++;
				}
			}
		}
		else {
			$count = 1;
			$plist = dbquery("SELECT panel_name, panel_id FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_hidden = '0' AND panel_type = '".$ptype['panel_type']."' ORDER BY ".($ptype['panel_type'] == "A" ? "panel_level," : "")."panel_order");
			while($p = dbassoc($plist)) {
				dbquery("UPDATE ".TABLEPREFIX."fanfiction_panels SET panel_order = '$count' WHERE panel_id = '".$p['panel_id']."' LIMIT 1");
				$count++;
			}
		}
	}
}

if($action == "settings") {
$output .= "<h1>"._SETTINGS."</h1><div style='text-align: center;'>
	<a href='admin.php?action=settings&amp;sect=main'>"._MAINSETTINGS."</a> |
	<a href='admin.php?action=settings&amp;sect=submissions'>"._SUBMISSIONSETTINGS."</a> |
	<a href='admin.php?action=settings&amp;sect=sitesettings'>"._SITESETTINGS."</a> |
	<a href='admin.php?action=settings&amp;sect=display'>"._DISPLAYSETTINGS."</a> |
	<a href='admin.php?action=settings&amp;sect=reviews'>"._REVIEWSETTINGS."</a> |
	<a href='admin.php?action=settings&amp;sect=useropts'>"._USERSETTINGS."</a> |
	<a href='admin.php?action=settings&amp;sect=email'>"._EMAILSETTINGS."</a> |
	<a href='admin.php?action=censor'>"._CENSOR."</a> <br />
	<a href='admin.php?action=messages&message=welcome'>"._WELCOME."</a> | 
	<a href='admin.php?action=messages&message=copyright'>"._COPYRIGHT."</a> | 
	<a href='admin.php?action=messages&message=printercopyright'>"._PRINTERCOPYRIGHT."</a> | 
	<a href='admin.php?action=messages&message=tinyMCE'>"._TINYMCE."</a> | 
	<a href='admin.php?action=messages&message=nothankyou'>"._NOTHANKYOU."</a> | 
	<a href='admin.php?action=messages&message=thankyou'>"._THANKYOU."</a><br />";
	$settingsquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_panels WHERE panel_type = 'AS' ORDER BY panel_title");
	while($os = dbassoc($settingsquery)) {
		if($os['panel_url']) $othersettings[] = "<a href='".$os['panel_url']."'>".$os['panel_title']."</a>";
	}
	if(isset($othersettings)) $output .= implode(" | ", $othersettings);
$output .= "</div> ";
}	

$sects = array("main", "submissions", "sitesettings", "display", "reviews", "useropts", "email");
//if(!isset($_GET['sect'])) $sect = "main";
//else $sect = $_GET['sect'];
$sect = isset($_GET['sect']) ? $_GET['sect'] : "main";
if(isset($_POST['submit'])) {
	if($sect == "main") {
		if(!preg_match("!^[a-z0-9_]{3,30}$!i", $_POST['newsitekey'])) $output .= write_error(_BADSITEKEY);
		else {
			$oldsitekey = $sitekey;
			$sitekey = descript($_POST['newsitekey']);
			$sitename = escapestring(descript(strip_tags($_POST['newsitename'])));
			$slogan = escapestring(descript(strip_tags($_POST['newslogan'])));
			$url = escapestring(descript(strip_tags($_POST['newsiteurl'])));
			// if the http:// is missing add it.
			if(substr($url, 0, 7) != "http://") $url = "http://".$url;
			// we also want to check for a trailing slash.
			if(substr($url, -1, 1) == "/") $url = substr($url, 0, strlen($url) - 1);
			$tableprefix = escapestring(descript(strip_tags($_POST['newtableprefix'])));
			$siteemail = escapestring(descript(strip_tags($_POST['newsiteemail'])));
			$skin = escapestring(descript(strip_tags($_POST['newskin'])));
			$language = escapestring(descript(strip_tags($_POST['newlanguage'])));
			if(empty($sitekey)) $output .= write_message(_SITEKEYREQUIRED);
			else {
				if($sitekey != $oldsitekey) $output .= write_message(_SITEKEYCHANGED);
				$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET sitekey = '$sitekey', sitename = '$sitename', slogan = '$slogan', url = '$url', tableprefix = '$tableprefix', siteemail = '$siteemail', skin = '$skin', language = '$language' WHERE sitekey = '$oldsitekey'");
			}
		}
	}
	else if($sect == "submissions") {
		$submissionsoff = $_POST['newsubmissionsoff'] == 1 ? 1 : 0;
		$autovalidate = $_POST['newautovalidate'] == 1 ? 1 : 0;
		$coauthallowed = $_POST['newcoauthallowed'] == 1 ? 1 : 0;
		$store = !empty($_POST['newstore']) ? ($_POST['newstore'] == "files" ? "files" : "mysql" ) : $store;
		$storiespath = descript($_POST['newstoriespath']);
		$minwords = isNumber($_POST['newminwords']) ? $_POST['newminwords'] : 0;
		$maxwords = isNumber($_POST['newmaxwords']) ? $_POST['newmaxwords'] : 0;
		$roundrobins = $_POST['newroundrobins'] == 1 ? 1 : 0;
		$allowseries = $_POST['newallowseries'] && isNumber($_POST['newallowseries']) ? $_POST['newallowseries'] : 0;
		$imageupload = $_POST['newimageupload'] == 1 ? 1 : 0;
		$imageheight = isNumber($_POST['newimageheight']) ? $_POST['newimageheight'] : 0;
		$imagewidth = isNumber($_POST['newimagewidth']) ? $_POST['newimagewidth'] : 0;
		$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET submissionsoff = '$submissionsoff', autovalidate = '$autovalidate', coauthallowed = '$coauthallowed', store = '$store', storiespath = '$storiespath', minwords = '$minwords', maxwords = '$maxwords', imageupload = '$imageupload', imageheight = '$imageheight', imagewidth = '$imagewidth', roundrobins = '$roundrobins', allowseries = '$allowseries' WHERE sitekey ='".SITEKEY."'");
		if($action == "settings") {
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_panels SET panel_hidden = '".($imageupload ? "0" : "1")."' WHERE panel_name LIKE 'manageimages'");
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_panels SET panel_hidden = '".($allowseries ? "0" : "1")."' WHERE (panel_name LIKE '%series%' OR panel_title LIKE '%series%') AND panel_type != 'A' AND panel_type != 'B'");
			updatePanelOrder( );
		}

	}
	else if($sect == "sitesettings") {
		$tinyMCE = ($_POST['newtinyMCE'] == 1  OR $_POST['newtinyMCE'] == 2) ? $_POST['newtinyMCE'] : 0;
		$allowed_tags = $_POST['newallowed_tags'];
		$favorites = $_POST['newfavorites'] == 1 ? 1 : 0;
		$multiplecats = $_POST['newmultiplecats'] == 1 ? 1 : 0;
		$newscomments = $_POST['newnewscomments'] == 1 ? 1 : 0;
		$logging = $_POST['newlogging'] == 1 ? 1 : 0;
		$maintenance = $_POST['newmaint'] == 1 ? 1 : 0;
		$debug = $_POST['newdebug'] == 1 ? 1 : 0;
		$captcha = $_POST['newcaptcha'] == 1 ? 1 : 0;
		$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET tinyMCE = '$tinyMCE', favorites = '$favorites', multiplecats = '$multiplecats', allowed_tags = '$allowed_tags', newscomments = '$newscomments', logging = '$logging', maintenance = '$maintenance', debug = '$debug', captcha = '$captcha' WHERE sitekey ='".SITEKEY."'");
	}
	else if($sect == "display") {
		$dateformat = $_POST['newdateformat'] ? descript(strip_tags($_POST['newdateformat'])) : descript(strip_tags($_POST['customdateformat']));
		$timeformat = $_POST['newtimeformat'] ? descript(strip_tags($_POST['newtimeformat'])) : descript(strip_tags($_POST['customtimeformat']));
		$extendcats = $_POST['newextendcats'] == 1 ? 1 : 0;
		if(isset($_POST['newdisplaycolumns']) && isNumber($_POST['newdisplaycolumns'])) $displaycolumns = $_POST['newdisplaycolumns'];
		if(isset($_POST['newitemsperpage']) && isNumber($_POST['newitemsperpage'])) $itemsperpage = $_POST['newitemsperpage'];
		if(isset($_POST['newlinkstyle']) && isNumber($_POST['newlinkstyle'])) $linkstyle = $_POST['newlinkstyle'];
		if(isset($_POST['newlinkrange']) && isNumber($_POST['newlinkrange'])) $linkrange = $_POST['newlinkrange'];
		$displayindex = $_POST['newstoryindex'] == 1 ? 1 : 0;
		$displayprofile = $_POST['newdisplayprofile'] == 1 ? 1 : 0;
		$defaultsort = $_POST['newdefaultsort'] == 1 ? 1 : 0;
		if(isNumber($_POST['newrecentdays'])) $recentdays = $_POST['newrecentdays'];
		$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET dateformat = '$dateformat', timeformat = '$timeformat', extendcats = '$extendcats', displaycolumns = '$displaycolumns', itemsperpage = '$itemsperpage', displayindex = '$displayindex', defaultsort = '$defaultsort', recentdays = '$recentdays', displayprofile = '$displayprofile', linkstyle = '$linkstyle', linkrange = '$linkrange' WHERE sitekey ='".SITEKEY."'");
	}
	else if($sect == "reviews") {
		$reviewsallowed = $_POST['newreviewsallowed'] == 1 ? 1 : 0;
		$anonreviews = $_POST['newanonreviews'] == 1 ? 1 : 0;
		$rateonly = $_POST['newrateonly'] == 1 ? 1 : 0;
		$ratings = isset($_POST['newratings']) && isNumber($_POST['newratings']) ? $_POST['newratings'] : 0;
		$revdelete = isset($_POST['newrevdelete']) && isNumber($_POST['newrevdelete']) ? $_POST['newrevdelete'] : 0;
		$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET reviewsallowed = '$reviewsallowed', anonreviews = '$anonreviews', rateonly = '$rateonly', ratings = '$ratings', revdelete = '$revdelete' WHERE sitekey ='".SITEKEY."'");
	}
	else if($sect == "useropts") {
		$alertson = $_POST['newalertson'] == 1 ? 1 : 0;
		$disablepopups = $_POST['newdisablepops'] == 1 ? 1 : 0;
		$agestatement  = $_POST['newagestatement'] == 1 ? 1 : 0;
		$pwdsetting = $_POST['newpwdsetting'] == 1 ? 1 : 0;
		$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET alertson = '$alertson', disablepopups = '$disablepopups', agestatement = '$agestatement', pwdsetting = '$pwdsetting' WHERE sitekey ='".SITEKEY."'");
	}
	else if($sect == "email") {
		$smtp_host = $_POST['newsmtp_host'];
		$smtp_username = $_POST['newsmtp_username'];
		$smtp_password = $_POST['newsmtp_password'];
		$result = dbquery("UPDATE ".$settingsprefix."fanfiction_settings SET smtp_host = '$smtp_host', smtp_username = '$smtp_username', smtp_password = '$smtp_password' WHERE sitekey ='".SITEKEY."'");
	}
	if($result) {
		$output .= write_message(_ACTIONSUCCESSFUL);
		$sect = $sects[(array_search($sect, $sects) + 1)];
		if(!$sect) $sect = $sects[0];
	}
	else $output .= write_error(_ERROR);
}
	$settingsresults = dbquery("SELECT * FROM ".$settingsprefix."fanfiction_settings WHERE sitekey ='".SITEKEY."'");
	$settings = dbassoc($settingsresults);
 
	foreach($settings as $var => $val) {
		if(is_NULL($val)) $val = '';
		$$var = stripslashes($val );
	}

	$output .= "<form method='POST' class='tblborder' style='' enctype='multipart/form-data' action='".($action == "settings" ? "admin.php?action=settings" : $_SERVER['PHP_SELF']."?step=".$_GET['step'])."&amp;sect=$sect'>";
	if($sect == "main") {
		$output .= "<h2>"._SITEINFO."</h2>
		<table class='acp'>
			<tr>
				<td><label for='newsitekey'>"._SITEKEY.":</label></td><td><input type='text' class='textbox' name='newsitekey' value='".SITEKEY."'> <a href='#' class='pophelp'>[?]<span>"._HELP_SITEKEY."</span></a></td>
			</tr>
			<tr>
				<td><label for='newsitename'>"._SITENAME.":</label></td><td><input type='text' class='textbox' name='newsitename' value='".htmlspecialchars($sitename, ENT_QUOTES)."'> <a href='#' class='pophelp'>[?]<span>"._HELP_SITENAME."</span></a></td>
			</tr>
			<tr>
				<td><label for='newslogan'>"._SITESLOGAN.":</label></td><td><input type='text' class='textbox' name='newslogan' value='".htmlspecialchars($slogan, ENT_QUOTES)."'> <a href='#' class='pophelp'>[?]<span>"._HELP_SLOGAN."</span></a></td>
			</tr>
			<tr>
				<td><label for='newurl'>"._SITEURL.":</label></td><td><input type='text' class='textbox' name='newsiteurl' value='$url'> <a href='#' class='pophelp'>[?]<span>"._HELP_URL."</span></a></td>
			</tr>
			<tr>				
				<td><label for='newtableprefix'>"._TABLEPREFIX.":</label></td><td><input type='text' class='textbox' name='newtableprefix' value='".TABLEPREFIX."'> <a href='#' class='pophelp'>[?]<span>"._HELP_TABLEPREFIX."</span></a></td>
			</tr>
			<tr>				
				<td><label for='newsiteemail'>"._ADMINEMAIL.":</label></td><td><input type='text' class='textbox' name='newsiteemail' value='$siteemail'> <a href='#' class='pophelp'>[?]<span>"._HELP_SITEEMAIL."</span></a></td>
			</tr>
			<tr>				
				<td><label for='newsiteskin'>"._DEFAULTSKIN.":</label></td><td><select name='newskin'>";
		$directory = opendir(_BASEDIR."skins");
		while($filename = readdir($directory)) {
			if($filename=="." || $filename==".." || !is_dir(_BASEDIR."skins/".$filename)) continue;
			$output .= "<option value='$filename'".($skin == $filename ? " selected" : "").">$filename</option>";
		}
		closedir($directory);
		$output .= "</select> <a href='#' class='pophelp'>[?]<span>"._HELP_SITESKIN."</span></a></td>
			</tr>
			<tr>
				<td><label for='newlanguage'>"._LANGUAGE.":</label></td><td><select name='newlanguage'>";
		$directory = opendir(_BASEDIR."languages");
			while($filename = readdir($directory)) {
				if($filename=="." || $filename==".." || substr($filename, 2) == "_admin.php") continue;
				$output .= "<option value='".substr($filename, 0, 2)."'".
					($language == substr($filename, 0, strpos($filename, ".php")) ? " selected" : "").">
				".substr($filename, 0, strpos($filename, ".php"))."</option>";
			}
		closedir($directory);
		$output .= "</select> <a href='#' class='pophelp'>[?]<span>"._HELP_LANGUAGE."</span></a></td></tr>";
	}
	else if($sect == "submissions") {
		$output .= "<h2>"._SUBMISSIONSETTINGS."</h2>
		<table class='acp'>
			<tr>
				<td><label for='newsubmissionsoff'>"._NOSUBS.":</label></td><td><select name='newsubmissionsoff'>
				<option value='1'".($submissionsoff == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($submissionsoff == "0" ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_SUBSOFF."</span></a></td>
			</tr>
			<tr>
				<td><label for='newautovalidate'>"._AUTOVALIDATE.":</label></td><td><select name='newautovalidate'>
				<option value='1'".($autovalidate == "1" ? "selected" : "").">"._YES."</option>
				<option value='0'".($autovalidate == "0" ? "selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_AUTOVALIDATE."</span></a></td>
			</tr>		
			<tr>
				<td><label for='newcoauthallowed'>"._COAUTHALLOW.":</label></td><td><select name='newcoauthallowed'>
				<option value='1'".($coauthallowed == "1" ? "selected" : "").">"._YES."</option>
				<option value='0'".($coauthallowed == "0" ? "selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_COAUTHORS."</span></a></td>
			</tr>			
			<tr>
				<td><label for='newroundrobins'>"._ALLOWRR.":</label></td><td><select name='newroundrobins'>
				<option value='1'".($roundrobins == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($roundrobins == "0" ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_ROUNDROBINS."</span></a></td>
			</tr>			
			<tr>
				<td><label for='newallowseries'>"._ALLOWSERIES.":</label></td><td><select name='newallowseries'>
				<option value='2'".($allowseries == "2" ? " selected" : "").">"._ALLMEMBERS."</option>
				<option value='1'".($allowseries == "1" ? " selected" : "").">"._AUTHORSONLY."</option>
				<option value='0'".($allowseries == "0" ? " selected" : "").">"._ADMINS."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_ALLOWSERIES."</span></a></td>
			</tr>
			<tr>
				<td><label for='newimageupload'>"._IMAGEUPLOAD.":</label></td><td><select name='newimageupload'>
				<option value='1'".($imageupload == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($imageupload == "0" ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_IMAGEUPLOAD."</span></a></td>
			</tr>
			<tr>
				<td colspan='2'><fieldset style='margin: 0 auto;'><legend>"._IMAGESIZE." <a href='#' class='pophelp'>[?]<span>"._HELP_IMAGESIZE."</span></a></legend>
				<label for='newimageheight'>"._MAXHEIGHT.":</label> <input  type='text' class='textbox=' name='newimageheight' value='$imageheight' size='5'> <br />
				<label for='newimagewidth'>"._MAXWIDTH.":</label> <input  type='text' class='textbox=' name='newimagewidth' value='$imagewidth' size='5'></fieldset></td>
			</tr>
			<tr>
				<td><label for='newstore'>"._HOWSTORE.":</label></td><td><select name='newstore' onChange='if (this.disabled) this.selectedIndex=0' disabled>
					<option value='files'".($store == "files" || !$store ? " selected" : "").">"._FILES."</option>
					<option value='mysql'".($store == "mysql" ? " selected" : "").">"._MYSQL."</option>
					</select> <input class='textbox=' type='checkbox' class='checkbox' name='r1' onClick='this.form.newstore.disabled=false' checked> <a href='#' class='pophelp'>[?]<span>"._HELP_STORE."</span></a></td>
			</tr>
			<tr>
				<td><label for='newstoriespath'>"._STORIESPATH.":</label></td><td><input type='text' class='textbox=' name='newstoriespath' value='$storiespath'> <a href='#' class='pophelp'>[?]<span>"._HELP_STORIESPATH."</span></a></td>
			</tr>
			<tr>
				<td colspan='2'><fieldset style='margin: 0 auto;'><legend>"._MAXMINWORDS."<a href='#' class='pophelp'>[?]<span>"._HELP_MINMAXWORDS."</span></a></legend>
				<label for='newminwords'>"._MIN.":</label> <input  type='text' class='textbox=' name='newminwords' value='$minwords' size='5'> <br />
				<label for='newmaxwords'>"._MAX.":</label> <input  type='text' class='textbox=' name='newmaxwords' value='$maxwords' size='7'></fieldset></td></tr>";
	}
	else if($sect == "sitesettings") {
		$output .= "<h2>"._SITESETTINGS."</h2>
		<table class='acp'>
			<tr>
				<td><label for='newtinyMCE'>"._USETINYMCE. ": </label></td><td><select name='newtinyMCE'>
				<option value=\"2\"" . ($tinyMCE == 2 ? " selected" : "") . ">TinyMce4</option>
				<option value=\"1\"" . ($tinyMCE == 1 ? " selected" : "") . ">TinyMce3</option>
				<option value=\"0\"" . (!$tinyMCE ? " selected" : "") . ">" . _NO . "</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_TINYMCE." "._TINYMCENOTE."</span></a></td>
			</tr>
			<tr>
				<td><label for='newallowed_tags'>"._TAGS.": </label></td><td><input type='text' class='textbox'  name='newallowed_tags' value='".($allowed_tags ? $allowed_tags : "<strong><em><br /><br><blockquote><strike><font><b><i><u><center><img><a><hr><p><ul><li><ol>")."' size='40'> <a href='#' class='pophelp'>[?]<span>"._HELP_ALLOWEDTAGS." "._TINYMCENOTE."</span></a></td>
			</tr>
			<tr>
				<td><label for='newfavorites'>"._FAVORITES.": </label></td><td><select name='newfavorites'>
				<option value='1'".($favorites == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($favorites == "0" ? " selected" : "").">"._NO."</option>
				</select><a href='#' class='pophelp'>[?]<span>"._HELP_FAVORITES."</span></a></td>
			</tr>
			<tr>
				<td><label for='newmultplecats'>"._NUMCATS.": </label></td><td><select name='newmultiplecats'>
				<option value='1'".($multiplecats == "1" ? "selected" : "").">"._MORETHANONE."</option>
				<option value='0'".($multiplecats == "0" ? "selected" : "").">"._ONLYONE."</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_NUMCATS."</span></a></td>
			</tr>
			<tr>
				<td><label for='newnewscomments'>"._NEWSCOMMENTS.": </label></td><td><select name='newnewscomments'>
				<option value='1'".($newscomments == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($newscomments == "0" ? " selected" : "").">"._NO."</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_NEWSCOMMENTS."</span></a></td>
			</tr>
			<tr>
				<td><label for='newlogging'>"._LOGGING.": </label></td><td><select name='newlogging'>
				<option value='1'".($logging == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($logging == "0" ? " selected" : "").">"._NO."</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_LOGGING."</span></a></td>
			</tr>
			<tr>
				<td><label for='newmaint'>"._MAINTENANCE.": </label></td><td><select name='newmaint'>
				<option value='1'".($maintenance == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($maintenance == "0" ? " selected" : "").">"._NO."</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_MAINTENANCE."</span></a></td>
			</tr>
			<tr>
				<td><label for='newdebug'>"._DEBUG.": </label></td><td><select name='newdebug'>
				<option value='1'".($debug == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".(!isset($debug) || $debug == "0" ? " selected" : "").">"._NO."</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_DEBUG."</span></a></td>
			</tr>
			<tr>
				<td><label for='newcaptcha'>"._CAPTCHA.": </label></td><td><select name='newcaptcha'>
				<option value='1'".($captcha == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".(!isset($captcha) || $captcha == "0" ? " selected" : "").">"._NO."</option>
				</select><a href='#' class='pophelp'>[?]<span>"._HELP_CAPTCHA."</span></a></td></tr>";
	}
	else if($sect == "display") {
		$settings = dbquery("SELECT defaultsort, displayindex FROM ".$settingsprefix."fanfiction_settings WHERE sitekey ='".SITEKEY."'");
		list($sitedefaultsort, $sitedisplayindex) = dbrow($settings);
		$defaultdates = array("m/d/y", "m/d/Y", "m/d/Y", "d/m/Y", "d/m/y", "d M Y", 
				"d.m.y", "Y.m.d", "m.d.Y", "d-m-y", "m-d-y", "M d Y", "M d, Y", "F d Y", "F d, Y");
		$defaulttimes = array("h:i a", "h:i A", "H:i", "g:i a", "g:i A", "G:i", "h:i:s a", "H:i:s", "g:i:s a", "g:i:s A", "G:i:s");
		$output .= "<h2>"._DISPLAYSETTINGS."</h2>
		<table class='acp'>
			<tr>
				<td><label for='newdateformat'>"._DATEFORMAT.":</label></td><td><select name='newdateformat'><option value=''>"._SELECTONE."</option>";
		foreach($defaultdates as $date) {
			$output .= "<option value='$date'>".date("$date")."</option>";
		}
		$output .= "</select> "._OR." <input type='text' name='customdateformat' class='textbox' value='$dateformat'> <a href='#' class='pophelp'>[?]<span>"._HELP_DATEFORMAT."</span></a></td>
		</tr>
		<tr>
			<td><label for='newtimeformat'>"._TIMEFORMAT.":</label></td><td><select name='newtimeformat'><option value=''>"._SELECTONE."</option>";
		foreach($defaulttimes as $time) {
			$output .= "<option value='$time'>".date("$time")."</option>";
		}
		$output .= "
			</select> "._OR." <input type='text' name='customtimeformat' class='textbox' value='$timeformat'> <a href='#' class='pophelp'>[?]<span>"._HELP_TIMEFORMAT."</span></a></td>
		</tr>
		<tr>
			<td><label for='newextendcats'>"._EXTENDCATS.":</label></td><td><select name='newextendcats'>
				<option value='1'".($extendcats == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($extendcats == "0" ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_EXTENDCATS."</span></a></td>
		</tr>
		<tr>
			<td><label for='newdisplaycolumns'>"._COLUMNS.":</label></td><td><input  type='text' class='textbox=' name='newdisplaycolumns' size='3' value='$displaycolumns'> <a href='#' class='pophelp'>[?]<span>"._HELP_COLUMNS."</span></a></td>
		</tr>
		<tr>
			<td><label for='newitemsperpage'>"._NUMITEMS.":</label></td><td><input  type='text' class='textbox' name='newitemsperpage' size='3' value='$itemsperpage'> <a href='#' class='pophelp'>[?]<span>"._HELP_ITEMSPERPAGE."</span></a></td>
		</tr>
		<tr>
			<td><label for='newrecentdays'>"._RECENTDAYS.":</label></td><td><input  type='text' class='textbox' name='newrecentdays' size='3' value='$recentdays'> <a href='#' class='pophelp'>[?]<span>"._HELP_RECENTDAYS."</span></a></td>
		</tr>
		<tr>
			<td><label for='newdefaultsort'>"._DEFAULTSORT.":</label></td><td><select name='newdefaultsort'>
				<option value='1'".($sitedefaultsort ? " selected" : "").">"._MOSTRECENT."</option>
				<option value='0'".(!$sitedefaultsort ? " selected" : "").">"._ALPHA."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_DEFAULTSORT."</span></a></td>
		</tr>
		<tr>
			<td><label for='newdisplayindex'>"._STORYINDEX.":</label></td><td><select name='newstoryindex'>
				<option value='1'".($sitedisplayindex ? " selected" : "").">"._YES."</option>
				<option value='0'".(!$sitedisplayindex ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_DISPLAYINDEX."</span></a></td>
		</tr>
		<tr>
			<td><label for='newdisplayprofile'>"._DISPLAYPROFILE.":</label></td><td><select name='newdisplayprofile'>
				<option value='1'".($displayprofile ? " selected" : "").">"._YES."</option>
				<option value='0'".(!$displayprofile ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_DISPLAYPROFILE."</span></a></td>
		</tr>
		<tr>
			<td><label for='newdisplayprofile'>"._LINKSTYLE.":</label></td><td><select name='newlinkstyle'>
				<option value='2'".($linkstyle == 2 ? " selected" : "").">"._BOTHLINKSTYLE."</option>
				<option value='1'".($linkstyle == 1 ? " selected" : "").">"._FIRSTLAST."</option>
				<option value='0'".(!$linkstyle ? " selected" : "").">"._NEXTPREV."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_LINKSTYLE."</span></a></td>
		</tr>
		<tr>
			<td><label for='newlinkrange'>"._LINKRANGE.":</label></td><td><input  type='text' class='textbox=' name='newlinkrange' size='3' value='$linkrange'> <a href='#' class='pophelp'>[?]<span>"._HELP_LINKRANGE."</span></a></td></tr>";
	}
	else if($sect == "reviews") {
		$output .= "<h2>"._REVIEWSETTINGS."</h2>
		<table class='acp'>
			<tr>
				<td><label for='newreviewsallowed'>"._ONREVIEWS.":</label></td><td><select name='newreviewsallowed'>
				<option value='1'".($reviewsallowed == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($reviewsallowed == "0" ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_REVIEWSON."</span></a></td>
		</tr>
		<tr>
				<td><label for='newanonreviews'>"._ANONREVIEWS.":</label></td><td><select name='newanonreviews'>
				<option value='1'".($anonreviews == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($anonreviews == "0" ? " selected" : "").">"._NO."</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_ANONREVIEWS."</span></a></td>
		</tr>
		<tr>
				<td><label for='newrevdelete'>"._REVDELETE.":</label></td><td><select name='newrevdelete'>
				<option value='2'".($revdelete == 2 ? " selected" : "").">"._ALLREV."</option>
				<option value='1'".($revdelete == 1 ? " selected" : "").">"._ANONREV."</option>
				<option value='0'".(empty($revdelete) ? " selected" : "").">"._NONE."</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_REVDELETE."</span></a></td>
		</tr>
		<tr>
				<td><label for='newratings'>"._WHATRATINGS.":</label></td><td><select name='newratings'>
				<option value='2'".($ratings == "2" ? " selected" : "").">"._LIKESYS."</option>
				<option value='1'".($ratings == "1" ? " selected" : "").">"._STARS."</option>
				<option value='0'".($ratings == "0" ? " selected" : "").">"._NONE."</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_RATINGS."</span></a></td>
		</tr>
		<tr>
				<td><label for='newrateonly'>"._ALLOWRATEONLY.":</label></td><td><select name='newrateonly'>
				<option value='1'".($rateonly == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($rateonly == "0" ? " selected" : "").">"._NO."</option>
				</select> <a href='#' class='pophelp'>[?]<span>"._HELP_RATEONLY."</span></a></td></tr>";
	}
	else if($sect == "useropts") {
		$output .= "<h2>"._USERSETTINGS."</h2>
		<table class='acp'>
			<tr>
				<td><label for='newalertson'>"._ALERTSON.":</label></td><td><select name='newalertson'>
				<option value='1'".($alertson == "1" ? " selected" : "").">"._YES."</option>
				<option value='0'".($alertson == "0" ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_ALERTSON."</span></a></td>
		</tr>
		<tr>
				<td><label for='newdisablepops'>"._USERPOPS.":</label></td><td><select name='newdisablepops'>
				<option value='1'".($disablepopups ? " selected" : "").">"._YES."</option>
				<option value='0'".(!$disablepopups ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_POPUPS."</span></a></td>
		</tr>
		<tr>
				<td><label for='newagestatement'>"._AGESTATEMENT.":</label></td><td><select name='newagestatement'>
				<option value='1'".($agestatement ? " selected" : "").">"._YES."</option>
				<option value='0'".(!$agestatement ? " selected" : "").">"._NO."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_AGECONSENT."</span></a></td>
		</tr>
		<tr>
				<td><label for='newpwdsetting'>"._PWDSETTING.":</label></td><td><select name='newpwdsetting'>
				<option value='1'".($pwdsetting ? " selected" : "").">"._SELF."</option>
				<option value='0'".(!$pwdsetting ? " selected" : "").">"._RANDOM."</option>
			</select> <a href='#' class='pophelp'>[?]<span>"._HELP_PWD."</span></a></td></tr>";
	}
	else if($sect == "email") {
		$output .= "<h2>"._EMAILSETTINGS."</h2>
		<table class='acp'>
			<tr>
				<td><label for='newsmtp_host'>"._SMTPHOST.":</label></td><td><input name='newsmtp_host' type='text' value='$smtp_host'> <a href='#' class='pophelp'>[?]<span>"._HELP_SMTPHOST."</span></a></td>
		</tr>
		<tr>
				<td><label for='newsmtp_username'>"._SMTPUSER.":</label></td><td><input name='newsmtp_username' type='text' value='$smtp_username'> <a href='#' class='pophelp'>[?]<span>"._HELP_SMTPUSER."</span></a></td>
		</tr>
		<tr>
				<td><label for='newsmtp_password'>"._SMTPPASS.":</label></td><td><input name='newsmtp_password' type='password' value='$smtp_password'> <a href='#' class='pophelp'>[?]<span>"._HELP_SMTPPWD."</span></a></td></tr>";		
		$output .= 	write_message(_SMTPOFF);
	}
	$output .= "<tr><td colspan='2'><div align='center'><input type='submit' id='submit' class='button' name='submit' value='"._SUBMIT."'></div></form></td></tr></table>";
?>