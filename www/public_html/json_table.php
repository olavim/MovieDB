<?php
require_once 'login.php';
include_once 'sql.php';
include_once '../include/MySQLiBinder.php';

use MySQLiBinder\Binder;

header('Content-type: application/json');
$response_array = array();
$response_array['status'] = 'success';

function get_s($s, $default = "")
{
    if (isset($_GET[$s])) {
        $_SESSION[$s] = $_GET[$s];
    }

    if (!isset($_SESSION[$s])) {
        $_SESSION[$s] = $default;
    }

    return $_SESSION[$s];
}

$select = get_s('select');
$search = get_s('search');
$select_arr = explode(',', $select);
$search_arr = explode(';', $search);
$order_by = get_s('order', $select_arr[0]);
$order_dir = get_s('dir', 'asc');
$param_arr = array();

$binder = new Binder($connection_moviedb, 'movie', 'select');
foreach ($select_arr as $select_param) {
    $binder->add_known_parameter($select_param);
}

foreach ($search_arr as $search_param) {
    $pair = explode('=', $search_param);
    $binder->add_where_parameter($pair[0], 's', 'like');
    $param_arr[] = "%".$pair[1]."%";
}

$binder->set_result_order('lower(' . $order_by . ')', $order_dir);

if ($binder->prepare()) {
    if ($result = $binder->execute($param_arr)) {
        $response_array['message'] = json_encode($result);
    } else {
        $response_array['status'] = 'error';
        $response_array['message'] = $binder->error;
    }
} else {
    $response_array['status'] = 'error';
    $response_array['message'] = $binder->error;
}

echo json_encode($response_array);
