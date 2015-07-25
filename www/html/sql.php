<?php
include_once __DIR__ . "/../include/psl-config.php";
$database = "moviedb";

$connection_moviedb = new mysqli(HOST, USER, PASSWORD, $database);

if ($connection_moviedb->connect_error) {
    die("Connection failed: " . $connection_moviedb->error);
}

$connection_moviedb->set_charset('utf8');
