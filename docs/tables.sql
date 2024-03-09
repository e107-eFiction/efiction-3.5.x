-- 
-- Table structure for table `fanfiction_authorfields`
-- 

CREATE TABLE `fanfiction_authorfields` (
  `field_id` int(11) NOT NULL auto_increment,
  `field_type` tinyint(4) NOT NULL default '0',
  `field_name` varchar(30) NOT NULL default '',
  `field_title` varchar(255) NOT NULL default '',
  `field_options` text,
  `field_code_in` text,
  `field_code_out` text,
  `field_on` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`field_id`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_authorinfo`
-- 

CREATE TABLE `fanfiction_authorinfo` (
  `uid` int(11) NOT NULL default '0',
  `field` int(11) NOT NULL default '0',
  `info` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`uid`,`field`),
  KEY `uid` (`uid`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_authorprefs`
-- 

CREATE TABLE `fanfiction_authorprefs` (
  `uid` int(11) NOT NULL default '0',
  `newreviews` tinyint(1) NOT NULL default '0',
  `newrespond` tinyint(1) NOT NULL default '0',
  `ageconsent` tinyint(1) NOT NULL default '0',
  `alertson` tinyint(1) NOT NULL default '0',
  `tinyMCE` tinyint(1) NOT NULL default '0',
  `sortby` tinyint(1) NOT NULL default '0',
  `storyindex` tinyint(1) NOT NULL default '0',
  `validated` tinyint(1) NOT NULL default '0',
  `userskin` varchar(60) NOT NULL default 'default',
  `level` tinyint(1) NOT NULL default '0',
  `categories` varchar(200) NOT NULL default '0',
  `contact` tinyint(1) NOT NULL default '0',
  `stories` int(11) NOT NULL default '',
  PRIMARY KEY  (`uid`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_authors`
-- 

CREATE TABLE `fanfiction_authors` (
  `uid` int(11) NOT NULL auto_increment,
  `penname` varchar(200) NOT NULL default '',
  `realname` varchar(200) NOT NULL default '',
  `email` varchar(200) NOT NULL default '',
  `website` varchar(200) NOT NULL default '',
  `bio` text NOT NULL,
  `image` varchar(200) NOT NULL default '',
  `date`  int(10) unsigned NOT NULL default '0',
  `admincreated` int(11) NOT NULL default '0',
  `password` varchar(40) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `penname` (`penname`),
  KEY `admincreated` (`admincreated`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_blocks`
-- 

CREATE TABLE `fanfiction_blocks` (
  `block_id` int(11) NOT NULL auto_increment,
  `block_name` varchar(30) NOT NULL default '',
  `block_title` varchar(150) NOT NULL default '',
  `block_file` varchar(200) NOT NULL default '',
  `block_status` tinyint(1) NOT NULL default '0',
  `block_variables` text NOT NULL,
  PRIMARY KEY  (`block_id`),
  KEY `block_name` (`block_name`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_categories`
-- 

CREATE TABLE `fanfiction_categories` (
  `catid` int(11) NOT NULL auto_increment,
  `parentcatid` int(11) NOT NULL default '-1',
  `category` varchar(60) NOT NULL default '',
  `description` text NOT NULL,
  `image` varchar(100) NOT NULL default '',
  `locked` tinyint(4) NOT NULL default '0',
  `leveldown` tinyint(4) NOT NULL default '0',
  `displayorder` int(4) NOT NULL default '0',
  `numitems` int(11) NOT NULL default '0',
  PRIMARY KEY  (`catid`),
  KEY `byparent` (`parentcatid`,`displayorder`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_chapters`
-- 

CREATE TABLE `fanfiction_chapters` (
  `chapid` int(11) NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `inorder` int(11) NOT NULL default '0',
  `notes` text NOT NULL,
  `storytext` text NOT NULL,
  `endnotes` text,
  `validated` tinyint(4) NOT NULL default '0',
  `wordcount` int(11) NOT NULL default '0',
  `rating` tinyint(4) NOT NULL default '0',
  `reviews` smallint(6) NOT NULL default '0',
  `sid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  PRIMARY KEY  (`chapid`),
  KEY `sid` (`sid`),
  KEY `uid` (`uid`),
  KEY `inorder` (`inorder`),
  KEY `title` (`title`),
  KEY `validated` (`validated`),
  KEY `forstoryblock` (`sid`,`validated`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_characters`
-- 

CREATE TABLE `fanfiction_characters` (
  `charid` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `charname` varchar(60) NOT NULL default '',
  `bio` text NOT NULL,
  `image` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`charid`),
  KEY `catid` (`catid`),
  KEY `charname` (`charname`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_classes`
-- 

CREATE TABLE `fanfiction_classes` (
  `class_id` int(11) NOT NULL auto_increment,
  `class_type` int(11) NOT NULL default '0',
  `class_name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`class_id`),
  KEY `byname` (`class_type`,`class_name`,`class_id`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_classtypes`
-- 

CREATE TABLE `fanfiction_classtypes` (
  `classtype_id` int(11) NOT NULL auto_increment,
  `classtype_name` varchar(50) NOT NULL default '',
  `classtype_title` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`classtype_id`),
  UNIQUE KEY `classtype_name` (`classtype_name`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_coauthors`
-- 

CREATE TABLE `fanfiction_coauthors` (
  `sid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sid`,`uid`),
) REPLACE=MyISAM;
-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_codeblocks`
-- 

CREATE TABLE `fanfiction_codeblocks` (
  `code_id` int(11) NOT NULL auto_increment,
  `code_text` text NOT NULL,
  `code_type` varchar(20) default NULL,
  `code_module` varchar(60) default NULL,
  PRIMARY KEY  (`code_id`),
  KEY `code_type` (`code_type`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_comments`
-- 

CREATE TABLE `fanfiction_comments` (
  `cid` int(11) NOT NULL auto_increment,
  `nid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `comment` text NOT NULL,
  `time` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cid`),
  KEY `commentlist` (`nid`,`time`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_favorites`
-- 

CREATE TABLE `fanfiction_favorites` (
  `uid` int(11) NOT NULL default '0',
  `item` int(11) NOT NULL default '0',
  `type` char(2) NOT NULL default '',
  `comments` text,
  UNIQUE KEY `byitem` (`item`,`type`,`uid`),
  UNIQUE KEY `byuid` (`uid`,`type`,`item`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_inseries`
-- 

CREATE TABLE `fanfiction_inseries` (
  `seriesid` int(11) NOT NULL default '0',
  `sid` int(11) NOT NULL default '0',
  `subseriesid` int(11) NOT NULL default '0',
  `confirmed` int(11) NOT NULL default '0',
  `inorder` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sid`,`seriesid`),
  KEY `seriesid` (`seriesid`,`inorder`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_log`
-- 

CREATE TABLE `fanfiction_log` (
  `log_id` int(11) NOT NULL auto_increment,
  `log_action` varchar(255) default NULL,
  `log_uid` int(11) NOT NULL default '0',
  `log_ip` int(11) default NULL,
  `log_timestamp` int(10) unsigned NOT NULL default '0',
  `log_type` char(2) NOT NULL default '',
  PRIMARY KEY  (`log_id`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_messages`
-- 

CREATE TABLE `fanfiction_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `message_name` varchar(50) NOT NULL default '',
  `message_title` varchar(200) NOT NULL default '',
  `message_text` text,
  PRIMARY KEY  (`message_id`),
  KEY `message_name` (`message_name`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_modules`
-- 

CREATE TABLE `fanfiction_modules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL default 'Test Module',
  `version` varchar(10) NOT NULL default '1.0',
  PRIMARY KEY  (`id`),
  KEY `name_version` (`name`,`version`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_news`
-- 

CREATE TABLE `fanfiction_news` (
  `nid` int(11) NOT NULL auto_increment,
  `author` varchar(60) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `story` text NOT NULL,
  `time` int(10) unsigned NOT NULL default '0',
  `comments` int(11) NOT NULL default '0',
  PRIMARY KEY  (`nid`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_pagelinks`
-- 

CREATE TABLE `fanfiction_pagelinks` (
  `link_id` int(11) NOT NULL auto_increment,
  `link_name` varchar(50) NOT NULL default '',
  `link_text` varchar(100) NOT NULL default '',
  `link_url` varchar(250) NOT NULL default '',
  `link_target` char(1) NOT NULL default '0',
  `link_access` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`link_id`),
  KEY `link_name` (`link_name`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_panels`
-- 

CREATE TABLE `fanfiction_panels` (
  `panel_id` int(11) NOT NULL auto_increment,
  `panel_name` varchar(50) NOT NULL default 'unknown',
  `panel_title` varchar(100) NOT NULL default 'Unnamed Panel',
  `panel_url` varchar(100) default NULL,
  `panel_level` tinyint(4) NOT NULL default '3',
  `panel_order` tinyint(4) NOT NULL default '0',
  `panel_hidden` tinyint(1) NOT NULL default '0',
  `panel_type` varchar(20) NOT NULL default 'A',
  PRIMARY KEY  (`panel_id`),
  KEY `panel_type` (`panel_type`,`panel_name`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_ratings`
-- 

CREATE TABLE `fanfiction_ratings` (
  `rid` int(11) NOT NULL auto_increment,
  `rating` varchar(60) NOT NULL default '',
  `ratingwarning` int(11) NOT NULL default '0',
  `warningtext` text NOT NULL,
  PRIMARY KEY  (`rid`),
  KEY `rating` (`rating`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_reviews`
-- 

CREATE TABLE `fanfiction_reviews` (
  `reviewid` int(11) NOT NULL auto_increment,
  `item` int(11) NOT NULL default '0',
  `chapid` int(11) NOT NULL default '0',
  `reviewer` varchar(60) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `review` text NOT NULL,
  `date` int(10) unsigned NOT NULL default '0',
  `rating` int(11) NOT NULL default '0',
  `respond` char(1) NOT NULL default '0',
  `type` char(2) NOT NULL default 'ST',
  PRIMARY KEY  (`reviewid`),
  KEY `psid` (`chapid`),
  KEY `rating` (`rating`),
  KEY `respond` (`respond`),
  KEY `avgrating` (`type`,`item`,`rating`),
  KEY `bychapter` (`chapid`,`rating`),
  KEY `byuid` (`uid`,`item`,`type`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_series`
-- 

CREATE TABLE `fanfiction_series` (
  `seriesid` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `summary` text,
  `uid` int(11) NOT NULL default '0',
  `isopen` tinyint(4) NOT NULL default '0',
  `catid` varchar(200) NOT NULL default '0',
  `rating` tinyint(4) NOT NULL default '0',
  `classes` varchar(200) default NULL,
  `characters` varchar(250) NOT NULL default '',
  `reviews` smallint(6) NOT NULL default '0',
  `numstories` int(11) NOT NULL default '0',
  `warnings` varchar(250) NOT NULL default '',
  `challenges` varchar(200) NOT NULL default '',
  `genres` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`seriesid`),
  KEY `catid` (`catid`),
  KEY `owner` (`uid`,`title`)
) REPLACE=MyISAM;

-- --------------------------------------------------------
-- 
-- Table structure for table `fanfiction_stats`
-- 

CREATE TABLE `fanfiction_stats` (
  `sitekey` varchar(50) NOT NULL default '0',
  `stories` int(11) NOT NULL default '0',
  `chapters` int(11) NOT NULL default '0',
  `series` int(11) NOT NULL default '0',
  `reviews` int(11) NOT NULL default '0',
  `wordcount` int(11) NOT NULL default '0',
  `authors` int(11) NOT NULL default '0',
  `members` int(11) NOT NULL default '0',
  `reviewers` int(11) NOT NULL default '0',
  `newestmember` int(11) NOT NULL default '0'
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_stories`
-- 

CREATE TABLE `fanfiction_stories` (
  `sid` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `summary` text,
  `storynotes` text,
  `catid` varchar(100) NOT NULL default '0',
  `classes` varchar(200) default NULL,
  `charid` varchar(250) NOT NULL default '0',
  `rid` varchar(25) NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `updated` int(10) unsigned NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `coauthors` varchar(200) default NULL,
  `featured` tinyint(4) NOT NULL default '0',
  `validated` tinyint(4) NOT NULL default '0',
  `completed` tinyint(4) NOT NULL default '0',
  `rr` tinyint(4) NOT NULL default '0',
  `wordcount` int(11) NOT NULL default '0',
  `rating` tinyint(4) NOT NULL default '0',
  `reviews` smallint(6) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `challenges` varchar(200) NOT NULL default '0',
  PRIMARY KEY  (`sid`),
  KEY `title` (`title`),
  KEY `catid` (`catid`),
  KEY `charid` (`charid`),
  KEY `rid` (`rid`),
  KEY `uid` (`uid`),
  KEY `featured` (`featured`),
  KEY `completed` (`completed`),
  KEY `rr` (`rr`),
  KEY `challenges` (`challenges`),
  KEY `validateduid` (`validated`,`uid`),
  KEY `recent` (`updated`,`validated`)
) REPLACE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `fanfiction_settings`
-- 

CREATE TABLE `fanfiction_settings` (
  `sitekey` varchar(50) NOT NULL default '1',
  `sitename` varchar(200) NOT NULL default 'Your Site',
  `slogan` varchar(200) NOT NULL default 'It''s a cool site!',
  `url` varchar(200) NOT NULL default 'http://www.yoursite.com',
  `siteemail` varchar(200) NOT NULL default 'you@yoursite.com',
  `tableprefix` varchar(50) NOT NULL default '',
  `skin` varchar(50) NOT NULL default 'default',
  `hiddenskins` varchar(255) default '',
  `language` varchar(10) NOT NULL default 'en',
  `submissionsoff` tinyint(1) NOT NULL default '0',
  `storiespath` varchar(20) NOT NULL default 'stories',
  `store` varchar(5) NOT NULL default 'files',
  `autovalidate` tinyint(1) NOT NULL default '0',
  `coauthallowed` int(1) NOT NULL default '0',
  `maxwords` int(11) NOT NULL default '0',
  `minwords` int(11) NOT NULL default '0',
  `imageupload` tinyint(1) NOT NULL default '0',
  `imageheight` int(11) NOT NULL default '200',
  `imagewidth` int(11) NOT NULL default '200',
  `roundrobins` tinyint(1) NOT NULL default '0',
  `allowseries` tinyint(4) NOT NULL default '2',
  `tinyMCE` tinyint(1) NOT NULL default '0',
  `allowed_tags` varchar(200) NOT NULL default '<b><i><u><center><hr><p><br /><br><blockquote><ol><ul><li><img><strong><em>',
  `favorites` tinyint(1) NOT NULL default '0',
  `multiplecats` tinyint(1) NOT NULL default '0',
  `newscomments` tinyint(1) NOT NULL default '0',
  `logging` tinyint(1) NOT NULL default '0',
  `maintenance` tinyint(1) NOT NULL default '0',
  `debug` tinyint(1) NOT NULL default '0',
  `captcha` tinyint(1) NOT NULL default '0',
  `dateformat` varchar(20) NOT NULL default 'd/m/y',
  `timeformat` varchar(20) NOT NULL default '- h:i a',
  `recentdays` tinyint(2) NOT NULL default '7',
  `displaycolumns` tinyint(1) NOT NULL default '1',
  `itemsperpage` tinyint(2) NOT NULL default '25',
  `extendcats` tinyint(1) NOT NULL default '0',
  `displayindex` tinyint(1) NOT NULL default '0',
  `defaultsort` tinyint(1) NOT NULL default '0',
  `displayprofile` tinyint(1) NOT NULL default '0',
  `linkstyle` tinyint(1) NOT NULL default '0',
  `linkrange` tinyint(2) NOT NULL default '5',
  `reviewsallowed` tinyint(1) NOT NULL default '0',
  `ratings` tinyint(1) NOT NULL default '0',
  `anonreviews` tinyint(1) NOT NULL default '0',
  `revdelete` tinyint(1) NOT NULL default '0',
  `rateonly` tinyint(1) NOT NULL default '0',
  `pwdsetting` tinyint(1) NOT NULL default '0',
  `alertson` tinyint(1) NOT NULL default '0',
  `disablepopups` tinyint(1) NOT NULL default '0',
  `agestatement` tinyint(1) NOT NULL default '0',
  `words` text,
  `version` varchar(10) NOT NULL default '3.0',
  `smtp_host` varchar(200) default NULL,
  `smtp_username` varchar(50) default NULL,
  `smtp_password` varchar(50) default NULL,
  `anonchallenges` tinyint(1) NOT NULL default '0',
  `anonrecs` tinyint(1) NOT NULL default '0',
  `rectarget` tinyint(1) NOT NULL default '0',
  `autovalrecs` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`sitekey`)
) REPLACE=MyISAM;