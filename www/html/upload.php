<?php
include_once '../config/conf.php';
require_once 'login.php';
include_once 'sql.php';
include_once '../include/MySQLiBinder.php';

use MySQLiBinder\Binder;

if (isset($_POST['submit'])) {
    $fh = fopen($_FILES['file']['tmp_name'], 'r+');

    $lines = array();
    while( ($row = fgetcsv($fh, 8192, ';')) !== FALSE ) {
        $lines[] = $row;
    }

    $query = "INSERT INTO movie (";

    $heading_keys = array();
    for ($i = 0; $i < count($lines[0]); $i++) {
        $heading = strtolower($lines[0][$i]);
        if (in_array($heading, $db_headings_alterable)) {
            if (count($heading_keys) > 0) {
                $query .= ', ';
            }
            $query .= $heading;
            $heading_keys[] = $i;
        }
    }
    $query .= ") VALUES (?";

    $types = 's';
    for ($i = 1; $i < count($heading_keys); $i++) {
        $query .= ', ?';
        $types .= 's';
    }
    $query .= ")";

    $binder = new Binder($connection_moviedb, $query, $types);
    for ($i = 1; $i < count($lines); $i++) {
        $params = array();
        foreach ($heading_keys as $key) {
            $params[] = $lines[$i][$key];
        }
        $binder->execute($params);
    }
    $binder->close(true);
} else {
?>
<!doctype html>
<html>
<head>
    <title>Movie Database - Search</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="http://fonts.googleapis.com/css?family=Roboto:100" rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="styles/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="styles/default.scss">
    <link rel="stylesheet" href="styles/form.css">
    <link rel="stylesheet" href="styles/global.css">
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/jquery.mobile-1.4.5.min.js"></script>
    <script>
    </script>
</head>
<body>
<div data-role="page" id="search-page">
    <div data-role="content">
        <form method="post" action="upload.php" enctype="multipart/form-data" data-ajax="false">
            <input type="file" name="file" id="file">
            <input type="submit" name="submit" id="submit" value="Upload">
        </form>
    </div>
</div>
</body>
</html>
<?php } ?>