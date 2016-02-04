<?php
namespace PhpXMP\Format\PDF;

use PhpXMP\Format\PDF\Parser\Symbol;

/**
 * Simple PDF parser.
 * A very bad implementation, im not really a CS major :) just taken from Recursive Descent parser examples.
 *
 * @package RsnSavant\Format\PDF
 */
class Parser
{
    /**
     * @see PDF File Specification 1.7 - Table 3.1 White space characters
     */
    const WHITESPACE_CHARACTERS = [
        "\x00", // NUL
        "\x09", // TAB
        "\x0A", // LF
        "\x0C", // FF
        "\x0D", // CR
        "\x20", // Space
    ];

    /**
     * @see PDF File Specification 1.7 - Section 3.1.1 Character Set
     */
    const DELIMITER_CHARACTERS = [
        "(", ")", "<", ">", "[", "]", "/", "%"
    ];

    /**
     * @var mixed The current symbol
     */
    private $symbol = null;

    /**
     * @var int Buffer offset pointer
     */
    private $offset = -1;

    /**
     * @var array List of valid symbols
     */
    protected $symbols = [];

    public function __construct($buffer)
    {
        $this->buffer = $buffer;

        $this->symbols['comment'] = Symbol::create('comment');
        $this->symbols['bool'] = Symbol::create('boolean');
        $this->symbols['integer'] = Symbol::create('integer');
        $this->symbols['real'] = Symbol::create('real');
        $this->symbols['string'] = Symbol::create('string');
        $this->symbols['name'] = Symbol::create('name');
        $this->symbols['arraystart'] = Symbol::create('arraystart');
        $this->symbols['arrayend'] = Symbol::create('arrayend');
        $this->symbols['stringstart'] = Symbol::create('stringstart');
        $this->symbols['stringend'] = Symbol::create('stringend');
        $this->symbols['lt'] = Symbol::create('lt');
        $this->symbols['gt'] = Symbol::create('gt');
        $this->symbols['EOL'] = Symbol::create('EOL');
    }

    protected function accept($symbol)
    {
        if ($this->symbol == $symbol) {
            $this->symbol = $this->nextSymbol();
            return true;
        }

        return false;
    }

    protected function expect($symbol)
    {
        if ($this->accept($symbol)) {
            return true;
        }

        echo "Error expected symbol not found\n";

        return false;
    }

    protected function acceptString()
    {
        if (!$this->symbol instanceof Symbol) {
            $str = $this->symbol;
            $this->symbol = $this->nextSymbol();
            return $str;
        }

        return false;
    }

    protected function expectString()
    {
        if ($this->acceptString()) {
            return true;
        }

        echo "Error expected string not found\n";

        return false;
    }

    /**
     * Get the next token: any contiguous set of characters not included in
     * the whitespace or delimiter entities.
     *
     * @return string
     */
    protected function nextToken()
    {
        $token = "";

        do {
            $this->offset++;

            $char = substr($this->buffer, $this->offset, 1);
            if (in_array($char, self::WHITESPACE_CHARACTERS)) {
                return $token;
            }

            if (in_array($char, self::DELIMITER_CHARACTERS)) {
                if (strlen($token) > 0) {
                    $this->offset--;
                    return $token;
                } else {
                    return $char;
                }
            } else {
                $token .= $char;
            }
        } while ($this->offset < (strlen($this->buffer) - 1));

        return $token;
    }

    protected function nextSymbol()
    {
        $token = $this->nextToken();

        switch($token) {
        case '%':
            return $this->symbols['comment'];
        case '<':
            return $this->symbols['lt'];
        case '>':
            return $this->symbols['gt'];
        case '(':
            return $this->symbols['stringstart'];
        case ')':
            return $this->symbols['stringend'];
        case '[':
            return $this->symbols['arraystart'];
        case ']':
            return $this->symbols['arrayend'];
        case '/':
            return $this->symbols['name'];
        default:
            return $token;
        }

    }

    /**
     * Parse a Hexadecimal value which is encoded as a string containing 0-9,A-F
     */
    public function hex()
    {

    }

    public function line()
    {
        $this->expect($this->symbols['EOL']);
    }

    /**
     * Parse a comment which can include any character until the end of a line.
     */
    public function comment()
    {

    }

    /**
     * Parse an array type
     */
    public function arr()
    {
        do {
            $this->symbol = $this->nextSymbol();
        } while ($this->symbol != $this->symbols['arrayend']);
    }

    public function dict()
    {
        do {
            $this->expect($this->symbols['name']);
            $key = $this->object();
            $value = $this->object();
        } while ($this->symbol != $this->symbols['gt']);



        if ($key === false) {
            throw new \Exception('Dictionary does not contain key or key was zero length');
        }

        // The next occurring object is the value.


        //return [$key => $value];
    }


    public function object()
    {
        do {
            $this->symbol = $this->nextSymbol();

            if ($this->accept($this->symbols['lt'])) {
                if ($this->accept($this->symbols['lt'])) {
                    $this->dict();
                } else {
                    $this->hex();
                }
            }

            if ($this->accept($this->symbols['comment'])) {
                $str = "";
                do {
                    $str .= $this->acceptString();
                } while ($this->acceptString());
            }

            if ($this->accept($this->symbols['name'])) {
                return $this->acceptString();
            }

            if ($this->accept($this->symbols['arraystart'])) {
                $this->arr();
            }


        } while ($this->symbol !== "");
    }

    public function parse()
    {
        $this->object();
    }
}