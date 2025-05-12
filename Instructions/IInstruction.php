<?php

/**
 * Interface for instructions
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

/**
 * Interface for instructions
 */
interface IInstruction {
    public function execute(): void;
}
