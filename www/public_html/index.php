﻿<?php
require_once '../check_login.php';

function get($s) {
	return isset($_GET[$s]) ? $_GET[$s] : "";
}

function session($s, $default = "") {
	return isset($_SESSION[$s]) && $_SESSION[$s] ? $_SESSION[$s] : $default;
}

if (isset($_GET['logout'])) {
	include_once '../include/logout.php';
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
	<link rel="stylesheet" href="styles/global.css">
	<link rel="stylesheet" href="styles/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="styles/default.css">
	<link rel="stylesheet" href="styles/jtable.css">
	<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="js/jquery.mobile-1.4.5.min.js"></script>
	<script type="text/javascript" src="js/jquery.toggle-action-1.0.js"></script>
	<script type="text/javascript" src="js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="js/jtable.js"></script>
	<script type="text/javascript" src="js/table.js"></script>
	<script type="text/javascript" src="js/navigation.js"></script>
	<script type="text/javascript">
		var jsonData;

		$(function () {
			$("[data-role='header'], [data-role='footer']").toolbar({theme: "a"});
            $("div:jqmData(role='footer')").hide();
			$("#menu-popup").enhanceWithin().popup();
			$("body>[data-role='panel']").panel();
		});

		$(document).one('pageinit', function() {
			if (!isMobile()) {
				$("#nav-button").attr("href", "#nav-panel");
			}

            $.ajax({
                url: "json_table.php",
                type: "get",
                contentType: 'application/json',
                dataType: 'html',
                data: {
                    select: "id,director,year,title,pick",
                    order: "<?=$order_by?>",
                    dir: "<?=$order_direction?>",
                    search: ",<?=$s_director.','.$s_year.','.$s_title.','.$s_pick?>"
                },
                beforeSend: function () {
                    showLoader();
                },
                success: function (data) {
                    hideLoader();
                    initTable(data);
                },
                error: function (data) {
                    $('body').html(data);
                }
            });
		});

        $(document).on("click", "#pick-btn", function() {
            $(".selected").toggleClass("picked");
            $.ajax({
                url: "set_picked.php",
                type: "get",
                data: {
                    id: $(".selected").jqmData("id"),
                    state: $(".selected").is(".picked") ? "on" : "off"
                }
            });
        });

        $(document).on("click", "#delete-btn", function() {
            var id = $(".selected").jqmData("id");
            location.href="delete_entry.php?id=" + id;
        });

        $(document).on("click", "#edit-btn", function() {
            var id = $(".selected").jqmData("id");
            location.href="edit_entry.php?id=" + id;
        });

        function initTable(data) {
            if (!data) {
                data = "[]"
            }

            jsonData = jQuery.parseJSON(data);

            $('body').jtable(jsonData, {
                orderBy: "<?=$order_by?>",
                asc: <?=$order_direction == "asc" ? "true" : "false"?>
            });

            var view = $('<ul data-role="listview"></ul>');
            for (var i = 1; i <= Math.ceil(jsonData.length / 20); i++) {
                view.append($('<li><a href="#page-' + i + '" data-transition="slide">Page ' + i + '</a></li>'));
            }

            if (!isMobile()) {
                $("#nav-panel").append(view);
            } else {
                $("#nav-page").append(view);
            }

            view.listview();
            $(":mobile-pagecontainer").pagecontainer("change", "#page-1", {transition: "fade"});
        }

		function printPage() {
			$("#pdf-page").find(".ui-content").html('<iframe id="pdf-object" src="create_pdf.php?<?="director=$s_director&year=$s_year&title=$s_title&pick=$s_pick"?>" style="height:inherit;width:100%;border:none;margin:0;padding:0"></iframe>');
			navnext($("#pdf-page"));
			$.mobile.loading("show");
			$("#pdf-object").load(function() {
				$.mobile.loading("hide");
			})
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
    <a href="#" id="nav-button" data-role="button" class="no-background ui-btn ui-icon-bars ui-nodisc-icon ui-alt-icon ui-mobile-safe ui-btn-left ui-btn-icon-left">Navigation</a>
    <h1 class="ui-title" id="page-number">Page 1</h1>
    <a href="#menu-popup" data-rel="popup" data-role="button" class="no-background ui-btn ui-icon-dots ui-nodisc-icon ui-alt-icon ui-mobile-safe ui-btn-right ui-btn-icon-right">Menu</a>
</div>
<div data-role="footer" data-position="fixed" data-tap-toggle="false">
    <a href="#" id="edit-btn" data-role="button" class="no-background ui-btn ui-alt-icon ui-btn-icon-left">Edit</a><!--
 --><a href="#" id="delete-btn" data-role="button" class="no-background ui-btn ui-alt-icon ui-btn-icon-left">Delete</a><!--
 --><a href="#" id="pick-btn" data-role="button" class="no-background ui-btn ui-alt-icon ui-btn-icon-left">Pick</a>
</div>
<div id="menu-popup" data-theme="b" class="ui-popup ui-body-a ui-overlay-shadow ui-corner-all">
	<ul data-role="listview" class="ui-listview">
		<li><a href="search.html" rel="external">Search</a></li>
		<li><a href="#" onclick="printPage()">Print</a></li>
		<li data-role="list-divider"></li>
		<li><a href="new_entry.php" rel="external">Add Movie</a></li>
		<li data-role="list-divider"></li>
		<li><a href="?logout=1" rel="external">Log out</a></li>
	</ul>
</div>
<div data-role="panel" id="nav-panel" title="Navigation" data-display="overlay" data-position="left" data-theme="b" data-position-fixed="true" class="ui-responsive-panel"></div>
<div data-role="page" id="pdf-page" title="Print"><div role="main" class="ui-content"></div></div>
<div data-role="page" data-theme="a" id="nav-page" data-next="#page-1"></div>
</body>
</html>