<?php
namespace PhpXMP\Format\Photoshop;


class ImageResources
{
    const IMAGE_RESOURCE_MIN_SIZE = 12; // 4 + 2 + 2 + 4

    // A few image resource IDs we need
    const ID_IPTC = 1028;
    const ID_COPYRIGHTFLAG = 1034;
    const ID_COPYRIGHTURL = 1035;
    const ID_EXIF = 1058;
    const ID_XMP = 1060;
    const ID_IPTCDIGEST = 1061;
    const ID_THUMBNAIL = 1036; // for version 5.0+

    /**
     * Unpack Photoshop PSIR section from open file descriptor.
     *
     * @param $fd
     * @param $psirOffset
     * @param $psirLength
     * @return array Array of ImageResource objects
     */
    public static function unpack($fd, $psirOffset, $psirLength)
    {
        $currPtr = ftell($fd);
        if ($currPtr != $psirOffset) {
            fseek($fd, $psirOffset, SEEK_SET);
        }

        $psirEnd = $psirOffset + $psirLength;
        $psirLimit = $psirEnd - self::IMAGE_RESOURCE_MIN_SIZE;
        $offset = $psirOffset;

        //echo "Starting at PSIR offset: {$psirOffset}\n";

        $resources = array();

        while ($offset <= $psirLimit) {
            fseek($fd, $offset, SEEK_SET);
            $bytes = fread($fd, 6);

            $type = substr($bytes, 0, 4);
            $id = unpack('nid', substr($bytes, 4));

            $offset += 6;
            fseek($fd, $offset, SEEK_SET);

            $bytes = fread($fd, 2);
            $nameLen = unpack('nnamelen', $bytes);

            $offset += (($nameLen['namelen'] + 2) & 0xFFFE); // Just a trick to round to an even offset
            fseek($fd, $offset, SEEK_SET);

            $bytes = fread($fd, 4);
            $dataLen = unpack('Ndatalen', $bytes);
            $offset += 4;
            fseek($fd, $offset, SEEK_SET); // go to start of the data (just after length int)

            $myPsirOffset = $offset - $psirOffset; // Figure out the start of this whole PSIR section

//            if ($type == '8BIM') {
//                echo "yes got 8BIM resource\n";
                $resource = new ImageResource();
                $resource->id = $id['id'];
                $resource->type = $type;
                $resource->psirOffset = $myPsirOffset;
                $resource->length = $dataLen['datalen'];

            // XMP Toolkit basically says dont do this, because there can be multiple instances of a single ID
                $resources[$resource->id] = $resource;
//            }

            $offset += (($dataLen['datalen'] + 1) & 0xFFFFFFFE); // Just a trick to round to an even offset per the spec.
        }

        return $resources;
    }
}