<?php
require_once __DIR__ . '/../vendor/autoload.php';
include_once 'functions.php';

sec_session_start();

// Add a header indicating this is an OAuth server
header('X-XRDS-Location: http://' . $_SERVER['SERVER_NAME'] .
    '/services.xrds.php');

// Connect to database
$oauth_db = new mysqli(HOST, USER, PASSWORD, 'oauth');

// Create a new instance of OAuthStore and OAuthServer
$store = OAuthStore::instance('MySQLi', array('conn' => $oauth_db));
$server = new OAuthServer();