<?php

/**
 * Custom exception for source structure errors
 * @author Tomáš Barták
 * @package IPP\Student\CustomExceptions
 * @version 1.0
 */

namespace IPP\Student\CustomExceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for source structure errors
 * Represents Error Code 32
 */
class SourceStructureException extends IPPException
{
    public function __construct(string $message = "Source structure error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::INVALID_SOURCE_STRUCTURE, $previous, false);
    }
}