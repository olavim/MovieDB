<?php
require_once '../../vendor/autoload.php';
require_once '../../include/functions.php';
require_once '../../lib/rest/Request.php';
require_once '../../lib/rest/Response.php';
require_once '../../lib/DBFactory.php';

sec_session_start();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
Flight::set('self', $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));

Flight::route('/', function() {
    REST\Response::send_response(REST\StatusCodes::HTTP_OK, array(
        'links' => array(
            array(
                'href' => Flight::get('self'),
                'rel' => 'self',
                'method' => 'GET'
            ),
            array(
                'href' => Flight::get('self') . '/entries',
                'rel' => 'list_all',
                'method' => 'GET'
            ),
            array(
                'href' => Flight::get('self') . '/entries',
                'rel' => 'insert',
                'method' => 'POST'
            )
        )
    ));
});

Flight::route('/token', function() {
    include_once '../../include/oauth/token.php';
});

Flight::route('/authorize', function() {
    include_once '../../include/oauth/authorize.php';
});

Flight::route('/entries/*', function() {
    Flight::set('mysqli', Database\DBFactory::getConnection(Database\DBFactory::CONNECTION_MAIN_DATABASE));
    return true;
});

Flight::route('GET /entries(/@id:[0-9\;]+)', function($entry_ids) {
    $entry_ids = get_entry_ids($entry_ids);
    if (!$entry_ids) {
        REST\Response::set('links', array(
            array(
                'href' => Flight::get('self') . '/entries',
                'requestBody' => array(
                    'search' => 's_{column}={value}&...',
                    'sort' => 'sort={column}&order=[asc|desc]',
                ),
                'rel' => 'self',
                'method' => 'GET'
            ),
            array(
                'href' => Flight::get('self') . '/entries',
                'requestBody' => '{column}={value}&...',
                'rel' => 'insert',
                'method' => 'POST'
            ),
            array(
                'href' => Flight::get('self') . '/entries/{id}',
                'requestBody' => '{column}={value}&...',
                'rel' => 'update',
                'method' => 'PUT'
            ),
            array(
                'href' => Flight::get('self') . '/entries/{id}',
                'rel' => 'delete',
                'method' => 'DELETE'
            )
        ));
    } else if (count($entry_ids) == 1) {
        REST\Response::append(array('entries',0,'links'), array(
            array(
                'href' => Flight::get('self') . '/entries/' . $entry_ids[0],
                'rel' => 'self',
                'method' => 'GET'
            ),
            array(
                'href' => Flight::get('self') . '/entries/' . $entry_ids[0],
                'requestBody' => '{column}={value}&...',
                'rel' => 'update',
                'method' => 'PUT'
            ),
            array(
                'href' => Flight::get('self') . '/entries/' . $entry_ids[0],
                'rel' => 'delete',
                'method' => 'DELETE'
            )
        ));
    } else if (count($entry_ids) > 1) {
        foreach ($entry_ids as $key => $entry) {
            REST\Response::append(array('entries',$key,'links'), array(
                array(
                    'href' => Flight::get('self') . '/entries/' . $entry,
                    'rel' => 'show',
                    'method' => 'GET'
                )
            ));
        }

        REST\Response::set('links', array(
                array(
                    'href' => Flight::get('self') . '/entries/{id}',
                    'requestBody' => '{column}={value}&...',
                    'rel' => 'update',
                    'method' => 'PUT'
                ),
                array(
                    'href' => Flight::get('self') . '/entries/{id}',
                    'rel' => 'delete',
                    'method' => 'DELETE'
                )
        ));
    }

    $restRequest = new REST\Request(Flight::get('mysqli'), 'GET', Flight::request()->query);
    $restRequest->execute($entry_ids);
});

Flight::route('PUT /entries/@id:[0-9\;]+', function($entry_ids) {
    $restRequest = new REST\Request(Flight::get('mysqli'), 'PUT', Flight::request()->data);
    $restRequest->execute(get_entry_ids($entry_ids));
});

Flight::route('POST /entries', function() {
    $restRequest = new REST\Request(Flight::get('mysqli'), 'POST', Flight::request()->data);
    $restRequest->execute();
});

Flight::route('DELETE /entries/@id:[0-9\;]+', function($entry_ids) {
    $restRequest = new REST\Request(Flight::get('mysqli'), 'DELETE', Flight::request()->data);
    $restRequest->execute(get_entry_ids($entry_ids));
});

Flight::start();

function get_entry_ids($entry_ids) {
    return $entry_ids ? explode(';', $entry_ids) : array();
}