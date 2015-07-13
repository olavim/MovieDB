<?php
require_once 'login.php';
include_once 'sql.php';
include_once '../config/conf.php';
include_once '../include/MySQLiBinder.php';

use MySQLiBinder\MySQLiBinder;

$error = '';
$set = false;
if (isset($_POST['form'])) {
    $form = $_POST['form'];
    foreach ($form as $key => $row) {
        foreach ($edit_required as $heading) {
            if (!array_key_exists($heading, $row)) {
                $error = 'Required field missing: ' . $heading;
                break 2;
            } else {
                $set = true;
                if (array_key_exists($heading, $edit_patterns)) {
                    $pattern = '/' . $edit_patterns[$heading] . '/';
                    if (!preg_match($pattern, $row[$heading])) {
                        $error = 'Field does not respect pattern restrictions: ' . $heading;
                        break 2;
                    }
                }
            }
        }
    }
}

if ($set && !$error) {
    $binder = new MySQLiBinder($connection_moviedb, 'movie', 'update');

    foreach ($db_headings_alterable as $heading) {
        $binder->add_update_parameter($heading);
    }

    $binder->add_where_parameter('id');
    $binder->prepare();

    foreach ($form as $row) {
        $id = $row['id'];
        if (!is_numeric($id)) {
            die("Invalid id: " . $id);
        }

        $params = array();
        foreach ($db_headings_alterable as $heading) {
            $val = array_key_exists($heading, $row) ? $row[$heading] : '';

            if ($edit_types[$heading] === 'checkbox') {
                $params[] = $val ? 'x' : '';
            } else {
                $params[] = $val;
            }
        }
        $params[] = $id;

        $binder->execute($params);
    }

    $binder->close();
}

if (isset($_GET['ids'])) {
    $ids = explode(',', $_GET['ids']);
    foreach ($ids as $id) {
        if (!is_numeric($id)) {
            die("Invalid id: " . $id);
        }
    }

    $query = "SELECT * FROM movie WHERE id in (".join(',', $ids).")";
    $results = array();
    if ($result = $connection_moviedb->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $result->free();
    } else {
        print_r($connection_moviedb->error);
    }
} else {
    die("Invalid request");
}

$connection_moviedb->close();
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
    <link rel="stylesheet" href="styles/default.css">
    <link rel="stylesheet" href="styles/form.css">
    <link rel="stylesheet" href="styles/global.css">
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/jquery.mobile-1.4.5.min.js"></script>
</head>
<body>
<form method="post" action="" data-ajax="false">
<div class="form input-form">
    <h1>Edit Movie</h1>
</div>
    <?php
    if ($set && $error) {
        echo '<p>Error: ' . $error . '</p>';
    }
    ?>
    <?php
    for ($i = 0; $i < count($results); $i++) {
        $row = $results[$i];
        $id = $row['id'];
        echo "<div class='form input-form'>";
        echo "<input type='hidden' name='form[{$i}][id]' value='$id'>";
        foreach ($db_headings_alterable as $heading) {
            $type = $edit_types[$heading];
            $required = in_array($heading, $edit_required) ? 'required' : '';
            $pattern = array_key_exists($heading, $edit_patterns) ? $edit_patterns[$heading] : '';
            $pattern = $pattern ? "pattern='$pattern'" : '';
            $value = "value='{$row[$heading]}'";
            if ($edit_types[$heading] === 'checkbox') {
                $value = $row[$heading] ? 'checked' : '';
            }

            $label = ($edit_types[$heading] === 'checkbox' ? ucfirst($heading) : strtoupper($heading));
            $class = ($edit_types[$heading] === 'checkbox' ? "class='ui-checkbox-off'" : '');
            echo "" .
                "<div class='input-section input-section-$type'>" .
                "<label for='{$heading}-{$i}'>{$label}</label>" .
                "<input type='$type' id='{$heading}-{$i}' name='form[{$i}][{$heading}]' $class $required $pattern $value>" .
                "</div>";
        }
        echo "</div>";
    }
    ?>
<div class="form input-form">
    <div style="overflow:hidden">
        <button class="ui-button left" data-role="none" onclick="location.href='./';return false;" style="width:45%">Cancel</button>
        <button class="ui-button right" data-role="none" style="width:45%">Save Changes</button>
    </div>
</div>
</form>
</body>
</html>
