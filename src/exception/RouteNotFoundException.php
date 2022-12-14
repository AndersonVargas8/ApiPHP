<?php

namespace Exception;

use Exception;

/**
 * An exception derivation which represents that a route has not been found
 *
 * @package Exception
 */
class RouteNotFoundException extends Exception
{
    public function __construct($message = "El recurso solicitado no existe", $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}