<?php
include_once '../config/conf.php';
require_once 'login.php';
include_once 'sql.php';
include_once '../include/MySQLiBinder.php';

use MySQLiBinder\Binder;

if (isset($_FILES['file'])) {
    $fh = fopen($_FILES['file']['tmp_name'], 'r+');

    $lines = array();
    while( ($row = fgetcsv($fh, 8192, ';')) !== FALSE ) {
        $lines[] = $row;
    }

    $user = $_SESSION['username'];

    // get columns

    $select_columns_binder = new Binder($connection_moviedb, 'entry_columns');
    $select_columns_binder->add_known_parameter('column_name');
    $select_columns_binder->add_where_parameter('user');
    $select_columns_binder->prepare();

    if ($select_columns_result = $select_columns_binder->execute(array($user))) {
        $columns = array();
        foreach ($select_columns_result as $column) {
            $columns[] = $column['column_name'];
        }
    } else {
        echo $select_columns_binder->error;
    }

    $select_columns_binder->close();

    // num entries

    $num_binder = new Binder($connection_moviedb, 'entry_data');
    $num_binder->add_known_parameter('count(*)');
    $num_binder->add_where_parameter('user');
    $num_binder->prepare();

    $num_result = intval($num_binder->execute(array($user)));

    $num_binder->close();

    $num_entries = $num_result / count($columns);

    // insert

    $insert_binder = new Binder($connection_moviedb, 'entry_data', 'insert');
    $insert_binder->add_insert_parameter('entry_id', 'i');
    $insert_binder->add_insert_parameter('user');
    $insert_binder->add_insert_parameter('column_name');
    $insert_binder->add_insert_parameter('entry_data');
    $insert_binder->prepare();

    $column_keys = array_flip($lines[0]);

    $connection_moviedb->begin_transaction();
    for ($i = 1; $i < count($lines); $i++) {
        $num_entries++;
        foreach ($columns as $column) {
            $key = $column_keys[$column];
            $insert_binder->execute(array($num_entries, $user, $column, $lines[$i][$key]));
        }
    }
    $connection_moviedb->commit();

    $insert_binder->close(true);
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
                    <button class="ui-button right" data-role="none" style="width:45%" data-ajax="false">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<?php } ?>