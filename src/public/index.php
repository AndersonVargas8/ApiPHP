<?php
require('../vendor/autoload.php');

use Config\Headers;
use Dotenv\Dotenv;
use Exception\AuthenticationException;
use Exception\RouteNotFoundException;
use Http\Response;
use Http\Router;
use Routes\Api;

/*+--------------------+
* | Set server headers |
* +--------------------+*/
include('../config/headersConfig.php');

/*+---------------------+
* | Set security config |
* +---------------------+*/
include('../config/securityConfig.php');

/*+--------------------------------+
* | Read the environment variables |
* +--------------------------------+*/
$dotenv = Dotenv::createImmutable('../../');
$dotenv->load();


/*+-----------------------------------------------------------------------------+
* | Create a new Router with the entry point defined on the environment variables |
* +-----------------------------------------------------------------------------+*/
$router = new Router($_ENV['API_ENTRY_POINT']);

/* +-------------------------------------------------+
* | Create a new Api with the Router created before |
* +-------------------------------------------------+*/
try {
    $api = new Api($router);
} catch (Exception $e) {
    echo $e->getMessage();
}

/*+---------------------------------------------------------+
* | Execute the run action of the Router to listen requests |
* +---------------------------------------------------------+*/
try {
    $router->run();
} catch (RouteNotFoundException $e) {
    Response::json(["message" => $e->getMessage()], Response::HTTP_NOT_FOUND);
} catch (AuthenticationException $e) {
    Response::json(["message" => $e->getMessage()], $e->getCode());
} catch (Exception $e) {
    Response::json(["message" => $e->getMessage()], Response::HTTP_BAD_REQUEST);
}


