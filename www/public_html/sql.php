<?php
include_once "/../include/psl-config.php";   // As functions.php is not included
$database = "moviedb";

$connection_moviedb = new mysqli(HOST, USER, PASSWORD, $database);

// Check connection
if ($connection_moviedb->connect_error) {
    die("Connection failed: " . $connection_moviedb->connect_error);
}

$connection_moviedb->set_charset('utf8');