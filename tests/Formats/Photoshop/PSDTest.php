<?php
namespace Tests\Formats\Photoshop;

use PhpXMP\Format\Photoshop\PSD;

class PSDTest extends \PHPUnit_Framework_TestCase
{
    public function testPsd()
    {
        $psd = new PSD(realpath(__DIR__ . '/../../../test_files/BlueSquare/BlueSquare.psd'));
        $xmp = $psd->getXMPString();

        $this->assertNotEmpty($xmp);
    }
}
