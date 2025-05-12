<?php

/**
 * DPRINT instruction class
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

/**
 * DPRINT instruction class
 * Represents opcode DPRINT
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 */
class DprintInstruction implements IInstruction
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
     */
    public function __construct(array $args, Interpreter $interpreter)
    {
        $this->args = $args;
        $this->interpreter = $interpreter;

        // arg check
        if (count($this->args) !== 1) {
            throw new SourceStructureException("DPRINT instruction has invalid amount of arguments");
        }
    }

    /**
     * Execute DPRINT instruction
     * @return void
     */
    public function execute(): void
    {
        // Retrieve value of symbol
        $symbValue = $this->getSymbValue($this->args[0]);
        
        // Write value to stderr
        $this->interpreter->writeToStderr($symbValue . "\n");
    }

    /**
     * Get value of symbol
     * @param array{type: string, value: mixed} $symbol Symbol
     * @throws OperandTypeException
     * @throws OperandValueException
     * @return mixed Value of symbol
     */
    private function getSymbValue($symbol): mixed {
        if ($symbol['type'] === 'var') {
            // If it is variable, get value from variable
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                return $symbValue['value'];
            }
            else {
                throw new OperandValueException("DPRINT instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === 'string' || $symbol['type'] === 'int' || $symbol['type'] === 'bool' || $symbol['type'] === 'nil'){
            // Otherwise return value of symbol
            $symbValue = $symbol;
            return $symbValue['value'];
        }
        else {
            throw new OperandTypeException("DPRINT instruction has invalid type of argument");
        }
    }
}