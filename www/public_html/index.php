<?php
require_once '../check_login.php';

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
	<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="styles/global.css">
	<link rel="stylesheet" type="text/css" href="styles/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" type="text/css" href="styles/default.css">
	<link rel="stylesheet" type="text/css" href="styles/jtable.css">
	<script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="js/jquery.mobile-1.4.5.min.js"></script>
	<script type="text/javascript" src="js/table.js"></script>
	<script type="text/javascript" src="js/ToggleAction.js"></script>
	<script type="text/javascript" src="js/navigation.js"></script>
	<script type="text/javascript">
		var jsonData;

		$(document).one('pageinit', function() {
			var navButton = $('<a href="#nav-panel" id="nav-button" data-role="button" class="no-background ui-btn ui-icon-bars ui-nodisc-icon ui-alt-icon ui-mobile-safe ui-btn-left ui-btn-icon-left">Navigation</a>');
			navButton.insertBefore("#page-number");

			if (!isMobile()) {
				var panel = $('<div data-role="panel" id="nav-panel" data-display="overlay" data-position="left" data-theme="b" data-position-fixed="true" class="ui-responsive-panel"></div>');
				panel.appendTo("body");
			} else {
				var page = $('<div data-role="page" data-theme="a" id="nav-page" data-next="#page-1"></div>');
				$("body").append(page);
			}

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
					jsonData = jQuery.parseJSON(data);;
					$('body').jtable(jsonData, {
						orderBy: "<?=$order_by?>",
						asc: <?=$order_direction == "asc" ? "true" : "false"?>,
						pageSelect: "#page-select"
					});

					var view = $('<ul data-role="listview"></ul>');
					for (var i = 1; i <= Math.ceil(jsonData.length / 20); i++) {
						view.append($('<li><a href="#page-'+i+'" data-transition="slide">Page ' + i + '</a></li>'));
					}

					if (!isMobile()) {
						$("#nav-panel").append(view);
					} else {
						$("#nav-page").append(view);
					}

					view.listview();
					hideLoader();
				},
				error: function (data) {
					$('body').html(data);
				}
			});

			$("[data-role='header'], [data-role='footer']").toolbar({theme: "a"});
			$("body>[data-role='panel']").panel();
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
	<!--
	<div id="page-number" data-role="controlgroup" data-type="horizontal" class="ui-btn-left ui-group-theme-b">
		<a class="ui-btn ui-icon-carat-l ui-corner-all ui-btn-icon-notext" id="nav-btn-prev">Previous</a><!--
	 -- <label for="page-select" class="ui-hidden-accessible">Page</label><!--
	 -- <select name="page-select" id="page-select" data-native-menu="false" data-theme="b">
			<option>Page 1</option>
		</select><!--
	 -- <a class="ui-btn ui-icon-carat-r ui-corner-all ui-btn-icon-notext" id="nav-btn-next">Next</a>
	</div>
	<div id="controls-right" data-role="controlgroup" data-type="horizontal" class="ui-btn-right ui-group-theme-a ui-mobile-safe">
		<a href="search.html" rel="external" class="ui-btn ui-btn-icon-right ui-icon-search ui-corner-all">Search</a>
		<a href="#" onclick="printPage()" class="ui-btn ui-btn-icon-right ui-icon-printer ui-corner-all">Print</a>
	</div>
	<h1>&nbsp;</h1>-->
	<h1 class="ui-title" id="page-number">Page 1</h1>
	<a href="#" data-role="button" class="no-background ui-btn ui-icon-gear ui-nodisc-icon ui-alt-icon ui-mobile-safe ui-btn-right ui-btn-icon-right">Menu</a>
</div>
<div data-role="page"></div>
<!--<div data-role="footer" data-position="fixed" id="footer" data-tap-toggle="false">
	<div data-role="controlgroup" data-type="horizontal" class="footer-button-left ui-group-theme-a ui-mobile-safe">
		<a href="#" class="ui-btn ui-btn-icon-right ui-icon-plus ui-corner-all">New Movie</a>
		<a href="#" class="ui-btn ui-btn-icon-right ui-icon-edit ui-corner-all ui-state-disabled">Edit</a>
		<a href="#" class="ui-btn ui-btn-icon-right ui-icon-delete ui-corner-all ui-state-disabled">Delete</a>
	</div>
	<h1>&nbsp;</h1>
</div>-->
</body>
</html>