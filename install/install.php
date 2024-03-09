<?php
// ----------------------------------------------------------------------
// Updated June 2014
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


function random_char($string)
{
	$length = strlen($string);
	$position = mt_rand(0, $length - 1);
	return $string[$position];
}

function random_string($charset_string, $length)
{
	$return_string = random_char($charset_string);
	for ($x = 1; $x < $length; $x++)
		$return_string .= random_char($charset_string);
	return $return_string;
}

$randomcharset = '23456789' . 'abcdefghijkmnpqrstuvwxyz' . 'ABCDEFGHJKLMNPQRSTUVWXYZ';


// Strip out slashes if magic quotes isn't on.
if (ini_get("magic_quotes_gpc"))
{
	foreach ($_POST as $var => $val)
	{
		$val = is_array($val) ? array_map('stripslashes', $val) : stripslashes($val);
	}
	foreach ($_GET as $var => $val)
	{
		$val = is_array($val) ? array_map('stripslashes', $val) : stripslashes($val);
	}
	foreach ($_COOKIE as $var => $val)
	{
		$val = is_array($val) ? array_map('stripslashes', $val) : stripslashes($val);
	}
}

define("_BASEDIR", "../");
define("_CHARSET", "utf-8");
Header('Cache-Control: private, no-cache, must-revalidate, max_age=0, post-check=0, pre-check=0');
header("Pragma: no-cache");
header("Expires: 0");

$output = "";
$language = "";

include("../includes/class.TemplatePower.inc.php");

/* PHP 8 fix - other way is separated LANs to smaller files */
$allowed_tags = '';
$recentdays = 7;
$sitename = "";  // this breaks stuff in PHP 8.2 
$url = "";
$multiplecats = '';
$minwords = '';
$maxwords = '';
$action = '';
$pwdsetting = '';
$imagewidth = '';
$imageheight = '';
$version = '';
$user = array();
$user['penname'] = '';
$user['email'] = '';
$penname = '';

if (!isset($_GET['step']) || $_GET['step'] == 2)
{
	include("../languages/en.php");
	include("../languages/en_admin.php");
	include("languages/en.php");
}
else if (isset($_REQUEST['language']))
{
	$language = $_REQUEST['language'];
	if (file_exists("../languages/" . $language . ".php")) include("../languages/" . $language . ".php");
	else include("../languages/en.php");
	if (file_exists("../languages/" . $language . "_admin.php")) include("../languages/" . $language . "_admin.php");
	else include("../languages/en_admin.php");
	if (file_exists("languages/" . $language . ".php")) include("languages/" . $language . ".php");
	else include("languages/en.php");
}
else
{
	include("../config.php");
	$dbconnect = dbconnect($dbhost, $dbuser, $dbpass, $dbname);
	$settings = dbquery("SELECT tableprefix, language FROM " . $settingsprefix . "fanfiction_settings WHERE sitekey = '" . $sitekey . "'");
	list($tableprefix, $language) = dbrow($settings);

	define("TABLEPREFIX", $tableprefix);
	define("SITEKEY", $sitekey);
	if (file_exists("../languages/" . $language . ".php")) include("../languages/" . $language . ".php");
	else include("../languages/en.php");
	if (file_exists("../languages/" . $language . "_admin.php")) include("../languages/" . $language . "_admin.php");
	else include("../languages/en_admin.php");
	if (file_exists("languages/" . $language . ".php")) include("languages/" . $language . ".php");
	else include("languages/en.php");
}
include(_BASEDIR . "includes/corefunctions.php");

//  So I don't have to keep updating the version number in 3 different files.
include("../version.php");

//make a new TemplatePower object
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html><head><title>eFiction {$version} Install</title>
<style>
LABEL { float: left; display: block; width: 45%; text-align: right; padding-right: 10px; clear: left;}
.row { float: left; width: 99%; }
#settingsform FORM { width: 80%; margin; 0 auto; }
#settingsform LABEL { float: left; display: block; width: 30%; text-align: right; padding-right: 10px; clear: left; }
#settingsform .fieldset SPAN { float: left; display: block; width: 30%; text-align: right; padding-right: 10px; clear: left;}
#settingsform .fieldset LABEL { float: none; width: auto; display: inline; text-align: left; clear: none; }
#settingsform .tinytoggle { text-align: center; }
#settingsform .tinytoggle LABEL { float: none; display: inline; width: auto; text-align: center; padding: 0; clear: none; }
#settingsform #submit { display: block; margin: 1ex auto; }
a.pophelp{
    position: relative; /* this is the key*/
    z-index:24;
    vertical-align: super;
    text-decoration: none;
}

a.pophelp:hover{z-index:100; border: none; text-decoration: none;}

a.pophelp span{display: none; position: absolute; text-decoration: none;}

a.pophelp:hover span{ /*the span will display just on :hover state*/
    display:block;
    position: absolute;
    top: 0; left: 8em; width: 225px;
    border:1px solid #000;
    background-color:#CCC; color:#000;
    text-decoration: none;
    text-align: left;
    padding: 5px;
    font-weight: normal;
}
.required { color: red; }
</style>
<link rel=\"stylesheet\" type=\"text/css\" href='../default_tpls/style.css'></head>";

$tpl = new TemplatePower("../default_tpls/default.tpl");
$tpl->assignInclude("header", "./../default_tpls/header.tpl");
$tpl->assignInclude("footer", "./../default_tpls/footer.tpl");
$tpl->prepare();
$tpl->newBlock("header");
$tpl->assign("sitename", "eFiction $version Install");
$tpl->gotoBlock("_ROOT");
$tpl->newBlock("footer");
$tpl->assign("footer", "eFiction $version &copy; 2007. <a href='http://efiction.org/'>http://efiction.org/</a>");
$tpl->gotoBlock("_ROOT");

if (!isset($_GET['step']))  $_GET['step'] = 0;
switch ($_GET['step'])
{
	case "9":
		if (isset($_POST['submit']))
		{
			$penname = descript($_POST['newpenname']);
			if ((!$_POST['email']) && !isADMIN) $fail .= "<div style='text-align: center;'>" . _EMAILREQUIRED . " " . _TRYAGAIN . "</div>";
			if ($penname && !preg_match("!^[a-z0-9_ ]{3,30}$!i", $penname)) $fail = "<div style='text-align: center;'>" . _BADUSERNAME . " " . _TRYAGAIN . "</div>";
			if (!preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9.-]+$/", $_POST['email'])) $fail = "<div style='text-align: center;'>" . _INVALIDEMAIL . " " . _TRYAGAIN . "</div>";
			if ($_POST['password'] == $_POST['password2']) $encryptpassword = md5($_POST['password']);
			else $fail =  write_message(_PASSWORDTWICE);
			if (!isset($fail))
			{
				$result = dbquery("INSERT INTO " . $tableprefix . "fanfiction_authors(`penname`, `email`, `password`, `date`) VALUES('$penname', '" . $_POST['email'] . "', '$encryptpassword', '" . time() . "')");
				define("USERUID", dbinsertid());
				$skinquery = dbquery("SELECT skin FROM " . $settingsprefix . "fanfiction_settings WHERE sitekey = '" . SITEKEY . "'");
				list($skin) = dbrow($skinquery);
				$result2 = dbquery("INSERT INTO " . $tableprefix . "fanfiction_authorprefs(`uid`, `userskin`, `level`) VALUES('" . USERUID . "', '$skin', '1')");
				if ($result && $result2) $output .= write_message(_ACTIONSUCCESSFUL . "<br /><br />Installation complete!  <a href='../user.php?action=login'>Log in</a> to your site and go to the Admin area to configure your archive. <strong>Note:</strong> Please delete the install/ folder!");
			}
			else $output .= $fail;
		}
		else
		{
			$output .= "<div id='pagetitle'>" . _ADMINACCT . "</div><form method=\"POST\" class='tblborder' style='margin: 1em auto; width: 400px;' enctype=\"multipart/form-data\" action=\"install.php?step=9\">
			<div class='row'><label for='newpenname'>" . _PENNAME . ":</label> 
			<input name=\"newpenname\" type=\"text\" class=\"textbox\" maxlength=\"200\" value=\"" . $user['penname'] . "\"></div>
		 	<div class='row'><label for='email'>" . _EMAIL . ":</label> <INPUT  type=\"text\" class=\"textbox=\" name=\"email\" value=\"" . $user['email'] .
				"\" maxlength=\"200\"></div>
			<div class='row'><label for='password'>" . _PASSWORD . ":</label>  <INPUT name=\"password\" class=\"textbox\" value=\"\" type=\"password\"></div> 
			<div class='row'><label for='password2'>" . _PASSWORD2 . ":</label> <INPUT name=\"password2\" class=\"textbox=\" value=\"\" type=\"password\"></div>
			<div style='text-align: center;'><INPUT type=\"submit\"class=\"button\" name=\"submit\" value=\"submit\"></div></form>";
		}
		break;

	case "8":
		$output .= "<div id='pagetitle'>" . _AUTHORFIELDS . "</div>";
		if (isset($_GET['install']))
		{
			if ($_GET['install'] == "automatic")
			{
				$fields[] = array('4', 'lj', 'Live Journal', 'http://{info}.livejournal.com', '', '', '0');
				$fields[] = array('1', 'website', 'Web Site', '', '', '', '1');
				$fields[] = array('5', 'AOL', 'AIM', '', '$output .= "<div><label for=\'AOL\'>".$field[\'field_title\'].":</label><INPUT type=\'text\' class=\'textbox\'  name=\'af_".$field[\'field_name\']."\' maxlength=\'40\' value=\'".(!empty($user[\'af_\'.$field[\'field_id\']]) ? $user[\'af_\'.$field[\'field_id\']] : "")."\' size=\'20\'></div>";', '$thisfield = "<img src=\"http://big.oscar.aol.com/".$field[\'info\']."?on_url=$url/images/aim.gif&off_url=$url/images/aim.gif\"> <a href=\"aim:goim?{aol}ScreenName=".$field[\'info\']."\">".format_email($field[\'info\'])."</a>";', '1');
				$fields[] = array('5', 'ICQ', 'ICQ', '', '$output .= "<div><label for=\'AOL\'>".$field[\'field_title\'].":</label><INPUT type=\'text\' class=\'textbox\'  name=\'af_".$field[\'field_name\']."\' maxlength=\'40\' value=\'".(!empty($user[\'af_\'.$field[\'field_id\']]) ? $user[\'af_\'.$field[\'field_id\']] : "")."\' size=\'20\'></div>";', '$thisfield = "<img src=\"http://status.icq.com/online.gif?icq=".$field[\'info\']."&img=5\"> ".$field[\'info\'];', '1');
				$fields[] = array('5', 'MSN', 'MSN IM', '', '$output .= "<div><label for=\'AOL\'>".$field[\'field_title\'].":</label><INPUT type=\'text\' class=\'textbox\'  name=\'af_".$field[\'field_name\']."\' maxlength=\'40\' value=\'".(!empty($user[\'af_\'.$field[\'field_id\']]) ? $user[\'af_\'.$field[\'field_id\']] : "")."\' size=\'20\'></div>";', '$thisfield = "<img src=\"images/msntalk.gif\" alt=\""._MSN."\"> ".format_email($field[\'info\']);', '1');
				$fields[] = array('5', 'Yahoo', 'Yahoo IM', '', '$output .= "<div><label for=\'AOL\'>".$field[\'field_title\'].":</label><INPUT type=\'text\' class=\'textbox\'  name=\'af_".$field[\'field_name\']."\' maxlength=\'40\' value=\'".(!empty($user[\'af_\'.$field[\'field_id\']]) ? $user[\'af_\'.$field[\'field_id\']] : "")."\' size=\'20\'></div>";', '$thisfield = "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=".$field[\'info\']."&.src=pg\"><img border=\'0\' src=\"http://opi.yahoo.com/online?u=".$field[\'info\']."&m=g&t=1\"> ".format_email($field[\'info\'])."</a>";', '1');
				$fields[] = array('4', 'da', 'Deviant Art', 'http://{info}.deviantart.com', '', '', '0');
				$fields[] = array('3', 'betareader', 'Beta-reader', '', '', '', '1');
				$fields[] = array('4', 'dj', 'DeadJournal', 'http://{info}.deadjournal.com/', '', '', '0');
				$fields[] = array('4', 'xanga', 'Xanga', 'http://www.xanga.com/{info}', '', '', '0');
				$fields[] = array('2', 'gender', 'Gender', 'male|#|female|#|undisclosed', '', '', 0);
				$fields[] = array('4', 'myspace', 'MySpace', 'http://www.myspace.com/{info}', '', '', '0');

				$output .= "<table class='tblborder' style='margin: 1em auto; padding: 1em;' cellpadding='5'><tr><th>" . _FIELD . "</th><th>" . _RESULT . "</th></tr>";
				foreach ($fields as $field)
				{
					$f = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_authorfields` (`field_type`, `field_name`, `field_title`, `field_options`, `field_code_in`, `field_code_out`, `field_on`) VALUES('" . $field[0] . "', '" . $field[1] . "','" . $field[2] . "','" . escapestring($field[3]) . "','" . escapestring($field[4]) . "','" . escapestring($field[5]) . "','" . $field[6] . "');");
					$output .= "<tr><td>" . $field[2] . "</td><td align='center'>" . ($f ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				}
				$output .= "</table>";
				$output .= write_message(_FIELDAUTOFAIL . "<br /><br /> <a href='install.php?step=9'>" . _CONTINUE . "</a>");
			}
			else
			{
				$output .= write_message(_FIELDMANUAL . "<br /><br /><a href='install.php?step=9'>" . _CONTINUE . "</a>");
			}
		}
		else $output .= write_message(_FIELDDATAINFO . "<br /><br /><a href='install.php?step=8&amp;install=automatic'>" . _AUTO . "</a> " . _OR . " <a href='install.php?step=8&amp;install=manual'>" . _MANUAL2 . "</a>");
		break;
	case "7":
		$output .= "<div id='pagetitle'>" . _MESSAGEDATA . "</div>";
		if (isset($_GET['install']))
		{
			if ($_GET['install'] == "automatic")
			{
				$output .= "<table class='tblborder' style='margin: 1em auto; padding: 1em;' cellpadding='5'><tr><th>" . _MESSAGE . "</th><th>" . _RESULT . "</th></tr>";
				$msg = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_messages` VALUES (1, 'welcome', '', 'This is your welcome message. It appears on the index page. Include it in your .tpl files with {welcome}.');");
				$output .= "<tr><td>Welcome</td><td align='center'>" . ($msg ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$msg = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_messages` VALUES (2, 'copyright', '', 'This is your sample copyright footer.  Include it in your footer.tpl with <b>{footer}</b><br />\r\n<u>Disclaimer:</u>  All publicly recognizable characters, settings, etc. are the property of their respective owners.  The original characters and plot are the property of the author.  No money is being made from this work.  No copyright infringement is intended.\r\n');");
				$output .= "<tr><td>Copyright</td><td align='center'>" . ($msg ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$msg = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_messages` VALUES (3, 'help', 'Help', '<h3>FAQ</h3><p><strong>I forgot my password!&nbsp; What do I do now?</strong></p><p>To recover a lost password, <a href=\"user.php?action=lostpassword\">click here</a> and enter the e-mail address with which you registered.&nbsp; Your password will be sent to you shortly.</p><p><strong>What kinds of stories are allowed?</strong></p><p>See our <a href=\"submission.php\">Submission Rules.</a></p><p><strong>How do I contact the site administrators?</strong></p><p>You can e-mail us via our <a href=\"contact.php\">contact form.</a></p><p><strong>How do I submit stories?</strong></p><p>If you have not already done so, please <a href=\"user.php?action=newaccount\">register for an account</a>. Once you\'ve logged in, click on <a href=\"user.php\">Account Information</a> and choose <a href=\"stories.php?action=newstory\">Add Story</a>.&nbsp; The form presented there will allow you to submit your story.</p><p><strong>What are the ratings used on the site?</strong></p><p>We use the ratings system from <a href=\"http://www.fictionratings.com/\">www.fictionratings.com</a>.</p><p><strong>What are the story classifications?</strong></p><p>Stories are classified by categories, genres, and warnings.</p>');");
				$output .= "<tr><td>Help</td><td align='center'>" . ($msg ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$msg = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_messages` VALUES (4, 'nothankyou', 'Submission Rejection', 'Your recent submission of \"{storytitle} : {chaptertitle}\" to {sitename} did not meet our requirements for submission.  Please review our {rules}.<br /><br />\r\n\r\n{adminname}');");
				$output .= "<tr><td>Rejection Letter</td><td align='center'>" . ($msg ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$msg = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_messages` VALUES (5, 'printercopyright', '', '<u>Disclaimer:</u> All publicly recognizable characters and settings are the property of their respective owners. The original characters and plot are the property of the author. No money is being made from this work. No copyright infringement is intended.');");
				$output .= "<tr><td>Printable Copyright</td><td align='center'>" . ($msg ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$msg = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_messages` VALUES (6, 'rules', 'Submission Rules', '<p align=\"center\"><strong>Submission Rules</strong></p>\r\n<ol>\r\n  <li>All submissions must be accompanied by a complete disclaimer. If a \r\n  suitable disclaimer is not included, the site administrators reserve the right \r\n  to add a disclaimer.&nbsp; Repeat offenders may be subject to further action \r\n  up to and including removal of stories and account.\r\n<div class=\"tblborder\" style=\"width: 400px; margin: 1em auto;\">\r\n<div style=\"background: #000; color: #FFF; padding: 5px; text-align: center; font-weight: bold;\">Sample Disclaimer</div>\r\n<div class=\"tblborder\" style=\"padding: 5px;\"><span style=\"text-decoration: underline;\">Disclaimer:</span>  All publicly recognizable characters, settings, etc. are the property of their respective owners.  The original characters and plot are the property of the author.&nbsp; \r\n        The author is in no way associated with the owners, creators, or producers of any media franchise.&nbsp;  No copyright infringement is intended.</div>\r\n  </div>\r\n  </li>\r\n  <li>Stories must be submitted to the proper category. &nbsp;If there is an appropriate sub-category, <strong>DO NOT</strong> add your story to the main category.&nbsp; The submission \r\n  form allows you to choose multiple categories for your story, and we worked very hard to add that functionality for you.&nbsp;&nbsp; <u><strong>So please \r\n  do NOT add your story multiple times!</strong></u></li>\r\n  <li>Titles and summaries must be kid friendly.&nbsp; No exceptions.&nbsp; </li>\r\n  <li>&quot;Please read&quot;, &quot;Untitled&quot;, etc. are not acceptable titles or summaries.</li>\r\n  <li>A number of authors have requested that fans refrain from writing fan \r\n  fiction based on their work.&nbsp; Therefore submissions will not be \r\n  accepted based on the works of P.N. Elrod, Raymond Feist, Terry Goodkind, \r\n  Laurell K. Hamilton, Anne McCaffrey, Robin McKinley, Irene Radford, Anne Rice, \r\n  and Nora Roberts/J.D. Robb.&nbsp; </li>\r\n  <li>Actor/actress stories are not permitted...not even if they\'re visiting an \r\n  alternate reality.</li>\r\n  <li>Correct grammar and spelling are expected of all stories submitted to this \r\n  site.&nbsp; The site administrators are not grammar Nazis.&nbsp; However, the \r\n  site administrators reserve the right to request corrections in submissions \r\n  with a multitude of grammar and/or spelling errors.&nbsp; If such a request is \r\n  ignored, the story will be deleted.</li>\r\n  <li>All stories must be rated correctly and have the appropriate warnings.&nbsp; \r\n  All adult rated stories are expected to have warnings.&nbsp; After all, they \r\n  wouldn\'t have that rating if there wasn\'t something to be warned about!&nbsp; The site administrators recognize \r\n  that there is an audience for these stories, but please respect those who do \r\n  not wish to read them by labeling them appropriately.&nbsp;\r\n  <u><strong>Please note: Stories containing adults having sex with minors are strictly forbidden.</strong></u>&nbsp; </li>\r\n  <li>Stories with multiple chapters should be archived as such and <span style=\"font-weight: bold; text-decoration: underline;\">NEVER</span> as \r\n  separate stories.&nbsp; Upload the first chapter of your story, then go to <a href=\"stories.php?action=viewstories\">Manage Stories</a> in \r\nyour account to add additional chapters.  If you have trouble with this, please contact the site administrator or ask a \r\n  friend to help you.</li>\r\n  <li>As much as possible, spoiler warnings are expected on all stories.  For categories with serialized content, such as series of books or television series, \r\n  spoilers are <strong>mandatory</strong> for the current season and/or most recent part.  An appropriate spoiler warning to place in your summary would be: Spoilers for <u>Star Trek II: The Wrath of Khan.</u> \r\n  <strong>DO NOT</strong> do anything like this: <u>Spoilers for the one where Spock dies.</u></li>\r\n</ol>\r\n  <p>Submissions found to be in violation of these rules may be removed and the \r\n  author\'s account suspended at the discretion of the site administrators and/or \r\n  moderators.&nbsp; The site administrators reserve the right to modify these \r\n  rules as needed.</p>');");
				$output .= "<tr><td>Submission Rules</td><td align='center'>" . ($msg ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$msg = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_messages` VALUES (7, 'thankyou', '{sitename} Submission Acceptance', 'Thanks for your submission.');");
				$output .= "<tr><td>Acceptance Letter</td><td align='center'>" . ($msg ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$msg = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_messages` VALUES (8, 'tos', 'Terms of Service', 'This is the Terms of Service for your site.  It will be displayed when a new member registers to the site.');");
				$output .= "<tr><td>Terms of Service</td><td align='center'>" . ($msg ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$msg = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_messages` VALUES (9, 'maintenance', 'Site Maintenance', '<p style=\"text-align: center;\">This site is currently undergoing maintenance.  Please check back soon.  Thank you.</p>');");
				$output .= "<tr><td>Site Maintenance</td><td align='center'>" . ($msg ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$output .= "</table>";
				$output .= write_message(_MESSAGEAUTOFAILUPGRADE . "<br /><br /> <a href='install.php?step=8'>" . _CONTINUE . "</a>");
			}
			else
			{
				$output .= write_message(_MESSAGEMANUAL . "<br /><br /><a href='install.php?step=8'>" . _CONTINUE . "</a>");
			}
		}
		else $output .= write_message(_MESSAGEDATANEW . "<br /><br /><a href='install.php?step=7&amp;install=automatic'>" . _AUTO . "</a> " . _OR . " <a href='install.php?step=7&amp;install=manual'>" . _MANUAL2 . "</a>");

		break;
	case "6":
		$output .= "<div id='pagetitle'>" . _BLOCKDATA . "</div>";
		if (isset($_GET['install']))
		{
			if ($_GET['install'] == "automatic")
			{
				$blocklist = array(
					array("categories", "Categories", "categories/categories.php", "1", ""),
					array("featured", "Featured Stories", "featured/featured.php", "1", ""),
					array("info", "Site Info", "info/info.php", "2", ""),
					array("login", "Log In", "login/login.php", "1", ""),
					array("menu", "Main Menu", "menu/menu.php", "1", ""),
					array("random", "Random Story", "random/random.php", "2", ""),
					array("recent", "Most Recent", "recent/recent.php", "2", "a:1:{s:3:\"num\";s:1:\"1\";}"),
					array("skinchange", "Skin Change", "skinchange/skinchange.php", "1", ""),
					array("news", "Site News", "news/news.php", "1", "a:1:{s:3:\"num\";s:1:\"1\";}")
				);
				$output .= "<table class='tblborder' style='margin: 1em auto; padding: 1em;' cellpadding='5'><tr><th>" . _BLOCK . "</th><th>" . _RESULT . "</th></tr>";
				foreach ($blocklist as $block)
				{
					$b = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_blocks` (`block_name`, `block_title`, `block_file`, `block_status`, `block_variables`) VALUES('" . $block[0] . "', '" . $block[1] . "', '" . $block[2] . "', '" . $block[3] . "', '" . escapestring($block[4]) . "');");
					$output .= "<tr><td>" . $block[1] . "</td><td align='center'>" . ($b ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				}
				$output .= "</table>";
				$output .= write_message(_BLOCKDATAFAILUPGRADE . "<br /><br /><a href='install.php?step=7'>" . _CONTINUE . "</a>");
			}
			else
			{
				$output .= write_message(_BLOCKDATAMANUAL . "<br /><br /><a href='install.php?step=6'>" . _CONTINUE . "</a>");
			}
		}
		else $output .= write_message(_BLOCKDATANEW . "<br /><br /><a href='install.php?step=6&amp;install=automatic'>" . _AUTO . "</a> " . _OR . " <a href='install.php?step=6&amp;install=manual'>" . _MANUAL2 . "</a>");
		break;
	case "5":
		$output .= "<div id='pagetitle'>" . _LINKDATA . "</div>";
		if (isset($_GET['install']))
		{
			if ($_GET['install'] == "automatic")
			{
				$pagelist = array(
					array("home", "Home", "index.php", "0", "0"),
					array("recent", "Most Recent", "browse.php?type=recent", "0", "0"),
					array("login", "Login", "user.php?action=login", "0", "0"),
					array("adminarea", "Admin", "admin.php", "0", "2"),
					array("logout", "Logout", "user.php?action=logout", "0", "1"),
					array("featured", "Featured Stories", "browse.php?type=featured", "0", "0"),
					array("catslink", "Categories", "browse.php?type=categories", "0", "0"),
					array("members", "Members", "authors.php?action=list", "0", "0"),
					array("authors", "Authors", "authors.php?list=authors", "0", "0"),
					array("help", "Help", "viewpage.php?page=help", "0", "0"),
					array("search", "Search", "search.php", "0", "0"),
					array("series", "Series", "browse.php?type=series", "0", "0"),
					array("tens", "Top Tens", "toplists.php", "0", "0"),
					array("challenges", "Challenges", "modules/challenges/challenges.php", "0", "0"),
					array("contactus", "Contact Us", "contact.php", "0", "0"),
					array("rules", "Rules", "viewpage.php?page=rules", "0", "0"),
					array("tos", "Terms of Service", "viewpage.php?page=tos", "0", "0"),
					array("rss", "<img src=\'images/xml.gif\' alt=\'RSS\' border=\'0\'>", "rss.php", "0", "0"),
					array("login", "Account Info", "user.php", "0", "1"),
					array("titles", "Titles", "browse.php?type=titles", "0", "0"),
					array("register", "Register", "user.php?action=register", "0", "0"),
					array("lostpassword", "Lost Password", "user.php?action=lostpassword", "0", "0"),
					array("newsarchive", "News Archive", "news.php", "0", "0"),
					array("browse", "Browse", "browse.php", "0", "0"),
					array("charslink", "Characters", "browse.php?type=characters", "0", "0"),
					array("ratings", "Ratings", "browse.php?type=ratings", "0", "0"),
				);
				$output .= "<table class='tblborder' style='margin: 1em auto; padding: 1em;' cellpadding='5'><tr><th>" . _PAGE . "</th><th>" . _RESULT . "</th></tr>";
				foreach ($pagelist as $page)
				{
					$pages = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_pagelinks` (`link_name`, `link_text`, `link_url`, `link_target`, `link_access`) VALUES ('" . $page[0] . "', '" . $page[1] . "', '" . $page[2] . "', '" . $page[3] . "', '" . $page[4] . "');");
					$output .= "<tr><td>" . stripslashes($page[1]) . "</td><td align='center'>" . ($pages ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				}

				$output .= "</table>";
				$output .= write_message(_LINKAUTOFAIL . "<br /><br /> <a href='install.php?step=6'>" . _CONTINUE . "</a>");
			}
			else
			{
				$output .= write_message(_LINKMANUAL . "<br /><br /><a href='install.php?step=5'>" . _CONTINUE . "</a>");
			}
		}
		else $output .= write_message(_LINKDATAINFO . "<br /><br />" . _LINKSETUP . " <a href='install.php?step=5&amp;install=automatic'>" . _AUTO . "</a> " . _OR . " <a href='install.php?step=5&amp;install=manual'>" . _MANUAL2 . "</a>");
		break;
	case "4":
		$output .= "<div id='pagetitle'>" . _PANELDATA . "</div>";
		if (isset($_GET['install']))
		{
			if ($_GET['install'] == "automatic")
			{

				$panellist = array(
					array("submitted", "Submissions", "", "3", "5", "0", "A"),
					array("versioncheck", "Version Check", "", "3", "7", "0", "A"),
					array("newstory", "Add New Story", "stories.php?action=newstory&admin=1", "3", "3", "0", "A"),
					array("addseries", "Add New Series", "series.php?action=add", "3", "3", "0", "A"),
					array("news", "News", "", "3", "5", "0", "A"),
					array("featured", "Featured Stories", "", "3", "5", "0", "A"),
					array("characters", "Characters", "", "2", "2", "0", "A"),
					array("ratings", "Ratings", "", "2", "3", "0", "A"),
					array("members", "Members", "", "2", "5", "0", "A"),
					array("mailusers", "Mail Users", "", "2", "6", "0", "A"),
					array("settings", "Settings", "", "1", "2", "0", "A"),
					array("blocks", "Blocks", "", "1", "3", "0", "A"),
					array("censor", "Censor", "", "1", "0", "1", "A"),
					array("admins", "Admins", "", "1", "6", "0", "A"),
					array("classifications", "Classifications", "", "2", "4", "0", "A"),
					array("categories", "Categories", "", "2", "1", "0", "A"),
					array("custpages", "Custom Pages", "", "1", "4", "0", "A"),
					array("validate", "Validate Submission", "", "3", "0", "1", "A"),
					array("yesletter", "Validation Letter", "", "3", "0", "1", "A"),
					array("noletter", "Rejection Letter", "", "3", "0", "1", "A"),
					array("links", "Page Links", "", "1", "5", "0", "A"),
					array("messages", "Message Settings", "", "2", "0", "1", "A"),
					array("login", "Login", "", "0", "0", "1", "U"),
					array("logout", "Logout", "", "1", "5", "0", "U"),
					array("revreceived", "Reviews Received", "", "1", "0", "1", "U"),
					array("editprefs", "Edit Preferences", "", "1", "2", "0", "U"),
					array("lostpassword", "Lost Password", "", "0", "0", "1", "U"),
					array("editbio", "Edit Bio", "", "1", "1", "0", "U"),
					array("register", "Register", "", "0", "0", "1", "U"),
					array("manageimages", "Manage Images", "", "1", "5", "0", "S"),
					array("revres", "Review Response", "", "1", "0", "1", "U"),
					array("stats", "View Your Statistics", "", "1", "3", "0", "U"),
					array("newstory", "Add New Story", "stories.php?action=newstory", "1", "1", "0", "S"),
					array("newseries", "Add New Series", "series.php?action=add", "1", "3", "0", "S"),
					array("managestories", "Manage Stories", "stories.php?action=viewstories", "1", "2", "0", "S"),
					array("manageseries", "Manage Series", "series.php?action=manage", "1", "4", "0", "S"),
					array("reviewsby", "Your Reviews", "", "1", "0", "1", "U"),
					array("storiesby", "Stories by {author}", "", "0", "1", "0", "P"),
					array("seriesby", "Series by {author}", "", "0", "2", "0", "P"),
					array("reviewsby", "Reviews by {author}", "", "0", "3", "0", "P"),
					array("categories", "Categories", "", "0", "1", "0", "B"),
					array("characters", "Characters", "", "0", "2", "0", "B"),
					array("ratings", "Ratings", "", "0", "3", "0", "B"),
					array("titles", "Titles", "", "0", "5", "0", "B"),
					array("class", "Classes", "", "0", "0", "1", "B"),
					array("recent", "Most Recent", "", "0", "0", "1", "B"),
					array("featured", "Featured Stories", "", "0", "0", "1", "B"),
					array("panels", "Panels", "", "1", "1", "0", "A"),
					array("phpinfo", "PHP Info", "", "1", "7", "0", "A"),
					array("contact", "Contact", "", "0", "0", "1", "P"),
					array("series", "Series", "", "0", "4", "0", "B"),
					array("viewlog", "Action Log", "", "1", "8", "0", "A"),
					array("shortstories", "10 Shortest Stories", "toplists/default.php", "0", "6", "0", "L"),
					array("longstories", "10 Longest Stories", "toplists/default.php", "0", "5", "0", "L"),
					array("largeseries", "10 Largest Series", "toplists/default.php", "0", "1", "0", "L"),
					array("smallseries", "10 Smallest Series", "toplists/default.php", "0", "2", "0", "L"),
					array("reviewedseries", "10 Most Reviewed Series", "toplists/default.php", "0", "4", "0", "L"),
					array("prolificauthors", "10 Most Prolific Authors", "toplists/default.php", "0", "10", "0", "L"),
					array("prolificreviewers", "10 Most Prolific Reviewers", "toplists/default.php", "0", "12", "0", "L"),
					array("reviewedstories", "10 Most Reviewed Stories", "toplists/default.php", "0", "8", "0", "L"),
					array("readstories", "10 Most Read Stories", "toplists/default.php", "0", "9", "0", "L"),
					array("manfavs", "Manage Favorites", "", "1", "2", "0", "F"),
					array("favstories", "10 Most Favorite Stories", "toplists/default.php", "0", "7", "0", "L"),
					array("favauthors", "10 Most Favorite Authors", "toplists/default.php", "0", "11", "0", "L"),
					array("favseries", "10 Most Favorite Series", "toplists/default.php", "0", "3", "0", "L"),
					array("favst", "Favorite Stories", "", "0", "0", "1", "F"),
					array("favse", "Favorite Series", "", "0", "0", "1", "F"),
					array("favau", "Favorite Authors", "", "0", "0", "1", "F"),
					array("favst", "Favorite Stories", "", "0", "0", "1", "U"),
					array("favse", "Favorite Series", "", "0", "0", "1", "U"),
					array("favau", "Favorite Authors", "", "0", "0", "1", "U"),
					array("favlist", "{author}\'s Favorites", "viewuser.php?action=manfavs", "0", "5", "0", "F"),
					array("skins", "Skins", "", "3", "6", "0", "A"),
					array("authorfields", "Profile Information", "", "1", "9", "0", "A"),
					array("maintenance", "Archive Maintenance", "", "1", "10", "0", "A"),
					array("manual", "Admin Manual", "", "3", "6", "0", "A"),
					array('modules', 'Modules', '', 1, "11", 0, 'A')
				);
				$output .= "<table class='tblborder' style='margin: 1em auto; padding: 1em;' cellpadding='5'><tr><th>Panel</th><th>" . _RESULT . "</th></tr>";
				foreach ($panellist as $panel)
				{
					unset($panels);
					$panels = dbquery("INSERT INTO `" . $tableprefix . "fanfiction_panels` (`panel_name`, `panel_title`, `panel_url`, `panel_level`, `panel_order`, `panel_hidden`, `panel_type`) VALUES ('" . $panel[0] . "', '" . $panel[1] . "', '" . $panel[2] . "', '" . $panel[3] . "', '" . $panel[4] . "', '" . $panel[5] . "', '" . $panel[6] . "');");
					$output .= "<tr><td>" . stripslashes($panel[1]) . "</td><td align='center'>" . ($panels ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				}
				$output .= "</table>";
				$output .= write_message(_PANELAUTOFAIL . "<br /><br /><a href='install.php?step=5'>" . _CONTINUE . "</a>");
			}
			else
			{
				$output .= write_message(_PANELMANUAL . "<br /><br /><a href='install.php?step=5'>" . _CONTINUE . "</a>");
			}
		}
		else $output .= write_message(_PANELDATAINFO . "<br /><br />" . _PANELSETUP . " <a href='install.php?step=4&amp;install=automatic'>" . _AUTO . "</a> " . _OR . " <a href='install.php?step=4&amp;install=manual'>" . _MANUAL2 . "</a>");
		break;
	case "3":
		$output .= "<div id='pagetitle'>" . _INSTALLTABLES . "</div>";
		if (isset($_GET['install']))
		{
			if ($_GET['install'] == "automatic")
			{
				$output .= "<table class='tblborder' style='margin: 1em auto; padding: 1em;' cellpadding='5'><tr><th>Table</th><th>" . _RESULT . "</th></tr>";
				$authorfields = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_authorfields` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_type` tinyint(4) NOT NULL default '0',
  `field_name` varchar(30) NOT NULL default ' ',
  `field_title` varchar(255) NOT NULL default ' ',
  `field_options` text,
  `field_code_in` text,
  `field_code_out` text,
  `field_on` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`field_id`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_authorfields</td><td align='center'>" . ($authorfields ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$authorinfo = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_authorinfo` (
  `uid` int(11) NOT NULL default '0',
  `field` int(11) NOT NULL default '0',
  `info` varchar(255) NOT NULL default ' ',
  PRIMARY KEY  (`uid`,`field`),
  KEY `uid` (`uid`)) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_authorinfo</td><td align='center'>" . ($authorinfo ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$authorprefs = dbquery("
CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_authorprefs` (
  `uid` int(11) NOT NULL default '0',
  `newreviews` tinyint(1) NOT NULL default '0',
  `newrespond` tinyint(1) NOT NULL default '0',
  `ageconsent` tinyint(1) NOT NULL default '0',
  `alertson` tinyint(1) NOT NULL default '0',
  `tinyMCE` tinyint(1) NOT NULL default '0',
  `sortby` tinyint(1) NOT NULL default '0',
  `storyindex` tinyint(1) NOT NULL default '0',
  `validated` tinyint(1) NOT NULL default '0',
  `userskin` varchar(60) NOT NULL default 'default',
  `level` tinyint(1) NOT NULL default '0',
  `categories` varchar(200) NOT NULL default '0',
  `contact` tinyint(1) NOT NULL default '0',
  `stories` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_authorprefs</td><td align='center'>" . ($authorprefs ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$authors = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_authors` (
  `uid` int(11) NOT NULL auto_increment,
  `penname` varchar(200) NOT NULL default '',
  `realname` varchar(200) NOT NULL default '',
  `email` varchar(200) NOT NULL default '',
  `bio` text NULL,
  `image` varchar(200) NOT NULL default '',
  `date`int(10) unsigned NOT NULL default '0',
  `admincreated` char(1) NOT NULL default '0',
  `password` varchar(40) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `penname` (`penname`),
  KEY `admincreated` (`admincreated`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_authors</td><td align='center'>" . ($authors ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$blocks = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_blocks` (
  `block_id` int(11) NOT NULL auto_increment,
  `block_name` varchar(30) NOT NULL default '',
  `block_title` varchar(150) NOT NULL default '',
  `block_file` varchar(200) NOT NULL default '',
  `block_status` tinyint(1) NOT NULL default '0',
  `block_variables` text NOT NULL,
  PRIMARY KEY  (`block_id`),
  KEY `block_name` (`block_name`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_blocks</td><td align='center'>" . ($blocks ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$categories = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_categories` (
  `catid` int(11) NOT NULL auto_increment,
  `parentcatid` int(11) NOT NULL default '-1',
  `category` varchar(60) NOT NULL default '',
  `description` text NOT NULL,
  `image` varchar(100) NOT NULL default '',
  `locked` char(1) NOT NULL default '0',
  `leveldown` tinyint(4) NOT NULL default '0',
  `displayorder` int(11) NOT NULL default '0',
  `numitems` int(11) NOT NULL default '0',
  PRIMARY KEY  (`catid`),
  KEY `byparent` (`parentcatid`,`displayorder`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_categories</td><td align='center'>" . ($categories ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$chapters = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_chapters` (
  `chapid` int(11) NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `inorder` int(11) NOT NULL default '0',
  `notes` text NULL,
  `storytext` text NULL,
  `endnotes` text NULL,
  `validated` char(1) NOT NULL default '0',
  `wordcount` int(11) NOT NULL default '0',
  `rating` tinyint(4) NOT NULL default '0',
  `reviews` smallint(6) NOT NULL default '0',
  `sid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  PRIMARY KEY  (`chapid`),
  KEY `sid` (`sid`),
  KEY `uid` (`uid`),
  KEY `inorder` (`inorder`),
  KEY `title` (`title`),
  KEY `validated` (`validated`),
  KEY `forstoryblock` (`sid`,`validated`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_chapters</td><td align='center'>" . ($chapters ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$characters = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_characters` (
  `charid` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `charname` varchar(60) NOT NULL default '',
  `bio` text NOT NULL,
  `image` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`charid`),
  KEY `catid` (`catid`),
  KEY `charname` (`charname`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_characters</td><td align='center'>" . ($characters ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$classes = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_classes` (
  `class_id` int(11) NOT NULL auto_increment,
  `class_type` int(11) NOT NULL default '0',
  `class_name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`class_id`),
  KEY `byname` (`class_type`,`class_name`,`class_id`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_classes</td><td align='center'>" . ($classes ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$classtypes = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_classtypes` (
  `classtype_id` int(11) NOT NULL auto_increment,
  `classtype_name` varchar(50) NOT NULL default '',
  `classtype_title` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`classtype_id`),
  UNIQUE KEY `classtype_name` (`classtype_name`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_classtypes</td><td align='center'>" . ($classtypes ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$coauthors = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_coauthors` (
  `sid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sid`,`uid`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_coauthors</td><td align='center'>" . ($coauthors ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$codeblocks = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_codeblocks` (
  `code_id` int(11) NOT NULL auto_increment,
  `code_text` text NOT NULL,
  `code_type` varchar(20) default NULL,
  `code_module` varchar(60) default NULL,
  PRIMARY KEY  (`code_id`),
  KEY `code_type` (`code_type`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_codeblocks</td><td align='center'>" . ($codeblocks ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$comments = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_comments` (
  `cid` int(11) NOT NULL auto_increment,
  `nid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `comment` text NOT NULL,
  `time` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cid`),
  KEY `commentlist` (`nid`,`time`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_comments</td><td align='center'>" . ($comments ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$favorites = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_favorites` (
  `uid` int(11) NOT NULL default '0',
  `item` int(11) NOT NULL default '0',
  `type` char(2) NOT NULL default '',
  `comments` text NOT NULL,
  UNIQUE KEY `byitem` (`item`,`type`,`uid`),
  UNIQUE KEY `byuid` (`uid`,`type`,`item`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_favorites</td><td align='center'>" . ($favorites ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$inseries = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_inseries` (
  `seriesid` int(11) NOT NULL default '0',
  `sid` int(11) NOT NULL default '0',
  `subseriesid` int(11) NOT NULL default '0',
  `confirmed` int(11) NOT NULL default '0',
  `inorder` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sid`,`seriesid`, `subseriesid`),
  KEY `seriesid` (`seriesid`,`inorder`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_inseries</td><td align='center'>" . ($inseries ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$logs = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_log` (
  `log_id` int(11) NOT NULL auto_increment,
  `log_action` varchar(255) default NULL,
  `log_uid` int(11) NOT NULL,
  `log_ip` int(11) UNSIGNED default NULL,
  `log_timestamp` int(10) unsigned NOT NULL default '0',
  `log_type` varchar(2) NOT NULL,
  PRIMARY KEY  (`log_id`)
) ENGINE=MyISAM");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_log</td><td align='center'>" . ($logs ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$messages = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `message_name` varchar(50) NOT NULL default '',
  `message_title` varchar(200) NOT NULL default '',
  `message_text` text NOT NULL,
  PRIMARY KEY  (`message_id`),
  KEY `message_name` (`message_name`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_messages</td><td align='center'>" . ($messages ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$modules = dbquery("
CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_modules` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default 'Test Module',
  `version` varchar(10) NOT NULL default '1.0',
  PRIMARY KEY  (`id`),
  KEY `name_version` (`name`,`version`)
)");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_modules</td><td align='center'>" . ($modules ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$news = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_news` (
  `nid` int(11) NOT NULL auto_increment,
  `author` varchar(60) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `story` text NOT NULL,
  `time` int(10) unsigned NOT NULL default '0',
  `comments` INT NOT NULL DEFAULT '0',
  PRIMARY KEY  (`nid`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_news</td><td align='center'>" . ($news ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$pagelinks = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_pagelinks` (
  `link_id` int(11) NOT NULL auto_increment,
  `link_name` varchar(50) NOT NULL default '',
  `link_text` varchar(100) NOT NULL default '',
  `link_key` CHAR( 1 ) NULL,
  `link_url` varchar(250) NOT NULL default '',
  `link_target` char(1) NOT NULL default '0',
  `link_access` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`link_id`),
  KEY `link_name` (`link_name`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_pagelinks</td><td align='center'>" . ($pagelinks ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$panels = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_panels` (
  `panel_id` int(11) NOT NULL auto_increment,
  `panel_name` varchar(50) NOT NULL default 'unknown',
  `panel_title` varchar(100) NOT NULL default 'Unnamed Panel',
  `panel_url` varchar(100) default NULL,
  `panel_level` tinyint(4) NOT NULL default '3',
  `panel_order` tinyint(4) NOT NULL default '0',
  `panel_hidden` tinyint(1) NOT NULL default '0',
  `panel_type` varchar(20) NOT NULL default 'A',
  PRIMARY KEY  (`panel_id`),
  KEY `panel_type` (`panel_type`,`panel_name`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_panels</td><td align='center'>" . ($panels ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$ratings = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_ratings` (
  `rid` int(11) NOT NULL auto_increment,
  `rating` varchar(60) NOT NULL default '',
  `ratingwarning` char(1) NOT NULL default '0',
  `warningtext` text NOT NULL,
  PRIMARY KEY  (`rid`),
  KEY `rating` (`rating`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_ratings</td><td align='center'>" . ($ratings ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$reviews = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_reviews` (
  `reviewid` int(11) NOT NULL auto_increment,
  `item` int(11) NOT NULL default '0',
  `chapid` int(11) NOT NULL default '0',
  `reviewer` varchar(60) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `review` text NOT NULL,
  `date`int(10) unsigned NOT NULL default '0',
  `rating` int(11) NOT NULL default '0',
  `respond` char(1) NOT NULL default '0',
  `type` char(2) NOT NULL default '',
  PRIMARY KEY  (`reviewid`),
  KEY `chapid` (`chapid`),
  KEY `rating` (`rating`),
  KEY `respond` (`respond`),
  KEY `avgrating` (`type`,`item`,`rating`),
  KEY `bychapter` (`chapid`,`rating`),
  KEY `byuid` (`uid`,`item`,`type`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_reviews</td><td align='center'>" . ($reviews ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$series = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_series` (
  `seriesid` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `summary` text NOT NULL,
  `uid` int(11) NOT NULL default '0',
  `isopen` tinyint(4) NOT NULL default '0',
  `catid` varchar(200) NOT NULL default '0',
  `rating` tinyint(4) NOT NULL default '0',
  `classes` varchar(200) NOT NULL default '',
  `characters` varchar(250) NOT NULL default '',
  `reviews` smallint(6) NOT NULL default '0',
  `numstories` INT NOT NULL DEFAULT '0',
  `challenges` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`seriesid`),
  KEY `catid` (`catid`),
  KEY `characters` (`characters`),
  KEY `classes` (`classes`),
  KEY `owner` (`uid`,`title`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_series</td><td align='center'>" . ($series ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$stats = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_stats` (
  `sitekey` varchar(50) NOT NULL default '0',
  `stories` int(11) NOT NULL default '0',
  `chapters` int(11) NOT NULL default '0',
  `series` int(11) NOT NULL default '0',
  `reviews` int(11) NOT NULL default '0',
  `wordcount` int(11) NOT NULL default '0',
  `authors` int(11) NOT NULL default '0',
  `members` int(11) NOT NULL default '0',
  `reviewers` int(11) NOT NULL default '0',
  `newestmember` int(11) NOT NULL default '0'
) ENGINE=MyISAM");
				dbquery("INSERT INTO " . $tableprefix . "fanfiction_stats(`sitekey`, `newestmember`) VALUES('" . SITEKEY . "', '1')");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_stats</td><td align='center'>" . ($stats ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$stories = dbquery("CREATE TABLE IF NOT EXISTS `" . $tableprefix . "fanfiction_stories` (
  `sid` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default 'Untitled',
  `summary` text NULL,
  `storynotes` text NULL,
  `catid` varchar(100) NOT NULL default '0',
  `classes` varchar(200) NOT NULL default '0',
  `charid` varchar(250) NOT NULL default '0',
  `rid` varchar(25) NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `updated` int(10) unsigned NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `coauthors` tinyint(1) NOT NULL default '0',
  `featured` char(1) NOT NULL default '0',
  `validated` char(1) NOT NULL default '0',
  `completed` char(1) NOT NULL default '0',
  `rr` char(1) NOT NULL default '0',
  `wordcount` int(11) NOT NULL default '0',
  `rating` tinyint(4) NOT NULL default '0',
  `reviews` smallint(6) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sid`),
  KEY `title` (`title`),
  KEY `catid` (`catid`),
  KEY `charid` (`charid`),
  KEY `rid` (`rid`),
  KEY `uid` (`uid`),
  KEY `featured` (`featured`),
  KEY `completed` (`completed`),
  KEY `rr` (`rr`),
  KEY `validateduid` (`validated`,`uid`),
  KEY `recent` (`updated`,`validated`)
) ENGINE=MyISAM;");
				$output .= "<tr><td>" . $tableprefix . "fanfiction_stories</td><td align='center'>" . ($stories ? "<img src=\"../images/check.gif\">" : "<img src=\"../images/X.gif\">") . "</td></tr>";
				$output .= "</table>";
				$output .= write_message(_TABLEFAILED . "<br /><br /><a href='install.php?step=4'>" . _CONTINUE . "</a>");
			}
			else if ($_GET['install'] == "manual")
			{
				$output .= write_message(_TABLESMANUAL . "<br /><br /><a href='install.php?step=3'>" . _CONTINUE . "</a>");
			}
		}
		else
		{

			$output .= write_message(_TABLESINSTALL . "<br /><br /><a href='install.php?step=3&amp;install=automatic'>" . _AUTO . "</a> " . _OR . " <a href='install.php?step=3&amp;install=manual'>" . _MANUAL2 . "</a>");
		}

		break;

	case "2";
		include("../config.php");
		if (dbnumrows(dbquery("SHOW TABLES LIKE '" . $settingsprefix . "fanfiction_settings'")))
		{
			include("../config.php");
			$dbconnect = dbconnect($dbhost, $dbuser, $dbpass, $dbname);
			$settings = dbquery("SELECT tableprefix, language FROM " . $settingsprefix . "fanfiction_settings WHERE sitekey = '" . $sitekey . "'");
			list($tableprefix, $language) = dbrow($settings);

			if ($tableprefix == '') $tableprefix = $settingsprefix;

			define("TABLEPREFIX", $tableprefix);
			define("SITEKEY", $sitekey);

			$sect = isset($_GET['sect']) ? $_GET['sect'] : "main";
			$settingsresults = dbquery("SELECT * FROM " . $settingsprefix . "fanfiction_settings WHERE sitekey = '" . SITEKEY . "'");
			if (dbnumrows($settingsresults) == 0)
			{
				dbquery("INSERT INTO " . $settingsprefix . "fanfiction_settings (`sitekey`) VALUES('" . SITEKEY . "');");
				$settingsresults = dbquery("SELECT * FROM " . $settingsprefix . "fanfiction_settings WHERE sitekey = '" . SITEKEY . "'");
			}
			$settings = dbassoc($settingsresults);
			foreach ($settings as $var => $val)
			{
				$$var = $val;
			}
			if ($sect == "submissions")
			{
				if (isset($_POST['submit']))
				{
					$storiespath = descript($_POST['newstoriespath']);
					if (!file_exists($storiespath) && !file_exists("../" . $storiespath))
					{
						if (!strrchr($storiespath, "/") && !strrchr($storiespath, "\\")) $storiespath = "../$storiespath";
						mkdir("$storiespath", 0755);
						@chmod("$storiespath", 0777);
						if (substr(sprintf('%o', @fileperms("../" . $storiespath)), -4) != "0777") $output .= write_message(_STORIESPATHNOTWRITABLE);
					}
				}
			}
			if ($sect == "email" && isset($_POST['submit']))
			{
				$output .= "<div id=\"pagetitle\">" . _SETTINGS . "</div>";
				$smtp_host = $_POST['newsmtp_host'];
				$smtp_username = $_POST['newsmtp_username'];
				$smtp_password = $_POST['newsmtp_password'];
				$result = dbquery("UPDATE " . $settingsprefix . "fanfiction_settings SET smtp_host = '$smtp_host', smtp_username = '$smtp_username', smtp_password = '$smtp_password' WHERE sitekey = '" . SITEKEY . "'");
				if ($result)
				{
					$output .= write_message(_ACTIONSUCCESSFUL);
					$output .= write_message("<a href='install.php?step=3'>" . _CONTINUE . "</a>");
				}
				else include("../admin/settings.php");
			}
			else include("../admin/settings.php");
		}
		else
		{
			if (isset($_GET['install']))
			{
				if ($_GET['install'] == "automatic")
				{

					$settings = dbquery("CREATE TABLE IF NOT EXISTS `" . $settingsprefix . "fanfiction_settings` (
  `sitekey` varchar(50) NOT NULL default '1',
  `sitename` varchar(200) NOT NULL default 'Your Site',
  `slogan` varchar(200) NOT NULL default 'It''s a cool site!',
  `url` varchar(200) NOT NULL default 'http://www.yoursite.com',
  `siteemail` varchar(200) NOT NULL default 'you@yoursite.com',
  `tableprefix` varchar(50) NOT NULL default '',
  `skin` varchar(50) NOT NULL default 'default',
  `hiddenskins` varchar(255) default '',
  `language` varchar(10) NOT NULL default 'en',
  `submissionsoff` tinyint(1) NOT NULL default '0',
  `storiespath` varchar(20) NOT NULL default 'stories',
  `store` varchar(5) NOT NULL default 'files',
  `autovalidate` tinyint(1) NOT NULL default '0',
  `coauthallowed` int(1) NOT NULL default '0',
  `maxwords` int(11) NOT NULL default '0',
  `minwords` int(11) NOT NULL default '0',
  `imageupload` tinyint(1) NOT NULL default '0',
  `imageheight` int(11) NOT NULL default '200',
  `imagewidth` int(11) NOT NULL default '200',
  `roundrobins` tinyint(1) NOT NULL default '0',
  `allowseries` tinyint(4) NOT NULL default '2',
  `tinyMCE` tinyint(1) NOT NULL default '0',
  `allowed_tags` varchar(200) NOT NULL default '<b><i><u><center><hr><p><br /><br><blockquote><ol><ul><li><img><strong><em>',
  `favorites` tinyint(1) NOT NULL default '0',
  `multiplecats` tinyint(1) NOT NULL default '0',
  `newscomments` tinyint(1) NOT NULL default '0',
  `logging` tinyint(1) NOT NULL default '0',
  `maintenance` tinyint(1) NOT NULL default '0',
  `debug` tinyint(1) NOT NULL default '0',
  `captcha` tinyint(1) NOT NULL default '0',
  `dateformat` varchar(20) NOT NULL default 'd/m/y',
  `timeformat` varchar(20) NOT NULL default '- h:i a',
  `recentdays` tinyint(2) NOT NULL default '7',
  `displaycolumns` tinyint(1) NOT NULL default '1',
  `itemsperpage` tinyint(2) NOT NULL default '25',
  `extendcats` tinyint(1) NOT NULL default '0',
  `displayindex` tinyint(1) NOT NULL default '0',
  `defaultsort` tinyint(1) NOT NULL default '0',
  `displayprofile` tinyint(1) NOT NULL default '0',
  `linkstyle` tinyint(1) NOT NULL default '0',
  `linkrange` tinyint(2) NOT NULL default '5',
  `reviewsallowed` tinyint(1) NOT NULL default '0',
  `ratings` tinyint(1) NOT NULL default '0',
  `anonreviews` tinyint(1) NOT NULL default '0',
  `revdelete` tinyint(1) NOT NULL default '0',
  `rateonly` tinyint(1) NOT NULL default '0',
  `pwdsetting` tinyint(1) NOT NULL default '0',
  `alertson` tinyint(1) NOT NULL default '0',
  `disablepopups` tinyint(1) NOT NULL default '0',
  `agestatement` tinyint(1) NOT NULL default '0',
  `words` text,
  `version` varchar(10) NOT NULL default '" . $version . "',
  `smtp_host` varchar(200) default NULL,
  `smtp_username` varchar(50) default NULL,
  `smtp_password` varchar(50) default NULL,
  PRIMARY KEY  (`sitekey`)
) ENGINE=MyISAM;");
					if ($settings) $output .= write_message("<img src=\"../images/check.gif\"> " . _SETTINGSTABLESUCCESS . " <br /><a href='install.php?step=2'>" . _CONTINUE . "</a>");
					else $output .= write_message(_SETTINGSTABLEAUTOFAIL . "<br /><br /> <a href='install.php?step=2'>" . _CONTINUE . "</a>");
				}
				else
				{
					$output .= write_message(_SETTINGSTABLEMANUAL . "<br /><br /><a href='install.php?step=2'>" . _CONTINUE . "</a>");
				}
			}
			else
			{

				$test = dbquery("SHOW TABLES");
				if (!$test) $output .= write_message(_CONFIGFAILED);
				else $output .= write_message(_SETTINGSTABLESETUP . " <a href='install.php?step=2&amp;install=automatic'>" . _AUTO . "</a> " . _OR . " <a href='install.php?step=2&amp;install=manual'>" . _MANUAL2 . "</a>");
			}
		}
		break;
	default:
		$output .= "<div id='pagetitle'>" . _CONFIGDATA . "</div>";
		if (isset($_POST['submit']))
		{
			$dbhost = descript($_POST['dbhost']);
			$dbname = descript($_POST['dbname']);
			$dbuser = descript($_POST['dbuser']);
			$dbpass = descript($_POST['dbpass']);
			$language = $_POST['language'];
			$sitekey = descript($_POST['sitekey']);
			$settingsprefix = descript($_POST['settingsprefix']);
			$mysqli_access = mysqli_connect($dbhost, $dbuser, $dbpass);
			if (!$mysqli_access)
			{
				$output .= write_message(_CONFIGFAILED);
			}
		}
		if (isset($_POST['submit']) && $mysqli_access)
		{
			$handle = fopen("../config.php", 'w');
			if (!$handle)
			{
				@chmod("../config.php", 0666);
				$handle = fopen("../config.php", 'w');
			}
			if ($handle)
			{
				$dbhost = descript($_POST['dbhost']);
				$dbname = descript($_POST['dbname']);
				$dbuser = descript($_POST['dbuser']);
				$dbpass = descript($_POST['dbpass']);
				$sitekey = descript($_POST['sitekey']);
				if (empty($sitekey)) $sitekey = random_string($randomcharset, 10);
				$language = $_POST['language'];
				$settingsprefix = descript($_POST['settingsprefix']);
				$text = "<?php 
\$dbhost = \"$dbhost\";
\$dbname = \"$dbname\";
\$dbuser= \"$dbuser\";
\$dbpass = \"$dbpass\";
\$sitekey = \"$sitekey\";
\$settingsprefix = \"$settingsprefix\";

include_once(\"includes/dbfunctions.php\");
if(!empty(\$sitekey)) \$dbconnect = dbconnect(\$dbhost, \$dbuser,\$dbpass, \$dbname);

?>";
				fwrite($handle, $text);
				fclose($handle);
				@chmod("../config.php", 0644);
				$output .= write_message(_CONFIGSUCCESS . "<br /><br /><a href='install.php?step=2&amp;language=$language'>" . _CONTINUE . "</a>");
			}
			else $output .= write_message(_ERROR_CONFIGWRITE);
		}
		else
		{
			if (file_exists("../config.php") && !isset($mysqli_access)) include("../config.php");
			if (isset($tinyMCE)) $output .= write_message(_CONFIG2DETECTED);
			else if (isset($sitename) && $sitename) $output .= write_message(_CONFIG1DETECTED);
			else
			{
				$output .=
					"<form method='POST' enctype='multipart/form-data' action='install.php?step=1' class='tblborder' style='width: 350px; margin: 1em auto;'>
						<div><label for='dbhost'>" . _DBHOST . "</label><input type='text'  name='dbhost' id='dbhost'" . (!empty($dbhost) ? "value='$dbhost'" : "value='localhost'") . "> <A HREF=\"#\" class=\"pophelp\">[?]<span>" . _HELP_DBHOST . "</span></A></div>
						<div><label for='dbname'>" . _DBNAME . "</label><input type='text' name='dbname' id='dbname'" . (!empty($dbname) ? "value='$dbname'" : "") . "> <A HREF=\"#\" class=\"pophelp\">[?]<span>" . _HELP_DBNAME . "</span></A></div>
						<div><label for='dbuser'>" . _DBUSER . "</label><input type='text' name='dbuser' id='dbuser'" . (!empty($dbuser) ? "value='$dbuser'" : "") . "> <A HREF=\"#\" class=\"pophelp\">[?]<span>" . _HELP_DBUSER . "</span></A></div>
						<div><label for='dbpass'>" . _DBPASS . "</label><input type='password' name='dbpass' id='dbpass'" . (!empty($dbpass) ? "value='$dbpass'" : "") . "> <A HREF=\"#\" class=\"pophelp\">[?]<span>" . _HELP_DBPASS . "</span></A></div>
						<div><label for='sitekey'>" . _SITEKEY . "</label><input type='text' name='sitekey' value='" . (!empty($sitekey) ? $sitekey : random_string($randomcharset, 10)) . "' id='sitekey'> <A HREF=\"#\" class=\"pophelp\">[?]<span>" . _HELP_INSTALL_SITEKEY . "</span></A></div>
						<div><label for=\"language\">" . _LANGUAGE . ":</label> <select name=\"language\">";
				$directory = opendir("../languages");
				while ($filename = readdir($directory))
				{
					if ($filename == "." || $filename == ".." || substr($filename, 2) == "_admin.php") continue;
					$output .= "<option value=\"" . substr($filename, 0, 2) . "\"" .
						($language == substr($filename, 0, strpos($filename, ".php")) ? " selected" : "") . ">
					" . substr($filename, 0, strpos($filename, ".php")) . "</option>";
				}
				closedir($directory);
				$output .=
					"</select> <A HREF=\"#\" class=\"pophelp\">[?]<span>" . _HELP_LANGUAGE . "</span></A></div>
						<label for='settingsprefix'>" . _SETTINGSPREFIX . "</label><input type='text' name='settingsprefix' id='settingsprefix'" . (isset($settingsprefix) ? "value='$settingsprefix'" : "") . "> <A HREF=\"#\" class=\"pophelp\">[?]<span>" . _HELP_SETTINGSPREFIX . "</span></A><br />
						<div style='text-align: center; margin: 1em;'><INPUT type=\"submit\"class=\"button\" name=\"submit\" value=\"submit\"></div>
					</form>";
			}
		}
}
$tpl->assign("output", $output);

$tpl->printToScreen();
if (isset($_GET['step']) && $_GET['step'] > 2) dbclose();
