<?php

/**
 * Custom exception for value errors
 * @author Tomáš Barták
 * @package IPP\Student\CustomExceptions
 * @version 1.0
 */

namespace IPP\Student\CustomExceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for value errors
 * Represents Error Code 56
 */
class ValueException extends IPPException
{
    public function __construct(string $message = "Value error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::VALUE_ERROR, $previous, false);
    }
}