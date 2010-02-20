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
</style>
</head><body>' . $page . "</body></html>";
}