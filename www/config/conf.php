<?php
include_once '../include/psl-config.php';
$query = <<<EOT
SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA='moviedb'
    AND TABLE_NAME='movie'
EOT;

$connection_info = new mysqli(HOST, USER, PASSWORD, 'information_schema');
$result = $connection_info->query($query);

$db_headings = array();

while ($row = $result->fetch_assoc()) {
    $db_headings[] = $row['COLUMN_NAME'];
}

// all columns in the database
// $db_headings = array('id', 'director', 'year', 'title', 'genre', 'format', 'pick');

// columns that should be visible in the main page
$db_headings_visible = array('director', 'year', 'title', 'genre', 'format');

// columns that can be changed
$db_headings_alterable = array('director', 'year', 'title', 'genre', 'format', 'pick');

// columns that can be used in search queries
$db_headings_searchable = array('director', 'year', 'title', 'genre', 'format', 'pick');

// when removing duplicates, compare these columns
$db_unique_key = array('director', 'year', 'title', 'genre', 'format');

// columns that should be visible when generating a pdf
$print_headings_visible = array('director', 'year', 'title', 'genre', 'format');

// fields that are required (must not be empty) when adding/editing entries
$edit_required = array('director', 'year', 'title');

// what kind of input field is appropriate for each column
// safe to choose: text, password, checkbox
$edit_types = array(
    "director" => "text",
    "year" => "text",
    "title" => "text",
    "genre" => "text",
    "format" => "text",
    "pick" => "checkbox"
);

// when adding/editing entries, the specified fields should match their respective regex patterns
$edit_patterns = array(
    "year" => "^[12][0-9]{3}$"
);