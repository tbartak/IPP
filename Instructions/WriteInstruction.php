<?php

/**
 * WRITE instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\Interpreter;
use IPP\Student\CustomExceptions\OperandValueException;
use IPP\Student\CustomExceptions\SourceStructureException;

/**
 * WRITE instruction class
 * Represents opcode WRITE
 * Implements interface of IInstruction
 * @throws OperandValueException
 * @throws SourceStructureException
 */
class WriteInstruction implements IInstruction
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
        if (count($this->args) !== 1) {
            throw new SourceStructureException("WRITE instruction expects exactly one argument.");
        }
    }

    /**
     * Execute WRITE instruction
     * @return void
     * @throws OperandValueException
     */
    public function execute(): void
    {
        $arg = $this->args[0];
        $argType = $arg['type'];

        // argument check
        if ($argType === 'var') {
            // If it is a variable, retrieve its value
            if (!preg_match('/^(GF|LF|TF)@([_\-$&%*!?a-zA-Z][_\-$&%*!?a-zA-Z0-9]*)$/', $arg['value'], $matches)) {
                throw new OperandValueException("Invalid variable format: " . $arg['value']);
            }
            $frameType = $matches[1];
            $variableName = $matches[2];
            $var = $this->interpreter->getVariableValue($frameType, $variableName);
            $value = $var['value'];
            $argType = $var['type'];
        } else {
            // Otherwise, use the value directly
            $value = $arg['value'];
        }

        // Write the value to stdout
        $this->interpreter->writeToStdout($value, $argType);
    }
}