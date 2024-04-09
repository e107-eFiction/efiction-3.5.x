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


dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_blocks(`block_name`, `block_title`, `block_status`, `block_file`, `block_variables`) VALUES('online', 'Who\'s Online', '0', 'online/online.php', '');");
dbquery("CREATE TABLE IF NOT EXISTS `".TABLEPREFIX. "fanfiction_online` (
  `online_uid` int(11) NOT NULL default '0',
  `online_ip` varbinary(16) DEFAULT NULL,
  `online_timestamp` int(11) NOT NULL default '0'
) ENGINE=MyISAM;");
?>
