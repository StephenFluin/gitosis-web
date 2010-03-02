<?php

session_start();

function forward($newUrl) {
	header ("Location: " . $newUrl );
        exit;
}
function displayPage($page) {
	print '<html><head>
<title>Gitosis Web</title>
<style type="text/css">
* { padding:0;margin:0;}
.rightPanel {width:600px;float:right;border:1px solid black;}
.leftPanel {width:500px;float:left;}
</style>
</head><body>' . $page . "<div style=\"color:red;text-align:center;\">Caution: All changes made using this tool are immediately pushed to the server, and can irrecoverably break your gitosis installation.</body></html>";
}
