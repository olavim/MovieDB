<?php
namespace Database;

require_once __DIR__ . '/../include/psl-config.php';

class DBFactory
{

    const CONNECTION_USER_DATABASE = 0;
    const CONNECTION_OAUTH_DATABASE = 1;
    const CONNECTION_MAIN_DATABASE = 2;

    private static $connection_methods = array(
        'connUserDB',
        'connOauthDB',
        'connMainDB'
    );

    public static function getConnection($method, $charset = 'utf8')
    {
        if (!isset(self::$connection_methods[$method])) {
            throw new \Exception('Undefined function: ' . $method);
        }

        $method = self::$connection_methods[$method];
        $mysqli = self::$method();
        $mysqli->set_charset($charset);
        return $mysqli;
    }

    private static function connUserDB() {
        return self::conn(USER_DATABASE);
    }

    private static function connOauthDB() {
        return self::conn(OAUTH_DATABASE);
    }

    private static function connMainDB() {
        return self::conn(MAIN_DATABASE);
    }

    private static function conn($db) {
        return new \mysqli(HOST, USER, PASSWORD, $db);
    }
}
