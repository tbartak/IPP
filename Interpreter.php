<?php

/**
 * Project 2 - IPP
 * Implementation of the IPPcode24 XML representation interpreter.
 * @author Tomáš Barták (xbarta51)
 */

namespace IPP\Student;

use DOMDocument;
use IPP\Core\AbstractInterpreter;
use IPP\Student\Instructions\InstructionFactory;
use IPP\Student\CustomExceptions\RedefinitionException;
use IPP\Student\CustomExceptions\UndefinedException;
use IPP\Core\Exception\XMLException;
use IPP\Core\Exception\InputFileException;
use IPP\Student\CustomExceptions\OperandTypeException;
use IPP\Student\Instructions\IInstruction;
use IPP\Student\CustomExceptions\SourceStructureException;
use IPP\Student\CustomExceptions\ValueException;
use IPP\Student\CustomExceptions\VariableAccessException;
use IPP\Student\Utilities\TypeValidate;


/**
 * Class Interpreter
 * Responsible for interpreting IPPcode24 XML representation and executing the instructions.
 * Supports operations with variables, frames (GF, LF, TF), stacks, and basic I/O operations.
 * @package IPP\Student
 * @version 1.0
 */
class Interpreter extends AbstractInterpreter
{
    /** @var IInstruction[] Holds instances of instructions to be executed */
    private array $instructions = [];
    /** @var array<array{labelName: string, order: int}> Maps label names to their corresponding order */
    private array $labelMap = [];
    /** @var int Current instruction pointer */
    public int $instructionPointer = 1;
    /** @var mixed[] Stack for storing data values for PUSHS, POPS instructions */
    public array $dataStack = [];
    /** @var int[] Stores values for instruction pointer after executing CALL instruction to ensure continuous execution */
    public array $callStack = [];
    /** @var array<string, mixed[]> Contains all frames, global frame by default */
    public array $frameStack = ['GF' => []];
    /** @var mixed[][] Stack of local frames */
    public array $localFrameStack = [];
    /** @var mixed[]|null Used for temporary data storage (before calling a function) */
    public ?array $temporaryFrame = null;

    /**
     * Executes the IPPcode24 XML representation until EXIT instruction is encountered or all instructions are already executed.
     * @return int Exit code
     * @throws XMLException
     * @throws RedefinitionException
     * @throws SourceStructureException
     * @throws UndefinedException
     * @throws OperandTypeException
     * @throws ValueException
     * @throws VariableAccessException
     */
    public function execute(): int
    {
        $dom = $this->source->getDOMDocument();

        // Check header of XML
        $root = $dom->documentElement;
        if ($root->nodeName !== 'program') {
            throw new SourceStructureException("Invalid XML structure root.");
        }

        // Load instructions from XML
        $this->loadInstructions($dom);

        $maxOrder = max(array_keys($this->instructions)); // Finds the highest order number to determine the end of the program

        // Sequential execution of instructions, until the end of the program, instructionPointer can also be changed by JUMP, CALL, RETURN
        while($this->instructionPointer <= $maxOrder) {
            if (isset($this->instructions[$this->instructionPointer])) {
                $instruction = $this->instructions[$this->instructionPointer];
                $instruction->execute();
            }
            $this->instructionPointer++;
        }

        return 0;
    }

    /**
     * Loads instructions from the XML file and creates instances of the corresponding classes.
     * It validates the structure of the XML file and checks for redefinitions of labels.
     * @param DOMDocument $dom The DOMDocument object containing the XML file
     * @return void
     * @throws SourceStructureException
     * @throws RedefinitionException
     */
    private function loadInstructions(DOMDocument $dom): void {
        $instructions = $dom->getElementsByTagName('instruction');
        if ($instructions->length === 0) {
            throw new SourceStructureException("No instructions found in XML file.");
        }       
        
        // Additional validation for elements outside 'instruction' tags
        foreach ($dom->documentElement->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE && $node->nodeName !== 'instruction') {
                throw new SourceStructureException("Invalid XML structure: found unexpected element '{$node->nodeName}'.");
            }
        }

        $seenOrderNumbers = []; // Array to store order numbers of instructions to check for redefinitions

        // Iterates over all instruction elements in the XML file
        foreach ($instructions as $element) {
            $order = $element->getAttribute('order');
            $opcode = $element->getAttribute('opcode');

            // Check for missing attributes
            if (empty($opcode) || empty($order)) {
                throw new SourceStructureException("Opcode/Order attribute is missing in instruction.");
            }

            // Check for invalid/duplicate order attribute
            if (isset($seenOrderNumbers[$order])) {
                throw new SourceStructureException("Order number $order is already defined.");
            }
            else {
                if (intval($order) < 1) {
                    throw new SourceStructureException("Negative order attribute.");
                }
                $seenOrderNumbers[$order] = true;
            }

            $args = $this->parseArgs($element);

            // Checks duplicate label definitions and creates a map of labels with their order numbers
            if ($opcode === 'LABEL') {
                $labelName = $args[0]['value'];
                if(isset($this->labelMap[$labelName])) {
                    throw new RedefinitionException("Label $labelName is already defined.");
                }
                $this->labelMap[$labelName] = intval($order);
            }
            // Create an instance of the instruction class and store it in the instructions array
            $this->instructions[intval($order)] = InstructionFactory::create(strtoupper($opcode), $args, $this);
        }
    }

    /**
     * Parses arguments of the instruction element and returns them as an array.
     * It also validates each argument and ensures that they are stored in the correct order.
     * @param \DOMElement $element Instruction element that contains arguments
     * @return array<array{type: string, value: mixed}> Array of arguments with their types and values
     * @throws SourceStructureException
     */
    private function parseArgs(\DOMElement $element): array {
        $args = [];
        // Goes through all arguments of the instruction
        foreach ($element->childNodes as $argument) {
            // Check if the argument is an element node
            if ($argument instanceof \DOMElement) { 
                // Check if the argument name is valid, has to be in format of arg1, arg2, arg3
                if (in_array($argument->tagName, ['arg1', 'arg2', 'arg3']) === false) {
                    throw new SourceStructureException("Invalid tag name in instruction arguments.");
                }
                $argNum = (int)substr($argument->tagName, 3) - 1; // Parses the argument number from the tag name for indexing of the arg array
                $argType = $argument->getAttribute('type');
                $argValue = $argument->nodeValue;
                // Check if the argument value is valid according to the type
                if (!$this->validateArgValue($argType, $argValue)) {
                    throw new SourceStructureException("Invalid argument type or value.");
                }
                // Store the argument in the array with its type and value
                $args[$argNum] = [
                    'type' => $argType,
                    'value' => $argValue
                ];
            }
        }
        // Checks if arguments are not missing
        if (count($args) === 2 && ((!isset($args[0])) || (!isset($args[1])))) {
            throw new SourceStructureException("Missing argument in instruction.");
        }
        elseif (count($args) === 1 && (!isset($args[0]))) {
            throw new SourceStructureException("Missing argument 1 in instruction.");
        }
        return $args;
    }  
    
    /**
     * Validates the value of the argument based on its type.
     * @param string $type The type of the argument
     * @param string $value The value of the argument
     * @return bool True if the value is valid, false otherwise
     */
    private function validateArgValue(string $type, string $value): bool {
        return TypeValidate::validate($value, $type);
    }

    /**
     * Returns the label map containing label names and their corresponding order numbers.
     * @return array<array{labelName: string, order: int}>
     */
    public function getLabelMap(): array {
        return $this->labelMap;
    }   

    /**
     * Adds variable to the frame, if it is not already defined, otherwise throws an exception
     * @param string $frameType
     * @param string $variableName
     * @throws UndefinedException
     * @throws RedefinitionException
     * @return void
     */
    public function addVariableToFrame(string $frameType, string $variableName): void {
        $frame = &$this->getFrameReference($frameType); // Reference to the frame based on the frame type
        if ($frame === false) {
            throw new UndefinedException("Frame $frameType not initialized yet.");
        }

        if (array_key_exists($variableName, $frame)) {
            throw new RedefinitionException("Variable '$variableName' is already defined in $frameType.");
        }

        if ($frameType === 'LF' && empty($this->localFrameStack)) {
            throw new UndefinedException("Local frame (LF) is not currently available.");
        }

        if ($frameType === 'TF' && is_null($this->temporaryFrame)) {
            throw new UndefinedException("Temporary frame (TF) is not currently available.");
        }
    
        $frame[$variableName] = null; // Initialize variable with null value
    }
    
    /**
     * Returns reference to the frame based on the frame type
     * @param string $frameType The type of the frame (GF, LF, TF)
     * @return mixed Reference to the frame
     * @throws UndefinedException
     * @throws SourceStructureException
     */
    protected function &getFrameReference(string $frameType): mixed {
        switch ($frameType) {
            case 'GF':
                return $this->frameStack['GF'];
            case 'LF':
                $lastIndex = count($this->localFrameStack) - 1;
                if ($lastIndex < 0) {
                    throw new UndefinedException("Local frame (LF) is not initialized yet.");
                }
                return $this->localFrameStack[$lastIndex];
            case 'TF':
                if (is_null($this->temporaryFrame)) {
                    throw new UndefinedException("Temporary frame (TF) is not initialized.");
                }
                return $this->temporaryFrame;
            default:
                throw new SourceStructureException("Unknown frame type: $frameType");
        }
    }

    /**
     * Checks if variable is defined in the frame
     * @param string $frameType The type of the frame (GF, LF, TF)
     * @param string $variableName The name of the variable
     * @return bool True if the variable is defined, false otherwise
     */
    public function isVarDefined(string $frameType, string $variableName): bool {
        $frame = $this->getFrameReference($frameType);
        return array_key_exists($variableName, $frame);
    }
    
    /**
     * Returns value of the variable from the frame
     * @param string $frameType The type of the frame (GF, LF, TF)
     * @param string $variableName The name of the variable
     * @return mixed The information about the variable
     * @throws UndefinedException
     * @throws ValueException
     */
    public function getVariableValue(string $frameType, string $variableName): mixed {
        $frame = $this->getFrameReference($frameType);
        if ($frame === null) {
            throw new UndefinedException("Frame $frameType not initialized yet.");
        }
        if (!isset($frame[$variableName])) {
            throw new ValueException("Variable $variableName not found in frame $frameType or is null.");
        }
        return $frame[$variableName];
    }

    /**
     * Sets value of the variable in the frame
     * @param string $frameType The type of the frame (GF, LF, TF)
     * @param string $variableName The name of the variable
     * @param mixed $value The value to be set
     * @param string $type The type of the value
     * @throws VariableAccessException
     * @return void
     */
    public function setVariableValue(string $frameType, string $variableName, mixed $value, string $type): void {
        $frame = &$this->getFrameReference($frameType);
        if (!array_key_exists($variableName, $frame)) {
            throw new VariableAccessException("Variable $variableName not found in frame $frameType.");
        }
        $frame[$variableName]['value'] = $value; // Set value
        $frame[$variableName]['type'] = $type; // Set type
    }
    
    /**
     * Writes the string to the standard output based on the type
     * @param string $string The string to be written
     * @param string $type The type of the string
     * @return void
     * @throws OperandTypeException
     */
    public function writeToStdout(string $string, string $type): void {
        if ($type === 'int') {
            $this->stdout->writeInt((int) $string);
        } elseif ($type === 'bool') {
            $this->stdout->writeBool($string === 'true');
        } elseif ($type === 'nil') {
            $this->stdout->writeString('');
        } elseif ($type === 'string'){
            $decodedString = $this->decodeUnicodeSequences($string);
            $this->stdout->writeString($decodedString);
        }
        else {
            throw new OperandTypeException("Unsupported type for WRITE instruction.");
        }
    }

    /**
     * Decodes escape sequences into actual characters.
     * @param string $string The string containing escape sequences.
     * @return string The decoded string with escape sequences replaced by actual characters.
     */
    private function decodeUnicodeSequences(string $string): string {
        // Looks for escape sequences in the string and replaces them with actual characters
        return preg_replace_callback('/\\\\([0-9]{3})/', function ($matches) {
            // Convert the matched value to the corresponding ASCII character
            $char = chr(intval($matches[1]));
            return $char;
        }, $string);
    }

    /**
     * Writes the string to the standard error output
     * @param string $string The string to be written
     * @return void
     */
    public function writeToStderr(string $string): void {
        $this->stderr->writeString($string);
    }

    /**
     * Reads the value from the standard input based on the type
     * @param string $type The type of the value to be read
     * @return mixed The value read from the standard input
     * @throws InputFileException
     * @throws OperandTypeException
     */
    public function readFromStdin(string $type): mixed {
        switch ($type) {
            case 'int':
                $value = $this->input->readInt();
                if ($value === null) { // If the integer value could not be read
                    throw new InputFileException("Invalid integer value.");
                }
                return $value;
            case 'string':
                $value = $this->input->readString();
                if ($value === null) { // If the string value could not be read
                    throw new InputFileException("Invalid string value.");
                }
                return $value;
            case 'bool':
                $valueStr = $this->input->readString();
                if ($valueStr === null) { // If the boolean value could not be read
                    throw new InputFileException("Invalid boolean value.");
                }
                if ($valueStr !== 'true' && $valueStr !== 'false') { // If the boolean value is nor true nor false
                    throw new InputFileException("Invalid boolean value.");
                }
                if ($valueStr === 'true') {
                    return 'true';
                }
                return 'false';
            default:
                throw new OperandTypeException("Unsupported type for READ instruction.");
        }
    }

    /**
     * Creates a new temporary frame and discards the current one.
     * @return void
     */
    public function createTemporaryFrame(): void {
        $this->temporaryFrame = [];
    }
    
    /**
     * Pushes the temporary frame onto the top of the local frame stack.
     * @return void
     * @throws UndefinedException
     */
    public function pushFrame(): void {
        if ($this->temporaryFrame === null) {
            throw new UndefinedException("Temporary frame not initialized.");
        }
    
        // Adds the temporary frame to the top of the local frame stack
        $this->localFrameStack[] = $this->temporaryFrame;
    
        // Resets the temporary frame
        $this->temporaryFrame = null;
    }

    /**
     * Pops the top frame from the local frame stack and stores it in the temporary frame.
     * @return void
     * @throws UndefinedException
     */
    public function popFrame(): void {
        if (empty($this->localFrameStack)) {
            throw new UndefinedException("Local frame stack is empty, no frame to pop.");
        }
    
        // Pops the top frame from the local frame stack and stores it in the temporary frame
        $this->temporaryFrame = array_pop($this->localFrameStack);
    }
}
