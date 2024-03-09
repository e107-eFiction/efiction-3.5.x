<?php
if(!defined("_CHARSET")) exit( );

global $language, $tinyMCE, $allowed_tags;
$blockquery = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_blocks WHERE block_name = 'poll'");
while($block = dbassoc($blockquery)) {
	if ($block['block_variables'])
	{
		$blocks[$block['block_name']] = unserialize($block['block_variables']);
	}
	$blocks[$block['block_name']]['title'] = $block['block_title'];
	 $blocks[$block['block_name']]['file'] = $block['block_file'];
	$blocks[$block['block_name']]['status'] = $block['block_status'];
}
include("blocks/".$blocks['poll']['file']);
	if(isset($_GET['delete']) && isNumber($_GET['delete'])) {
		$delete = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_poll WHERE poll_id = '$_GET[delete]' LIMIT 1");
		if($delete) $output .= write_message(_ACTIONSUCCESSFUL);
	}	
	if(isset($_POST['close_current'])) {
		$final = "";
		foreach($pollopts as $num => $opt) {
			if(strlen($final) > 0) $final .= "#";
			$n = $num + 1;
			$final .= (isset($results[$n]) ? $results[$n] : "0");
		}
		$closepoll =dbquery("UPDATE ".TABLEPREFIX."fanfiction_poll SET poll_results = '$final', poll_end = '" . time() . "' WHERE poll_id = '".$currentpoll['poll_id']."'");
		if($closepoll) $emptyvotes = dbquery("TRUNCATE TABLE `".TABLEPREFIX."fanfiction_poll_votes`");
		$output .= write_message(_ACTIONSUCCESSFUL);
	}
	if(isset($_POST['submit'])) {
		$poll_question = escapestring(descript(stripslashes($_POST['poll_question'])));
		$opts = explode("\r\n", ltrim(strip_tags(preg_replace( '!<p>!iU', "\r\n",  stripslashes(trim($_POST['poll_opts']))), preg_replace("!<p>!iu", "", $allowed_tags))));
		$new_opts = array( );
		foreach($opts as $opt) { 
			if(strlen(trim(preg_replace("!&nbsp;!", " ", $opt))) > 0) $new_opts[] = $opt;
		}
		$new_opts = escapestring(implode("|#|", $new_opts));
		$newpoll = dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_poll(`poll_question`, `poll_opts`, `poll_start`) VALUES('$poll_question', '$new_opts', '" . time() . "')");
		include("blocks/".$blocks['poll']['file']);
		$output .= "<div style='text-align: center;'><b>"._CURRENT.":</b><br /><div class=\"tblborder\" style=\"width: 200px; margin: 0 auto; text-align: left;\">$content</div><br /></div>";
	}
	if($currentpoll && !isset($_POST['close_current'])) {
		include("blocks/".$blocks['poll']['file']);
		$output .= "<div style='text-align: center;'><b>"._CURRENT.":</b><br /><div class=\"tblborder\" style=\"width: 200px; margin: 0 auto; text-align: left;\">$content</div><br /></div>";
		$output .= "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks&amp;admin=poll\" style='text-align: center;'><INPUT type=\"submit\" class=\"button\" name=\"close_current\" value=\""._CLOSEPOLL."\"></form>";
	}
	else  {
		$output .= "<div style='width: 400px; margin: 1em auto; text-align: center;'><form method=\"POST\" enctype=\"multipart/form-data\" action=\"admin.php?action=blocks&admin=poll\">
			<label for=\"poll_question\">"._POLLQUESTION."</label><textarea name='poll_question' cols='40' id='poll_question' rows='5'></textarea><br />";
		if($tinyMCE) 
			$output .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('poll_question');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
		$output .= "<label for=\"poll_opts\">"._POLLOPTS."</label><textarea name='poll_opts' id='poll_opts' class='mceNoEditor' cols='40' rows='5'></textarea><br />";
		$output .= "<INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></form></div>";
	}
	$oldpolls = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_poll WHERE poll_end != 0 ORDER BY poll_id DESC");
	if(dbnumrows($oldpolls)) {
		$output .= "<table class='tblborder' style='width: 400px; margin: 1em auto; text-align: center;'><tr><th>"._POLLQUESTION."</th><th>"._OPTIONS."</th></tr>";
		while($poll = dbassoc($oldpolls)) {
			$output .= "<tr><td class='tblborder'>".stripslashes($poll['poll_question'])."</td><td class='tblborder'><a href='admin.php?action=blocks&amp;admin=poll&amp;delete=".$poll['poll_id']."'>"._DELETE."</a></td></tr>";
		}
		$output .= "</table><br />";
	}
?>