<?php

/**
 * READ instruction class
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
 * READ instruction class
 * Represents opcode READ
 * Implements interface of IInstruction
 * @throws OperandTypeException
 * @throws OperandValueException
 * @throws SourceStructureException
 */
class ReadInstruction implements IInstruction
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
     * @throws OperandValueException
     */
    public function __construct(array $args, Interpreter $interpreter)
    {
        $this->args = $args;
        $this->interpreter = $interpreter;

        // arg check
        if (count($this->args) != 2) {
            throw new SourceStructureException("READ instruction requires exactly two arguments.");
        }

        // arg1 check
        if ($this->args[0]['type'] != 'var') {
            throw new OperandTypeException("READ instruction requires first argument to be of type var.");
        }

        // arg2 check
        if ($this->args[1]['type'] != 'type') {
            if ($this->args[1]['type'] === 'var') {
                // arg2 value check
                throw new SourceStructureException("READ instruction requires second argument to be of type type.");
            }
            throw new OperandTypeException("READ instruction requires second argument to be of type type.");
        }

        if ($this->args[1]['type'] === 'type') {
            // arg2 value check
            if ($this->args[1]['value'] != 'int' && $this->args[1]['value'] != 'bool' && $this->args[1]['value'] != 'string') {
                throw new OperandValueException("READ instruction requires second argument to be of type int, bool or string.");
            }
        }
    }

    /**
     * Execute READ instruction
     * @return void
     * @throws OperandValueException
     */
    public function execute(): void
    {
        $var = $this->args[0]['value'];
        $type = $this->args[1]['value'];        

        // parse variable
        if (preg_match('/^(GF|LF|TF)@(.+)$/', $var, $matches)) {
            $frameType = $matches[1];
            $varName = $matches[2];
        }
        else {
            throw new OperandValueException("READ instruction has invalid format of argument 1");
        }

        $value = null;
        try { // read from stdin
            $value = $this->interpreter->readFromStdin($type);
        }
        catch (\Exception $e) { // error while reading -> set value and type to nil
            $value = 'nil';
            $type = 'nil';
        }

        // set variable value
        $this->interpreter->setVariableValue($frameType, $varName, $value, $type);
    }
}