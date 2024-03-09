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


dbquery("INSERT INTO ".TABLEPREFIX."fanfiction_blocks(`block_name`, `block_title`, `block_status`, `block_file`, `block_variables`) VALUES('poll', 'Poll', '0', 'poll/poll.php', '');");
dbquery("CREATE TABLE IF NOT EXISTS `".TABLEPREFIX."fanfiction_poll_votes` (
  `vote_id` int(11) NOT NULL auto_increment,
  `vote_user` int(11) NOT NULL default '0',
  `vote_opt` int(11) NOT NULL default '0',
  `vote_poll` int(11) NOT NULL default '0',
  PRIMARY KEY  (`vote_id`),
  KEY `vote_user` (`vote_user`,`vote_poll`)
) ENGINE=MyISAM;") ;
dbquery("CREATE TABLE IF NOT EXISTS `".TABLEPREFIX. "fanfiction_poll` (
`poll_id` INT NOT NULL AUTO_INCREMENT ,
`poll_question` VARCHAR( 250 ) NOT NULL ,
`poll_opts` TEXT NOT NULL ,
`poll_start` int(10) unsigned NOT NULL default '0',
`poll_end` int(10) unsigned NOT NULL default '0',
`poll_results` VARCHAR( 250 ) NULL ,
PRIMARY KEY ( `poll_id` ))");
?>