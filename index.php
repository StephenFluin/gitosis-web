<?php

require("functions.inc.php");


if($_POST["path"]) {
	$_SESSION["gitosisurl"] = $_POST["path"];
}


if($_SESSION["gitosisurl"]) {
	// Initial version will require a locally checked out version of gitosis-admin belonging to www-data. This means the user will have to setup gitosis, add a key for www-data before being able to use this web tool.
	$dir = $_SESSION["gitosisurl"];
		

	if(file_exists($dir) && file_exists($dir . ".git/config") && file_exists($dir . "gitosis.conf")) {
		$body .= "Valid gitosis-admin repo found.  ";

		if($_POST["config"]) {
			$fp = fopen($dir . "gitosis.conf","w");
			fwrite($fp, $_POST["config"]);
			chdir($dir);
			system("git commit -am 'automated config update from gitosis-web'");
			system("git push");
			forward($_SERVER["PHP_SELF"]);
		}

		$config = file_get_contents($dir . "gitosis.conf");
		$body .= '<form method="post"><textarea name="config" style="width:600px;height:400px;">' . $config . '</textarea><br/><button type="submit">Save</button></form>';
	
	} else {
		$body .= "Invalid gitorious-admin repo specified, please logout and try again..";
		$_SESSION["gitosisurl"] = "";
	}
	$body .= '<a href="?action=logout">Log Out</a>';

} else {
	$body .= "Please enter the path on the server for the checked out version of gitosis-admin:";
	$body .= '<form method="post"><input type="text" name="path"/><button type="submit">Setup</button>	</form>';
}

print $body;

