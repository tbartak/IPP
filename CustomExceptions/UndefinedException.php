<?php

/**
 * Custom exception for undefined frame errors
 * @author Tomáš Barták
 * @package IPP\Student\CustomExceptions
 * @version 1.0
 */

namespace IPP\Student\CustomExceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for undefined frame errors
 * Represents Error Code 55
 */
class UndefinedException extends IPPException
{
    public function __construct(string $message = "Undefined frame error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::FRAME_ACCESS_ERROR, $previous, false);
    }
}