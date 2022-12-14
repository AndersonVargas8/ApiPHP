<?php

namespace App\Controllers;

interface ControllerInterface
{
    /**
     * Get an instance of Controller
     *
     * @return Controller
     */
    static function getController(): Controller;
}