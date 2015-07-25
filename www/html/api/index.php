<?php
require_once '../../vendor/autoload.php';
require_once '../../include/functions.php';
require_once '../../include/StatusCodes.php';
require_once '../../include/Response.php';
require_once '../../include/oauth/server.php';
require_once '../../include/api.inc.php';

sec_session_start();

Flight::route('/', function() {
});

Flight::route('/token', function() {
    include_once '../../include/oauth/token.php';
});

Flight::route('/authorize', function() {
    include_once '../../include/oauth/authorize.php';
});

Flight::route('GET /entries', function() {
    if (($id = verify_request()) !== false) {
        $result = get_entries($id, Flight::request()->query);
        Response::send_response(StatusCodes::HTTP_OK, get_array($result));
    } else {
        Response::send_response(StatusCodes::HTTP_UNAUTHORIZED);
    }
});

Flight::route('GET /entries/@id:[0-9]+', function($entry_id) {
    if (($id = verify_request()) !== false) {
        if ($result = get_entry($id, $entry_id)) {
            Response::send_response(get_array($result));
        }
    } else {
        Response::send_response(StatusCodes::HTTP_UNAUTHORIZED);
    }
});

Flight::route('POST /entries', function() {
    if (($id = verify_request()) !== false) {
        add_entry($id, Flight::request()->data);
        Response::send_response(StatusCodes::HTTP_OK);
    } else {
        Response::send_response(StatusCodes::HTTP_UNAUTHORIZED);
    }
});

Flight::route('PUT /entries/@id:[0-9\;]+', function($entry_ids) {
    if (($id = verify_request()) !== false) {
        $entry_ids = explode(';', $entry_ids);

        $updated = update_entry($id, $entry_ids, Flight::request()->data);

        if ($updated === false) {

            // entry does not exist
            Response::send_response(StatusCodes::HTTP_NOT_FOUND);
        } else if ($updated > 0) {

            // query was successful, and entry was updated
            Response::send_response(StatusCodes::HTTP_NO_CONTENT);
        } else {

            // query was successful, but nothing was updated
            Response::send_response(StatusCodes::HTTP_OK);
        }
    } else {
        Response::send_response(StatusCodes::HTTP_UNAUTHORIZED);
    }
});

Flight::route('DELETE /entries/@id:[0-9\;]+', function($entry_ids) {
    if (($id = verify_request()) !== false) {
        $entry_ids = explode(';', $entry_ids);

        $deleted = delete_entries($id, $entry_ids, Flight::request()->data);

        if ($deleted === false) {

            // entry does not exist
            Response::send_response(StatusCodes::HTTP_NOT_FOUND);
        } else {

            // query was successful, and entry was updated
            Response::send_response(StatusCodes::HTTP_NO_CONTENT);
        }
    } else {
        Response::send_response(StatusCodes::HTTP_UNAUTHORIZED);
    }
});

Flight::map('error', function(Exception $ex){
    $error = array(
        'internal_error_message' => $ex->getMessage(),
        'internal_error_line' => $ex->getLine()
    );

    Response::set('internal_error', $error);
    Response::send_response(StatusCodes::HTTP_INTERNAL_SERVER_ERROR);
});

Flight::start();

function verify_request() {
    $response = array();
    $server = Oauth2\ServerInstance::getServer();

    // Handle a request to a resource and authenticate the access token
    if ($server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
        $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
        return $token['user_id'];
    } else if (isset($_REQUEST['access_token'])) {
        $response[] = 'Invalid access token';
    }

    $mysqli = DBFactory::getConnection(DBFactory::CONNECTION_USER_DATABASE);
    if (login_check($mysqli)) {
        if (!isset(Flight::request()->query['access_token'])) {
            return $_SESSION['user_id'];
        } else {
            $response[] = 'You have a valid ongoing session. To authorize as that user, please omit the access token.';
        }
    }

    if ($response) {
        Response::set('response', $response);
    }

    return false;
}