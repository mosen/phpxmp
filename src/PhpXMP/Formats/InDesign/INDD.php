<?php
namespace PhpXMP\Format\InDesign;

/**
 * InDesign File Support.
 *
 * Allows for quick extraction of XMP packets from .indd files.
 *
 * @package PhpXMP\Format\InDesign
 */
class INDD
{
    /**
     * @var string The GUID that indicates that the following object is a Master Page
     */
    const MASTER_PAGE_GUID = "\x06\x06\xED\xF5\xD8\x1D\x46\xE5\xBD\x31\xEF\xE7\xFE\x74\xB7\x1D";

    /**
     * @var int InDesign page size
     */
    const PAGESIZE = 4096; // 4096 bytes per page

    /**
     * @var string The GUID that marks the start of a contiguous object header.
     */
    const OBJECT_HEADER_GUID = "\xDE\x39\x39\x79\x51\x88\x4B\x6C\x8E\x63\xEE\xF8\xAE\xE0\xDD\x38";

    /**
     * @var string The GUID that marks the end of a contiguous object header.
     */
    const OBJECT_TRAILER_GUID = "\xFD\xCE\xDB\x70\xF7\x86\x4B\x4F\xA4\xD3\xC7\x28\xB3\x41\x71\x06";

    // Endianness of page contents
    const LITTLE_ENDIAN = 1;
    const BIG_ENDIAN = 2;

    // XMP examples which determine the length of bytes to read when finding the XMP packet(s)
    const XMP_HEADER_EXAMPLE = "\"<?xpacket begin='xxx' id='W5M0MpCehiHzreSzNTczkc9d'\"";
    const XMP_TRAILER_EXAMPLE = "<?xpacket end=\"w\"?>";
    const XMP_ID_EXAMPLE = "W5M0MpCehiHzreSzNTczkc9d";
    const XMP_BEGIN_EXAMPLE = "<?xpacket begin=";

    /**
     * @var array Array holding the first and second master pages.
     */
    protected $masters = Array();

    /**
     * @var int The number of pages in the file. Does not refer to physical pages, just structure.
     */
    protected $pages;

    /**
     * @var int The endianness of the objects in this file. Either LITTLE_ENDIAN or BIG_ENDIAN
     */
    protected $endian;

    /**
     * Determine whether the specified file is valid for this format.
     *
     * @param string $uri Path to the file location.
     * @return bool Boolean indicating whether the file is valid for this format.
     * @throws \Exception If file could not be read
     */
    public static function validate($uri)
    {
        $fd = fopen($uri, 'rb');
        if (!$fd) {
            throw new \Exception("Could not open .indd file for reading: {$uri}");
        }

        $bytes = fread($fd, self::PAGESIZE * 2);
        fclose($fd);

        if (strlen($bytes) < self::PAGESIZE * 2) return false;

        // Check both master pages
        if (substr($bytes, 0, self::PAGESIZE) !== self::MASTER_PAGE_GUID) return false;
        if (substr($bytes, self::PAGESIZE) !== self::MASTER_PAGE_GUID) return false;

        return true;
    }

    /**
     * Construct an instance of an InDesign (.indd) file handler for extracting XMP metadata.
     *
     * @param string $uri Full path to the file.
     * @throws \Exception
     */
    public function __construct($uri)
    {
        $xpacket = null;
        $fd = fopen($uri, 'rb');
        if (!$fd) {
            throw new \Exception("Could not open .indd file for reading: {$uri}");
        }

        try {
            $bytes = fread($fd, self::PAGESIZE * 2);
            $firstMaster = MasterPage::unpack($bytes);
            $secondMaster = MasterPage::unpack(substr($bytes, self::PAGESIZE));

            if ($secondMaster->SequenceNumber > $firstMaster->SequenceNumber) {
                $this->pages = $secondMaster->FilePages;
                $this->endian = $secondMaster->ObjectStreamEndian;
            } else {
                $this->pages = $firstMaster->FilePages;
                $this->endian = $firstMaster->ObjectStreamEndian;
            }

            // Skip past the data pages indicated by FilePages to get to the contiguous object section.
            $cobjPos = $this->pages * self::PAGESIZE;
            $cobjPos -= (2 * ContiguousObjectMarker::LENGTH);
            $streamLength = 0;

            $xmpOffset = null;
            $xmpLength = null;

            while (!feof($fd)) {
                $cobjPos += $streamLength + (2 * ContiguousObjectMarker::LENGTH);
                fseek($fd, $cobjPos);

                $bytes = fread($fd, ContiguousObjectMarker::LENGTH);
                $marker = ContiguousObjectMarker::unpack($bytes);

                $xmpObjId = $marker->ObjectUID;
                $xmpClassId = $marker->ObjectClassID;
                $streamLength = $marker->StreamLength;
                $xmp_header_length = strlen(self::XMP_HEADER_EXAMPLE);
                $xmp_trailer_length = strlen(self::XMP_TRAILER_EXAMPLE);

                // too small to be xmp
                if ($streamLength < (4 + $xmp_header_length + $xmp_trailer_length)) continue;

                fseek($fd, $cobjPos + ContiguousObjectMarker::LENGTH);
                $bytes = fread($fd, (4 + $xmp_header_length));

                $innerLength = ($this->endian === self::LITTLE_ENDIAN)
                    ? unpack('Vlen', $bytes)
                    : unpack('Nlen', $bytes);

                echo "Inner length {$innerLength['len']}\n";

                if ($innerLength['len'] != ($streamLength - 4)) {
                    echo "Endian wrong!\n";
                }



                $charPtr = 4;
                $packetBegin = "<?xpacket begin=";
                $startLen = strlen($packetBegin);
                $idLen = strlen("W5M0MpCehiHzreSzNTczkc9d");


                if (substr($bytes, $charPtr, $startLen) != $packetBegin) continue;
                $charPtr += $startLen;

                $quoteChar = substr($bytes, $charPtr, 1);
                if ($quoteChar !== '\'' && $quoteChar !== '"') continue;
                $charPtr += 1;

                // TODO: a whole lot of checking which is superfluous for us

                $xmpOffset = $cobjPos + ContiguousObjectMarker::LENGTH + 4; // Skip marker and innerLength
                $xmpLength = $innerLength['len'];

                // TODO: strip trailing content which pads the XMP to 4096 page boundary
                // I dont really care about the whitespace at the moment, since the XML parser discards it.

                break;
            }

            if ($xmpOffset && $xmpLength) {
                fseek($fd, $xmpOffset);
                $xpacket = fread($fd, $xmpLength);

            } else {
                $xpacket = null;
            }

            fclose($fd);

        } catch (\Exception $e) {
            fclose($fd);
        }

        return $xpacket;
    }
}