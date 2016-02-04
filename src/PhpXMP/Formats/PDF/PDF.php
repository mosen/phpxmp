<?php
namespace PhpXMP\Format\PDF;
use PhpXMP\Format\PDF\CrossReferenceTable\Parser;

/**
 * Portable Document Format (PDF) Support.
 *
 * @package RsnSavant\Format\PDF
 */
class PDF
{
    /**
     * @var string Path or URI to the PDF file.
     */
    protected $uri;

    /**
     * @var int File descriptor of the open file.
     */
    private $fd;

    private $header;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Find the offset of the cross reference table by reading from the end of the file.
     *
     * You should access objects this way to avoid parsing the entire file into memory which may not even
     * be possible given your `memory_limit`.
     *
     * @return int File offset of the Cross Reference Table.
     * @throws \Exception If file is unreadable
     */
    public function getCrossReferenceTableOffset($fd)
    {
        // The `startxref` keyword should be present in the last 4k, since it is the last keyword before the EOF marker.
        fseek($fd, -4096, SEEK_END);
        $buf = fread($fd, 4096);

        // Read lines backwards by parsing until a CR/LF/CRLF is encountered.
        $offset = 4096;
        $line = "";

        while ($offset > 0) {
            $prevOffset = $offset;
            $offset = $this->prevLineEnd($buf, $offset-1);

            $oldLine = $line;
            $line = substr($buf, $offset + 1, ($prevOffset - ($offset + 1)));


            if (substr($line, 0, strlen("startxref")) == "startxref") {
                return $oldLine;
            }
        }

        return null;
    }

    /**
     * Parse the PDF trailer and XREF sections.
     * Everything else lazy loaded.
     *
     * @throws \Exception
     */
    public function parse()
    {
        $fd = fopen($this->uri, 'r');

        if (!$fd) {
            throw new \Exception("Unable to open file for reading: {$this->uri}.");
        }

        $xrefOffset = $this->getCrossReferenceTableOffset($fd);

        if ($xrefOffset === null || !is_numeric($xrefOffset)) {
            throw new \Exception("Cannot find XREF section, or maybe not a PDF, aborting.");
        }

        $xrefOffset = intval($xrefOffset);
        echo "Found XREF at offset: {$xrefOffset}\n";

        $crossReferenceTable = CrossReferenceTable::unpack($fd, $xrefOffset);

        print_r($crossReferenceTable->getReferences());
    }

    protected function prevLineEnd($buf, $offset)
    {
        while ($offset > 0 && !in_array($buf[$offset], ["\r", "\n"])) {
            // Also check for CRLF
            $offset--;
        }

        return $offset;
    }

}