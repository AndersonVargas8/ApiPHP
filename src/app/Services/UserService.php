<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Exception\DuplicatedValueException;
use Exception\IndexNotFoundException;

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
     * @throws DuplicatedValueException Username already exists
     * @throws IndexNotFoundException User id does not exist
     */
    public function updateUser(User $user): User
    {
        $user->setPassword(password_hash($user->getPassword(), PASSWORD_DEFAULT));

        /*+--------------------+
        * | Update user record |
        * +--------------------+*/
        $model = $this->userRepository->update($user);

        /*+-------------------+
        * | Update user roles |
        * +-------------------+*/
        $this->userRepository->deleteUserRoles($user->getId());

        foreach ($user->getRoles() as $role) {
            $this->userRepository->saveUserRole($user->getId(), $role);
        }

        $user->fill($model->__toArray());
        $user->setRoles($this->roleRepository->findByUser($user->getId()));
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
            $user->setRoles($roles);
        }

        return $users;
    }

    /**
     * Get a User find by the given username
     *
     * @param string $username
     * @return User|null
     * @throws IndexNotFoundException
     */
    public function getUserByUsername(string $username): ?User
    {
        $user = $this->userRepository->findByUser($username);

        if (is_null($user)) {
            throw new IndexNotFoundException();
        }
        $roles = $this->roleRepository->findByUser($user->getId());
        $user->setRoles($roles);
        return $user;
    }

    /**
     * @throws IndexNotFoundException
     */
    public function getUserById(int $id): ?User
    {
        $model = $this->userRepository->findById($id);
        if (is_null($model))
            throw new IndexNotFoundException();

        $user = new User();
        $user->fill($model->__toArray());

        $roles = $this->roleRepository->findByUser($user->getId());
        $user->setRoles($roles);

        return $user;
    }

    /**
     * @param int $id
     * @return Role|null
     * @throws IndexNotFoundException
     */
    public function getRoleById(int $id): ?Role
    {
        $result = $this->roleRepository->findById($id);

        if (is_null($result))
            throw new IndexNotFoundException();

        $role = new Role();
        $role->fill($result->__toArray());

        return $role;
    }

}