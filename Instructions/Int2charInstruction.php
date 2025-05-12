<?php

/**
 * INT2CHAR instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\Interpreter;
use IPP\Student\CustomExceptions\StringOperationException;
use IPP\Student\CustomExceptions\OperandTypeException;
use IPP\Student\CustomExceptions\OperandValueException;
use IPP\Student\CustomExceptions\SourceStructureException;
use IPP\Student\CustomExceptions\VariableAccessException;

/**
 * INT2CHAR instruction class
 * Represents opcode INT2CHAR
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 * @throws VariableAccessException
 * @throws StringOperationException
 */
class Int2charInstruction implements IInstruction
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
            throw new SourceStructureException("INT2CHAR instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("INT2CHAR instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'var' && $this->args[1]['type'] !== 'int') {
            throw new OperandTypeException("INT2CHAR instruction has invalid type of argument 2");
        }
    }


    /**
     * Execute INT2CHAR instruction
     * @throws OperandValueException
     * @throws VariableAccessException
     * @return void
     */
    public function execute(): void
    {
        // arg1 check
        if(preg_match('/^(GF|LF|TF)@(.+)$/', $this->args[0]['value'], $matches)) {
            $varFrame = $matches[1];
            $varName = $matches[2];
            if (!$this->interpreter->isVarDefined($varFrame, $varName)) {
                throw new VariableAccessException("INT2CHAR instruction has undefined variable");
            }
        } else {
            throw new OperandValueException("INT2CHAR instruction has invalid format of argument 1");
        }
        
        // retrieve the value of symbol
        $symbValue = $this->getSymbValue($this->args[1]);

        // INT2CHAR
        $result = $this->intToChar($symbValue);
        $type = 'string';

        // save result to variable
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
            // If it is variable, get value from variable
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                if ($symbValue['type'] !== 'int') {
                    throw new OperandTypeException("Operand must be of type int.");
                }
                return $symbValue['value'];
            }
            else {
                throw new OperandValueException("INT2CHAR instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === 'int'){
            // Otherwise return value of symbol
            $symbValue = $symbol;
            return $symbValue['value'];
        }
        else {
            throw new OperandTypeException("INT2CHAR instruction has invalid type of argument");
        }
    }

    /**
     * Convert int to char
     * @param int $value Value to convert
     * @throws StringOperationException
     * @return string Converted value
     */
    private function intToChar(int $value): string {
        if ($value < 0 || $value > 1114111) { // Unicode range
            throw new StringOperationException("Invalid Unicode value.");
        }
        return chr($value); // convert int to char
    }
}