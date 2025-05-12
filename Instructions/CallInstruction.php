<?php

/**
 * CALL instruction class
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
 * CALL instruction class
 * Represents opcode CALL
 * Implements interface of IInstruction
 * @throws SourceStructureException
 * @throws OperandTypeException
 * @throws RedefinitionException
 */
class CallInstruction implements IInstruction
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
            throw new SourceStructureException("CALL instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'label') {
            throw new OperandTypeException("CALL instruction has invalid type of argument 1");
        }
    }

    /**
     * Execute CALL instruction
     * @throws RedefinitionException
     * @return void
     */
    public function execute(): void
    {
        $label = $this->args[0]['value'];
        $labelMap = $this->interpreter->getLabelMap();

        if (!array_key_exists($label, $labelMap)) {
            throw new RedefinitionException("CALL instruction has undefined label");
        }

        // push current instruction pointer to call stack
        array_push($this->interpreter->callStack, $this->interpreter->instructionPointer);

        // set instruction pointer to where the corresponding label is located
        $this->interpreter->instructionPointer = intval($labelMap[$label]);
    }
}