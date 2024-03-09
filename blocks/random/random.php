<?php

if(!defined("_CHARSET")) exit( );
	$count = 0;
	$content = "";
	$use_tpl = isset($blocks['random']['tpl']) && $blocks['random']['tpl'] ? true : false;
	$limit = isset($blocks['random']['limit']) && $blocks['random']['limit'] > 0 ? $blocks['random']['limit'] : 1;
	$randomquery = dbquery(_STORYQUERY." ORDER BY rand( ) DESC LIMIT $limit");
	if($use_tpl && dbnumrows($randomquery) >0) $tpl->newBlock("randomblock");
	while($stories = dbassoc($randomquery))
	{
		if(!isset($blocks['random']['allowtags'])) $stories['summary'] = strip_tags($stories['summary']);
		$stories['summary'] = truncate_text(stripslashes($stories['summary']), (isset($blocks['random']['sumlength']) ? $blocks['random']['sumlength'] : 75));
		if(!$use_tpl) $content .= "<div class='randomstory'>".title_link($stories)." "._BY." ".author_link($stories)." ".$ratingslist[$stories['rid']]['name']."<br />".$stories['summary']."</div>";
		else {
			include(_BASEDIR."includes/storyblock.php");
		}
	}
	if($use_tpl && dbnumrows($randomquery) >0) $tpl->gotoBlock("_ROOT");	
?>