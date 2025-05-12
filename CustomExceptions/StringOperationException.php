<?php

/**
 * Custom exception for string operation errors
 * @author Tomáš Barták
 * @package IPP\Student\CustomExceptions
 * @version 1.0
 */

namespace IPP\Student\CustomExceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for string operation errors
 * Represents Error Code 58
 */
class StringOperationException extends IPPException
{
    public function __construct(string $message = "String operation error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::STRING_OPERATION_ERROR, $previous, false);
    }
}