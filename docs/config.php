<?php 
$dbhost = "localhost";
$dbname = "dbname";
$dbuser= "dbuser";
$dbpass = "dbpass";
$sitekey = "";
$settingsprefix = "settings";

include_once("includes/dbfunctions.php");
if(!empty($sitekey)) $dbconnect = dbconnect($dbhost, $dbuser,$dbpass, $dbname);

?>