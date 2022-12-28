<?php

namespace Routes;

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
        /*+-----+
        * | App |
        * +-----+*/
        $this->router->get('/logo', function () {
            echo json_encode(['url' => "images/logo.jpg"]);
        });
        /*+----------------+
        * | Authentication |
        * +----------------+*/
        $this->router->post('/login', [AuthController::getController(), 'login']);
        $this->router->post('/signup', [AuthController::getController(), 'signup']);
        $this->router->get('/logout', [AuthController::getController(), 'logout']);
        $this->router->get('/verifySession', [AuthController::getController(), 'verifySession']);
        /*+-------+
        * | Users |
        * +-------+*/
        $this->router->get('/user', [UserController::getController(), 'allUsers'], ROLES['ADMIN']);
        $this->router->get('user/{idUser}', [UserController::getController(), 'userById'], ROLES['ADMIN']);
        $this->router->get('/user/username/{username}', [UserController::getController(), 'userByUsername'], ROLES['ADMIN']);
        $this->router->put('/user/{idUser}', [UserController::getController(), 'updateUser'], ROLES['ADMIN']);
        $this->router->delete('/user/{idUser}', [UserController::getController(), 'deleteUser'], ROLES['ADMIN']);
    }

}

