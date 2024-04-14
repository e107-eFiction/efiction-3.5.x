<?php
if(!defined("_CHARSET")) exit( );

	global $dateformat, $tpl;
	if(file_exists(_BASEDIR."blocks/online/{$language}.php")) include(_BASEDIR."blocks/online/{$language}.php");
	else include(_BASEDIR."blocks/online/en.php");

	$where = "online_uid=" . (USERUID ? USERUID : "0 AND online_ip = INET6_ATON('" . $_SERVER['REMOTE_ADDR'] . "')");
 
	$result = dbquery("SELECT * FROM ".TABLEPREFIX."fanfiction_online WHERE $where");
 
	if(dbnumrows($result) > 0) {
		
			dbquery("UPDATE ".TABLEPREFIX."fanfiction_online SET online_timestamp = '".time( )."' WHERE $where");
	}
	else {
		$ip = $_SERVER['REMOTE_ADDR'];
		if (filter_var($ip, FILTER_VALIDATE_IP, array(FILTER_FLAG_IPV4, FILTER_FLAG_IPV6)))
		{

		} 
		else {
			$ip = "0";	
		}
		
		dbquery("INSERT INTO " . TABLEPREFIX . "fanfiction_online(online_uid, online_ip, online_timestamp)
		 VALUES('" . (USERUID ? USERUID : 0) . "', INET6_ATON('" . $ip . "'), '" . time() . "')");
	} 
 
	$result = dbquery("DELETE FROM ".TABLEPREFIX."fanfiction_online WHERE online_timestamp < ".(time()-60));
	$q1 = dbquery("SELECT COUNT(online_ip) FROM ".TABLEPREFIX."fanfiction_online WHERE online_uid = 0");
 
	list($guests) = dbrow($q1);
	$tpl->assignGlobal("guests", empty($guests) ? 0 : $guests);
	$q2 = dbquery("SELECT o.online_uid, "._PENNAMEFIELD." as penname FROM ".TABLEPREFIX."fanfiction_online as o, "._AUTHORTABLE." WHERE o.online_uid = "._UIDFIELD." AND o.online_uid != 0");
	$omlist = array( );
	while($om = dbassoc($q2)) {
		$omlist[] = "<a href='"._BASEDIR."viewuser.php?uid=".$om['online_uid']."'>".$om['penname']."</a>";
	}
	$tpl->assignGlobal("onlinemembers", count($omlist) ? implode(", ", $omlist) : "");
	$content = "<div id='who_online'><span class='label'>"._GUESTS.":</span> ".($guests ? $guests : 0)."<br />\n
		<span class='label'>"._MEMBERS.":</span> ".(count($omlist) ? implode(", ", $omlist) : "")."</div>";
?>
