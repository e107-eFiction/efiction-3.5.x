<?php
include(_BASEDIR."version.php");

$defaulttitles = array("welcome" => "Welcome", "copyright" => "Copyright Footer", "printercopyright" => "Printer Friendly Copyright", 
"help" => "Help", "rules" => "{sitename} Submission Rules", "thankyou" => "{sitename} Acceptance Letter", "nothankyou" => "Rejection Letter", 
"tos" => "Terms of Service");

define("_ALTER11CHARACTERS", "Altering fanfiction_characters table");
define("_ALTER11CATEGORIES", "Altering fanfiction_categories table");
define("_ALTER11RATINGS", "Altering ratings table.");
define("_DONE", "Done!");
define("_ERROR_CONFIGWRITE", "ERROR! Could not open config.php for writing.");
define("_CONFIG_WRITTEN", "Configuration file written.");
define("_MANUAL2", "manual");
define("_ADMINACCT", "Set-up Admin Account");
define("_AUTO", "automatic");
define("_PAGE", "Page");
define("_BLOCK", "Block");
define("_MESSAGE", "Message");
define("_RESULT", "Result");
define("_FIELD", "Field");
define("_AUTHORFIELDS", "Install Author Profile Fields");
define("_FIELDDATAINFO", "This step installs the default field options for the profile pages.  You can add, edit, or delete fields from the admin panel.");
define("_FIELDUPDATE", "If you have already added additional fields other than the ones listed above to your site, you may wish to manually add those fields to the database in the fanfiction_authorfields table before continuing.  The next step will move the data from the authors table to the new format.  Also make sure your name for the field and the name given by the update script match.");
define("_FIELDMANUAL", "Manually install the default field data provided in authorfields.sql in the docs/ folder.");
define("_FIELDAUTOFAIL", "If a field failed to install, please manually install it using the information in authorfields.sql in the docs/ folder now.");
define("_AUTHORUPDATE", "Populate Author Preferences Table");
define("_AUTHORUPDATEINFO", "This step populates the fanfiction_authorprefs and fanfiction_authorinfo tables with information from the authors table.");
define("_AUTHORRESULT", "Author preferences moved.");
define("_AUTHORDROPRESULT", "Columns dropped from authors table.");
define("_BLOCKDATA", "Install Block Data");
define("_BLOCKDATAUPGRADE", "This step will move your block data from 2.0 into the database. You may change these settings at any time 
			using the Block admin interface in your admin area.");
define("_BLOCKDATANEW", "This step will install the default block data. You may change these settings at any time using the Block admin interface in your admin area.");
define("_BLOCKDATAFAILUPGRADE", "If a block failed to install, please use the default data for blocks provided in the information in blocks.sql file in the docs/ folder to install it manually now.");
define("_BLOCKDATAMANUAL", "Manually install the default data for the blocks provided in the information in tables.sql found in the docs/ folder now.");
define("_CHALLENGESALTER", "Added column 'responses' to stories table.");
define("_CHALLENGESETTING", "Added column 'anonchallenges' to settings table.");
define("_CHALLENGEUPDATE", "Update Challenge Data");
define("_CHALLENGEUPDATEINFO", "This step updates the challenges table to add a new column for responses, and updates the data to include a count of responses and switches the character info to the charid values.");
define("_CHALLENGESEMPTY", "No challenges found.  The new challenges module will not be installed.  If you wish to use challenges in the future, install the module using the install.php file in the modules/challenges/ folder.");
define("_CONFIG1DETECTED", "eFiction 1.1 config file detected.<br /><a href='upgrade11.php'>Continue with upgrade.</a>");
define("_CONFIG2DETECTED", "eFiction 2.0 config file detected.<br /><a href='upgrade20.php'>Continue with upgrade.</a>");
define("_CONFIGFAILED", "Cannot connect to the database.  The information you supplied appears to be incorrect.  Please try again.");
define("_CONFIGDATA", "Configuration File Setup");
define("_CONFIGSUCCESS", "Configuration file written.");
define("_DBHOST", "Database Host:");
define("_DBNAME", "Database Name:");
define("_DBUSER", "Database User:");
define("_DBPASS", "Database Password:");
define("_DROPGENRES", "Drop genres table");
define("_DROPWARNINGS", "Drop warnings table");
define("_DROPGWSTORIES", "Dropped 'wid' and 'gid' from stories table.");
define("_DROPGWSERIES", "Dropped 'wid' and 'gid' from series table.");
define("_FAVUPDATE", "Update Favorites Table");
define("_FAVUPDATEINFO", "This step moves the favorites information into the new favorites table and removes the old favstor, favseries, and favauth tables.");
define("_FAVUPDATEINFO11", "This step moves the favorites information into the new favorites table and removes the old favstor and favauth tables.");
define("_FAV1", "Favorite stories information moved.");
define("_FAV2", "Favorite series information moved.");
define("_FAV3", "Favorite authors information moved.");
define("_FAV4", "Table favstor dropped.");
define("_FAV5", "Table favseries dropped.");
define("_FAV6", "Table favauth dropped.");
define("_INSTALLTABLES", "Install Tables");
define("_LINKDATA", "Install Navigation Link Data");
define("_LINKDATAINFO", "This step will install the default navigation link data. You may change these settings at any time 
			using the Page Links admin interface in your admin area.");
define("_LINKMANUAL", "Manually install the default data for the navigation links provided in the information in pagelinks.sql found in the docs/ folder now.");
define("_LINKSETUP", "How do you want to install the page links data?");
define("_LINKAUTOFAIL", "If a link failed to install, please use the default data for pagelinks provided in the information in pagelinks.sql file in the docs/ folder to install it manually now.");
define("_MESSAGEDATA", "Message Data");
define("_MESSAGEMANUAL", "Manually install the messages from the messages.sql file found in the docs/ folder now.");
define("_MESSAGEDATAUPGRADE", "This step will move the default messages for your site into the database. You may change these texts at any time using the Settings admin interface in your admin area.");
define("_MESSAGEDATANEW", "This step will install the default messages for your site. You may change these texts at any time using the Settings admin interface in your admin area.");
define("_MESSAGEAUTOFAILUPGRADE", "If one of the default messages failed to install, you may use the default data for messages provided in the information in messages.sql file in the docs/ folder to install it manually now or add it through the admin panel later.");
define("_MISCDBUPDATE", "Misc. Database Updates");
define("_MISCDBUPDATEINFO", "This step will alter fields in several tables to keep the database smaller.  How do you wish to proceed?");
define("_MISCDBUPDATEMANUAL", "You may manually alter your database now using the information in docs/upgrade11_step18.sql.");
define("_MOVECLASSES", "Move Genres and Warnings");
define("_MOVECLASSESINFO", "This step moves your genres and warnings information into the the new classes tables.");
define("_MOVECLASSFAIL", "If any of the genres or warnings failed to be moved, manually insert them now.");
define("_NEWSUPDATE", "Update News Comments");
define("_NEWSUPDATEINFO", "This step change the uname field in the comments table to be uid and populates it with the uid of the person rather than penname.");
define("_NEWSUPDATERESULT", "News comments updated.");
define("_NEWTABLESSETUP", "How do you want to install the new tables?");
define("_NEWTABLESMANUAL", "Manually install the tables from the upgrade20_step4.sql file found in the docs/ folder now.");
define("_NEWTABLEAUTOFAIL", "If a table failed to install, please use the upgrade20_step4.sql file in the docs/ folder to install it manually now.");
define("_NEWTABLEAUTOFAIL11", "If a table failed to install, please use the upgrade11_step4.sql file in the docs/ folder to install it manually now.");
define("_OPTIMIZEDB", "Optimize Database");
define("_OPTIMIZEINFO", "This step will optimize the database through the removal of some indexes on the tables and the addition of a number of new ones. How do you want to optimize the database?");
define("_OPTIMIZEMANUAL", "Manually optimize the database now using the optimize.sql found in the docs/ folder now.");

define("_PANELDATA", "Install Panel Data");
define("_PANELDATAINFO", "This step will install the default configuration of panels for the admin area, user account area, top 10 lists
			 and user profile page.	You may change these settings at any time using the panels admin interface in your admin area.");
define("_PANELSETUP", "How do you want to install the panel data?");
define("_PANELMANUAL", "Manually install the default data for panels provided in the information in panels.sql found in the docs/ folder now.");
define("_PANELAUTOFAIL", "If a panel failed to install, please use the default data for panels provided in the information in panels.sql file in the docs/ folder to install it manually now.");
define("_REVIEWUPDATE", "Update Reviews Table and Data");
define("_REVIEWALTER1", "Column 'sid' changed to 'item'");
define("_REVIEWALTER2", "Added column 'type'");
define("_REVIEWALTER3", "Column 'member' changed to 'uid'");
define("_REVIEWALTER4", "Drop column 'seriesid'");
define("_REVIEWALTER5", "Add column 'respond'");
define("_REVIEWALTER6", "Column 'sid' changed to 'chapid'");
define("_REVIEWALTER7", "Column 'psid' changed to 'item'");
define("_REVIEWUPDATE1", "Set type to 'ST' for all story reviews.");
define("_REVIEWUPDATE2", "Set item to seriesid and type to 'SE' for all series reviews.");
define("_REVIEWUPDATE3", "Mark all reviews with author's responses as responded to.");
define("_REVIEWUPDATEINFO", "This step will alter the reviews table then convert all reviews in the table to the new format.");
define("_SERIESREVIEWS", "Update Series Review");
define("_SERIESREVIEWSINFO", "This step updates the series reviews and ratings to include the reviews and ratings of included stories and series.");
define("_SETTINGSPREFIX", "Settings Table Prefix:");
define("_SETTINGSTABLE", "Create Settings Table"); // Added 01/15/07
define("_SETTINGSTABLEMANUAL", "Manually install the settings table from the tables.sql file found in the docs/ folder now.");
define("_SETTINGSTABLEMANUALUP", "Manually install the settings table from the settingstable.sql file found in the docs/ folder now.");
define("_SETTINGSTABLENOTE", "You may set a prefix for your settings table different from the other tables in your eFiction install.  This will allow you to share the settings table between multiple installs of eFiction in the same database.");
define("_SETTINGSTABLESETUP", "How do you want to install the settings table?");
define("_SETTINGSTABLESUCCESS", "Settings table created successfully!");
define("_SETTINGSTABLEAUTOFAIL", "Automatic creation of settings table failed!  Please create this table manually.");
define("_SITESETTINGSMOVED", "Site settings have been moved to settings table.");
define("_SITEKEYNOTE", "You may use the randomly generated key or choose one of your own.");
define("_SKINWARNING", "<strong>Note:</strong>  eFiction ".$version." will require you to make some updates to your skins.  You are <strong>highly</strong> encouraged to choose one of the skins included in the download as default until you can upgrade your skins.");
define("_STORIESPATHNOTWRITABLE", "The folder in which you wish to store your stories may not be writable!  You must CHMOD this folder to 777 (on some systems 755) to be able to write stories to the folder!");
define("_STORIESUPDATED", "The stories have been updated.");
define("_TABLESMANUAL", "Manually install the tables found in the docs/ folder now.");
define("_TABLESINSTALL", "Install the database tables.");
define("_TABLEFAILED", "If a table failed to install, please use the tables.sql file in the docs/ folder to install it manually now.");
define("_UPDATECATORDER", "Update Categories Order");
define("_UPDATECATORDERINFO", "<strong>Note:</strong> This will <u>NOT</u> affect how your categories are displayed on screen, merely how that information is stored in the database.");
define("_UPDATESTORIES", "Update Story and Series Information");
define("_UPDATESTORIES11", "Update Stories");
define("_UPDATESTORIES11INFO", "This step will update the data for your stories and alter some of the fields in the table.  This will be done in batches of 200 with the database alterations done at the end.");
define("_UPDATESTORIESINFO", "This step will update the data for your stories converting the names of genres, warnings, characters, and ratings to id numbers.  This will be done in batches of 200 stories.");
define("_UPDATESTORIESTABLE", "Update Stories and Series Tables");
define("_UPDATESTORIESTABLEINFO", "This step will create the classes column in your stories and series tables. How do you want to proceed?");
define("_UPDATESTORIESTABLEMANUAL", "Manually update your stories table from the upgrade20_step10.sql file in the docs/ folder now.");
define("_UPDATESTORIESTABLE11", "Update Stories Table");
define("_UPDATESTORIESTABLEINFO11", "This step will modify the stories table to conform to eFiction ".$version.".  How do you want to proceed?");
define("UPDATESTORIESTABLEMANUAL11", "Manually update your stories table from the upgrade11_step12.sql file in the docs/ folder now.");
define("_UPGRADEEND","You may now delete the following files and directories:<br />
<ul style='text-align: left !important; width: 60%; margin: 1em auto;'>
<li>messages/ folder</li>
<li>your databasepath folder and dbconfig.php file</li>
<li>blocks_config.php</li>
<li>categories.php (handled through browse.php)</li>
<li>formselect.js</li>
<li>func.naughty.php</li>
<li>func.pagemenu.php</li>
<li>func.ratingpics.php</li>
<li>functions.php</li>
<li>func.reviewform.php</li>
<li>help.php (saved in the database now)</li>
<li>javascript.js (A new version has been placed in the includes/ folder)</li>
<li>lib/ folder</li>
<li>members_list.php (A new version has been placed in the includes/ folder)</li>
<li>naughtywords.php</li>
<li>seriesblock.php (A new version has been placed in the includes/ folder)</li>
<li>storyblock.php (A new version has been placed in the includes/ folder)</li>
<li>submission.php (saved in the database now)</li>
<li>timefunctions.php</li>
<li>titles.php (handled through browse.php)</li>
<li>tos.php (saved in the database now)</li>
<li><strong>install/ folder</strong></li>
</ul>
<p>You are also encouraged to CHMOD config.php to 644 as a security measure.</p>
<p><a href='../index.php'>Return to your Site.</a></p");

define("_UPGRADE11END", "You may now delete the following files and directories:<br />
<ul style='text-align: left !important; width: 60%; margin: 1em auto;'>
<li>adminfunctions.php</li>
<li>adminheader.php</li>
<li>adminnews.php</li>
<li>adminstories.php</li>
<li>adminusers.php</li>
<li>blocks.php</li>
<li>categories.php (handled through browse.php)</li>
<li>functions.php</li>
<li>help.php (saved in the database now)</li>
<li>javascript.js (A new version has been placed in the includes/ folder)</li>
<li>langadmin.php</li>
<li>languser.php</li>
<li>lib/ folder</li>
<li>phpinfo.php <strong>(Poses a security risk!)</strong></li>
<li>storyblock.php (A new version has been placed in the includes/ folder)</li>
<li>timefunctions.php</li>
<li>titles.php (handled through browse.php)</li>
<li>install/ folder<strong>(Poses a security risk!)</strong></li>
<li>your databasepath folder and dbconfig.php file</li>
</ul><p><a href='../index.php'>Return to your Site.</a></p>
<p>You are also encouraged to CHMOD config.php to 644 as a security measure.</p>
<p><a href='../index.php'>Return to your Site.</a></p>");
define("_HELP_DBHOST", "The host server for your mySQL database.  Generally 'localhost' is the most likely setting unless your web host has informed you differently.");
define("_HELP_DBNAME", "This should be the name of your database.  You should have already created the database before starting this install.  You will most likely create your database through your web host's control panel.");
define("_HELP_DBUSER", "This is the user you (or your webhost) assigned to the database when it was created.");
define("_HELP_DBPASS", "This is the password for the database user.");
define("_HELP_INSTALL_SITEKEY", "The sitekey will be used to access the settings for your site.  It is also used to prevent crossed member logins when two or more sites are installed on the same domain.  You <strong>must</strong> have a <strong>unique</strong> sitekey for each eFiction site!  If you leave this blank, the script will randomly generate another one for you.");
define("_HELP_SETTINGSPREFIX", "This allows you to define a separate prefix for the settings table.  This allows you to share the settings table among more than one eFiction site in the same database.");

//  Missing LANs - fatal error  
define("_EMAILREQUIRED", "Email is required");
define("_BADSITEKEY", "Wrong Sitekey");
