<?php

/**
 * DEFVAR instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\CustomExceptions\OperandTypeException;
use IPP\Student\CustomExceptions\OperandValueException;
use IPP\Student\CustomExceptions\SourceStructureException;
use IPP\Student\Interpreter;

/**
 * DEFVAR instruction class
 * Represents opcode DEFVAR
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 */
class DefvarInstruction implements IInstruction
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
     * @throws OperandValueException
     */
    public function __construct(array $args, Interpreter $interpreter)
    {
        $this->args = $args;
        $this->interpreter = $interpreter;

        // arg check
        if (count($this->args) !== 1) {
            throw new SourceStructureException("DEFVAR instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("DEFVAR instruction has invalid type of argument 1");
        }

        // arg1 value check
        if (!preg_match('/^(GF|LF|TF)@([_\-$&%*!?a-zA-Z][_\-$&%*!?a-zA-Z0-9]*)$/', $this->args[0]['value'])) {
            throw new OperandValueException("Invalid variable format: " . $this->args[0]['value']);
        }
    }

    /**
     * Execute DEFVAR instruction
     * @return void
     */
    public function execute(): void
    {
        if (preg_match('/^(GF|LF|TF)@([_\-$&%*!?a-zA-Z][_\-$&%*!?a-zA-Z0-9]*)$/', $this->args[0]['value'], $matches)) {
            $frameType = $matches[1];
            $variableName = $matches[2];

            $this->interpreter->addVariableToFrame($frameType, $variableName);
        }
    }
}