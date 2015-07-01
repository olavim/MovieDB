# MovieDB
A sleek, mobile friendly web app intended to be used as a movie database.

---

The hierarchy assumes that the directory `public_html` is the document root. The directory `include` (outside document root) contains functions global to the whole website that are not supposed to be accessed by URL. Keep your sensitive data there.

---

I have omitted `secure.php` from my commits as it contains solely personal information. Right now that file contains my database user password and *secret* that is used to identify GitHub's webhooks. I use webhooks for workflow: every time I push a commit here, my webserver automatically pulls the changes without me having to do so separately.

The contents of the file are basically:

    <?php
    define("PASSWORD", <password>); // Database user password.
    define("SECRET", <secret>);     // GitHub pull secret.

---

The site requires logging in, though this can be easily changed if need be. All one has to do is include the following piece of code in the top of all the sites that shouldn't be accessible without proper credentials:

`require_once $_SERVER['DOCUMENT_ROOT'] . '/../check_login.php';`

It includes the file `check_login.php` file that is in the `www` directory, just outside document root. The file checks for the user's logged in -status, doing nothing if the user is logged in, and showing a login screen in case he/she is not. Note that if the login screen is displayed, the script exits; any code after including `check_login.php` will not be executed.