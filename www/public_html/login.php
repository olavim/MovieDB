<?php
include_once '../include/LoginProcessor.php';
include_once '../include/db_connect.php';
include_once '../include/functions.php';
use Login\LoginProcessor;

sec_session_start();

if (isset($_POST['username'], $_POST['p'])) {
    $processor = new LoginProcessor($_POST['username'], $_POST['p'], $mysqli);
    $processor->login();
}

if (login_check($mysqli) != true) {
	$path = dirname($_SERVER['SCRIPT_NAME']);
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="http://fonts.googleapis.com/css?family=Roboto:100" rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="<?=$path?>/styles/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="<?=$path?>/styles/global.css">
    <link rel="stylesheet" href="<?=$path?>/styles/default.css">
    <link rel="stylesheet" href="<?=$path?>/styles/form.css">
    <script src="<?=$path?>/js/jquery-2.1.4.min.js"></script>
    <script src="<?=$path?>/js/jquery.mobile-1.4.5.min.js"></script>
    <script src="<?=$path?>/js/sha512.js"></script>
    <script src="<?=$path?>/js/forms.js"></script>
</head>
<body>
<div data-role="page">
    <div data-role="content">
        <div class="form input-form">
            <h1>Log-in</h1>
            <form method="post" action="./" data-ajax="false">
                <div class="input-section input-section-text">
                    <input type="text" id="username" name="username" placeholder="Username">
                </div>
                <div class="input-section input-section-text">
                    <input type="password" id="password" name="password" placeholder="Password">
                </div>
                <div>
                    <button class="ui-button" data-role="none" onclick="formhash(this.form, this.form.password);">log in</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<?php exit(); } ?>
