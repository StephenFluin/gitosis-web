<?php

require("functions.inc.php");


if($_POST["path"]) {
	$_SESSION["gitosisurl"] = $_POST["path"];
}


if($_SESSION["gitosisurl"]) {
	// Iniital version will require a locally checked out version of gitosis-admin belonging to www-data. This means the user will have to setup gitosis, add a key for www-data before being able to use this web tool.
	$dir = $_SESSION["gitosisurl"];
	if(file_exists($dir)) {
		$body .= "Found the path.  ";
	} else {
		$body .= "Didn't find the path.  ";
	}

	if(file_exists($dir . ".git/config") && file_exists($dir . "gitosis.conf")) {
		$body .= "Valid gitosis-admin repo found.  ";
	}
	$body .= '<a href="?action=logout">Log Out</a>';

} else {
	$body .= "Please enter the path on the server for the checked out version of gitosis-admin:";
	$body .= '<form method="post"><input type="text" name="path"/><button type="submit">Setup</button>	</form>';
}

print $body;

