<?php
include_once 'include/db_connect.php';
include_once 'include/functions.php';

sec_session_start();

if (login_check($mysqli) != true) {
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles/jquery.mobile-1.4.5.min.css">
    <script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="js/jquery.mobile-1.4.5.min.js"></script>
</head>
<body>
    <div data-role="page">
        <div data-role="header" data-position="fixed">
            <h1>Login</h1>
        </div>
        <div data-role="content">

        </div>
    </div>
</body>
</html>
<?php exit(); } ?>