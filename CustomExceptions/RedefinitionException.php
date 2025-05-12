<?php

/**
 * Custom exception for redefinition/label errors
 * @author Tomáš Barták
 * @package IPP\Student\CustomExceptions
 * @version 1.0
 */

namespace IPP\Student\CustomExceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for redefinition/label errors
 * Represents Error Code 52
 */
class RedefinitionException extends IPPException
{
    public function __construct(string $message = "Redefinition/Label error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::SEMANTIC_ERROR, $previous, false);
    }
}