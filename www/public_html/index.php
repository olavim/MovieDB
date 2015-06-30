<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/../include/db_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/../include/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/../check_login.php';

function get($s) {
	return isset($_GET[$s]) ? $_GET[$s] : "";
}

function session($s, $default = "") {
	return isset($_SESSION[$s]) && $_SESSION[$s] ? $_SESSION[$s] : $default;
}

$order_by 		 = session('order', "director");
$order_direction = session('dir', "asc");

$s_director = get('s_director');
$s_year     = get('s_year');
$s_title    = get('s_title');
$s_pick 	= get('s_pick') ? "x" : "";
?>
<!doctype html>
<html>
<head>
	<title>Movie Database</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="styles/global.css">
	<link rel="stylesheet" type="text/css" href="styles/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" type="text/css" href="styles/default.css">
	<link rel="stylesheet" type="text/css" href="styles/jtable.css">
	<script type="text/javascript" src="js/jquery.mobile-1.4.5.min.js"></script>
	<script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="js/table.js"></script>
	<script type="text/javascript" src="js/navigation.js"></script>
	<script type="text/javascript">
		var jsonData;

		$(document).on("pageinit", function() {
			$("[data-role='header'], [data-role='footer']").toolbar({theme: "a"});
		});

		$(document).one('pageinit', function() {
			$.ajax({
				url: "json_table.php",
				type: "get",
				contentType: 'application/json',
				dataType: 'html',
				data: {
					select: "director,year,title,pick",
					order: "<?=$order_by?>",
					dir: "<?=$order_direction?>",
					search: "<?=$s_director.','.$s_year.','.$s_title.','.$s_pick?>"
				},
				beforeSend: function() { showLoader(); },
				success: function (data) {
					jsonData = data;
					$('body').jtable(data, {
						orderBy: "<?=$order_by?>",
						asc: <?=$order_direction == "asc" ? "true" : "false"?>,
						pageSelect: "#page-select"
					});

					hideLoader();
				},
				error: function (data) {
					$('body').html(data);
				}
			});
		});

		$(document).on("pageshow", ".ui-page", function() {
			$("body").css("overflow", "hidden");
			setTimeout(function() {
				$(".ui-page").css("min-height", getContentHeight() + "px");
				$("body").css("overflow", "auto");
			}, 100);
		});

		function getContentHeight() {
			var screen = $.mobile.getScreenHeight();
			var header = $(".ui-header").hasClass("ui-header-fixed") ? $(".ui-header").outerHeight()  - 1 : $(".ui-header").outerHeight();
			var footer = $(".ui-footer").hasClass("ui-footer-fixed") ? $(".ui-footer").outerHeight() - 1 : $(".ui-footer").outerHeight();
			return screen - header - footer;
		}
		
		function printPage() {
			window.open("create_pdf.php?<?="director=$s_director&year=$s_year&title=$s_title&pick=$s_pick"?>");
		}

		function showLoader() {
			$("body").append(
				$("<div id=\"loader\"></div>").css({
					"position": "absolute",
					"z-index": "99999",
					"display": "table",
					"height": "100%",
					"width": "100%"
				}).append(
					$("<div>Loading...</div>").css({
						"display": "table-cell",
						"vertical-align": "middle",
						"text-align": "center",
					})
				)
			);
		}

		function hideLoader() {
			$("#loader").fadeOut(100, function() {
				$(this).remove();
			});
		}
	</script>
</head>
<body>
<input type="hidden" id="order-by" name="order_by" value="<?=$order_by?>">
<input type="hidden" id="order-direction" name="order_direction" value="<?=$order_direction?>">
<div data-role="header" data-position="fixed" data-tap-toggle="false">
	<div id="page-number" data-role="controlgroup" data-type="horizontal" class="ui-btn-left ui-group-theme-a">
		<a class="ui-btn ui-icon-carat-l ui-corner-all ui-btn-icon-notext" id="nav-btn-prev">Previous</a><!--
	 --><label for="page-select" class="ui-hidden-accessible">Page</label><!--
	 --><select name="page-select" id="page-select" data-native-menu="false" data-theme="a">
			<option>Page 1</option>
		</select><!--
	 --><a class="ui-btn ui-icon-carat-r ui-corner-all ui-btn-icon-notext" id="nav-btn-next">Next</a>
	</div>
	<div id="controls-right" data-role="controlgroup" data-type="horizontal" class="ui-btn-right ui-group-theme-a ui-mobile-safe">
		<a href="search.html" rel="external" class="ui-btn ui-btn-icon-right ui-icon-search ui-corner-all">Search</a>
		<a href="#" onclick="printPage()" class="ui-btn ui-btn-icon-right ui-icon-printer ui-corner-all">Print</a>
	</div>
	<h1>&nbsp;</h1>
</div>
<div data-role="page"></div>
<div data-role="footer" data-position="fixed" id="footer" data-tap-toggle="false">
	<div data-role="controlgroup" data-type="horizontal" class="footer-button-left ui-group-theme-a ui-mobile-safe">
		<a href="#" class="ui-btn ui-btn-icon-right ui-icon-plus ui-corner-all">New Movie</a>
		<a href="#" class="ui-btn ui-btn-icon-right ui-icon-edit ui-corner-all ui-state-disabled">Edit</a>
		<a href="#" class="ui-btn ui-btn-icon-right ui-icon-delete ui-corner-all ui-state-disabled">Delete</a>
	</div>
	<h1>&nbsp;</h1>
</div>
</body>
</html>