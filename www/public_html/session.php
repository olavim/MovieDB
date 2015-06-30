<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/../include/db_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/../include/functions.php';

sec_session_start();

function set_session($s)
{
    if (isset($_GET[$s])) {
        $_SESSION[$s] = $_GET[$s];
    }
}

set_session('order');
set_session('dir');