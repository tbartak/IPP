<?php

/**
 * EXIT instruction class
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Instructions\IInstruction;
use IPP\Student\Interpreter;
use IPP\Student\CustomExceptions\OperandValueException;
use IPP\Student\CustomExceptions\OperandTypeException;
use IPP\Student\CustomExceptions\SourceStructureException;

/**
 * EXIT instruction class
 * Represents opcode EXIT
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 */
class ExitInstruction implements IInstruction
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
        if (count($this->args) !== 1) {
            throw new SourceStructureException("EXIT instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var' && $this->args[0]['type'] !== 'int') {
            throw new OperandTypeException("EXIT instruction has invalid type of argument 1");
        }
    }

    /**
     * Execute EXIT instruction
     * @throws OperandValueException
     * @return void
     */
    public function execute(): void
    {
        // retrieve the exit value from the argument
        $exitValue = $this->getSymbValue($this->args[0], 'int');

        // EXIT
        if ($exitValue < 0 || $exitValue > 9) {
            throw new OperandValueException("EXIT instruction has invalid value");
        }

        exit((int)$exitValue); // exits the program with the given exit code
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
            // If it is variable, get value from variable
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
                throw new OperandValueException("EXIT instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === $type){
            // Otherwise return value of symbol
            $symbValue = $symbol;
            return $symbValue['value'];
        }
        else {
            throw new OperandTypeException("EXIT instruction has invalid type of argument");
        }
    }
}