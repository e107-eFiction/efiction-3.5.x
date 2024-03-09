<?php
/* CATEGORY BLOCK
		Categories are to show only on the index.
		I currently have it set to display:
		The image, then the link, the story count in brackets, and then the description in a div tag. (template)
		Three columns (columns)*/
$blocks["categories"]["status"] = '1';
$blocks["categories"]["template"] = "{image} {link} [{count}] <div>{description}</div>";
$blocks["categories"]["columns"] = '3';


/* FEATURED BLOCK
		By default, the featured stories is off.*/
$blocks["featured"]["status"] =  '1';


/* INFO BLOCK
		Info is set to display only on the home page.  I don't bother to style it.*/
$blocks["info"]["status"] = '1';


/* MENU BLOCK
		The menu is set to display on all pages.
		Additional styles include:
		As an inline list. (style=2)
		Links to home, recent, authors, categories,titles,series,search,top tens, browse by, help, contact us, and the login info. */
$blocks["menu"]["status"] = '1';
$blocks["menu"]["content"] = array (
  0	=>	'home',
  1 => 	'recent',
  2 => 	'authors',
  3 => 	'titles',
  4 => 	'catslink',
  5 => 	'browse',
  6 => 	'help',
  7 => 	'contact us');
$blocks["menu"]["style"] = 0;


/* LOG-IN BLOCK
		The login form is set to display on all pages, even though I only actually call it on the index.  It can safely be changed to just the index, but I did this to address a possible bug.
		I currently have it set to display:
		In long format (form) */
$blocks["login"]["status"] = '1';
$blocks["login"]["form"] = '1';


/* RANDOM STORIES BLOCK
		Status is set to display on just the index page.*/
$blocks["random"]["status"] = '1';


/* RECENT STORIES BLOCK
		By default, the recent stories block is off.*/
$blocks["recent"]["status"] = '1';


/* SKIN CHANGER
		Status is set to display on all pages. */
$blocks["skinchange"]["status"] = '1';


/* NEWS BLOCK
		Status is set to display on just the index page. */
$blocks["news"]["status"] = '1';


/* NEW STORY INDICATOR
		Styled: Plain text surrounded with the superscript HTML tag with font-size set to extra small and italic.*/
$new = "<sup style='font-size:small;font-style:bold'>New!</sup>";
?>