<?php

require("functions.inc.php");


if($_POST["path"]) {
	$_SESSION["gitosisurl"] = $_POST["path"];
	forward("index.php");
}
if($_GET["action"] == "logout") {
	session_destroy();
	forward("index.php");
}


if($_SESSION["gitosisurl"]) {
	// Initial version will require a locally checked out version of gitosis-admin belonging to www-data. This means the user will have to setup gitosis, add a key for www-data before being able to use this web tool.
	$dir = $_SESSION["gitosisurl"];
		

	if(file_exists($dir) && file_exists($dir . ".git/config") && file_exists($dir . "gitosis.conf")) {
		$body .= "Valid gitosis-admin repo found.<br/>";

		$body .= showGitosisAdmin($dir);

	} else {
		$body .= "Invalid gitorious-admin repo specified, please logout and try again..";
		$_SESSION["gitosisurl"] = "";
	}
	$body .= '<div style="clear:both;"><a href="?action=logout">Log Out</a></div>';

} else {
	$body .= "Please enter the path on the server for the checked out version of gitosis-admin:";
	$body .= '<form method="post"><input type="text" name="path"/><button type="submit">Setup</button>	</form>';
}

displayPage($body);


function showGitosisAdmin($dir) {
		authenticate();
		if($_POST["config"]) {
			$fp = fopen($dir . "gitosis.conf","w");
			
			fwrite($fp, strtr($_POST["config"],"\r\n",""));
			chdir($dir);
			system("git commit -am 'automated config update from gitosis-web'");
			system("git push");
			forward($_SERVER["PHP_SELF"]);
		} else {
			chdir($dir);
			system("git pull");
		}

		$config = getConfig();
		$body .= getUserList($config);
		$body .= '<form method="post"><textarea name="config" style="width:500px;height:400px;">' . $config . '</textarea><br/><button type="submit">Save and Push</button></form>';
	return $body;
}

function authenticate() {
	if(!$_SESSION["gitosisuser"]) {
		$n = $_POST["name"];
		$p = $_POST["password"];
		if($n && $p) {
			$body .= "Authenticating against the provided user.<br/>";
			$data = getConfig();
			if(strpos($data, "name = $n") !== false && strpos($data, "$n = $p") !== false) {
				//Successfully authenticated the admin.
				$_SESSION["gitosisuser"] = $n;
				$_SESSION["gitosisadmin"] = true;
				forward("index.php");
			} else {
				$body .= "Invalid admin.<br/><pre>$data</pre>";
			}
		}
			
		
		$body .= 'Please enter a username and password.';
		$body .= '<form method="post"><input type="text" name="name"/><input type="password" name="password"/><button type="submit">Login</button<?form>';
		displayPage($body);
		exit;
	}
}

function getConfig() {
	return file_get_contents($_SESSION["gitosisurl"] . "gitosis.conf");
}
function getUserList($data) {
	preg_match_all("@\[group user-([^\]]*)\].*members = ([^\r\n]*)\r?\n@ms",$data,$matches);
	
	$body .= "<h2>User List</h2>";
	$i = 0;
	while($matches[1][$i]) {
		$body .= "<h3>" . $matches[1][$i] . "</h3><br/>\n";
		$keys = explode(" ",$matches[2][$i]);
		foreach($keys as $key) {
			$body .= "$key:<br/>\n";
			$body .= "<textarea style=\"width:100%;height:75px;\">" . file_get_contents($_SESSION["gitosisurl"] . "keydir/" . $key . ".pub") . "</textarea>";
		}
		$body .= '<form method="post"><h4>New</h4><input type="text" name="keyname"/><textarea style="width:100%;height:75px;" name="keyvalue"></textarea><button type="submit">Create</button></form>';
		$i++;
		
		
	}
	$body = '<div style="width:600px;float:right;border:1px solid black;">' . $body . '</div>';
	return $body;
}


	
