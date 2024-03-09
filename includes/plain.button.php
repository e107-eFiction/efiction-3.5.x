<?php

session_start();
define("_BASEDIR", "../");
include("../config.php");
unset($_SESSION[$sitekey.'_digit']);

$image = imagecreate(120, 30);

$white    = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
$gray    = imagecolorallocate($image, 0xC0, 0xC0, 0xC0);
$darkgray = imagecolorallocate($image, 0x50, 0x50, 0x50);

srand((double)microtime()*1000000);
unset($digit, $cnum);
for ($i = 0; $i < 30; $i++) {
  $x1 = rand(0,120);
  $y1 = rand(0,30);
  $x2 = rand(0,120);
  $y2 = rand(0,30);
  imageline($image, $x1, $y1, $x2, $y2 , $gray);  
}

for ($i = 0; $i < 5; $i++) {
$cnum[$i] = rand(0,9);
}


for ($i = 0; $i < 5; $i++) {
 $fnt = rand(3,5);
 $x = $x + rand(12 , 20);
 $y = rand(7 , 12); 
 imagestring($image, $fnt, $x, $y, $cnum[$i] , $darkgray); 
}

$digit = "$cnum[0]$cnum[1]$cnum[2]$cnum[3]$cnum[4]";

$_SESSION[$sitekey.'_digit'] = md5($sitekey.$digit);
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
exit( );
?> 

