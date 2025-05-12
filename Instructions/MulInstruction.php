<?php

/**
 * MUL instruction class
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
 * MUL instruction class
 * Represents opcode MUL
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 * @throws VariableAccessException
 */
class MulInstruction implements IInstruction
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
            throw new SourceStructureException("MUL instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("MUL instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'var' && $this->args[1]['type'] !== 'int') {
            throw new OperandTypeException("MUL instruction has invalid type of argument 2");
        }

        // arg3 check
        if ($this->args[2]['type'] !== 'var' && $this->args[2]['type'] !== 'int') {
            throw new OperandTypeException("MUL instruction has invalid type of argument 3");
        }
    }

    /**
     * Execute MUL instruction
     * @throws VariableAccessException
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
                throw new VariableAccessException("MUL instruction has undefined variable");
            }
        } else {
            throw new OperandValueException("MUL instruction has invalid format of argument 1");
        }

        // retrieve value of symb1
        $symb1Value = $this->getSymbValue($this->args[1]);

        // retrieve value of symb2
        $symb2Value = $this->getSymbValue($this->args[2]);

        // MUL
        $result = $symb1Value * $symb2Value;
        $type = 'int';

        // Save result to variable
        $this->interpreter->setVariableValue($varFrame, $varName, $result, $type);
    }

    /**
     * Get value of symbol
     * @param array{type: string, value: mixed} $symbol Symbol
     * @return int Value of symbol
     * @throws OperandTypeException
     * @throws OperandValueException
     */
    private function getSymbValue($symbol): int {
        if ($symbol['type'] === 'var') {
            // If it is a variable, retrieve its value
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                if ($symbValue['type'] !== 'int')
                {
                    throw new OperandTypeException("MUL instruction has invalid type of argument");
                }
                return intval($symbValue['value']);
            }
            else {
                throw new OperandValueException("MUL instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === 'int'){
            // Otherwise return value of symbol
            $symbValue = intval($symbol['value']);
            return $symbValue;
        }
        else {
            throw new OperandTypeException("MUL instruction has invalid type of argument");
        }
    }
}