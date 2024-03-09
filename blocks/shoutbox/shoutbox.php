<?php
if(!defined("_CHARSET")) exit( );

if(file_exists(_BASEDIR."blocks/shoutbox/{$language}.php")) include_once(_BASEDIR."blocks/shoutbox/{$language}.php");
else include_once(_BASEDIR."blocks/shoutbox/en.php");
$content = "";
if(isset($_POST['shouthidden']) && (isMEMBER || !empty($blocks['shoutbox']['guestshouts']))) {
	http_response_code(202);
	if(isMEMBER) $shout_name = USERUID;
	else {
		$shout_name = trim(descript($_POST['shoutname']));
		if(isNumber($shout_name)) $shout_name = _GUEST;
		if($captcha && !captcha_confirm()) $shout_name = false;
	}
	$shout_message = trim($_POST['shout_message']);
	$shout_message = preg_replace("/^(.{200}).*$/", "$1", $shout_message);
	$shout_message = preg_replace("/[^\s]{25}/", "$1\n", $shout_message);
	$search = array("\"", "'", "\\", '\"', "\'", "<", ">", "&nbsp;", "\n");
	$replace = array("&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ", " ");
	$shout_message = str_replace($search, $replace, replace_naughty(trim($shout_message)));
	$shout_message = str_replace("\n", "<br />", $shout_message);
	if(!empty($shout_name) && !empty($shout_message)) {
		dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_shoutbox(`shout_name`, `shout_message`, `shout_datestamp`) VALUES('$shout_name', '$shout_message', '".time()."')");
	}

}
if(isMEMBER || !empty($blocks['shoutbox']['guestshouts'])) {
	$content = "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js\"></script>
	<script language=\"javascript\" type=\"text/javascript\">
	$(document).ready(function() {
		$(function () {
		  $('#shout').click( function () {
			$.ajax({
			  type: 'post',
			  url: $('#shoutbox').attr('action'),
			  data: $('#shoutbox').serialize(),
			  success: function () {
				location.reload();
				$('#shout_message').val(\"\");
			  }
			});
		  });
		});
	});
var shoutend = '"._SHOUTEND."';
function ismaxlength(obj){
var mlength = 200
shout_messagelength = document.shoutbox.shout_message.value.length;
document.shoutbox.counter.value = 200 - shout_messagelength;
if (document.shoutbox.shout_message.value.length >= mlength) {
   alert(shoutend);
   document.shoutbox.shout_message.value = document.shoutbox.shout_message.value.substr(0, mlength)
}
}

</script><div id='shoutbox_container' style='width: 100%;'>\n<form name='shoutbox' id='shoutbox' method='POST' action='".$_SERVER['SCRIPT_NAME'].($_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING']  : "")."'>\n";
	if(!isMEMBER) $content .= "<div><label for='shoutname'>"._NAME.":</label> <input type='text' name='shoutname' value='' style='display: block; width: 90%; margin: 0 auto;' maxlength='30'></div>";
	else $content .= "<div><span class='label'>"._NAME.":</span> ".USERPENNAME."</div>";
	if(!isMEMBER && $captcha) $content .= "<div><label for='userdigit'>"._CAPTCHANOTE."</label><input MAXLENGTH=5 SIZE=5 name=\"userdigit\" type=\"text\" value=\"\"><div style='text-align: center;'><img width=120 height=40 src=\""._BASEDIR."includes/button.php\" style=\"border: 1px solid #111;\"></div></div>";
	$content .= "<div><label for='shout_MESSAGE'>"._SHOUT.":</label> <textarea name='shout_message' class='mceNoEditor' id='shout_message' rows='4' cols='15' maxlength='200' style='display: block; width: 90%; margin: 0 auto;' onkeyDown='return ismaxlength(this)'></textarea></div> <input type='button' name='shout' id='shout' value='"._SHOUT."'> <input size='1' class='small' type='text' id='counter' value='200'> <input type='hidden' name='shouthidden' id='shouthidden' value='"._SHOUT."'>
	</form></div>";
}
if(isset($blocks['shoutbox']['shoutlimit'])) $shoutlimit = $blocks['shoutbox']['shoutlimit'];
else $shoutlimit = 10;
if(!empty($blocks['shoutbox']['shoutdate'])) $shoutdate = $blocks['shoutbox']['shoutdate'];
else $shoutdate = $dateformat." ".$timeformat;
$shouts = dbquery("SELECT shouts.*, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_shoutbox as shouts LEFT JOIN "._AUTHORTABLE." ON "._UIDFIELD." = shouts.shout_name ORDER BY shout_datestamp DESC");
$totalshouts = dbnumrows($shouts);
$shouts = dbquery("SELECT shouts.*, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_shoutbox as shouts LEFT JOIN "._AUTHORTABLE." ON "._UIDFIELD." = shouts.shout_name ORDER BY shout_datestamp DESC LIMIT 0, ".$shoutlimit);
$content .= "<div id='shoutlist' style='height: 200px; overflow: auto;'>";
if(dbnumrows($shouts) != 0) {
	while($shout = dbassoc($shouts)) {
		if(isNumber($shout['shout_name']) && isset($shout['penname'])) $shoutname = "<a href='"._BASEDIR."viewuser.php?uid=".$shout['shout_name']."'>".$shout['penname']."</a>";
		else if(isset($shout['shout_name'])) $shoutname = $shout['shout_name'];
		else $shout = _GUEST; // Just in case.
		$content .= "<span class='sbname'>$shoutname</span><br /><span class='sbdatetime'>".date("$shoutdate", $shout['shout_datestamp']);
		if(isADMIN) $content .= " [<a href='"._BASEDIR."admin.php?action=blocks&amp;admin=shoutbox&amp;shout_id=".$shout['shout_id']."' class='sbadmin'>"._EDIT."</a>]";
		$content .= "</span><br />\n<span class='sbshout'>".stripslashes($shout['shout_message'])."</span><br />";
	}
}
else $content .= write_message(_NOSHOUTS);
$content .= "</div>";
if($totalshouts > $shoutlimit) $content .= write_message("<a href='"._BASEDIR."blocks/shoutbox/archive.php'>"._SHOUTARCHIVE."</a>");
?>
