<?php
include_once '../include/db_connect.php';
include_once '../include/functions.php';
include_once 'sql.php';

sec_session_start();

if (isset($_GET['id'], $_GET['state'])) {
    $id = $_GET['id'];
    $state = $_GET['state'];

    if (!is_numeric($id)) {
        die("Invalid id: " . $id);
    }

    if ($state !== "on" && $state !== "off") {
        die("Invalid state: " . $state);
    }

    $state = $state === "on" ? "x" : "";

    $query = "UPDATE movie SET pick='$state' WHERE id=$id";
    $connection_moviedb->query($query);
    print_r($connection_moviedb->error);
} else {
    echo "Invalid request";
}