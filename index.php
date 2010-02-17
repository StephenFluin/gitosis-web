<?php

require("functions.inc.php");

if($_SESSION["gitosisurl"]) {
	// Iniital version will require a locally checked out version of gitosis-admin belonging to www-data. This means the user will have to setup gitosis, add a key for www-data before being able to use this web tool.


} else {
	$body .= "Please enter the path on the server for the checked out version of gitosis-admin:";
	$body .= "<form method="post"><input type="text" name="path"/><button type="submit">Setup</button>	</form>";
}

print $body;

