<?php
require_once 'server.php';

$request = OAuth2\Request::createFromGlobals();
$response = new OAuth2\Response();

$server = Oauth2\ServerInstance::getServer();

// validate the authorize request
if (!$server->validateAuthorizeRequest($request, $response)) {
    $response->send();
    die;
}

// display an authorization form
if (empty($_POST)) {
    exit;
}

// print the authorization code if the user has authorized your client
$server->handleAuthorizeRequest($request, $response, true);
$response->send();