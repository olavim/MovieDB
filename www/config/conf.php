<?php
require_once '../vendor/autoload.php';
require_once '../lib/DBFactory.php';
require_once '../include/functions.php';

if (!login_check(Database\DBFactory::getConnection(Database\DBFactory::CONNECTION_USER_DATABASE))) {
    die('Login required.');
}

$mysqli = Database\DBFactory::getConnection(Database\DBFactory::CONNECTION_MAIN_DATABASE);
$db = new MysqliDb($mysqli);

$db->where('user_id', $_SESSION['user_id']);
$db->orderBy('column_order', 'asc');
$result = $db->get('entry_columns', null, 'column_name');

$db_headings = array_map(function($a) { return $a['column_name']; }, $result);

$db->where('user_id', $_SESSION['user_id']);
$db->orderBy('column_order', 'asc');
$result = $db->get('entry_columns', null, 'column_name');

// columns that should be visible in the main page
$db_headings_visible = array_map(function($a) { return $a['column_name']; }, $result);

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