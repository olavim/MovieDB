<?php
include_once '../include/db_connect.php';
include_once '../include/functions.php';
include_once 'sql.php';

if (isset($_POST['director'], $_POST['year'], $_POST['title'])) {
    if ($stmt = $connection_moviedb->prepare("INSERT INTO movie (director, year, title, pick) VALUES (?, ?, ?, ?)")) {
        $stmt->bind_param("ssss", $director, $year, $title, $pick);
        $director = $_POST['director'];
        $year = $_POST['year'];
        $title = $_POST['title'];
        $pick = isset($_POST['pick']) ? "x" : "";

        if (!preg_match("/^([ \x{00c0}-\x{01ff}a-zA-Z'-&])+$/u", $director)) {
            die("Error: invalid director name: " . $director);
        }

        if (!preg_match("/^[12][0-9]{3}$/", $year)) {
            die("Error: invalid year: " . $year);
        }

        $stmt->execute();

        $stmt->close();
        $connection_moviedb->close();

        header("Location: ./");
    } else {
        printf("Errormessage: %s\n", $connection_moviedb->error);
    }
} else {
    die("Invalid request");
}