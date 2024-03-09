<?php
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
// Valid HTML 4.01 Transitional
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

/* The $current variable is used to create the {page_id} variable. It is also 
used in the pagelinks to identify the current page being displayed so that 
link in the menu block can be highlighted.  If you decide to set this, check 
the docs/creating_skins.htm file for a list of words already in use.  If you
don't intend to use {page_id} in your skins and haven't added this page to 
the $pagelinks list.  You can just ignore it */

$current = "template";

include ("header.php");

if(file_exists("$skindir/default.tpl")) $tpl = new TemplatePower( "$skindir/default.tpl" );
else $tpl = new TemplatePower(_BASEDIR."default_tpls/default.tpl");
$tpl->assignInclude( "header", "./$skindir/header.tpl" );
$tpl->assignInclude( "footer", "./$skindir/footer.tpl" );
//let TemplatePower do its thing, parsing etc.
$tpl->prepare();

include("includes/pagesetup.php");

//Start modifying below:

/* This page is included for pages that require the inclusion of php scripting.  
If you need to include html/text only you're encouraged to use the custom page 
panel in the admin area.*/


//Don't modify below this line	
$tpl->assign("output", $output);
$tpl->printToScreen();
dbclose( );
?>