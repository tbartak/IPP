<?php

/**
 * PUSHS instruction class
 * Represents opcode PUSHS
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\Interpreter;
use IPP\Student\CustomExceptions\OperandTypeException;
use IPP\Student\CustomExceptions\OperandValueException;
use IPP\Student\CustomExceptions\SourceStructureException;

/**
 * PUSHS instruction class
 * Represents opcode PUSHS
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 */
class PushsInstruction implements IInstruction
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
     */
    public function __construct(array $args, Interpreter $interpreter)
    {
        $this->args = $args;
        $this->interpreter = $interpreter;

        // arg check
        if (count($this->args) !== 1) {
            throw new SourceStructureException("PUSHS instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var' && $this->args[0]['type'] !== 'int' && $this->args[0]['type'] !== 'bool' && $this->args[0]['type'] !== 'string' && $this->args[0]['type'] !== 'nil'){
            throw new OperandTypeException("PUSHS instruction has invalid type of argument 1");
        }
    }

    /**
     * Execute PUSHS instruction
     * @return void
     */
    public function execute(): void
    {
        $arg = $this->args[0];

        // retrieve value of symbol
        $symbValue = $this->getSymbValue($arg);

        array_push($this->interpreter->dataStack, $symbValue);
    }

    /**
     * @param array{type: string, value: mixed} $symbol Symbol to get value from
     * @return array{type: string, value: mixed} Information about symbol
     * @throws OperandTypeException
     * @throws OperandValueException
     */
    private function getSymbValue($symbol): array {
        if ($symbol['type'] === 'var') {
            // If it is variable, retrieve its value
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                return $symbValue;
            }
            else {
                throw new OperandValueException("PUSHS instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === 'string' || $symbol['type'] === 'int' || $symbol['type'] === 'bool' || $symbol['type'] === 'nil'){
            // Otherwise, return the symbol
            $symbValue = $symbol;
            return $symbValue;
        }
        else {
            throw new OperandTypeException("PUSHS instruction has invalid type of argument");
        }
    }
}