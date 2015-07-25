<?php
include_once '../include/register.inc.php';
include_once '../include/functions.php';
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="//fonts.googleapis.com/css?family=Roboto:100" rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="styles/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="styles/global.css">
    <link rel="stylesheet" href="styles/default.css">
    <link rel="stylesheet" href="styles/form.css">
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/jquery.mobile-1.4.5.min.js"></script>
    <script src="js/sha512.js"></script>
    <script src="js/forms.js"></script>
</head>
<body>
<div data-role="page">
    <div data-role="content">
        <div class="form input-form">
            <h1>Register</h1>
            <?php
            if (!empty($error_msg)) {
                echo $error_msg;
            }
            ?>
            <form method="post" action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" data-ajax="false" name="registration_form">
                <div class="input-section input-section-text">
                    <label for="username">USERNAME</label>
                    <input type="text" id="username" name="username">
                </div>
                <div class="input-section input-section-text">
                    <label for="email">E-MAIL</label>
                    <input type="text" id="email" name="email">
                </div>
                <div class="input-section input-section-text">
                    <label for="password">PASSWORD</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="input-section input-section-text">
                    <label for="confirmpwd">CONFIRM PASSWORD</label>
                    <input type="password" id="confirmpwd" name="confirmpwd">
                </div>
                <div>
                    <button class="ui-button" data-role="none"
                            onclick="return regformhash(this.form,
                                                        this.form.username,
                                                        this.form.email,
                                                        this.form.password,
                                                        this.form.confirmpwd);">register</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
