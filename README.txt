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


There are notes in the docs/ folder about the changes to skins in this version.
However, there is no tutorial as of yet.  

It's also strongly suggested that you NOT put the site into maintenance mode or 
start modifying the script (even by adding modules or blocks) until you've 
actually confirmed that the install was successful.


Upgrading from v.1.1:

BACK UP YOUR FILES AND YOUR DATABASE FIRST!!  Upload the 3.0 files OVER your 1.1
files.  DO NOT DELETE YOUR 1.1 FILES UNTIL THE UPGRADE IS COMPLETE!  Go to your site.  
The script will detect that the config.php file is for 1.1 and start the upgrade.  

Note: You're encouraged to choose one of the skins provided with the 3.0 
script until you can update your 1.1 skins.  There are numerous changes in the
skins from 1.1 to 3.0.  

Note 2: Make sure your config.php file is CHMOD'd to 666.

Upgrading from v.2.0.X:

BACK UP YOUR FILES AND YOUR DATABASE FIRST!!  Upload the 3.0 files OVER your 2.0
files.  DO NOT DELETE YOUR 2.0 FILES UNTIL THE UPGRADE IS COMPLETE!  Go to your site.  
The script will detect that the config.php file is for 2.0 and start the upgrade.  

Note: You're encouraged to choose one of the skins provided with the 3.0 
script until you can update your 2.0 skins.

Note 2: Make sure your config.php file is CHMOD'd to 666.

Note 3: If you have installed blocks that did not come with the 2.0 package, 
turn them off before starting the upgrade.  They will most likely be
incompatible!

Minor Upgrade from v.3.X to v.3.Y:

BACK UP YOUR FILES AND YOUR DATABASE FIRST!!  Upload the 3.Y files OVER your 3.X
files making sure you do not over-write your config.php file.  Generally, the images,
tinymce, and skins folders will contain no updates and do not need to be replaced.
Go to your eFiction site.  The script should detect the updated files and ask you 
to update.  If it doesn't go to http://www.yoursite.com/update.php. 

NOTE: If you have your site bridged, remember to put the bridge back in place 
before running the update. 

Brand New Install:

If you know basic web stuff like CHMODing, look below at step #1. If you have
no clue what you're doing, just go to http://www.yoursite.com/ after uploading 
all the files to your server.

1) If you intend to write the story texts to the server or allow image uploads, you
must create a folder where these files will be stored. CHMOD this folder to 777.  
During installation, the script will ask you for the location of this folder.  
If this folder is missing the script will attempt to create it for you during the 
install, but it is best if you create the folder yourself before beginning the
installation script.  

2) Move config.php from the docs folder to the main eFiction folder.  This file was
placed in the docs folder to help prevent accidental overwriting of config.php
for upgrades. If the config.php is missing the install script will attempt to 
create it, but it's best if you move it.

3) CHMOD config.php to 666.  

4) Go to http://www.yoursite.com/ to run the install and set up the admin login.

5) Login with the admin login and password set in step #4, and complete the
configuration of your site through the Admin panel.

Other info:

1) As written, the script is intended to be used in the following way:

- Categories: you can have only one category, or as many as you want, including 
sub-categories.  Categories were written with the intention that they would 
indicate different fandoms or categories of story.
- Characters: Intended to mean the characters that fall within that fandom or 
category.
- Classifications: Beyond categories and characters you can set up your own 
classifications such as genres, warnings, spoilers, story type, couple, etc.  
- Series: Series are collections of related stories by the same or multiple 
authors.  A story and its sequels, for example, would be a series.  Series 
might also be used for a "shared universe" in which multiple authors write.  
The person who creates the series is considered the series "owner" and
controls whether or not the series is "open" to contributions by other 
authors or not.  Even if the series is "closed", the series owner has the 
option to include stories from other authors in the series.  
- Challenges: Challenges are ideas/wishes for stories that a member or an 
anonymous visitor "challenges" authors to write.  Stories written in response 
to the challenge can be listed under the challenge by the story author or an admin.  
Note: in 3.0 challenges have been made into an optional module.  To install 
challenges goto http://www.yoursite.com/modules/challenges/install.php.

Check out the Admin area for more information on all of these settings and more.

2) You can modify most of the text that is displayed in the script by changing the 
language file in the languages/ folder for the language you selected. Just change 
the text within the quotes for each line to whatever you want it to say.  Some 
wording is included within the skin's files.  If you have created a translation for 
eFiction, we encourage you to share it!

Original Developer:
Rebecca Smallwood (Rivka)
http://orodruin.sourceforge.net

2.0 Developer: Tammy Keefer

Released under the GPL.


Credits:

Fanfiction Script: Rebecca Smallwood (Rivka)
TemplatePower Templating System: R.P.J. Velzeboer, http://templatepower.codocad.com/

A Big Thank You to my original Beta-testers: Theresa Sanchez, Khuffie, Mona Carol-Kaufman, 
Michele Bumbarger, Stephanie Smith, eFanfiction, Amy Cheng, arakune, Peganino, Ceit, 
brihana25, Annabelle Crane

Big thanks also to the 2.0 beta-testers: babaca, Jayelle, Carrie, Nicole, eyedam, 
Dreamhowler, Roberto-san, Calic0cat, West4me, emmekappa, Osfer, Sarit, Silvermoon, 
Carissa, Jan_AQ, Lazuli

Also thanks for our 3.0 Beta testers who were too many to name.