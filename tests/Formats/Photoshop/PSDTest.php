<?php
namespace Tests\Formats\Photoshop;

use PhpXMP\Format\Photoshop\PSD;

class PSDTest extends \PHPUnit_Framework_TestCase
{
    public function testPsd()
    {
        $png = new PSD(realpath(__DIR__ . '/../../../test_files/BlueSquare/BlueSquare.psd'));
        $xmp = $png->getXMPString();

        $this->assertNotEmpty($xmp);
    }
}
