<?php

/**
 * MOVE instruction class
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
 * MOVE instruction class
 * Represents opcode MOVE
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 */
class MoveInstruction implements IInstruction
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
            throw new SourceStructureException("MOVE instruction has invalid amount of arguments");
        }

        // arg1 check
        if ($this->args[0]['type'] !== 'var') {
            throw new OperandTypeException("MOVE instruction has invalid type of argument 1");
        }

        // arg2 check
        if ($this->args[1]['type'] !== 'var' && $this->args[1]['type'] !== 'int' && $this->args[1]['type'] !== 'bool' && $this->args[1]['type'] !== 'string' && $this->args[1]['type'] !== 'nil') {
            throw new OperandTypeException("MOVE instruction has invalid type of argument 2");
        }
    }

    /**
     * Execute MOVE instruction
     * @throws OperandValueException
     * @return void
     */
    public function execute(): void
    {
        $dst = $this->args[0];
        $src = $this->args[1];

        // arg1 check
        if (!preg_match('/^(GF|LF|TF)@([_\-$&%*!?a-zA-Z][_\-$&%*!?a-zA-Z0-9]*)$/', $dst['value'], $dstMatches)) {
            throw new OperandValueException("Invalid variable format for destination in MOVE instruction: " . $dst['value']);
        }
        $dstFrameType = $dstMatches[1];
        $dstVariableName = $dstMatches[2];

        // retrieve the value of the symbol
        $symbValue = $this->getSymbValue($src);
        $srcValue = $symbValue['value'];
        $srcType = $symbValue['type'];

        // MOVE -> save value to variable
        $this->interpreter->setVariableValue($dstFrameType, $dstVariableName, $srcValue, $srcType);
    }

    /**
     * Get value of symbol
     * @param array{type: string, value: mixed} $symbol Symbol
     * @return array{type: string, value: mixed} Information about symbol
     * @throws OperandTypeException
     * @throws OperandValueException
     */
    private function getSymbValue($symbol): array {
        if ($symbol['type'] === 'var') {
            // If it is variable, get value from variable
            if(preg_match('/^(GF|LF|TF)@(.+)$/', $symbol['value'], $matches)) {
                $symbFrame = $matches[1];
                $symbName = $matches[2];
                $symbValue = $this->interpreter->getVariableValue($symbFrame, $symbName);
                return $symbValue;
            }
            else {
                throw new OperandValueException("MOVE instruction has invalid format of argument");
            }
        } elseif ($symbol['type'] === 'int' || $symbol['type'] === 'bool' || $symbol['type'] === 'string' || $symbol['type'] === 'nil'){
            // Otherwise return value of symbol
            $symbValue = $symbol;
            return $symbValue;
        }
        else {
            throw new OperandTypeException("MOVE instruction has invalid type of argument");
        }
    }
}