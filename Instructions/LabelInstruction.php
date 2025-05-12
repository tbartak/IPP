<?php

/**
 * LABEL instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\CustomExceptions\SourceStructureException;

/**
 * LABEL instruction class
 * Represents opcode LABEL
 * Implements interface of IInstruction
 * @throws SourceStructureException
 */
class LabelInstruction implements IInstruction
{
     /**
     * @var array<array{type: string, value: mixed}> $args Arguments of instruction
     */
    private array $args;

    /**
     * @param array<array{type: string, value: mixed}> $args Arguments of instruction
     * @throws SourceStructureException
     */
    public function __construct(array $args)
    {
        $this->args = $args;

        // arg check
        if (count($this->args) !== 1) {
            throw new SourceStructureException("LABEL instruction has invalid amount of arguments");
        }
    }

    /**
     * Execute LABEL instruction
     * @return void
     */
    public function execute(): void
    {
        // do nothing
    }
}