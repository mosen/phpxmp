<?php
namespace PhpXMP\Format\InDesign;

/**
 * This class represents a contiguous object marker (either header or trailer marker).
 *
 * @package PhpXMP\Format\InDesign
 */
class ContiguousObjectMarker
{
    const LENGTH = 32;

    public $GUID; // 16 bytes
    public $ObjectUID; // unsigned 32 bit
    public $ObjectClassID; // unsigned 32 bit
    public $StreamLength; // unsigned 32 bit
    public $Checksum; // unsigned 32 bit

    public $IsHeader = false;
    public $IsTrailer = false;

    public static function unpack($buf) {
        $marker = new self();

        $struct = unpack('H32guid/Vobjectuid/Vclassid/Vstreamlength/Vchecksum', $buf);

        $marker->GUID = $struct['guid'];
        $marker->ObjectUID = $struct['objectuid'];
        $marker->ObjectClassID = $struct['classid'];
        $marker->StreamLength = $struct['streamlength'];
        $marker->Checksum = $struct['checksum'];
        $marker->IsHeader = ($struct['guid'] == 'de39397951884b6c8e63eef8aee0dd38');
        $marker->IsTrailer = ($struct['guid'] == '');

        return $marker;
    }
}