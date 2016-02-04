<?php
namespace PhpXMP\Format\Photoshop;

/**
 * This class unpacks a PSIR (Photoshop Image Resource) structure.
 *
 * @package PhpXMP\Format\Photoshop
 */
class ImageResource
{
    /**
     * @var int Image resource identifier
     */
    public $id;

    /**
     * @var string Image resource type code
     */
    public $type;

    /**
     * @var int Image resource data length
     */
    public $length;

    /**
     * @var int Image resource PSIR offset (relative to the start of the PSIR section, not the file).
     */
    public $psirOffset;

    public static function unpack($buf)
    {

    }
}