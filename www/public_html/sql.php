<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviedb";

$connection_moviedb = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connection_moviedb->connect_error) {
    die("Connection failed: " . $connection_moviedb->connect_error);
}

$connection_moviedb->set_charset('utf8');