<?php

require("functions.inc.php");


if($_POST["path"]) {
	$path = $_POST["path"];
	$_SESSION["gitosisurl"] = $path;
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
			writeConfig(strtr($_POST["config"],"\r\n",""));
			chdir($dir);
			system("git commit -am 'automated config update from gitosis-web'");
			system("git push");
			#forward($_SERVER["PHP_SELF"] . "?msg=cupdated");
		} else {
			chdir($dir);
			exec("git pull");
		}
		


		$config = getConfig();
		checkKeyChanges($config);
		$body .= '<form method="post" class="leftPanel"><textarea name="config" style="width:500px;height:400px;">' . $config . '</textarea><br/><button type="submit">Save and Push</button></form>';
		$body .= getUserList($config);
                $body .= getKeyList();
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
				forward("index.php" . "?msg=loginsuccess");
			} else {
				$body .= "Invalid admin.<br/><pre>$data</pre>";
			}
		}
			
		
		$body .= 'Please enter a username and password.';
		$body .= '<form method="post"><input type="text" name="name" placeholder="username"/><input type="password" name="password" placeholder="password"/><button type="submit">Login</button<?form>';
		displayPage($body);
		exit;
	}
}

function getConfig() {
	return file_get_contents($_SESSION["gitosisurl"] . "gitosis.conf");
}
function writeConfig($content) {
	if($_SESSION["gitosisurl"]) {
		$dir = $_SESSION["gitosisurl"];
		$fp = fopen($dir . "gitosis.conf","w");
		fwrite($fp, $content);
	}
}
function getUserList($data) {
	preg_match_all("@\[group user-([^\]]*)\][^\[]*?members = ([^\r\n]*)\r?\n@ms",$data,$matches);
	//var_dump($matches);
	$body .= "<h2>User List</h2>";
	$i = 0;
	while($matches[1][$i]) {
		$body .= "<h3>" . $matches[1][$i] . "</h3><br/>\n";
		$keys = explode(" ",$matches[2][$i]);
		foreach($keys as $key) {
			$body .= "$key: <a href=\"?action=delete&user=" . $matches[1][$i] . "&key=$key\">delete</a><br/>\n";
			$body .= "<textarea style=\"width:100%;height:50px;\">" . file_get_contents($_SESSION["gitosisurl"] . "keydir/" . $key . ".pub") . "</textarea>";
		}
		$body .= '<form method="post"><h4>New</h4><input type="hidden" name="user" value="' . $matches[1][$i] . '"/><input type="text" name="keyname"/><textarea style="width:100%;height:50px;" name="keyvalue"></textarea><button type="submit">Create</button></form>';
		$i++;
		
		
	}
	$body = '<div class="rightPanel">' . $body . '</div>';
	return $body;
}
function getKeyList() {
	$body .= "<h2>Key List</h2>";
	$files = scandir($_SESSION["dir"] . "keydir/");
	foreach($files as $file) {
		if(preg_match("/^(.*)\.pub$/",$file)) {
			$body .= $file . "<br/>";
		}
	}

	return '<div class="rightPanel">' . $body . '</div>';
}
	

function checkKeyChanges($config) {
	$k = $_POST["keyname"];
	$v = $_POST["keyvalue"];
	$u = $_POST["user"];
	$us = $_GET["user"];
	$ke = $_GET["key"];
	$a = $_GET["action"];

	if($k && $v && $u) {
		$dir = $_SESSION["gitosisurl"];

		do {
			$k = strtr($k,array("/"=>"","."=>""));
		} while(strpos($k,".") !== false);
		if(file_exists($dir . "keydir/" . $k . ".pub")) {
			// Refuse to overwrite anything.
		} else {
			$fp = fopen($dir . "keydir/" . $k . ".pub","w");
			$result=fwrite($fp,$v);
			if($result) {
				chdir($dir);
				system("git add keydir/" . $k . ".pub");
				system("git commit -am \"Added new key for $k\"");
				system("git push");
			}
		}
		forward("index.php" . "?msg=keyadded");
	} else if($us && $ke && $a == "delete") {
		$dir = $_SESSION["gitosisurl"];
		if(file_exists($dir . "keydir/" . $ke . ".pub")) {
			
			do {
				$ke = strtr($ke,array("/"=>"","."=>""));
			} while(strpos($k,".") !== false);
			if(file_exists($dir . "keydir/" . $ke . ".pub")) {
				
				$pattern = "/^members = (.*) $ke(.*)$/m";
				if(preg_match($pattern, $config)) {
					//print "Replace pattern matched.<br/>";
				} else {
					//print "Replace pattern '$pattern' didn't match.<br/>";
				}
				$config = preg_replace($pattern, 'members = $1$2',$config);
				//print "Replaced and resulted in <pre>$config</pre><br/>";
				//print 'Trying to delete ' . $ke .' for ' . $us . ".<br/>";

				chdir($dir);
				writeConfig($config);
				exec("git rm keydir/" . $ke . ".pub");
				exec("git add gitosis.conf");
				exec("git commit -m \"Removed key for $us, removed from config.\"");
				system("git push");
				forward("index.php" . "?msg=keydeleted");

			}
		} else {
			// Key file not found.
			forward("index.php");
		}
	}
	
}
	

	
