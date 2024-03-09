<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(-1);

session_start();
define("_BASEDIR", "../");
include("../config.php");
unset($_SESSION[$sitekey.'_digit']);

$image = imagecreate(140, 40);

$white    = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
$gray    = imagecolorallocate($image, 0xC0, 0xC0, 0xC0);
$darkgray = imagecolorallocate($image, 0x50, 0x50, 0x50);
$black = imagecolorallocate($image, 0x00, 0x00, 0x00);


srand((double)microtime()*1000000);
unset($digit, $cnum);


function rgb_rand($min=0,$max=255) {
$color['r'] = mt_rand($min,$max);
$color['g'] = mt_rand($min,$max);
$color['b'] = mt_rand($min,$max);
return $color;
}

/* Build the list of fonts */

putenv('GDFONTPATH=' . realpath('.'));

$folder=dir("cFonts/"); //The directory where your fonts reside

while($font=$folder->read()) {
  if(stristr($font,'.ttf')) $fontList[] = "cFonts/".$font;
}

$folder->close();

for ($i = 0; $i < 5; $i++) {
$cnum[$i] = rand(0,9);
$rC = rgb_rand(0, 175);
$rColors[$i] = imagecolorallocate($image, $rC['r'], $rC['g'], $rC['b']);

for ($x = 0; $x < 2; $x++) {
  $x1 = rand(0,140);
  $y1 = rand(0,40);
  $x2 = rand(0,140);
  $y2 = rand(0,40);
  imageline($image, $x1, $y1, $x2, $y2 , $rColors[$i]);  
}

}

/* generate random dots in background */
for( $i=0; $i<(140*40)/3; $i++ ) {
  imagefilledellipse($image, mt_rand(0,140), mt_rand(0,40), 1, 1, $gray);
}

for ($i = 0; $i < 5; $i++) {
 $x = $x + mt_rand(16, 24);
 $y = mt_rand(26, 32); 
 $angle = mt_rand(-15, 15);
 $c = $color[$i];
 $fnt = mt_rand(0, sizeof($fontList) - 1);
 $colori = $rColors[$i];
 imagettftext($image, mt_rand(20, 24), $angle,  $x, $y, $colori, $fontList[$fnt], $cnum[$i]); 

}
 
$digit = "$cnum[0]$cnum[1]$cnum[2]$cnum[3]$cnum[4]";

$_SESSION[$sitekey.'_digit'] = md5($sitekey.$digit);
 
header("Expires: Tue, 11 Jun 1985 05:00:00 GMT");  
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
header("Cache-Control: no-store, no-cache, must-revalidate");  
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);

exit( );
?> 

