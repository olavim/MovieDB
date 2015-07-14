<?php
require_once 'login.php';
include_once 'sql.php';
include_once '../config/conf.php';
include_once '../include/MySQLiBinder.php';

use MySQLiBinder\Binder;

$error = '';
$set = false;
if (isset($_POST['form'])) {
    var_dump($_POST['form']);
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
    $binder = new Binder($connection_moviedb, 'movie', 'update');

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
} else if ($error) {
    echo $error;
}

$connection_moviedb->close();
?>