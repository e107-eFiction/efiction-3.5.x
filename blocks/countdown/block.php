<?php
if(!defined("_CHARSET")) exit( );
if(file_exists(_BASEDIR."blocks/countdown/".$language.".php")) include(_BASEDIR."blocks/countdown/".$language.".php");
else include(_BASEDIR."blocks/countdown/en.php");

if(empty($blocks['countdown']['target'])) {
	$content = _NOCOUNTDOWN;
}
else { 
$target = $blocks['countdown']['target'];
if(empty($blocks['countdown']['CDformat'])) $CDformat = _COUNTDOWNFORMAT;
else $CDformat = $blocks['countdown']['CDformat'];
if(empty($blocks['countdown']['CDfinal'])) $CDfinal = _COUNTDOWNOVER;
else $CDfinal = $blocks['countdown']['CDfinal'];

	$content = '<span id="countdown"></span>
<script language="JavaScript" type="text/javascript">

TargetDate = "'.$target.'";
DisplayFormat = "'.$CDformat.'";
FinishMessage = "'.$CDfinal.'";

function CountBack(secs) {
  if (secs < 0) {
    document.getElementById("countdown").innerHTML = FinishMessage;
    return;
  }
  origSecs = secs;
  days = Math.floor(secs / (60 * 60 * 24));
  secs %= (60 * 60 * 24);
  hours = Math.floor(secs / (60 * 60));
  secs %= (60 * 60);
  minutes = Math.floor(secs / 60);
  secs %= 60;
  seconds = secs;

  DisplayStr = DisplayFormat.replace(/{days}/g, days);
  DisplayStr = DisplayStr.replace(/{hours}/g, hours);
  DisplayStr = DisplayStr.replace(/{minutes}/g, minutes);
  DisplayStr = DisplayStr.replace(/{seconds}/g, seconds);

  document.getElementById("countdown").innerHTML = DisplayStr;
  setTimeout("CountBack(" + (origSecs - 1) + ")", SetTimeOutPeriod);
}
var SetTimeOutPeriod = 1000;

var dateTarget = new Date(TargetDate);
var dateNow = new Date();
  dateDiff = new Date(dateTarget - dateNow);
gsecs = Math.floor(dateDiff.valueOf()/1000);
CountBack(gsecs);
</script>
';
}
?>