<?php
require_once 'login.php';
include_once 'sql.php';
include_once '../config/conf.php';
include_once '../include/MySQLiBinder.php';

use MySQLiBinder\Binder;

header('Content-type: application/json');
$response_array = array();
$response_array['status'] = 'success';

$set = false;
if (isset($_POST['form'])) {
    $form = $_POST['form'];
    foreach ($edit_required as $heading) {
        if (!array_key_exists($heading, $form)) {
            $response_array['status'] = 'error';
            $response_array['message'] = 'Required field missing: ' . $heading;
            break;
        } else {
            $set = true;
            if (array_key_exists($heading, $edit_patterns)) {
                $pattern = '/' . $edit_patterns[$heading] . '/';
                if (!preg_match($pattern, $form[$heading])) {
                    $response_array['status'] = 'error';
                    $response_array['message'] = 'Field does not respect pattern restrictions: ' . $heading;
                    break;
                }
            }
        }
    }
}

if ($set && $response_array['status'] == 'success') {
    $binder = new Binder($connection_moviedb, 'movie', 'update');

    foreach ($db_headings_alterable as $heading) {
        $binder->add_update_parameter($heading);
    }

    $binder->add_where_parameter('id');

    if ($binder->prepare()) {
        $id = $form['id'];
        if (!is_numeric($id)) {
            $response_array['status'] = 'error';
            $response_array['message'] = "Invalid id: " . $id;
        }

        $params = array();
        foreach ($db_headings_alterable as $heading) {
            $val = array_key_exists($heading, $form) ? $form[$heading] : '';

            if ($edit_types[$heading] === 'checkbox') {
                $params[] = $val ? 'x' : '';
            } else {
                $params[] = $val;
            }
        }
        $params[] = $id;

        if (!$binder->execute($params)) {
            $response_array['status'] = 'error';
            $response_array['message'] = $binder->error;
        }
    } else {
        $response_array['status'] = 'error';
        $response_array['message'] = $binder->error;
    }

    $binder->close();
}

$connection_moviedb->close();

echo json_encode($response_array);
