<?php

namespace Exception;

use Exception;
use Http\Response;

/**
 * An exception derivation which represents a not found value
 *
 * @package Exception
 */
class IndexNotFoundException extends Exception
{
    public function __construct($message = "Index not found", $code = Response::HTTP_NOT_FOUND, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}