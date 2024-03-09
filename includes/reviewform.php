<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
// Based on eFiction 1.1
// Copyright (C) 2003 by Rebecca Smallwood.
// http://efiction.sourceforge.net/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------

if(!defined("_CHARSET")) exit( );
if(!isset($item)) $item = "";
if(!isset($action)) $action == "add";
$form = "<form method=\"POST\" id=\"reviewform\" enctype=\"multipart/form-data\" action=\"reviews.php?action=".($action == "edit" ? "edit&amp;reviewid=".$review['reviewid'] : "add&amp;type=$type&amp;item=$item").(!empty($nextchapter) ? "&amp;next=$nextchapter" : "")."\">
<div style=\"width: 350px; margin: 0 auto; text-align: left;\"><label for=\"reviewer\">"._NAME.":</label> ";
if($action != "edit") {
	$review = array('reviewid' => '', 'reviewer' => '', 'review' => '', 'rating' => '-1');
	if(isMEMBER)
		$form .= USERPENNAME." <INPUT type=\"hidden\" name=\"reviewer\" value=\"".USERPENNAME."\"><INPUT type=\"hidden\" name=\"uid\" value=\"".USERUID."\">";
	else
		$form .= "<INPUT name=\"reviewer\" id=\"reviewer\" size=\"30\" maxlength=\"200\">";
}
else $form .= $review['reviewer']."<INPUT type=\"hidden\" name=\"reviewid\" value=\"".$review['reviewid']."\">";
$form .= "<br /><label for=\"review\">"._REVIEW.":</label><br />
<textarea class=\"textbox\" name=\"review\" id=\"review\" cols=\"40\" rows=\"5\">".$review['review']."</textarea>";
if($tinyMCE) 
	$form .= "<div class='tinytoggle'><input type='checkbox' name='toggle' onclick=\"toogleEditorMode('review');\" checked><label for='toggle'>"._TINYMCETOGGLE."</label></div>";
if($ratings == "2"){
	$form .= "<div><label for=\"rating\">"._OPINION."</label> <select id=\"rating\" name=\"rating\" class=\"textbox\">
		<option value=\"1\"".($review['rating'] == 1 ? " selected" : "").">"._LIKED."</option><option value=\"0\"".($review['reviewid'] && !$review['rating'] ? " selected" : "").">"._DISLIKED."</option><option value=\"-1\"".($review['rating'] == -1 || $action == "add" ? " selected" : "").">"._NONE."</option></select></div>";
}
if($ratings == "1") {
	$form .= "<div><label for=\"rating\">"._REVIEWRATING.":</label> <select name=\"rating\">";
	for($x=10; $x > 0; $x--) {
		$form .= "<option value=\"$x\"".($review['rating'] == $x ? " selected" : "").">$x</option>";
	}
	$form .= "<option value=\"-1\"".($review['rating'] == -1 || $action != "edit" ? " selected" : "").">"._NONE."</option></select></div>";
}
if(!USERUID && !empty($captcha)) $form .= "<div><span class=\"label\">"._CAPTCHANOTE."</span><input MAXLENGTH=5 SIZE=5 name=\"userdigit\" type=\"text\" value=\"\"><br /><img width=120 height=30 src=\""._BASEDIR."includes/button.php\" style=\"border: 1px solid #111;\"></div>";

$form .= "<INPUT type=\"hidden\" name=\"chapid\" value=\"".(isset($chapid) ? $chapid : "")."\"><div style=\"text-align: center; margin: 1ex;\"><INPUT type=\"submit\" class=\"button\" name=\"submit\" value=\""._SUBMIT."\"></div>";
if(!empty($rateonly)) $form .= "<div>"._REVIEWNOTE."</div>";
$form .= "</div></form>";
?>