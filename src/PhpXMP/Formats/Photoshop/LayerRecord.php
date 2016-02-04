<?php
namespace PhpXMP\Format\Photoshop;

/**
 * Photoshop Layer Record
 *
 * @see http://www.adobe.com/devnet-apps/photoshop/fileformatashtml/#50577409_13084
 * @package PhpXMP\Format\Photoshop
 */
class LayerRecord
{
    public static function unpack($buf)
    {
        $struct = unpack('Ntop/Nleft/Nbottom/Nright/nchannels', $buf);

    }
}