<?php
include_once '../secure.php';
/**
 * These are the database login details
 */  
define("HOST", "localhost");     // The host you want to connect to.
define("USER", "sec_user");      // The database username.
define("USER_DATABASE", "secure_login");
 
define("CAN_REGISTER", "any");
define("DEFAULT_ROLE", "member");
 
define("SECURE", FALSE);    // FOR DEVELOPMENT ONLY!!!!