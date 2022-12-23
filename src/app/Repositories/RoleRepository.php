<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends Repository
{
    private static ?RoleRepository $instance = null;

    private function __construct()
    {
        parent::__construct(Role::class);
    }

    /**
     * Get an instance of RoleRepository.
     *
     * @return RoleRepository
     */
    static function getRepository(): RoleRepository
    {
        if (is_null(self::$instance))
            self::$instance = new RoleRepository();

        return self::$instance;
    }

    /**
     *
     * @param int $userId
     * @return array
     */
    public function findByUser(int $userId): array
    {
        $result = $this
            ->find('id', 'description')
            ->join('user_role', 'roles', 'role_id', 'id')
            ->where(['user_role.user_id' => $userId])
            ->result();

        if (is_null($result->getData()))
            return array();

        if (!is_array($result->getData())) {
            return array($result->getData());
        }

        return $result->getData();
    }


}