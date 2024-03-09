<?php

// Default query strings used throughout the script.  You may need to alter these to bridge to other scripts or databases.

define ("_AUTHORPREFIX", defined("TABLEPREFIX") ? TABLEPREFIX : $tableprefix);

define ("_UIDFIELD", "author.uid");  // Do not change the aliasing (the "author." part)!
define ("_PENNAMEFIELD", "author.penname");  // Do not change the aliasing (the "author." part)!
define ("_EMAILFIELD", "author.email");  // Do not change the aliasing (the "author." part)!
define ("_PASSWORDFIELD", "author.password"); //  Do not change the aliasing (the "author." part)!
define ("_AUTHORTABLE", _AUTHORPREFIX."fanfiction_authors as author"); // Do not change the aliasing (the "as author" part)!

define ("_STORYQUERY",  "SELECT stories.*, "._PENNAMEFIELD." as penname, stories.date as date, stories.updated as updated FROM ("._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_stories as stories) WHERE "._UIDFIELD." = stories.uid AND stories.validated > 0 ");
define ("_STORYCOUNT", "SELECT count(sid) FROM ".TABLEPREFIX."fanfiction_stories as stories WHERE validated > 0");
define ("_SERIESQUERY", "SELECT series.*, "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE.", ".TABLEPREFIX."fanfiction_series as series WHERE "._UIDFIELD." = series.uid ");
define ("_SERIESCOUNT", "SELECT COUNT(seriesid) FROM ".TABLEPREFIX."fanfiction_series as series ");
define ("_MEMBERLIST", "SELECT "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid, ap.stories FROM "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs AS ap ON "._UIDFIELD." = ap.uid");
define ("_MEMBERCOUNT", "SELECT COUNT("._UIDFIELD.") FROM "._AUTHORTABLE." LEFT JOIN ".TABLEPREFIX."fanfiction_authorprefs AS ap ON ap.uid = "._UIDFIELD);
?>