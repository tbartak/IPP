<?php

/**
 * RETURN instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\Interpreter;
use IPP\Student\CustomExceptions\SourceStructureException;
use IPP\Student\CustomExceptions\ValueException;

/**
 * RETURN instruction class
 * Represents opcode RETURN
 * Implements interface of IInstruction
 * @throws ValueException
 * @throws SourceStructureException
 */
class ReturnInstruction implements IInstruction
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
            throw new SourceStructureException("RETURN instruction has invalid amount of arguments");
        }
    }

    /**
     * Execute RETURN instruction
     * @throws ValueException
     * @return void
     */
    public function execute(): void
    {
        if ($this->interpreter->callStack === []) {
            throw new ValueException("RETURN instruction has no CALL to return from");
        }

        // pop return pointer from call stack
        $returnPointer = array_pop($this->interpreter->callStack);

        // set instruction pointer to return pointer
        $this->interpreter->instructionPointer = $returnPointer;
    }
}