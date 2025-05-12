<?php

/**
 * POPFRAME instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\Interpreter;
use IPP\Student\CustomExceptions\SourceStructureException;

/**
 * POPFRAME instruction class
 * Represents opcode POPFRAME
 * Implements interface of IInstruction
 * @throws SourceStructureException
 */
class PopframeInstruction implements IInstruction
{
    /**
     * @var array<array{type: string, value: mixed}> $args Arguments of instruction
     * @var Interpreter $interpreter Interpreter instance
     */
    private array $args;
    private Interpreter $interpreter;

    /**
     * @param array<array{type: string, value: mixed}> $args Arguments of instruction
     * @param Interpreter $interpreter Interpreter instance
     * @throws SourceStructureException
     */
    public function __construct(array $args, Interpreter $interpreter)
    {
        $this->args = $args;
        $this->interpreter = $interpreter;

        // arg check
        if (count($this->args) !== 0) {
            throw new SourceStructureException("POPFRAME instruction has invalid amount of arguments");
        }
    }

    /**
     * Execute POPFRAME instruction
     * @return void
     */
    public function execute(): void
    {
        $this->interpreter->popFrame();
    }
}