<?php

/**
 * NOT instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\Interpreter;
use IPP\Student\CustomExceptions\OperandTypeException;
use IPP\Student\CustomExceptions\OperandValueException;
use IPP\Student\CustomExceptions\SourceStructureException;
use IPP\Student\CustomExceptions\VariableAccessException;

/**
 * NOT instruction class
 * Represents opcode NOT
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 * @throws VariableAccessException
 */
class NotInstruction implements IInstruction
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
        if (count($this->args) !== 2) {
            throw new SourceStructureException("NOT instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("NOT instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'bool' && $this->args[1]['type'] !== 'var') {
            throw new OperandTypeException("NOT instruction has wrong (not bool) operand types");
        }
    }

    /**
     * Execute NOT instruction
     * @throws VariableAccessException
     * @throws OperandTypeException
     * @throws OperandValueException
     * @return void
     */
    public function execute(): void
    {
        // arg1 check
        if(preg_match('/^(GF|LF|TF)@(.+)$/', $this->args[0]['value'], $matches)) {
            $varFrame = $matches[1];
            $varName = $matches[2];
            if (!$this->interpreter->isVarDefined($varFrame, $varName)) {
                throw new VariableAccessException("NOT instruction has undefined variable");
            }
        } else {
            throw new OperandValueException("NOT instruction has invalid format of argument 1");
        }

        // retrieve symb1 value
        $symb1Value = $this->getSymbValue($this->args[1]);

        // NOT
        if ($symb1Value === true) {
            $result = 'false';
        } else {
            $result = 'true';
        }
        $type = 'bool';

        // Save result to variable
        $this->interpreter->setVariableValue($varFrame, $varName, $result, $type);
    }

    /**
     * @param array{type: string, value: mixed} $symbol Symbol
     * @throws OperandTypeException
     * @throws OperandValueException
     * @return bool Value of symbol
     */
    private function getSymbValue($symbol): bool {
        if ($symbol['type'] === 'var') {
            // If it is variable, get value from variable
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                if ($symbValue['type'] !== 'bool') {
                    throw new OperandTypeException("Operand must be of type bool.");
                }
                return $symbValue['value'] === 'true'; // Convert string 'true' or 'false' to boolean value
            }
            else {
                throw new OperandValueException("NOT instruction has invalid format of argument");
            }
        } else if ($symbol['type'] === 'bool') {
            // Otherwise, get value from symbol
            return $symbol['value'] === 'true'; // Convert string 'true' or 'false' to boolean value
        } else {
            throw new OperandTypeException("Operand must be of type bool.");
        }
    }
}