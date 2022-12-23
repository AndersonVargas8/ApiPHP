<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\UserService;
use Exception\DuplicatedValueException;
use Exception\IndexNotFoundException;
use Http\Response;
use stdClass;

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

    public function updateUser(int $idUser, stdClass $userRequest): void
    {
        if (!isset($userRequest->user) || !isset($userRequest->password) || !isset($userRequest->confirm_password) || !isset($userRequest->roles)) {
            Response::json(["message" => "El usuario, la contraseña y los roles no deben ser nulos"], Response::HTTP_BAD_REQUEST);
            return;
        }

        if ($userRequest->user == "" || $userRequest->password == "") {
            Response::json(["message" => "El usuario y la contraseña no deben estar vacíos"]);
            return;
        }

        if ($userRequest->password != $userRequest->confirm_password) {
            Response::json(["message" => "Las contraseñas no coinciden"], Response::HTTP_BAD_REQUEST);
            return;
        }

        try {
            $this->userService->getUserById($idUser);
        } catch (IndexNotFoundException) {
            Response::json(['message' => "No existe un usuario con el id ingresado"], Response::HTTP_NOT_FOUND);
            return;
        }

        $user = new User();
        $user->fill($userRequest);
        $user->setId($idUser);
        $user->setRoles(array());

        try {
            foreach ($userRequest->roles as $roleId) {
                $role = $this->userService->getRoleById($roleId);
                $user->addRole($role);
            }
        } catch (IndexNotFoundException) {
            Response::json(['message' => "No existe un rol con el id ingresado"], Response::HTTP_NOT_FOUND);
            return;
        }


        try {
            $user = $this->userService->updateUser($user);
        } catch (DuplicatedValueException $e) {
            Response::json(['message' => "El nombre de usuario ingresado ya existe"], $e->getCode());
            return;
        } catch (IndexNotFoundException) {
        }

        Response::json($user);
    }
}