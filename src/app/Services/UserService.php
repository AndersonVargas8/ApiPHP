<?php

namespace App\Services;

use App\Models\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Exception\DuplicatedValueException;

class UserService extends Service
{
    private static ?UserService $instance = null;

    private UserRepository $userRepository;
    private RoleRepository $roleRepository;

    private function __construct()
    {
        $this->userRepository = UserRepository::getRepository();
        $this->roleRepository = RoleRepository::getRepository();
    }

    /**
     * Get an instance of UserService.
     *
     * @return UserService
     */
    public static function getService(): UserService
    {
        if (is_null(self::$instance)) {
            self::$instance = new UserService();
        }

        return self::$instance;
    }

    /**
     * Persist a new user encrypting the password
     *
     * @param User $user
     * @return User
     * @throws DuplicatedValueException
     */
    public function createUser(User $user): User
    {
        $user->setPassword(password_hash($user->getPassword(), PASSWORD_DEFAULT));
        $model = $this->userRepository->save($user);
        $user->fill($model->__toArray());
        $user->setPassword("<<secret-value>>");
        return $user;
    }

    /**
     * Verify if the given credentials are correct.
     *
     * @param string $user
     * @param string $password
     * @return bool
     */
    public function checkCredentials(string $user, string $password): bool
    {
        $user = $this->userRepository->findByUser($user);
        if (is_null($user)) {
            return false;
        }

        return password_verify($password, $user->getPassword());
    }

    /**
     * @return array
     */
    public function getAllUsers(): array
    {
        $users = $this->userRepository->findAllWithoutPassword();

        foreach ($users as $user) {
            $roles = $this->roleRepository->findByUser($user->getId());
            if (is_null($roles))
                continue;
            $user->setRoles($roles);
        }

        return $users;
    }

    /**
     * Get a User find by the given username
     *
     * @param string $username
     * @return User|null
     */
    public function getUserByUsername(string $username): ?User
    {
        $user = $this->userRepository->findByUser($username);
        $roles = $this->roleRepository->findByUser($user->getId());
        $user->setRoles($roles);
        return $user;
    }

}