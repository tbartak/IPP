<?php

/**
 * TYPE instruction class
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
use IPP\Student\CustomExceptions\ValueException;
use IPP\Student\CustomExceptions\VariableAccessException;

/**
 * TYPE instruction class
 * Represents opcode TYPE
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 * @throws VariableAccessException
 */
class TypeInstruction implements IInstruction
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
            throw new SourceStructureException("TYPE instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("TYPE instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'var' && $this->args[1]['type'] !== 'string' && $this->args[1]['type'] !== 'int' && $this->args[1]['type'] !== 'bool' && $this->args[1]['type'] !== 'nil') {
            throw new OperandTypeException("TYPE instruction has invalid type of argument 2");
        }
    }

    /**
     * Execute TYPE instruction
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
                throw new VariableAccessException("TYPE instruction has undefined variable");
            }
        } else {
            throw new OperandValueException("TYPE instruction has invalid format of argument 1");
        }

        // retrieve symb type
        $symbType = $this->getSymbType($this->args[1]);

        // TYPE
        $result = $symbType;
        $type = 'string';

        // Save result to variable
        $this->interpreter->setVariableValue($varFrame, $varName, $result, $type);
    }

    /**
     * Get type of symbol
     * Catches ValueException in case the variable is not initialized yet
     * @param array{type: string, value: mixed} $symbol Symbol
     * @return string Type of symbol
     * @throws OperandTypeException
     * @throws VariableAccessException
     * @throws OperandValueException
     */
    private function getSymbType($symbol): string {
        if ($symbol['type'] === 'var') {
            // If it is a variable, retrieve its value
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                if (!$this->interpreter->isVarDefined($symbFrame, $symbName)) {
                    throw new VariableAccessException("TYPE instruction has undefined variable");
                }
                try {
                    $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                } catch (ValueException $e) { // in case the variable is not initialized yet
                    return '';
                }
                return $symbValue['type'];
            }
            else {
                throw new OperandValueException("TYPE instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === 'string' || $symbol['type'] === 'int' || $symbol['type'] === 'bool' || $symbol['type'] === 'nil'){
            // Otherwise, return type of constant
            $symbValue = $symbol;
            return $symbValue['type'];
        }
        else {
            throw new OperandTypeException("TYPE instruction has invalid type of argument");
        }
    }
}