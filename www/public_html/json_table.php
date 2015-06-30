<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/../include/db_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/../include/functions.php';
include_once 'sql.php';

sec_session_start();

class JSONTable
{
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function get_json()
    {
        $return_arr = array();

        while ($row = mysqli_fetch_array($this->data, MYSQL_ASSOC)) {
            array_push($return_arr,$row);
        }

        return json_encode($return_arr);
    }
}

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
$from = "movie";
$search = get_s('search');

$select_arr = explode(',', $select);
$search_arr = explode(',', $search);

if (count($select_arr) < count($search_arr)) {
    die("Error: invalid search parameters");
}

while (count($select_arr) > count($search_arr)) {
    array_push($search_arr, "");
}

$where = "";
for ($i = 0; $i < count($select_arr); $i++) {
    $where .= $select_arr[$i] . " LIKE ";
    $where .= '"%' . $search_arr[$i] . '%"';
    if ($i + 1 < count($select_arr)) {
        $where .= " AND ";
    }
}

$order_by = get_s('order', $select_arr[0]);
$order_dir = get_s('dir', 'asc');

if ($order_dir != 'asc' && $order_dir != 'desc') {
    die("Error: invalid ORDER BY: value must be 'asc' or 'desc'");
}

$sql = "SELECT $select FROM $from WHERE $where ORDER BY $order_by $order_dir";
$result = mysqli_query($connection_moviedb, $sql);

if ($result) {
    $table = new JSONTable($result);
    echo $table->get_json();
} else {
    printf("Errormessage: %s\n", $connection_moviedb->error);
}
