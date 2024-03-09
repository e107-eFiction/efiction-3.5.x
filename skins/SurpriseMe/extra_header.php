<?php
if(!defined("_CHARSET")) exit( );
if(!isset($hiddenskins)) $hiddenskins = array( );
if(is_string($hiddenskins)) $hiddenskins = explode(",", $hiddenskins);

$directory = opendir(_BASEDIR."skins");
while($filename = readdir($directory)) {
	if($filename== "." || $filename == $siteskin || $filename== ".." || !is_dir(_BASEDIR."skins/".$filename) || (in_array($filename, $hiddenskins) && !isADMIN)) continue;
	$skinlist[] = $filename;
}
$siteskin = $skinlist[(rand(0, count($skinlist) - 1))];
$skindir = _BASEDIR."skins/$siteskin";
?>