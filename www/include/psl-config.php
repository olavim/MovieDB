<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/../secure.php';
/**
 * These are the database login details
 */  
define("HOST", "localhost");     // The host you want to connect to.
define("USER", "sec_user");    // The database username. 
define("DATABASE", "secure_login");    // The database name.
 
define("CAN_REGISTER", "any");
define("DEFAULT_ROLE", "member");
 
define("SECURE", FALSE);    // FOR DEVELOPMENT ONLY!!!!