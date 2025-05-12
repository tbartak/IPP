<?php

/**
 * POPS instruction class
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
 * POPS instruction class
 * Represents opcode POPS
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 * @throws ValueException
 * @throws VariableAccessException
 */ 
class PopsInstruction implements IInstruction
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
     * @throws OperandValueException
     * @throws OperandTypeException
     */
    public function __construct(array $args, Interpreter $interpreter)
    {
        $this->args = $args;
        $this->interpreter = $interpreter;

        // arg check
        if (count($this->args) !== 1) {
            throw new SourceStructureException("POPS instruction has invalid amount of arguments");
        }

        if (!preg_match('/^(GF|LF|TF)@[_\-$&%*!?a-zA-Z][_\-$&%*!?a-zA-Z0-9]*$/', $this->args[0]['value'])) {
            throw new OperandValueException("POPS instruction has invalid format of argument 1");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var'){
            throw new OperandTypeException("POPS instruction has invalid type of argument 1");
        }
    }

    /**
     * Execute POPS instruction
     * @throws ValueException
     * @throws VariableAccessException
     * @return void
     */
    public function execute(): void
    {
        if (empty($this->interpreter->dataStack)) {
            throw new ValueException("POPS instruction has empty data stack");
        }

        // arg1 check
        if (preg_match('/^(GF|LF|TF)@(.+)$/', $this->args[0]['value'], $matches)) {
            $varFrame = $matches[1];
            $varName = $matches[2];
            if (!$this->interpreter->isVarDefined($varFrame, $varName)) {
                throw new VariableAccessException("POPS instruction has undefined variable");
            }
            
            // Gets the last value from the data stack and assigns it to the variable
            $varValue = array_pop($this->interpreter->dataStack);
            $this->interpreter->setVariableValue($varFrame, $varName, $varValue['value'], $varValue['type']);
        }
    }
}