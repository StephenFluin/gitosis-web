<?php

session_start();

function forward($newUrl) {
	header ("Location: " . $newUrl );
        exit;
}
