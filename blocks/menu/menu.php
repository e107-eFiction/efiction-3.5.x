<?php
if(!defined("_CHARSET")) exit( );

	if(isset($blocks[$block]['content'])) {
		foreach($blocks[$block]['content'] as $page) {
			if(isset($pagelinks[$page]['link'])) {
				if(empty($blocks[$block]['style'])) $content .= "<li ".($current == $page ? "id=\"menu_current\"" : "").">".$pagelinks[$page]['link']."</li>";
				else $content .= $pagelinks[$page]['link'];
			}
		}
	}
	else {
		$pages = array('home', 'recent', 'titles', 'catslink', 'series', 'members', 'authors', 'challenges', 'search', 'tens', 'featured', 'help', 'contactus', 'login', 'logout', 'adminarea');
		foreach($pages as $page) {
			if(empty($pagelinks[$page])) continue;
			if(empty($blocks[$block]['style'])) $content .= "<li ".($current == $page ? "id=\"menu_current\"" : "").">".$pagelinks[$page]['link']."</li>";
			else $content .= $pagelinks[$page]['link'];
		}
	}
	if(empty($blocks[$block]['style'])) $content = "<ul>$content</ul>";
	$content = "<div id=\"$block\">$content</div>";
?>