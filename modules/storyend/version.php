<?php
/*
This file will be called by admin/modules.php and update.php to determine if 
the module version in the database is the current version of the module.  
The version number in this file will be the current version.
*/

if(!defined("_CHARSET")) exit( );

$moduleVersion = "1.3";
$moduleName = "Story End";

$moduleDescription = "This module adds information to the end of the story. It will display either \"The End.\" or \"The End...Maybe?\" depending on whether or not the story is marked as complete. Beneath that it will also display information about the number of other stories the author has written and the number of people who have this story as one of their favorites. It also links to a list of stories those people also liked.  If the story is part of a series, that information will also be displayed along with links to the previous and next stories in the series.";
$moduleAuthor = "Tammy Keefer";
$moduleAuthorEmail = "efiction@hugosnebula.com";
$moduleWebsite = "http://efiction.hugosnebula.com";

?>