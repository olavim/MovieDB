<?php
include_once '../include/db_connect.php';
include_once '../include/functions.php';
include_once 'sql.php';

sec_session_start();

if (isset($_GET['id'])) {
    $id_arr = explode(',', $_GET['id']);

    $query = "DELETE FROM movie WHERE ";
    for ($i = 0; $i < count($id_arr); $i++) {
        $id = $id_arr[$i];
        if (!is_numeric($id)) {
            die("Invalid id: " . $id);
        }

        $query .= "id='$id'";
        if ($i < count($id_arr) - 1) {
            $query .= " OR ";
        }
    }

    $connection_moviedb->query($query);
    if ($connection_moviedb->error) {
        die($connection_moviedb->error);
    }

    header('Location: ./');
} else {
    echo "Invalid request";
}