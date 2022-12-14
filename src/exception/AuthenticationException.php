<?php

namespace Exception;

use Exception;
use Http\Response;

/**
 * An exception derivation which represents a duplicated value
 *
 * @package Exception
 */
class AuthenticationException extends Exception
{
    public function __construct($message = "Unauthorized user", $code = Response::HTTP_UNAUTHORIZED, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}