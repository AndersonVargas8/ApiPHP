<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Get an instance of URepository.
     *
     * @return Repository
     */
    static function getRepository(): Repository;
}