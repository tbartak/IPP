<?php

/**
 * AND instruction class
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
 * AND instruction class
 * Represents opcode AND
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 * @throws VariableAccessException
 */
class AndInstruction implements IInstruction
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
        if (count($this->args) !== 3) {
            throw new SourceStructureException("AND instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("AND instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'var' && $this->args[1]['type'] !== 'bool') {
            throw new OperandTypeException("AND instruction has invalid type of argument 2");
        }

        // arg3 check
        if ($this->args[2]['type'] !== 'var' && $this->args[2]['type'] !== 'bool') {
            throw new OperandTypeException("AND instruction has invalid type of argument 3");
        }
    }

    /**
     * Execute AND instruction
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
                throw new VariableAccessException("AND instruction has undefined variable");
            }
        } else {
            throw new OperandValueException("AND instruction has invalid format of argument 1");
        }

        // retrieve value of symb1
        $symb1Value = $this->getSymbValue($this->args[1]);

        // retrieve value of symb2
        $symb2Value = $this->getSymbValue($this->args[2]);

        // AND
        $result = $symb1Value && $symb2Value;
        $resultString = $result ? 'true' : 'false'; // Convert bool to string 'true' or 'false'
        $type = 'bool';

        // Saves result to variable
        $this->interpreter->setVariableValue($varFrame, $varName, $resultString, $type);
    }

    /**
     * Get value of symbol
     * @param array{type: string, value: mixed} $symbol Symbol
     * @return bool Value of symbol
     * @throws OperandTypeException
     * @throws OperandValueException
     */
    private function getSymbValue($symbol): bool {
        if ($symbol['type'] === 'var') {
            // If it is a variable, retrieve its value
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                if ($symbValue['type'] !== 'bool') {
                    throw new OperandTypeException("Operand must be of type bool.");
                }
                return $symbValue['value'] === 'true'; // Convert string 'true' or 'false' to bool
            }
            else {
                throw new OperandValueException("AND instruction has invalid format of argument");
            }
        } else if ($symbol['type'] === 'bool') {
            // Otherwise, return value of symbol
            return $symbol['value'] === 'true'; // Convert string 'true' or 'false' to bool
        } else {
            throw new OperandTypeException("Operand must be of type bool.");
        }
    }
}