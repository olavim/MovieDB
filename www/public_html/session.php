<?php
require_once 'login.php';

function set_session($s) {
    if (isset($_GET[$s])) {
        $_SESSION[$s] = $_GET[$s];
    }
}

set_session('order');
set_session('dir');
set_session('elementsPerPage');