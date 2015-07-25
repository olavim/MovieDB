<?php
require_once 'server.php';

$server = Oauth2\ServerInstance::getServer();

// Handle a request for an OAuth2.0 Access Token and send the response to the client
$server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();