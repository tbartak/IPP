<?php

/**
 * EQ instruction class
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
 * EQ instruction class
 * Represents opcode EQ
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 * @throws VariableAccessException
 */
class EqInstruction implements IInstruction
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
            throw new SourceStructureException("EQ instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("EQ instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'var' && $this->args[1]['type'] !== 'int' && $this->args[1]['type'] !== 'bool' && $this->args[1]['type'] !== 'string' && $this->args[1]['type'] !== 'nil') {
            throw new OperandTypeException("EQ instruction has invalid type of argument 2");
        }

        // arg3 check
        if ($this->args[2]['type'] !== 'var' && $this->args[2]['type'] !== 'int' && $this->args[2]['type'] !== 'bool' && $this->args[2]['type'] !== 'string' && $this->args[2]['type'] !== 'nil') {
            throw new OperandTypeException("EQ instruction has invalid type of argument 3");
        }
    }

    /**
     * Execute EQ instruction
     * @throws VariableAccessException
     * @throws OperandValueException
     * @throws OperandTypeException
     * @return void
     */
    public function execute(): void
    {
        
        // arg1 check
        if(preg_match('/^(GF|LF|TF)@(.+)$/', $this->args[0]['value'], $matches)) {
            $varFrame = $matches[1];
            $varName = $matches[2];
            if (!$this->interpreter->isVarDefined($varFrame, $varName)) {
                throw new VariableAccessException("EQ instruction has undefined variable");
            }
        } else {
            throw new OperandValueException("EQ instruction has invalid format of argument 1");
        }

        // retrieve value of symb1
        $symb1Value = $this->getSymbValue($this->args[1]);

        // retrieve value of symb2
        $symb2Value = $this->getSymbValue($this->args[2]);

        if ($symb1Value['type'] !== $symb2Value['type'] && $symb1Value['type'] !== 'nil' && $symb2Value['type'] !== 'nil') {
            throw new OperandTypeException("EQ instruction has different operand types");
        }

        // EQ
        $result = false;
        switch ($symb1Value['type']) {
            case 'int':
                $result = intval($symb1Value['value']) === intval($symb2Value['value']) ? 'true' : 'false';
                break;
            case 'bool':
                $bool1 = $symb1Value['value'] === 'true' ? 1 : 0; // Convert true to 1, false to 0
                $bool2 = $symb2Value['value'] === 'true' ? 1 : 0; // Convert true to 1, false to 0
                $result = $bool1 === $bool2 ? 'true' : 'false';
                break;
            case 'string':
                $result = strcmp($symb1Value['value'], $symb2Value['value']) === 0 ? 'true' : 'false';
                break;
            case 'nil':
                $result = $symb1Value['type'] === $symb2Value['type'] ? 'true' : 'false';
                break;
            default:
                throw new OperandTypeException("EQ instruction has invalid type of argument");
        }
        $type = 'bool';

        // Save result to variable
        $this->interpreter->setVariableValue($varFrame, $varName, $result, $type);
    }

    /**
     * @param array{type: string, value: mixed} $symbol Symbol
     * @return array{type: string, value: mixed} Information about symbol
     * @throws OperandTypeException
     * @throws OperandValueException
     */
    private function getSymbValue($symbol): array {
        if ($symbol['type'] === 'var') {
            // If it is a variable, retrieve its value
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                return $symbValue;
            }
            else {
                throw new OperandValueException("EQ instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === 'int' || $symbol['type'] === 'bool' || $symbol['type'] === 'string' || $symbol['type'] === 'nil'){
            // Otherwise, we assume it is a value
            $symbValue = $symbol;
            return $symbValue;
        }
        else {
            throw new OperandTypeException("EQ instruction has invalid type of argument");
        }
    }
}