<?php

/**
 * JUMP instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\Interpreter;
use IPP\Student\CustomExceptions\OperandTypeException;
use IPP\Student\CustomExceptions\RedefinitionException;
use IPP\Student\CustomExceptions\SourceStructureException;

/**
 * JUMP instruction class
 * Represents opcode JUMP
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws RedefinitionException
 * @throws SourceStructureException
 */
class JumpInstruction implements IInstruction
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
     * @throws OperandTypeException
     */
    public function __construct(array $args, Interpreter $interpreter)
    {
        $this->args = $args;
        $this->interpreter = $interpreter;

        // arg check
        if (count($this->args) !== 1) {
            throw new SourceStructureException("JUMP instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'label') {
            throw new OperandTypeException("JUMP instruction has invalid type of argument 1");
        }
    }

    /**
     * Execute JUMP instruction
     * @throws RedefinitionException
     */
    public function execute(): void
    {
        $labelMap = $this->interpreter->getLabelMap();
        $label = $this->args[0]['value'];

        // JUMP
        if(!array_key_exists($label, $labelMap)) {
            throw new RedefinitionException("JUMP instruction has undefined label");
        }

        // jump to label
        $this->interpreter->instructionPointer = intval($labelMap[$label]);
    }
}