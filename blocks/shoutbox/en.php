<?php

// ----------------------------------------------------------------------
// eFiction 3.2
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

if(!defined("_CHARSET")) exit( );


define("_SHOUT", "Shout");
define("_NOSHOUTS", "No messages have been posted.");
if(!defined("_GUEST")) define("_GUEST", "Guest");
define("_EDITSHOUT", "Edit Shout");
define("_SHOUTARCHIVE", "Shout Archive");
define("_DELETESHOUTS", "Delete shouts older than ");
define("_DAYS", " days.");
define("_SHOUTDATE", "Shout Date Format");
define("_SHOUTLIMIT", "Shout Limit");
define("_GUESTSHOUTS", "Allow guests to shout");
define("_SHOUTEND", "End of shout.  Shouts are limited to 200 characters!");

define("_HELP_SHOUTDATEFORMAT", "The format for date (and optionally time) to be displayed in the shout box.  For custom fomats, use PHP's date format.");
?>