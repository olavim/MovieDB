<?php
include_once '../include/db_connect.php';
include_once '../include/functions.php';
include_once 'sql.php';

sec_session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (!is_numeric($id)) {
        die("Invalid id: " . $id);
    }

    $query = "DELETE FROM movie WHERE id=$id";
    $connection_moviedb->query($query);
    if ($connection_moviedb->error) {
        die($connection_moviedb->error);
    }

    header('Location: ./');
} else {
    echo "Invalid request";
}