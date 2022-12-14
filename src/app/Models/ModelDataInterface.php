<?php

namespace App\Models;

interface ModelDataInterface
{
    /**
     * Attributes that do not keep in mind to update in the database
     *
     * @return array
     */
    function transientAttributes(): array;
}