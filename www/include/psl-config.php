<?php
include_once __DIR__ . '/../secure.php';
/**
 * These are the database login details
 */  
define("HOST", "localhost");     // The host you want to connect to.
define("USER", "sec_user");      // The database username.
define("USER_DATABASE", "secure_login");
define("OAUTH_DATABASE", "oauth");
define("MAIN_DATABASE", "moviedb");
 
define("CAN_REGISTER", "any");
define("DEFAULT_ROLE", "member");
 
define("SECURE", TRUE);    // FOR DEVELOPMENT ONLY!!!!