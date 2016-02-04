<?php
namespace PhpXMP\Format\PDF\CrossReferenceTable;

use PhpXMP\Format\PDF\CrossReferenceTable;
use PhpXMP\Format\PDF\Tokens;

class Parser
{
    /**
     * @var resource File Handle
     */
    private $fd;

    /**
     * @var string Accumulated value
     */
    private $value;

    /**
     * @var array Tokens occurring on the current line.
     */
    private $line = [];

    /**
     * @var string Track the object context eg. "xref" if we are inside the cross reference table.
     */
    private $context;


    private $referenceTable = null;

    public function __construct($fd)
    {
        $this->fd = $fd;
    }

    public function nextToken()
    {
        $byte = fread($this->fd, 1);
        $value = null;
        $token = null;

        if (in_array($byte, Tokens::$WHITE_SPACE)) {
            if (strlen($this->value) > 0) {
                $finalValue = $this->value;
                $this->value = "";

                return [$finalValue, $this->nextToken()];
            } else {
                return $this->nextToken();
            }
        }

        switch ($byte) {
        case "%":
            $token = Tokens::COMMENT;
            break;
        case "(":
            $token = Tokens::BEGIN_STRING;
            break;
        case ")":
            $token = Tokens::END_STRING;
            break;
        case "<": // Could be dict or hex
            $token = Tokens::BEGIN_HEX;
            break;
        case ">": // Ditto
            $token = Tokens::END_HEX;
            break;
        case "\n":
            $token = Tokens::LF;
            break;
        case "\r":
            $token = Tokens::CR;
            break;
        case " ":
            $token = Tokens::SPACE;
            break;
        case "/":
            $token = Tokens::BEGIN_NAME;
            break;
        case "[":
            $token = Tokens::BEGIN_ARRAY;
            break;
        case "]":
            $token = Tokens::END_ARRAY;
            break;
        default:
            $this->value .= $byte;
        }

        if ($token == Tokens::LF
        || $token == Tokens::CR
        || $token == Tokens::SPACE) {
            $value = $this->value;
            $this->value = "";

            return [$value, $token];
        } else {
            return $token;
        }

    }

    public function parse()
    {

        while (!feof($this->fd)) {
            $tokens = $this->nextToken();

            // has a value
            if (is_array($tokens)) {
                $value = $tokens[0];
                $delimiter = $tokens[1];

                if (($delimiter == Tokens::CR || $delimiter == Tokens::LF) && empty($value)) continue;

                if ($this->context === 'xref') {
                    if (in_array($delimiter, Tokens::$EOL_MARKERS)) {
                        print_r($this->line);
                        $this->line = [];
                    } else {
                        $this->line[] = $value;
                    }
                }


                if (in_array($value, Tokens::$CONTEXT_BEGIN)) {
                    $this->context = Tokens::$CONTEXT_BEGIN[$value];
                    echo "Switched context to {$this->context}\n";

                    if ($this->referenceTable === null) {
                        //$this->referenceTable = new CrossReferenceTable();
                        //$crLf = $this->nextToken();
                        $objectStart = $this->nextToken();
                        $objectCount = $this->nextToken();

                        $this->referenceTable = CrossReferenceTable::unpack($this->fd, ftell($this->fd), $objectStart[0], $objectCount[0]);

                        print_r($this->referenceTable);
                    }
                }



                echo $value."\n";
//                if ($delimiter !== Tokens::CR && $delimiter !== Tokens::LF) {
//                    $this->line[] = $tokens;
//                } else {
//                    echo "Print this line\n";
//                    print_r($this->line);
//                    $this->line = [];
//                }
            } else {
                if ($tokens !== Tokens::CR && $tokens !== Tokens::LF) {
                    $this->line[] = $tokens;
                } else {
                    echo "Print this line\n";
                    print_r($this->line);
                    $this->line = [];
                }
            }
        }
    }
}