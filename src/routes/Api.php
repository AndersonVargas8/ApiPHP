<?php

namespace Routes;

use App\Controllers\JugadoresController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use Exception;
use Http\Router;

class Api
{
    /**
     * Router to define the endpoints.
     *
     * @var Router
     */
    private Router $router;

    /**
     * @param Router $router
     * @throws Exception
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->setRoutes();
    }

    /**
     * Define the endpoints to the api
     *
     * @return void
     * @throws Exception
     */
    private function setRoutes(): void
    {
        /*+----------------+
        * | Authentication |
        * +----------------+*/
        $this->router->post('/login', [AuthController::getController(), 'login']);
        $this->router->post('/signup', [AuthController::getController(), 'signup']);
        /*+-------+
        * | Users |
        * +-------+*/
        $this->router->get('/user',[UserController::getController(), 'allUsers'],ROLES['ADMIN']);
        $this->router->put('/user/{idUser}', [UserController::getController(), 'updateUser'], ROLES['ADMIN']);
    }

}

