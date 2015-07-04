<?php
include_once '../include/db_connect.php';
include_once '../include/functions.php';
include_once "sql.php";
include_once "dompdf/dompdf_config.inc.php";

sec_session_start();

header('Content-type: application/pdf');

function get($s) {
	return isset($_GET[$s]) ? $_GET[$s] : "";
}

function session($s, $default) {
	return isset($_SESSION[$s]) && $_SESSION[$s] ? $_SESSION[$s] : $default;
}

$director = "%" . get("director") . "%";
$year = "%" . get("year") . "%";
$title = "%" . get("title") . "%";
$pick = "%" . get("pick") . "%";
$order = session('order', 'director');
$dir = session('dir', 'asc');

$query = "SELECT director, year, title, pick
		  FROM movie
		  WHERE director LIKE ? AND year LIKE ? AND title LIKE ? AND pick LIKE ?
		  ORDER BY $order $dir";

if ($stmt = $connection_moviedb->prepare($query)) {
	$stmt->bind_param("ssss", $director, $year, $title, $pick);
	$stmt->execute();
	$stmt->bind_result($director, $year, $title, $pick);
	$html =
	"<html>".
	"<head>".
	"<meta charset=\"UTF-8\">".
	"<style type=\"text/css\">".
	"table, td, th { font-family: Arial, sans-serif; font-size: 10pt; text-align: left }".
	"th, td { padding-top: 5px; padding-bottom: 5px; padding-right: 20px; }".
	"th { border-bottom: 2px solid #666 }".
	"</style>".
	"</head>".
	"<body>".
	"<table>".
	"<thead>".
	"<tr>".
	"<th>Director</th>".
	"<th>Year</th>".
	"<th>Title</th>".
	"</tr>".
	"</thead>".
	"<tbody>";

	while($stmt->fetch()) {
		$html .=
		"<tr>".
		"<td>$director</td>".
		"<td>$year</td>".
		"<td>$title</td>".
		"</tr>";
	}

	$html .=
	"</tbody>".
	"</table>".
	"</body>".
	"</html>";

	$stmt->close();

	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	$dompdf->render();
	echo $dompdf->output();
	//$dompdf->stream("sample.pdf", array("Attachment"=>0));
}
