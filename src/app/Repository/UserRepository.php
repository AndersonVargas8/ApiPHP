<?php

namespace App\Repository;

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
        $user = $this->findAllBy(['user' => $user])->result();

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
        $result = $this->find('id', 'user')->result();

        if (is_null($result))
            return null;

        if (!is_array($result))
            $result = array($result);

        return $result;
    }

    /*+---------------------------------+
    * | EJEMPLO DE USO DEL CUSTOM QUERY |
    * +---------------------------------+*/
    public function count(int $id1, int $id2): int
    {
        $query = "SELECT COUNT(id) AS count FROM " . $this->table . " WHERE id > ? AND id < ? AND user LIKE ?";
        $u = "%jiba%";
        $result = $this->customQuery($query, $id1, $id2, $u)->count;
        return 0;
    }

}