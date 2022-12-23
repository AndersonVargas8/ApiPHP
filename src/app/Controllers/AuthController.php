<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Exception\DuplicatedValueException;
use Exception\IndexNotFoundException;
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
        if (!isset($request->user) || !isset($request->password)) {
            Response::json(["message" => "El usuario y la contraseña no deben ser nulos"], Response::HTTP_BAD_REQUEST);
            return;
        }
        $user = strtolower(trim($request->user));
        $check = $this->userService->checkCredentials($user, $request->password);

        if (!$check) {
            Response::json(["message" => "Usuario o contraseña incorrectos"], Response::HTTP_BAD_REQUEST);
            return;
        }

        try {
            $user = $this->userService->getUserByUsername($user);
        } catch (IndexNotFoundException) {
        }

        $jwt = AuthService::generateJWT($user);
        Response::json(["token" => $jwt]);
    }

    /**
     * @param stdClass $request - Json object with the params.
     * @return void
     */
    public function signup(stdClass $request): void
    {
        if (!isset($request->user) || !isset($request->password) || !isset($request->confirm_password)) {
            Response::json(["message" => "El usuario y la contraseña no deben ser nulos"], Response::HTTP_BAD_REQUEST);
            return;
        }

        if ($request->user == "" || $request->password == "") {
            Response::json(["message" => "El usuario y la contraseña no deben estar vacíos"]);
            return;
        }

        if ($request->password != $request->confirm_password) {
            Response::json(["message" => "Las contraseñas no coinciden"], Response::HTTP_BAD_REQUEST);
            return;
        }

        $user = new User();
        $request->user = strtolower(trim($request->user));
        $user->fill($request);

        try {
            $userCreated = $this->userService->createUser($user);
        } catch (DuplicatedValueException $e) {
            if ($_ENV['APP_DEBUG'])
                Response::json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            else
                Response::json(['message' => "El nombre de usuario ingresado ya existe"], Response::HTTP_BAD_REQUEST);

            return;
        }

        Response::json($userCreated, Response::HTTP_CREATED);
    }
}