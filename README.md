# efiction v3.5.9.1

Fixes ported from official eFiction 3.5.79-3.5.84. No database change: update.php only records the new version.

- search: %, _ and ! in a search term are matched literally ('%%%' no longer lists every story)
- full-text search lists only validated stories and chapters, each story once, with correct totals; blank or short terms are refused
- full-text and pen-name searches apply the other filters; a story with several matching co-authors is listed once
- the related-series link keeps the search mode and filters; malformed search modes and classification lists are refused
- includes/categorylist.php: element name validated, every value JSON-encoded, no PHP notices in the JavaScript reply
- preferences: switching back to the site's default skin also changes the skin of the current session
- block settings that are empty or not an array count as empty settings


# efiction v3.5.9

Security release. Fixes ported from official eFiction 3.5.7 and 3.5.73-3.5.78,
plus fixes of this line. Database changes: the update step 3.5.8 -> 3.5.9 widens
authors.password to varchar(255), adds smtp_port and smtp_secure to the settings
table, repairs doubled Site URL values and cleans HTML already stored in reviews,
news comments and challenges; the earlier step to 3.5.8 converts IP columns to
varbinary(16) with proper IPv4 data conversion. Each step is re-run automatically
if any of its columns is missing or still has the old type.

Passwords and accounts
- passwords are stored with bcrypt (password_hash); existing MD5 hashes are
  accepted and upgraded on the member's next login; after that login,
  remember-me cookies saved in the member's other browsers stop working and the
  member logs in once more there
- accounts created by an administrator cannot log in, be used with a forged
  cookie, or request a password by e-mail before the administrator releases them
- login: the remember-me cookie value must be numeric (SQL injection)

Authorization (a signed-in member could change things belonging to others)
- editbio: a member could change another member's e-mail and password through
  the uid parameter (account takeover)
- reviews: anyone could rewrite any review or any news comment; author
  responses now check story ownership
- series: edit, delete, reorder, approve and add-to-series check the owner
- stories: round-robin edits, chapter reorder and complete/in-progress toggles
  check the owner; favourites comments can only be changed by their owner
- challenges: only the owner and administrators can edit or delete a challenge
- poll block: no votes by guests, in closed polls, or for non-existent options

SQL injection
- browse.php (rating, word count, character and class lists, search term),
  browse.php?type= file include, series.php, user.php editbio uid, admin.php
  ids, shoutbox guest name (could read password hashes), challenges search

XSS
- descript() rewritten: every tag is rebuilt from its attributes; all on*
  handlers, javascript:/vbscript:/data: links and dangerous style values are
  removed; news comments are cleaned on save; stored content is cleaned by the
  update step
- includes/userlist.php and includes/characterlist.php: values JSON-encoded,
  element name validated
- search, browse, skin switcher, poll and shoutbox no longer repeat the query
  string unencoded; custom profile fields and member links are escaped
  (javascript: links refused)

Other
- stripslashes() and related string functions guarded against null arguments
  (PHP 8.1 deprecation), escapestring() tolerates null
- update.php: ALTER errors while converting the IP columns are reported instead
  of silently ignored
- installer: refuses to run on an installed site (HTTP 403), uses random_int(),
  escapes config.php values, new standalone stylesheet and page template, SMTP
  test hidden during installation, efiction.org footer link removed
- PHP 8 fixes from official 3.5.73-3.5.77 (missing chapter files, series
  counts, browse and search edge cases, Site URL saved as http://https://)


# efiction v3.5.8 fixes

- rating display fix


# efiction v3.5.8

- added notification module
- changed files: user/editbio.php and user/lostpassword.php 
- chanded way for loading DB functions - moved from config file! 
- fixed bug in install.php - script worked but in PHP 8.x it caused warnings
- new ranking system - number of likes, used as {ratingpics} likes in tpl.


# efiction v3.5.7

minimum requirements:  MySQL 5.6.3  or MariaDB 10.5.0 


### Database changes:

- all IP adress fields - from integer to 46 chars

### Other changes:

- tinymce4 settings fix
- INET6 support needed
- file uploading fixed for PHP 8.1
- national characters fix in search form 



# efiction v3.5.6

non official version of 3.5.x efiction software

## Only for testing purposes


### Database changes:

- all date fields from datestamp format to unix time format

### File's changes

- related date field type changes
- found issues fixed
- deleted bridges 

### Looking for missing modules
If you have source code of missing and used modules, share it and I can try to add and fix them


### Installation
No change

### Update
Classic as before, run update process that should change your date fields without lossing your data. It works only for update from 3.5.5, not sooner.



<code>
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

see more in README.txt file
</code>
