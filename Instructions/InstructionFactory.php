<?php

/**
 * Factory for creating instructions based on opcode
 * @author Tomáš Barták
 * @package IPP\Student\Instructions
 * @version 1.0
 */

namespace IPP\Student\Instructions;

use IPP\Student\Interpreter;
use IPP\Student\Instructions\IInstruction;
use IPP\Student\CustomExceptions\SourceStructureException;

/**
 * Factory for creating instructions based on opcode
 * @throws SourceStructureException
 */
class InstructionFactory {
    /**
     * Create an instance of instruction based on opcode
     * @param string $opcode Opcode of instruction
     * @param array<array{type: string, value: mixed}> $args Arguments of instruction
     * @param Interpreter $interpreter Interpreter instance
     * @return IInstruction Instance of instruction
     * @throws SourceStructureException
     */
    public static function create(string $opcode, array $args, Interpreter $interpreter): IInstruction {
        switch ($opcode) {
            case 'MOVE':
                return new MoveInstruction($args, $interpreter);
            case 'CREATEFRAME':
                return new CreateframeInstruction($args, $interpreter);
            case 'PUSHFRAME':
                return new PushframeInstruction($args, $interpreter);
            case 'POPFRAME':
                return new PopframeInstruction($args, $interpreter);
            case 'DEFVAR':
                return new DefvarInstruction($args, $interpreter);
            case 'CALL':
                return new CallInstruction($args, $interpreter);
            case 'RETURN':
                return new ReturnInstruction($args, $interpreter);
            case 'PUSHS':
                return new PushsInstruction($args, $interpreter);
            case 'POPS':
                return new PopsInstruction($args, $interpreter);
            case 'ADD':
                return new AddInstruction($args, $interpreter);
            case 'SUB':
                return new SubInstruction($args, $interpreter);
            case 'MUL':
                return new MulInstruction($args, $interpreter);
            case 'IDIV':
                return new IdivInstruction($args, $interpreter);
            case 'LT':
                return new LtInstruction($args, $interpreter);
            case 'GT':
                return new GtInstruction($args, $interpreter);
            case 'EQ':
                return new EqInstruction($args, $interpreter);
            case 'AND':
                return new AndInstruction($args, $interpreter);
            case 'OR':
                return new OrInstruction($args, $interpreter);
            case 'NOT':
                return new NotInstruction($args, $interpreter);
            case 'INT2CHAR':
                return new Int2charInstruction($args, $interpreter);
            case 'STRI2INT':
                return new Stri2intInstruction($args, $interpreter);
            case 'READ':
                return new ReadInstruction($args, $interpreter);
            case 'WRITE':
                return new WriteInstruction($args, $interpreter);
            case 'CONCAT':
                return new ConcatInstruction($args, $interpreter);
            case 'STRLEN':
                return new StrlenInstruction($args, $interpreter);
            case 'GETCHAR':
                return new GetcharInstruction($args, $interpreter);
            case 'SETCHAR':
                return new SetcharInstruction($args, $interpreter);
            case 'TYPE':
                return new TypeInstruction($args, $interpreter);
            case 'LABEL':
                return new LabelInstruction($args);
            case 'JUMP':
                return new JumpInstruction($args, $interpreter);
            case 'JUMPIFEQ':
                return new JumpifeqInstruction($args, $interpreter);
            case 'JUMPIFNEQ':
                return new JumpifneqInstruction($args, $interpreter);
            case 'EXIT':
                return new ExitInstruction($args, $interpreter);
            case 'DPRINT':
                return new DprintInstruction($args, $interpreter);
            case 'BREAK':
                return new BreakInstruction($args, $interpreter);

            default:
                throw new SourceStructureException("Unknown instruction opcode: $opcode");
        }
    }
}
