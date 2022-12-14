<?php

namespace App\Services;

interface ServiceInterface
{
    /**
     * Get an instance of Service
     *
     * @return Service
     */
    static function getService(): Service;
}