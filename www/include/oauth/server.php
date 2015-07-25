<?php
namespace OAuth2;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../psl-config.php';

Autoloader::register();

class ServerInstance
{
    private static $instance;

    private $server;

    private function __construct()
    {
        $dsn      = 'mysql:dbname='.OAUTH_DATABASE.';host='.HOST;
        $username = USER;
        $password = PASSWORD;

        $storage = new Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));

        $this->server = new Server($storage);
        $this->server->addGrantType(new GrantType\ClientCredentials($storage));
        $this->server->addGrantType(new GrantType\AuthorizationCode($storage));
    }

    public static function getServer()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance->server;
    }
}