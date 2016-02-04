<?php
namespace PhpXMP\Format;

/**
 * PNG File Support
 *
 * @package PhpXMP\Format
 */
class PNG
{
    const ITXT_HEADER_LENGTH = 22;
    const ITXT_HEADER_DATA_XMP = "XML:com.adobe.xmp\0\0\0\0\0";

    public static $CHUNK_TYPES = [
        'IHDR',
        'PLTE',
        'IDAT',
        'IEND',
        'cHRM',
        'gAMA',
        'iCCP',
        'sBIT',
        'sRGB',
        'bKGD',
        'hIST',
        'tRNS',
        'pHYs',
        'sPLT',
        'tIME',
        'iTXt',
        'tEXt',
        'zTXt'
    ];

    protected $chunks = [];

    public static function validate($uri)
    {

    }

    public function __construct($uri)
    {
        $fd = fopen($uri, 'rb');
        $offset = 8;
        fseek($fd, $offset, SEEK_SET); // First 8 bytes is just identification

        // begin loop
        while(!feof($fd)) {
            $startOffset = $offset;

            $bytes = fread($fd, 4);
            $offset += 4;

            if (strlen($bytes) < 4) { // Nothing left to read
                break;
            }

            $chunkLength = unpack('Nlength', $bytes);
//            echo "Chunk length: {$chunkLength['length']}\n";

            $bytes = fread($fd, 4);
            $offset += 4;
            $chunkType = unpack('a4type', $bytes);
//            echo "Chunk type: {$chunkType['type']}\n";

//            echo "Seeking ahead {$chunkLength['length']} bytes...\n";
            fseek($fd, $chunkLength['length'], SEEK_CUR); // skip over data to CRC
            $offset += $chunkLength['length'];

            $bytes = fread($fd, 4);
            $offset += 4;
            $crc = unpack('Ncrc', $bytes);

            $chunk = [
                'pos' => $startOffset,
                'len' => $chunkLength['length'],
                'type' => $chunkType['type'],
                'xmp' => false,
                'xmpOffset' => 0,
                'xmpData' => null
            ];

            $currPtr = ftell($fd); // store this in case we go off on a mission

            if ($chunk['type'] == 'iTXt') { // Could be XMP possibly
                fseek($fd, $chunk['pos']+8, SEEK_SET); // +8 skip length/type

                $bytes = fread($fd, self::ITXT_HEADER_LENGTH);
                if (strcmp($bytes, self::ITXT_HEADER_DATA_XMP) == 0) {
                    $chunk['xmp'] = true;
                    $chunk['xmpOffset'] = $startOffset + 8 + self::ITXT_HEADER_LENGTH;
                    $chunk['xmpLength'] = $chunkLength['length'] - self::ITXT_HEADER_LENGTH;

                    $chunk['xmpData'] = fread($fd, $chunk['xmpLength']);
                }
            }

            fseek($fd, $currPtr);
            $this->chunks[] = $chunk;
        }

    }

    public function getXMPString()
    {
        foreach ($this->chunks as $chunk) {
            if ($chunk['xmp'] == true) {
                return $chunk['xmpData'];
            }
        }

        return null;
    }

}