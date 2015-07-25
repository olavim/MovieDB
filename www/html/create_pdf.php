<?php
require_once 'login.php';
include_once "sql.php";
require_once '../lib/snappy/autoload.php';
include_once '../config/conf.php';
include_once '../include/MySQLiBinder.php';

use Knp\Snappy\Pdf;

function get($s) {
	return isset($_GET[$s]) ? $_GET[$s] : "";
}

function session($s, $default) {
	return isset($_SESSION[$s]) && $_SESSION[$s] ? $_SESSION[$s] : $default;
}

$binder = new MySQLiBinder\Binder($connection_moviedb, 'movie', 'select');
foreach ($print_headings_visible as $heading) {
	$binder->add_known_parameter($heading);
}

$params = array();
foreach ($_GET as $heading => $param) {
	if (in_array($heading, $print_headings_visible)) {
		$binder->add_where_parameter($heading, 's', 'like');
		$params[] = "%{$param}%";
	}
}

$binder->set_result_order(session('order', $print_headings_visible[0]), session('dir', 'asc'));

$binder->prepare();
$result = $binder->execute($params);

$binder->close(true);

$html = <<<EOT
<html>
<head>
	<meta charset="UTF-8">
	<style type="text/css">
		table, td, th { font-family: Arial, sans-serif; font-size: 10pt; text-align: left }
		th, td { padding-top: 5px; padding-bottom: 5px; padding-right: 20px; }
		th { border-bottom: 2px solid #666 }
		thead { display: table-header-group }
		tfoot { display: table-row-group }
		tr { page-break-inside: avoid }
	</style>
</head>
<body>
	<table>
		<thead>
			<tr>
EOT;

foreach ($print_headings_visible as $heading) {
	$html .= "<th>{$heading}</th>";
}

$html .= <<<EOT
			</tr>
		</thead>
		<tbody>
EOT;

foreach ($result as $row) {
	$html .= "<tr>";
	foreach ($print_headings_visible as $heading) {
		$html .= "<td>{$row[$heading]}</td>";
	}
	$html .= "</tr>";
}

$html .= <<<EOT
		</tbody>
	</table>
</body>
</html>
EOT;

header('Content-Type: application/pdf');

$filename = "../tmp/pdf_" . rand(100000, 999999) . '.pdf';
$snappy = new Pdf(realpath('../lib/wkhtmltopdf'));
$snappy->generateFromHtml($html, $filename);
echo file_get_contents($filename);

ignore_user_abort(true);
unlink($filename);