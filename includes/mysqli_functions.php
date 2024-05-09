<?php

// Some DB functions 
if(!function_exists("dbconnect")) { // just in case.  Some people seemed to be having an issue and this was the easiest fix.

function dbconnect($dbhost, $dbuser, $dbpass, $dbname ) {
	$mysqli_access = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
 
	try
	{
		$mysqli_access = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	}
	catch (mysqli_sql_exception $e)
	{
		if (mysqli_connect_error())
		{
			include(_BASEDIR . "languages/en.php"); // Because we haven't got a language set yet.
			die(_FATALERROR . " " . _NOTCONNECTED);
		}
	}
	//mysqli_query($mysql_access, "SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION'");
	mysqli_query($mysqli_access, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
    mysqli_query($mysqli_access, "SET NAMES UTF8;");
	return $mysqli_access;
}


function dbquery($query) {
	global $debug, $headerSent, $dbconnect;
	if($debug && $headerSent) echo "<!-- $query -->\n";
	$result = NULL;
	try
	{
			$result = $dbconnect->query($query);
	}
	catch (mysqli_sql_exception $e)
	{
		if(function_exists('accessDenied')) {
			accessDenied(_FATALERROR . (isADMIN ? "Query: " . $query . "<br />Error: (" . $dbconnect->errno . ") " . $dbconnect->error : ""));
		}
		else {		
			// this is very rare case. Uncomment this if you really lost what is doing. This is before pagesetup.php, so Admin is not set
			//	echo "Query: " . $query . "<br />Error: (" . $dbconnect->errno . ") " . $dbconnect->error  <br /> ;
			die(" <b>A fatal MySQL error was encountered.</b><br /> Check tableprefix. ");	
		}
			
	} 
	return $result;
}

function dbnumrows($query) {
	global $debug, $dbconnect;
	if ($query === false && $debug) {
		echo "<!-- dbnumrows ".$dbconnect->error." -->\n";
	}
	return $query->num_rows;
}

function dbassoc($query) {
	global $debug, $dbconnect;
	if ($query === false && $debug) {
		echo "<!-- dbassoc ".$dbconnect->errno." -->\n";
	}
	return $query->fetch_assoc();
}

function dbinsertid($tablename = 0) {
	global $dbconnect;
	return $dbconnect->insert_id;
}

function dbrow($query) {
	global $debug, $dbconnect;
	if ($query === false && $debug) {
		if($dbconnect->error) echo "<!-- dbrow ".$dbconnect->errno." -->\n";
	}
	return $query->fetch_row();
}

function dbclose() {
	global $dbconnect;
	$dbconnect->close();
}

// Used to escape text being put into the database.
function escapestring($str) {
	global $dbconnect;
   if (!is_array($str)) return $dbconnect->real_escape_string($str);
   return array_map('escapestring', $str);
}

// End DB functions

}
?>
