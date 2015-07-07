<?php
require_once 'login.php';

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
			$("[data-role='popup']").enhanceWithin().popup();
			$("body>[data-role='panel']").panel();

            $(window).bind('resize', function (event) {
                var content_height = $.mobile.activePage.children('[data-role="content"]').height(),
                    header_height  = $.mobile.activePage.children('[data-role="header"]').height(),
                    footer_height  = $.mobile.activePage.children('[data-role="footer"]').height(),
                    window_height  = $(this).height();

                if (content_height < (window_height - header_height - footer_height)) {
                    $.mobile.activePage.css('min-height', (content_height + header_height + footer_height));
                    setTimeout(function () {
                        $.mobile.activePage.children('[data-role="footer"]').css('top', 0);
                    }, 500);
                }
                event.stopImmediatePropagation();
            }).trigger('resize');
		});

		$(document).one('pageinit', function() {
			if (!isMobile()) {
				$("#nav-button").attr("href", "#nav-panel");
                $("body").data("elements-per-page", 50);
			} else {
                $("body").data("elements-per-page", 20);
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
            $(".selected").each(function() {
                $.ajax({
                    url: "set_picked.php",
                    type: "get",
                    data: {
                        id: $(this).jqmData("id"),
                        state: $(this).is(".picked") ? "on" : "off"
                    }
                });
            });
        });

        $(document).on("click", "#delete-btn-confirm", function() {
            var id = "";
            var numSelected = $(".selected").length;

            $(".selected").each(function(index) {
                id += $(this).jqmData("id");
                if (index < numSelected - 1) {
                    id += ",";
                }
            });

            window.location.href = 'delete_entry.php?id='+id;
        });

        $(document).on("click", "#edit-btn", function() {
            var id = $(".selected").jqmData("id");
            window.location.href="edit_entry.php?id=" + id;
        });

        $(document).on('change', ':jqmData(role="viewselect")', function () {
            $("body").data("elements-per-page", $(this).val());
            $("body").jtable("refresh", {
                elementsPerPage: $("body").data("elements-per-page")
            });
            $(":mobile-pagecontainer").pagecontainer("change", "#page-1", {transition: "none"});
            refreshNavigation();
            $(window).resize();
        });

        $(document).on('pagebeforeshow', '.ui-page', function() {
            $(this).find(':jqmData(role="viewselect")').selectmenu("refresh");
        });

        function initTable(data) {
            if (!data) {
                data = "[]"
            }

            jsonData = jQuery.parseJSON(data);

            $('body').jtable(jsonData, {
                orderBy: "<?=$order_by?>",
                asc: <?=$order_direction == "asc" ? "true" : "false"?>,
                elementsPerPage: $("body").data("elements-per-page")
            });

            refreshNavigation();
            $(":mobile-pagecontainer").pagecontainer("change", "#page-1", {transition: "fade"});
        }

        function refreshNavigation() {
            var view = $('<ul data-role="listview"></ul>');
            for (var i = 1; i <= Math.ceil(jsonData.length / $("body").data("elements-per-page")); i++) {
                view.append($('<li><a href="#page-' + i + '" data-transition="slide">Page ' + i + '</a></li>'));
            }

            if (!isMobile()) {
                $("#nav-panel").html(view);
            } else {
                $("#nav-page").html(view);
            }

            view.listview();
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
 --><a href="#delete-popup" data-rel="popup" data-position-to="window" data-transition="pop" data-role="button" id="delete-btn" class="no-background ui-btn ui-alt-icon ui-btn-icon-left">Delete</a><!--
 --><a href="#" id="pick-btn" data-role="button" class="no-background ui-btn ui-alt-icon ui-btn-icon-left">Pick</a>
</div>
<div data-role="popup" id="delete-popup" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;">
    <div data-role="header" data-theme="a">
        <h1>Confirm Delete</h1>
        </div>
    <div role="main" class="ui-content">
        <h3 class="ui-title">Are you sure you want to delete this row?</h3>
        <p>This action cannot be undone.</p>
        <a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back">Cancel</a>
        <a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-transition="flow" id="delete-btn-confirm">Delete</a>
    </div>
</div>
<div data-role="popup" id="menu-popup" data-theme="b" class="ui-popup ui-body-a ui-overlay-shadow ui-corner-all">
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