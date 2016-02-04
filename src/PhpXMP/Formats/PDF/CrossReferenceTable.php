<?php
namespace RsnSavant\Format\PDF;

use RsnSavant\Format\PDF\CrossReferenceTable\Parser;

/**
 * This class is a basic representation of a PDF Cross Reference Table.
 * @see PDF Specification 1.7, Section 3.4.3 Cross-Reference Table
 *
 * @package RsnSavant\Format\PDF
 */
class CrossReferenceTable
{
    const START_KEYWORD = "xref\r";

    protected $references = [];

    /**
     * Unpack a PDF Cross Reference Table using an open file pointer.
     *
     * This is a factory method for the CrossReferenceTable class.
     *
     * @param resource $fd Open file handle/file descriptor
     * @param int $offset Offset to the start of the Cross reference table.
     * @return CrossReferenceTable
     * @throws \Exception
     */
    public static function unpack($fd, $offset)
    {
        $currOffset = ftell($fd);

        if ($currOffset !== $offset) {
            $status = fseek($fd, $offset, SEEK_SET);
            if ($status == -1) {
                throw new \Exception("Cannot seek to cross reference table, maybe you tried to seek past the end of the file.");
            }
        }

        $crossReferenceTable = new self();

        $sectionStart = fread($fd, strlen(self::START_KEYWORD));
        if ($sectionStart !== self::START_KEYWORD) {
            throw new \Exception("The cross reference table could not be found at this offset.");
        }

        $objectBounds = "";
        $char = "";

        while ($char != "\r") {
            $char = fread($fd, 1);
            $objectBounds .= $char;
        }

        $bounds = explode(" ", $objectBounds);
        $objectStart = $bounds[0];
        $objectCount = $bounds[1];
        $xrefTable = fread($fd, 20 * $objectCount);

        for ($i = 0; $i < $objectCount; $i++) {
            $entry = substr($xrefTable, $i * 20, 20);
            $entryAttrs = explode(' ', $entry);
            $crossReferenceTable->addReference($objectStart+$i, $entryAttrs[0], $entryAttrs[1], (trim($entryAttrs[2]) == "n"));
        }

        return $crossReferenceTable;
    }

    /**
     * Add an object reference to the cross reference table.
     *
     * @param int  $objectNumber The indirect object number.
     * @param int  $offset       The file offset of the object.
     * @param int  $generation   The generation number.
     * @param bool $inUse        This reference is used (not free)
     */
    public function addReference($objectNumber, $offset, $generation, $inUse) {
        if ($inUse) {
            $this->references[$objectNumber] = [$offset, $generation];
        }
    }

    /**
     * Get all of the object references.
     *
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }
}