<?php
if (isset($_GET['logout'])) {
    include_once '../include/logout.php';
}

require_once 'login.php';
include_once '../config/conf.php';

function get($s) {
	return isset($_GET[$s]) ? $_GET[$s] : "";
}

function session($s, $default = "") {
	return isset($_SESSION[$s]) && $_SESSION[$s] ? $_SESSION[$s] : $default;
}

$order_by 		 = session('order', $db_headings[0]);
$order_direction = session('dir', "asc");

$search = array();
foreach ($db_headings_searchable as $heading) {
    $search[] = $heading . '=' . get('s_'.$heading);
}
?>
<!doctype html>
<html>
<head>
	<title>Movie Database</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="styles/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="styles/jquery.jtable.css">
	<link rel="stylesheet" href="styles/default.css">
    <link rel="stylesheet" href="styles/global.css">
	<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="js/jquery.mobile-1.4.5.min.js"></script>
	<script type="text/javascript" src="js/jquery.toggle-action-1.0.js"></script>
	<script type="text/javascript" src="js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="js/jquery.mobile.jtable.js"></script>
	<script type="text/javascript" src="js/table.js"></script>
	<script type="text/javascript" src="js/navigation.js"></script>
	<script type="text/javascript" src="js/main.js"></script>
    <script>
        $(function () {
            MAIN.init();
        });

        $(document).one('pageinit', function() {
            if (!isMobile()) {
                $("#nav-button").attr("href", "#nav-panel");
                $("body").data("elements-per-page", <?=isset($_SESSION['elementsPerPage']) ? $_SESSION['elementsPerPage'] : 50?>);
            } else {
                $("body").data("elements-per-page", <?=isset($_SESSION['elementsPerPage']) ? $_SESSION['elementsPerPage'] : 20?>);
            }

            jsonAjaxCall("api/entries", "GET", {
                order: "<?=$order_by?>",
                dir: "<?=$order_direction?>",
                search: "<?=join('&', $search)?>"
            }, function (data) {
                initTable(data);
            });
        });

        function initTable(data) {
            data = data['entries'];
            if (!data) {
                data = "[]";
            }

            if (typeof data !== 'object') {
                data = JSON.parse(data);
            }

            var body = $('body');
            body.jqmData('json', data);
            body.jtable(data, {
                headings: "<?=join(',', $db_headings)?>",
                orderBy: "<?=$order_by?>",
                asc: <?=$order_direction == "asc" ? "true" : "false"?>,
                elementsPerPage: body.data("elements-per-page")
            });

            refreshNavigation();
            $(":mobile-pagecontainer").pagecontainer("change", "#page-1", {transition: "fade"});
        }

        function printPage() {
            $("#pdf-page").find(".ui-content").html('<iframe id="pdf-object" src="create_pdf.php?<?=join('&', $search)?>" style="height:inherit;width:100%;border:none;margin:0;padding:0"></iframe>');
            navnext($("#pdf-page"));
            $.mobile.loading("show");
            $("#pdf-object").load(function() {
                $.mobile.loading("hide");
            })
        }
    </script>
</head>
<body>
<input id="order-by" type="hidden" name="order_by" value="<?=$order_by?>">
<input id="order-direction" type="hidden" name="order_direction" value="<?=$order_direction?>">
<div data-role="header" data-position="fixed" data-tap-toggle="false">
    <a class="no-background ui-btn ui-icon-bars ui-nodisc-icon ui-alt-icon ui-mobile-safe ui-btn-left ui-btn-icon-left" id="nav-button" href="#" data-role="button">Navigation</a>
    <h1 class="ui-title" id="page-number">Page 1</h1>
    <a class="no-background ui-btn ui-icon-dots ui-nodisc-icon ui-alt-icon ui-mobile-safe ui-btn-right ui-btn-icon-right" href="#menu-popup" data-rel="popup" data-role="button" >Menu</a>
</div>
<div id="modify-footer" data-role="footer" data-position="fixed" data-tap-toggle="false">
    <a class="no-background ui-btn ui-alt-icon ui-btn-icon-left" id="edit-btn" href="#" data-role="button">Edit</a><!--
 --><a class="no-background ui-btn ui-alt-icon ui-btn-icon-left" id="delete-btn" href="#delete-popup" data-rel="popup" data-position-to="window" data-transition="pop" data-role="button">Delete</a><!--
 --><a class="no-background ui-btn ui-alt-icon ui-btn-icon-left" id="pick-btn" href="#" data-role="button">Pick</a>
</div>
<div id="edit-footer" data-role="footer" data-position="fixed" data-tap-toggle="false">
    <a class="no-background ui-btn ui-alt-icon ui-btn-icon-left" id="edit-cancel-btn" href="#" data-role="button">Cancel</a><!--
 --><a class="no-background ui-btn ui-alt-icon ui-btn-icon-left" id="edit-save-btn" href="#" data-role="button">Save Changes</a>
</div>
<div data-role="popup" id="delete-popup" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;">
    <div data-role="header" data-theme="a">
        <h1>Confirm Delete</h1>
        </div>
    <div role="main" class="ui-content">
        <h3 class="ui-title">Are you sure you want to delete this row?</h3>
        <p>This action cannot be undone.</p>
        <a class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" href="#" data-rel="back">Cancel</a>
        <a class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" href="#" id="delete-confirm-btn" data-transition="flow" data-rel="back">Delete</a>
    </div>
</div>
<div class="ui-popup ui-body-a ui-overlay-shadow ui-corner-all" data-role="popup" id="menu-popup" data-theme="b">
	<ul class="ui-listview" data-role="listview">
		<li><a href="search.php" rel="external">Search</a></li>
		<li><a href="#" onclick="printPage()">Print</a></li>
		<li data-role="list-divider"></li>
        <li><a id="add-btn" onclick="$('#menu-popup').popup('close')">Add Movie</a></li>
        <li><a href="import.php" rel="external">Import</a></li>
		<li data-role="list-divider"></li>
		<li><a href="?logout=1" rel="external">Log out</a></li>
	</ul>
</div>
<div class="ui-responsive-panel" id="nav-panel" data-role="panel" title="Navigation" data-display="overlay" data-position="left" data-theme="b" data-position-fixed="true"></div>
<div class="ui-content" id="pdf-page" data-role="page" title="Print"><div role="main"></div></div>
<div id="nav-page" data-role="page" data-theme="a" data-next="#page-1"></div>
</body>
</html>
