<?php
if(!defined("_CHARSET")) exit( );
	$count = 0;
	$content = "";
	$use_tpl = isset($blocks['recent']['tpl']) && $blocks['recent']['tpl'] ? true : false;
	if(isset($blocks['recent']['num'])) $numupdated = $blocks['recent']['num'];
	else $numupdated = 1;
	$result5 = dbquery(_STORYQUERY." ORDER BY stories.updated DESC LIMIT $numupdated");
	while($stories = dbassoc($result5))
	{
		if(!isset($blocks['recent']['allowtags'])) $stories['summary'] = strip_tags($stories['summary']);
		$stories['summary'] = truncate_text(stripslashes($stories['summary']), (!empty($blocks['recent']['sumlength']) ? $blocks['recent']['sumlength'] : 75));
		if(!$use_tpl) $content .= "<div class='recentstory'>".title_link($stories)." "._BY." ".author_link($stories)." ".$ratingslist[$stories['rid']]['name']."<br />".stripslashes($stories['summary'])."</div>";
		else {
			$tpl->newBlock("recentblock");
			include(_BASEDIR."includes/storyblock.php");
		}

	}	
	if($use_tpl && dbnumrows($result5) >0) $tpl->gotoBlock("_ROOT");	
	unset($updated, $result5);
?>