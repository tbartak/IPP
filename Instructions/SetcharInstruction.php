<?php

/**
 * SETCHAR instruction class
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

/**
 * SETCHAR instruction class
 * Represents opcode SETCHAR
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 * @throws StringOperationException
 */
class SetcharInstruction implements IInstruction
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
            throw new SourceStructureException("SETCHAR instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("SETCHAR instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'var' && $this->args[1]['type'] !== 'int') {
            throw new OperandTypeException("SETCHAR instruction has invalid type of argument 2");
        }

        // arg3 check
        if ($this->args[2]['type'] !== 'var' && $this->args[2]['type'] !== 'string') {
            throw new OperandTypeException("SETCHAR instruction has invalid type of argument 3");
        }
    }

    /**
     * Execute SETCHAR instruction
     * @throws StringOperationException
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
            $varValue = $this->interpreter->getVariableValue($varFrame, $varName);
            if ($varValue['type'] !== 'string') {
                throw new OperandTypeException("SETCHAR instruction has invalid type of argument 1");
            }
        } else {
            throw new OperandValueException("SETCHAR instruction has invalid format of argument 1");
        }

        // retrieve index from argument
        $symbIndex = $this->getSymbValue($this->args[1], 'int');

        // retrieve value from argument
        $symbValue = $this->getSymbValue($this->args[2], 'string');

        // SETCHAR
        if ($symbIndex < 0 || $symbIndex >= strlen($varValue['value'])) {
            throw new StringOperationException("SETCHAR instruction has invalid index");
        }
        // Retrive character that should be changed
        $charToChange = substr($symbValue, 0, 1);
        // Change the character
        $result = substr_replace($varValue['value'], $charToChange, $symbIndex, 1);
        $type = 'string';

        // Save result to variable
        $this->interpreter->setVariableValue($varFrame, $varName, $result, $type);
    }

    /**
     * @param array{type: string, value: mixed} $symbol Symbol
     * @param string $type Type of symbol
     * @return mixed Value of symbol
     * @throws OperandTypeException
     * @throws OperandValueException
     */
    private function getSymbValue($symbol, $type): mixed {
        if ($symbol['type'] === 'var') {
            // If it is a variable, retrieve its value
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                if ($symbValue['type'] !== $type) {
                    throw new OperandTypeException("Operand must be of type $type.");
                }
                return $symbValue['value'];
            }
            else {
                throw new OperandValueException("SETCHAR instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === $type){
            // Otherwise return value of symbol
            $symbValue = $symbol;
            return $symbValue['value'];
        }
        else {
            throw new OperandTypeException("SETCHAR instruction has invalid type of argument");
        }
    }
}