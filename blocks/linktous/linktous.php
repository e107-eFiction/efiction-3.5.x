<?php
if(!defined("_CHARSET")) exit( );

/* Start block configuration */

// The text that will appear under your image.  
$link2us = "Link to Us!";

// The folder or URL to the folder where your images are stored.
// Include the trailing slash!
$imgfolder = _BASEDIR."buttons/";

// The page you're sending them to for the code
$codepage = _BASEDIR."promote.php";

// The list of your images.  One per line.
$images[] = "88x31-A.jpg";
$images[] = "88x31-B.jpg";
$images[] = "88x31-C.jpg";
$images[] = "88x31-D.jpg";
$images[] = "88x31-E.jpg";
$images[] = "88x31-F.jpg";
$images[] = "88x31-G.jpg";
$images[] = "88x31-H.jpg";
$images[] = "88x31-I.jpg";
$images[] = "88x31-J.jpg";
$images[] = "88x31-K.jpg";
$images[] = "88x31-L.jpg";
$images[] = "88x31-M.jpg";
$images[] = "88x31-N.jpg";
$images[] = "88x31-O.jpg";
$images[] = "100x35-A.jpg";
$images[] = "100x35-B.jpg";
$images[] = "100x35-C.jpg";
$images[] = "100x35-D.jpg";
$images[] = "100x35-E.jpg";
$images[] = "100x35-F.jpg";
$images[] = "100x35-G.jpg";

/* End block configuration */

$img = $images[mt_rand(0, count($images) - 1)];

$content = "<div style='text-align: center;'><a href='$codepage' alt='$link2us'><img src='$imgfolder$img' alt='$sitename' /></a><br /><a href='$codepage'>$link2us</a></div>";
unset($images, $img);
?>