<?php

/**
 * Custom exception for operand value errors
 * @author Tomáš Barták
 * @package IPP\Student\CustomExceptions
 * @version 1.0
 */

namespace IPP\Student\CustomExceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for operand value errors
 * Represents Error Code 57
 */
class OperandValueException extends IPPException
{
    public function __construct(string $message = "Operand value error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::OPERAND_VALUE_ERROR, $previous, false);
    }
}