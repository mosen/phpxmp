<?php
namespace PhpXMP\Format\Photoshop;

/**
 * Photoshop (.PSD) file header (partial implementation).
 *
 * @see http://www.adobe.com/devnet-apps/photoshop/fileformatashtml/#50577409_pgfId-1055726
 * @package PhpXMP\Format\Photoshop
 */
class Header
{
    const LENGTH = 30;
    const MAGIC = '8BPS';
    const VERSION_PSD = 1;
    const VERSION_PSB = 2;

    static $MODES = [
        0 => "BITMAP",
        1 => "GRAYSCALE",
        2 => "INDEXED",
        3 => "RGB",
        4 => "CMYK",
        7 => "MULTICHANNEL",
        8 => "DUOTONE",
        9 => "LAB"
    ];

    public $magic;
    public $version;
    public $channels;
    public $imageHeight;
    public $imageWidth;
    public $depth;
    public $colorMode;
    public $colorModeLength; // Length of the color mode section

    /**
     * Unpack a sequence of bytes into a PSD Header Object.
     *
     * @param string $buf PSD Header bytes
     * @return Header
     */
    public static function unpack($buf)
    {
        $header = new self();

        $struct = unpack('a4magic/nversion/c6reserved/nchannels/Nheight/Nwidth/ndepth/nmode/Ncmlen', $buf);

        $header->magic = $struct['magic'];
        $header->version = $struct['version'];
        $header->channels = $struct['channels'];
        $header->imageHeight = $struct['height'];
        $header->imageWidth = $struct['width'];
        $header->depth = $struct['depth'];
        $header->colorMode = $struct['mode'];
        $header->colorModeLength = $struct['cmlen'];

        return $header;
    }
}