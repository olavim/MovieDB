<?php
namespace REST;

require_once __DIR__ . '/../../include/oauth/server.php';
require_once 'Response.php';
require_once __DIR__ . '/../DBFactory.php';

abstract class RequestAbstract
{
    protected $verb;
    protected $requestBody;
    protected $acceptType;
    protected $user_id;
    protected $response;

    protected $searchParams;

    private $authorized;

    public function __construct($verb, $requestBody)
    {
        $this->verb = $verb;
        $this->requestBody = $requestBody;
        $this->acceptType = 'application/json';
        $this->searchParams = array();

        foreach ($requestBody as $key => $value) {
            if ($this->isSearchParam($key)) {
                $key = substr($key, 2);
                $this->searchParams[$key] = $value;
            }
        }

        if (($id = $this->verifyRequestAuthorization()) !== false) {
            $this->user_id = $id;
            $this->authorized = true;
        } else {
            $this->authorized = false;
        }
    }

    public function execute(array $params = null) {
        if (!$this->authorized) {
            Response::set('message', $this->response);
            Response::send_response(StatusCodes::HTTP_UNAUTHORIZED);
        }

        switch (strtoupper($this->verb)) {
            case 'GET':
                $code = $this->getEntries($params);
                break;
            case 'POST':
                $code = $this->addEntry();
                break;
            case 'PUT':
                $code = $this->updateEntries($params);
                break;
            case 'DELETE':
                $code = $this->deleteEntries($params);
                break;
            default:
                $code = StatusCodes::HTTP_METHOD_NOT_ALLOWED;
        }

        Response::send_response($code);
    }

    protected function verifyRequestAuthorization() {
        $server = \OAuth2\ServerInstance::getServer();
        $request = \OAuth2\Request::createFromGlobals();

        // Handle a request to a resource and authenticate the access token
        if ($server->verifyResourceRequest($request)) {
            $token = $server->getAccessTokenData($request);
            return $token['user_id'];
        } else if (isset($_REQUEST['access_token'])) {
            $this->response[] = 'Invalid access token';
        }

        $mysqli = \Database\DBFactory::getConnection(\Database\DBFactory::CONNECTION_USER_DATABASE);
        if (login_check($mysqli)) {
            if (!isset($_REQUEST['access_token'])) {
                return $_SESSION['user_id'];
            } else {
                $this->response[] = 'You have a valid ongoing session.'
                                  . 'To authorize as that user, please omit the access token.';
            }
        }

        return false;
    }

    /**
     * @return int
     */
    protected abstract function addEntry();

    /**
     * @param array $params
     * @return int
     */
    protected abstract function updateEntries(array $params);

    /**
     * @param array $params
     * @return int
     */
    protected abstract function deleteEntries(array $params);

    /**
     * @param array $params
     * @return int
     */
    protected abstract function getEntries(array $params);

    private function isSearchParam($haystack) {
        return strrpos($haystack, 's_', -strlen($haystack)) !== FALSE;
    }
}