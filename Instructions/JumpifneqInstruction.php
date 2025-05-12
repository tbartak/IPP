<?php

/**
 * JUMPIFNEQ instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\Interpreter;
use IPP\Student\CustomExceptions\OperandTypeException;
use IPP\Student\CustomExceptions\OperandValueException;
use IPP\Student\CustomExceptions\RedefinitionException;
use IPP\Student\CustomExceptions\SourceStructureException;
use IPP\Student\CustomExceptions\VariableAccessException;

/**
 * JUMPIFNEQ instruction class
 * Represents opcode JUMPIFNEQ
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 * @throws RedefinitionException
 * @throws VariableAccessException
 */
class JumpifneqInstruction implements IInstruction
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
            throw new SourceStructureException("JUMPIFNEQ instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'label') {
            throw new OperandTypeException("JUMPIFNEQ instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'var' && $this->args[1]['type'] !== 'int' && $this->args[1]['type'] !== 'bool' && $this->args[1]['type'] !== 'string' && $this->args[1]['type'] !== 'nil') {
            throw new OperandTypeException("JUMPIFNEQ instruction has invalid type of argument 2");
        }

        // arg3 check
        if ($this->args[2]['type'] !== 'var' && $this->args[2]['type'] !== 'int' && $this->args[2]['type'] !== 'bool' && $this->args[2]['type'] !== 'string' && $this->args[2]['type'] !== 'nil') {
            throw new OperandTypeException("JUMPIFNEQ instruction has invalid type of argument 3");
        }
    }

    /**
     * Execute JUMPIFNEQ instruction
     * @throws OperandTypeException
     * @throws RedefinitionException
     * @return void
     */
    public function execute(): void
    {
        // arg2 check
        $symb1 = $this->getSymbValue($this->args[1]);

        // arg3 check
        $symb2 = $this->getSymbValue($this->args[2]);

        $labelMap = $this->interpreter->getLabelMap();
        $label = $this->args[0]['value'];

        if ($symb1['type'] !== $symb2['type'] && $symb1['type'] !== 'nil' && $symb2['type'] !== 'nil') {
            throw new OperandTypeException("JUMPIFNEQ instruction has different types of arguments or are not nil");
        }

        if(!array_key_exists($label, $labelMap)) {
            throw new RedefinitionException("JUMPIFNEQ instruction has undefined label");
        }

        // Set values to correct format for comparison
        $symb1Value = $this->updateValue($symb1);
        $symb2Value = $this->updateValue($symb2);

        // JUMPIFNEQ
        if($symb1Value !== $symb2Value)
        {
            // Jump to label
            $this->interpreter->instructionPointer = intval($labelMap[$label]);
        }
    }

    /**
     * Get value of symbol
     * @param array{type: string, value: mixed} $symbol Symbol
     * @return array{type: string, value: mixed} Value of symbol
     * @throws OperandTypeException
     * @throws OperandValueException
     * @throws VariableAccessException
     */
    private function getSymbValue($symbol): mixed {
        if ($symbol['type'] === 'var') {
            // If it is variable, get value from variable
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                if (!$this->interpreter->isVarDefined($symbFrame, $symbName)) {
                    throw new VariableAccessException("JUMPIFNEQ instruction has undefined variable");
                }
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                return $symbValue;
            }
            else {
                throw new OperandValueException("JUMPIFNEQ instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === 'string' || $symbol['type'] === 'int' || $symbol['type'] === 'bool' || $symbol['type'] === 'nil'){
            // Otherwise return symbol
            $symbValue = $symbol;
            return $symbValue;
        }
        else {
            throw new OperandTypeException("JUMPIFNEQ instruction has invalid type of argument");
        }
    }

    /**
     * Set values to correct format for comparison
     * @param array{type: string, value: mixed} $symbol Symbol
     * @return mixed Value of symbol
     */
    private function updateValue($symbol): mixed {
        if ($symbol['type'] === 'int') {
            return intval($symbol['value']);
        } elseif ($symbol['type'] === 'bool') {
            return $symbol['value'] === 'true' ? true : false;
        } elseif ($symbol['type'] === 'nil') {
            return null;
        } else {
            return $symbol['value'];
        }
    }
}