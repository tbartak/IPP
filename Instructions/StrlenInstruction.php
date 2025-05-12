<?php

/**
 * STRLEN instruction class
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

class StrlenInstruction implements IInstruction
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
            throw new SourceStructureException("STRLEN instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("STRLEN instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'var' && $this->args[1]['type'] !== 'string') {
            throw new OperandTypeException("STRLEN instruction has invalid type of argument 2");
        }
    }

    /**
     * Execute STRLEN instruction
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
                throw new VariableAccessException("STRLEN instruction has undefined variable");
            }
        } else {
            throw new OperandValueException("STRLEN instruction has invalid format of argument 1");
        }

        // retrieve value of symb
        $symbValue = $this->getSymbValue($this->args[1], 'string');

        // STRLEN
        $result = strlen($symbValue);
        $type = 'int';

        // Save result to variable
        $this->interpreter->setVariableValue($varFrame, $varName, $result, $type);
    }

    /**
     * Get value of symbol
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
                throw new OperandValueException("STRLEN instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === $type){
            // Otherwise return value of symbol
            $symbValue = $symbol;
            return $symbValue['value'];
        }
        else {
            throw new OperandTypeException("STRLEN instruction has invalid type of argument");
        }
    }
}