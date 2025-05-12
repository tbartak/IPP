<?php

/**
 * Custom exception for variable access errors
 * @author Tomáš Barták
 * @package IPP\Student\CustomExceptions
 * @version 1.0
 */

namespace IPP\Student\CustomExceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for variable access errors
 * Represents Error Code 54
 */
class VariableAccessException extends IPPException
{
    public function __construct(string $message = "Variable access error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::VARIABLE_ACCESS_ERROR, $previous, false);
    }
}