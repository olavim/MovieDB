<?php
include_once '../config/conf.php';
require_once 'login.php';
include_once 'sql.php';

$query = <<<EOT
SELECT id
FROM movie m1
WHERE EXISTS (
    SELECT 1
    FROM movie m2
    WHERE m2.id < m1.id
EOT;

foreach ($db_unique_key as $key) {
    $query .= ' AND m2.' . $key . ' = m1.' . $key;
}

$query .= ')';

$result = $connection_moviedb->query($query);
$delete_query = 'DELETE FROM movie WHERE id in (';
while ($row = $result->fetch_assoc()) {
    $delete_query .= $row['id'] . ',';
}
$delete_query = rtrim($delete_query, ',') . ')';
$connection_moviedb->query($delete_query);