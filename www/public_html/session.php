<?php
require_once 'login.php';

header('Content-type: application/json');
$response_array = array();
$response_array['status'] = 'success';
$response_array['message'] = '';

function set_session($s) {
    if (isset($_GET[$s])) {
        $_SESSION[$s] = $_GET[$s];
    }
}

set_session('order');
set_session('dir');
set_session('elementsPerPage');

echo json_encode($response_array);