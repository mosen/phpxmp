<?php
namespace PhpXMP\Format\PDF;

/**
 * PDF Syntax : Tokens.
 *
 * Static consts are used because PHP has no Enum type.
 *
 * @package PhpXMP\Format\PDF
 */
class Tokens
{
    const COMMENT = '%';

    const FALSE = 'false';
    const TRUE = 'true';

    const INTEGER = 'int';
    const REAL = 'real';

    const BEGIN_STRING = '('; // "("
    const END_STRING = ')'; // ")"

    const BEGIN_HEX = '<'; // "<"
    const END_HEX = '>'; // ">"

    const BEGIN_NAME = '/'; // "/"

    const BEGIN_ARRAY = '['; // "["
    const END_ARRAY = ']'; // "]"
    const BEGIN_DICT = '<<'; // "<<"
    const END_DICT = '>>'; // ">>"

    const KEYWORD = 'keyword';

    const EOF_MARKER = "%%EOF";

    const LF = 16;
    const CR = 17;
    const SPACE = 18;

    public static $EOL_MARKERS = [
        "\x0A", // LF
        "\x0D"  // CR
    ];

    public static $WHITE_SPACE = [
        "\x00", // NUL
        "\x09", // TAB
        "\x0A", // LF
        "\x0C", // FF
        "\x0D", // CR
        "\x20", // Space
    ];

    public static $KEYWORDS = [
        'xref',
        'startxref',
        'stream',
        'endstream',
        'null',
        'trailer',
        'obj',
        'endobj'
    ];

    public static $CONTEXT_BEGIN = [
        'xref' => 'xref',
        'stream' => 'stream',
        'trailer' => 'trailer',
        'obj' => 'obj'
    ];

    const START_XREF = "startxref";
}