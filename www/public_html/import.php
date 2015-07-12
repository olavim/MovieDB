<?php
include_once '../config/conf.php';
require_once 'login.php';
include_once 'sql.php';
include_once '../include/MySQLiBinder.php';

use MySQLiBinder\MySQLiBinder;

if (isset($_POST['file'])) {
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

    if ($binder = new MySQLiBinder($connection_moviedb, $query, $types)) {
        for ($i = 1; $i < count($lines); $i++) {
            $params = array();
            foreach ($heading_keys as $key) {
                $params[] = $lines[$i][$key];
            }
            $binder->execute($params);
        }
        $binder->close();
    }
    $connection_moviedb->close();

    header('Location: ./');
} else {
?>
<!doctype html>
<html>
<head>
    <title>Movie Database - New Movie</title>
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
    <script src="js/jquery.nicefileinput.min.js"></script>
    <script>
        $(function() {
            $("input[type=file]").nicefileinput({
                label : 'Browse...'
            });
            $(document).keypress(function (e) {
                if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                    document.forms[0].submit();
                    return false;
                } else {
                    return true;
                }
            });
        });
    </script>
</head>
<body>
<div data-role="page" id="search-page">
    <div data-role="content">
        <div class="form input-form">
            <h1>Import CSV</h1>
            <form method="post" action="import.php" data-ajax="false" enctype="multipart/form-data">
                <input type="file" name="file" multiple data-role="none"/>
                <div style="overflow:hidden">
                    <button class="ui-button left" data-role="none" onclick="location.href='./';return false;" style="width:45%">Cancel</button>
                    <button class="ui-button right" data-role="none" style="width:45%">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<?php } ?>