<?php

namespace Http;

use App\Services\AuthService;
use ArgumentCountError;
use Closure;
use Config\SystemConfig;
use Exception;
use Exception\AuthenticationException;
use Exception\RouteNotFoundException;
use TypeError;

class Router
{
    /**
     * The base path of the URL.
     *
     * @var string
     */
    private string $entry_point;

    /**
     * list of actions associated with the different endpoints.
     *
     * @var array|array[]
     */
    private array $methods = [
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
        'DELETE' => array(),
        'PATCH' => array()
    ];

    /**
     * @param $entry_point - It must start whit a '/' (e.g /api)
     */
    public function __construct($entry_point)
    {
        $this->entry_point = $entry_point;
    }

    /**
     * Set an action to a given method and uri.
     *
     * @param string $method
     * @param string $uri
     * @param array|Closure $action - In clase of array it must be in the format [className,'action']
     * @param string|null ...$roles - String with descriptions or names of roles with authority granted.
     * @return void
     * @throws Exception
     */
    private function setUriAction(string $method, string $uri, array|Closure $action, ?string ...$roles): void
    {
        $method = strtoupper($method);

        if ($action == null) {
            throw new Exception('The action value can not be null');
        }

        $temp = array(
            'uri' => $uri,
            'action' => $action,
            'grantedAuth' => $roles
        );

        /*+-----------------------------------------------------------------------------------------------+
        * | Se procesan las variables (ej: user/{id}) de la uri para convertirlas a expresiones regulares |
        * +-----------------------------------------------------------------------------------------------+*/
        $uri = preg_replace("#\{\w+}#", "(\w+)", $uri);

        $uri = '/' . trim($uri, '/');

        /*+---------------------------------------------+
        * | Se define la uri como una expresión regular |
        * +---------------------------------------------+*/
        $expr = '#^' . $uri . '/?$#';
        $this->methods[$method][$expr] = (object)$temp;
    }

    /**
     * Define a new route with the GET method.
     *
     * @param string $uri - It must start with '/' and the url variables must be between '{ }' (e.g /user/{id})
     * @param array|Closure|null $action - In clase of array it must be in the format [className,'action']
     * @param mixed ...$roles
     * @return void
     * @throws Exception
     */
    public function get(string $uri, array|Closure $action = null, ...$roles): void
    {
        $this->setUriAction('GET', $uri, $action, ...$roles);
    }

    /**
     * Define a new route with the POST method.
     *
     * @param string $uri - It must start with '/' and the url variables must be between '{ }' (e.g /user/{id})
     * @param array|Closure|null $action - In clase of array it must be in the format [className,'action']
     * @param mixed ...$roles
     * @return void
     * @throws Exception
     */
    public function post(string $uri, array|Closure $action = null, ...$roles): void
    {
        $this->setUriAction('POST', $uri, $action, ...$roles);
    }

    /**
     * Define a new route with the PUT method.
     *
     * @param string $uri - It must start with '/' and the url variables must be between '{ }' (e.g /user/{id})
     * @param array|Closure|null $action - In clase of array it must be in the format [className,'action']
     * @param mixed ...$roles
     * @return void
     * @throws Exception
     */
    public function put(string $uri, array|Closure $action = null, ...$roles): void
    {
        $this->setUriAction('PUT', $uri, $action, ...$roles);
    }

    /**
     * Define a new route with the DELETE method.
     *
     * @param string $uri - It must start with '/' and the url variables must be between '{ }' (e.g /user/{id})
     * @param array|Closure|null $action - In clase of array it must be in the format [className,'action']
     * @param mixed ...$roles
     * @return void
     * @throws Exception
     */
    public function delete(string $uri, array|Closure $action = null, ...$roles): void
    {
        $this->setUriAction('DELETE', $uri, $action, ...$roles);
    }

    /**
     * Define a new route with the PATCH method
     *
     * @param string $uri - It must start with '/' and the url variables must be between '{ }' (e.g /user/{id})
     * @param array|Closure|null $action - In clase of array it must be in the format [className,'action']
     * @param mixed ...$roles
     * @return void
     * @throws Exception
     */
    public function patch(string $uri, array|Closure $action = null, ...$roles): void
    {
        $this->setUriAction('PATCH', $uri, $action, ...$roles);
    }

    /**
     * Listen the received request and executes the associated action.
     *
     * @throws RouteNotFoundException
     * @throws AuthenticationException
     * @throws Exception
     */
    public function run(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!array_key_exists($method, $this->methods)) {
            if ($method == "OPTIONS") {
                Response::json([]);
                return;
            }
            throw new Exception('The method <' . $method . '> is not allowed');
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = str_replace($this->entry_point, "", $uri);

        $methodUris = array_keys($this->methods[$method]);

        foreach ($methodUris as $uriExpr) {
            $params = array();
            if (preg_match($uriExpr, $uri, $params)) {
                $roles = $this->methods[$method][$uriExpr]->{'grantedAuth'};

                /*+-----------------------------------------+
                * | Verify authentication and authorization |
                * +-----------------------------------------+*/
                if (!in_array($this->methods[$method][$uriExpr]->{'uri'}, AUTHORIZE_REQUESTS)) {//The request has auth restriction
                    if (!$this->verifyAuth())
                        throw new AuthenticationException('Debe iniciar sesión');

                    if (!$this->verifyRoles($roles))
                        throw new AuthenticationException('Acceso restringido al recurso', Response::HTTP_FORBIDDEN);
                } else{
                    if (!$this->verifyApp())
                        throw new AuthenticationException('Acceso restringido al recurso');
                }

                $action = $this->methods[$method][$uriExpr]->{'action'};
                try {
                    $this->runAction($action, array_slice($params, 1));
                } catch (ArgumentCountError $e) {
                    if ($_ENV['APP_DEBUG'])
                        Response::json($e->getMessage(), Response::HTTP_BAD_REQUEST);
                    else
                        Response::json("Too few arguments", Response::HTTP_BAD_REQUEST);
                } catch (TypeError $e) {
                    if ($_ENV['APP_DEBUG'])
                        Response::json($e->getMessage(), Response::HTTP_BAD_REQUEST);
                    else
                        Response::json("Incorrect argument type", Response::HTTP_BAD_REQUEST);
                }
                return;
            }
        }
        throw new RouteNotFoundException();

    }

    /**
     * Execute an action of an endpoint. First the url parameters are sent to the function
     * and then the json request parameters.
     *
     * @param array|Closure|null $action
     * @param array $params
     * @return void
     * @throws Exception
     */
    private function runAction(array|Closure|null $action, array $params = array()): void
    {
        $jsonRequest = json_decode(file_get_contents('php://input'));
        if (!is_null($jsonRequest)) {
            $params[] = $jsonRequest;
        }

        if ($action instanceof Closure) {
            $action(...$params);
        } else if (is_array($action)) {
            $classObject = $action[0];
            $objectAction = $action[1];
            $classObject->{$objectAction}(...$params);
        } else {
            throw new Exception('Class not found');
        }
    }

    /**
     * Verify if is a logged user with JWT
     *
     * @return bool
     */
    private function verifyAuth(): bool
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (!isset($_COOKIE['AuthToken']))
                return false;
            else
                $token = $_COOKIE['AuthToken'];
        } else
            $token = str_replace('Bearer ', '', getenv('HTTP_AUTHORIZATION'));

        return AuthService::validateToken($token);

    }

    /**
     * Compare the granted authorization roles for the request with the logged user roles.
     *
     * @param array $authRoles
     * @return bool
     */
    private function verifyRoles(array $authRoles): bool
    {
        if (!sizeof($authRoles)) //If the request has no role restriction
            return true;

        $loggedUserRoles = AuthService::getLoggedUserRoles();

        if (is_null($loggedUserRoles) || !sizeof($loggedUserRoles)) //If the User has no role
            return false;

        foreach ($authRoles as $authRole) {
            if (in_array($authRole, $loggedUserRoles))
                return true;
        }

        return false;
    }

    /**
     * Validate if API-KEY exists and it is an authorized app
     *
     * @return bool
     */
    private function verifyApp(): bool
    {
        if (!isset($_SERVER['HTTP_APP_KEY'])) {
            return false;
        }

        $app = SystemConfig::decodeAppName($_SERVER['HTTP_APP_KEY']);

        if (!in_array($app, APPS)) {
            return false;
        }

        AuthService::setAppName($app);
        return true;
    }
}