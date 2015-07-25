<?php
require_once '../vendor/autoload.php';
require_once 'login.php';
include_once 'sql.php';

$id = $_SESSION['user_id'];
$mysqli = DBFactory::getConnection(DBFactory::CONNECTION_MAIN_DATABASE);
$db = new MysqliDb($mysqli);
$db->where("user_id", $id);
$user_columns = $db->get("entry_columns", null, "column_name");
?>
<!doctype html>
<html>
<head>
    <title>Movie Database - New Movie</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="//fonts.googleapis.com/css?family=Roboto:100" rel='stylesheet'>
    <link href='//fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet'>
    <link rel="stylesheet" href="styles/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="styles/default.scss">
    <link rel="stylesheet" href="styles/form.css">
    <link rel="stylesheet" href="styles/global.css">
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/jquery.mobile-1.4.5.min.js"></script>
    <script src="js/jquery.nicefileinput.min.js"></script>
    <script>
        $(function() {
            $("#submit-btn").click(function() {
                $.post("api/entries", $("#form").serialize(), function(data) {
                    console.log(data);
                }).fail(function(data) {
                    console.log(data);
                });;
            });
        });
    </script>
</head>
<body>
<div class="form input-form">
    <h1>New Movie</h1>
    <form id="form">
        <?php
        foreach ($user_columns as $column) {
            $name = $column['column_name'];

            echo "" .
                "<div class='input-section input-section-text'>" .
                "<label for='$name'>" . strtoupper($name) . "</label>" .
                "<input type='text' id='$name' name='$name'>" .
                "</div>";
        }
        ?>
        <div class="input-section">
            <label for="picked">Picked</label>
            <input type="checkbox" id="picked" name="picked" data.mini="true">
        </div>
    </form>
    <div style="overflow:hidden">
        <button class="ui-button left" data-role="none" onclick="location.href='./'" style="width:45%">Cancel</button>
        <button class="ui-button right" data-role="none" style="width:45%" id="submit-btn">Submit</button>
    </div>
</div>
</body>
</html>