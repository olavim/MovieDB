<?php
require_once 'login.php';
include_once 'sql.php';
include_once '../include/MySQLiBinder.php';

use MySQLiBinder\MySQLiBinder;

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
$search_arr = explode(',', $search);
$order_by = $connection_moviedb->real_escape_string(get_s('order', $select_arr[0]));
$order_dir = $connection_moviedb->real_escape_string(get_s('dir', 'asc'));
$params = "";

if (count($select_arr) < count($search_arr)) {
    die("Error: invalid search parameters");
}

if ($order_dir != 'asc' && $order_dir != 'desc') {
    die("Error: invalid ORDER BY: value must be 'asc' or 'desc'");
}

while (count($select_arr) > count($search_arr)) {
    $search_arr[] = "";
}

for ($i = 0; $i < count($search_arr); $i++) {
    $search_arr[$i] = "%".$search_arr[$i]."%";
}

$query = "SELECT " . $select . " FROM movie WHERE ";
for ($i = 0; $i < count($select_arr); $i++) {
    $query .= $select_arr[$i] . " LIKE ?";
    if ($i + 1 < count($select_arr)) {
        $query .= " AND ";
    }

    $params .= 's';
}
$query .= ' ORDER BY lower(' . $order_by . ') ' . $order_dir;

$binder = new MySQLiBinder($query, $params, $search_arr);
if ($result = $binder->execute($connection_moviedb)) {
    echo json_encode($result);
}
