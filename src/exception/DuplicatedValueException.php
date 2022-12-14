<?php

namespace Exception;

use Exception;

/**
 * An exception derivation which represents a duplicated value
 *
 * @package Exception
 */
class DuplicatedValueException extends Exception
{
    public function __construct($message = "Duplicated value", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}