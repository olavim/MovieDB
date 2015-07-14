<?php
require_once 'login.php';
include_once 'sql.php';
include_once '../config/conf.php';
include_once '../include/MySQLiBinder.php';

use MySQLiBinder\Binder;

$error = '';
$set = false;
$post = array_change_key_case($_POST, CASE_LOWER);
foreach ($edit_required as $heading) {
    if (!array_key_exists($heading, $post)) {
        $error = 'Required field missing: ' . $heading;
        break;
    } else {
        $set = true;
        if (array_key_exists($heading, $edit_patterns)) {
            $pattern = '/'.$edit_patterns[$heading].'/';
            if (!preg_match($pattern, $post[$heading])) {
                $error = 'Field does not respect pattern restrictions: ' . $heading;
                break;
            }
        }
    }
}

if ($set && !$error) {
    $binder = new Binder($connection_moviedb, 'movie', 'insert');
    $params = array();

    foreach ($post as $key => $param) {
        if (!in_array($key, $db_headings_alterable)) {
            continue;
        }

        $binder->add_insert_parameter($key);

        if ($edit_types[$key] === 'checkbox') {
            $params[] = $param ? 'x' : '';
        } else {
            $params[] = $param;
        }
    }

    $binder->prepare();
    $binder->execute($params);
    $binder->close(true);

    header("Location: ./");
}
?>
<!doctype html>
<html>
<head>
    <title>Movie Database - New Movie</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="http://fonts.googleapis.com/css?family=Roboto:100" rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet'>
    <link rel="stylesheet" href="styles/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="styles/default.scss">
    <link rel="stylesheet" href="styles/form.css">
    <link rel="stylesheet" href="styles/global.css">
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/jquery.mobile-1.4.5.min.js"></script>
    <script src="js/jquery.nicefileinput.min.js"></script>
</head>
<body>
<div class="form input-form">
    <h1>New Movie</h1>
    <?php
    if ($set && $error) {
        echo '<p>Error: ' . $error . '</p>';
    }
    ?>
    <form method="post" action="new_entry.php" data-ajax="false">
        <?php
        foreach ($db_headings_alterable as $heading) {
            $type = $edit_types[$heading];
            $required = in_array($heading, $edit_required) ? 'required' : '';
            $pattern = array_key_exists($heading, $edit_patterns) ? $edit_patterns[$heading] : '';
            $pattern = $pattern ? "pattern='$pattern'" : '';

            echo "" .
                "<div class='input-section input-section-$type'>" .
                    "<label for='$heading'>" . strtoupper($heading) . "</label>" .
                    "<input type='$type' id='$heading' name='$heading' $required $pattern>" .
                "</div>";
        }
        ?>
        <div style="overflow:hidden">
            <button class="ui-button left" data-role="none" onclick="location.href='./';return false;" style="width:45%">Cancel</button>
            <button class="ui-button right" data-role="none" style="width:45%" data-ajax="false">Submit</button>
        </div>
    </form>
</div>
</body>
</html>