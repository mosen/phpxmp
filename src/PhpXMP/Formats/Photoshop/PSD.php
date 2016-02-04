<?php
namespace PhpXMP\Format\Photoshop;

/**
 * Extract the XMP Packet(s) from the .PSD Photoshop format.
 *
 * @package PhpXMP\Formats\Photoshop
 */
class PSD
{
    /**
     * @var Header PSD File Header
     */
    protected $header;

    /**
     * @var array Array of ImageResource classes indicating offsets to Image Resource sections.
     */
    protected $resources;

    /**
     * @var string XMP Data as unparsed string.
     */
    protected $xmpData;

    /**
     * @var string IPTC Binary Data.
     */
    protected $iptcData;

    public static function validate($uri)
    {
        $fd = fopen($uri, 'rb');
        if (!$fd) return false;

        $bytes = fread($fd, 4);

        //$magicValue = unpack('c4magic', $bytes);
        if ($bytes !== Header::MAGIC) {
            fclose($fd);
            return false;
        }

        fseek($fd, 4, SEEK_CUR);
        $bytes = fread($fd, 2);
        $fileVersion = unpack('nversion', $bytes);
        if ($fileVersion['version'] != 1 && $fileVersion['version'] != 2) {
            fclose($fd);
            return false;
        }

        fclose($fd);
        return true;
    }

    public function __construct($uri)
    {
        $fd = fopen($uri, 'rb');

        $bytes = fread($fd, 30);
        if (fseek($fd, 30, SEEK_CUR) == -1) return false;

        $header = Header::unpack($bytes);
        $this->header = $header;

        $psirOrigin = 26 + 4 + $header->colorModeLength;

        if (fseek($fd, $psirOrigin, SEEK_SET) == -1) return false;

        $bytes = fread($fd, 4);
        fseek($fd, 4, SEEK_CUR);
        $psirLen = unpack('Nlength', $bytes);
        //echo "PSIR Length: {$psirLen['length']}\n";

        $resources = ImageResources::unpack($fd, $psirOrigin+4, $psirLen['length']); // +4 is to offset the 32bit PSIR length int
        $this->resources = $resources;

        if (isset($this->resources[ImageResources::ID_XMP])) {
            $resource = $this->resources[ImageResources::ID_XMP];
            fseek($fd, $psirOrigin+4+$resource->psirOffset, SEEK_SET);
            $resourceData = fread($fd, $resource->length);
            $this->xmpData = $resourceData;
        }

        if (isset($this->resources[ImageResources::ID_IPTC])) {
            $resource = $this->resources[ImageResources::ID_IPTC];
            fseek($fd, $psirOrigin+4+$resource->psirOffset, SEEK_SET);
            $resourceData = fread($fd, $resource->length);
            $this->iptcData = $resourceData;
        }

        $layerMaskOffset = $psirOrigin + 4 + $psirLen['length'];
        fseek($fd, $layerMaskOffset, SEEK_SET);
        $bytes = fread($fd, 4);
        $layerMaskLen = unpack('Nlength', $bytes);

        //$layerMaskInfo = LayerMaskInfo::unpack($fd, $layerMaskOffset+4, $layerMaskLen);


        fclose($fd);
    }

    public function getXMPString()
    {
        return $this->xmpData;
    }

    public function getIPTCData()
    {
        return iptcparse($this->iptcData);
    }

    public function getHeader()
    {
        return $this->header;
    }

}