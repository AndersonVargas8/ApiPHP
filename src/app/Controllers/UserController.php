<?php

namespace App\Controllers;

use App\Services\UserService;
use Http\Response;

class UserController extends Controller
{
    static private ?UserController $instance = null;

    private UserService $userService;

    private function __construct()
    {
        $this->userService = UserService::getService();
    }

    /**
     * Get an instance of AuthController.
     *
     * @return UserController
     */
    public static function getController(): UserController
    {
        if (is_null(self::$instance)) {
            self::$instance = new UserController();
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    public function allUsers(): void
    {
        $users = $this->userService->getAllUsers();

        Response::json($users);
    }
}