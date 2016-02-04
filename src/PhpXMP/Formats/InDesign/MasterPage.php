<?php
namespace PhpXMP\Format\InDesign;

class MasterPage
{
    const LENGTH = 4096;

    // Master Pages are little endian
    public $GUID; // 16 bytes
    public $Magic; // 8 bytes
    public $ObjectStreamEndian; // 8 bit endianness
    public $Crap1; // 239 bytes of who cares
    public $SequenceNumber; // Unsigned 64bit Little Endian
    public $Crap2; // 8 bytes of who cares
    public $FilePages; // Unsigned 32bit Little Endian Number of pages
    public $Crap3; // 3812 bytes of who cares

    public static function unpack($buf) {
        $page = new self();

        $struct = unpack('H32guid/a8magic/cendian/H478crap/Psequence/H16crap2/Vpages/H7624crap3', $buf);

        if ($struct['guid'] != '0606edf5d81d46e5bd31efe7fe74b71d') {
            return false;
        }

        $page->GUID = $struct['guid'];
        $page->Magic = $struct['magic'];
        $page->ObjectStreamEndian = $struct['endian'];
        $page->Crap1 = $struct['crap'];
        $page->SequenceNumber = $struct['sequence'];
        $page->Crap2 = $struct['crap2'];
        $page->FilePages = $struct['pages'];
        $page->Crap3 = $struct['crap3'];

        return $page;
    }
}