<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Exception\DuplicatedValueException;
use Http\Response;
use stdClass;

class AuthController extends Controller
{
    static private ?AuthController $instance = null;

    private UserService $userService;

    private function __construct()
    {
        $this->userService = UserService::getService();
    }

    /**
     * Get an instance of AuthController.
     *
     * @return AuthController
     */
    public static function getController(): AuthController
    {
        if (is_null(self::$instance)) {
            self::$instance = new AuthController();
        }

        return self::$instance;
    }

    /**
     * @param stdClass $request - Json object with the params
     * @return void
     */
    public function login(stdClass $request): void
    {
        if (is_null($request->user) || is_null($request->password)) {
            Response::json(["Message" => "El usuario y la contraseña no deben estar vacíos"], Response::HTTP_BAD_REQUEST);
            return;
        }

        $check = $this->userService->checkCredentials($request->user, $request->password);

        if (!$check) {
            Response::json(["Message" => "Usuario o contraseña incorrectos"], Response::HTTP_BAD_REQUEST);
            return;
        }

        $user = $this->userService->getUserByUsername($request->user);

        $jwt = AuthService::generateJWT($user);
        Response::json(["token" => $jwt]);
    }

    /**
     * @param stdClass $request - Json object with the params.
     * @return void
     */
    public function signup(stdClass $request): void
    {
        $user = new User();
        $user->fill($request);

        try {
            $userCreated = $this->userService->createUser($user);
        } catch (DuplicatedValueException $e) {
            if ($_ENV['APP_DEBUG'])
                Response::json(['Message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            else
                Response::json(['Message' => "El usuario ingresado ya existe"], Response::HTTP_BAD_REQUEST);

            return;
        }

        Response::json($userCreated, Response::HTTP_CREATED);
    }
}