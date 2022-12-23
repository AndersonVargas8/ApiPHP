<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;

class UserRepository extends Repository
{
    private static ?UserRepository $instance = null;

    private function __construct()
    {
        parent::__construct(User::class);
    }

    /**
     * Get an instance of UserRepository.
     *
     * @return UserRepository
     */
    public static function getRepository(): UserRepository
    {
        if (is_null(self::$instance))
            self::$instance = new UserRepository();

        return self::$instance;
    }

    /**
     * Retrieve a user by the 'user' attribute
     *
     * @param string $user
     * @return User|null
     */
    public function findByUser(string $user): null|User
    {
        $user = $this->findAllBy(['user' => $user])->result()->getData();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    /**
     * @return array|null
     */
    public function findAllWithoutPassword(): ?array
    {
        $result = $this->find('id', 'user')->result()->getData();

        if (is_null($result))
            return null;

        if (!is_array($result))
            $result = array($result);

        return $result;
    }

    /**
     * Remove all user's roles
     *
     * @param int $idUser
     * @return int Number of affected rows
     */
    public function deleteUserRoles(int $idUser): int
    {
        $query = "DELETE FROM user_role WHERE user_id = ?";
        $result = $this->customQuery($query, $idUser);
        return $result->getRowsAffected();
    }

    /**
     * @param int $userId
     * @param Role $role
     * @return bool
     */
    public function saveUserRole(int $userId, Role $role): bool
    {
        $query = "INSERT INTO user_role (user_id, role_id) VALUES (?,?)";
        $result = $this->customQuery($query, $userId, $role->getId());
        return $result->getStatus();
    }

}