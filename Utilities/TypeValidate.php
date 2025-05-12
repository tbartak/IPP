<?php

/**
 * Type validation utility
 * Validates the type of the given value
 * @author Tomáš Barták
 * @package IPP\Student\Utilities
 * @version 1.0
 */

namespace IPP\Student\Utilities;

class TypeValidate {
    /**
     * Validates the type of the given value
     * @param mixed $value Value to validate
     * @param string $type Type to validate
     * @return bool True if the value is valid, false otherwise
     */
    public static function validate(mixed $value, string $type): bool {
        switch ($type) {
            case 'int':
                return preg_match('/^[-+]?[0-9]+$/', $value); // integer
            case 'bool':
                if ($value === 'false' || $value === 'true') { // boolean
                    return true;
                }
                return false;
            case 'string':
                return preg_match('/^([^#\\\\]|\\\\[0-9]{3})*$/', $value); // string
            case 'nil':
                return preg_match('/^nil$/', $value); // nil
            case 'all':
                return true;
            case 'var':
                return preg_match('/^(GF|LF|TF)@(.+)$/', $value); // variable
            case 'label':
                return preg_match('/^[_\-$&%*!?a-zA-Z][_\-$&%*!?a-zA-Z0-9]*$/', $value); // label
            case 'type':
                return preg_match('/^(int|string|bool|nil)$/', $value); // type
            default:
                return false;
        }
    }
}
