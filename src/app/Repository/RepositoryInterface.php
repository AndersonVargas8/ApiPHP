<?php

namespace App\Repository;

interface RepositoryInterface
{
    /**
     * Get an instance of URepository.
     *
     * @return Repository
     */
    static function getRepository():Repository;
}