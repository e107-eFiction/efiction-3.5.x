<?php

if(function_exists("mysqli_connect")) include_once(_BASEDIR."includes/mysqli_functions.php");
else {
	include(_BASEDIR."languages/en.php"); // Because we haven't selected a language setting yet
	die(_FATALERROR._NODBFUNCTIONALITY);
}
 