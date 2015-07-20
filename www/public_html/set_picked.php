<?php
require_once 'login.php';
include_once 'sql.php';

header('Content-type: application/json');
$response_array = array();
$response_array['status'] = 'success';

do {
    if (isset($_GET['id'], $_GET['state'])) {
        $id = $_GET['id'];
        $state = $_GET['state'];

        if (!is_numeric($id)) {
            $response_array['status'] = 'error';
            $response_array['message'] = "Invalid id: " . $id;
            break;
        }

        if ($state !== "on" && $state !== "off") {
            $response_array['status'] = 'error';
            $response_array['message'] = "Invalid state: " . $state;
            break;
        }

        $state = $state === "on" ? "x" : "";

        $query = "UPDATE movie SET pick='$state' WHERE id=$id";
        if (!$connection_moviedb->query($query)) {
            $response_array['status'] = 'error';
            $response_array['message'] = $connection_moviedb->error;
        }
    } else {
        $response_array['status'] = 'error';
        $response_array['message'] = "Invalid request";
    }
} while (0);

echo json_encode($response_array);