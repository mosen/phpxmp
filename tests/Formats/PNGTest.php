<?php
namespace Tests\Formats;


use PhpXMP\Format\PNG;

class PNGTest extends \PHPUnit_Framework_TestCase
{
    public function testPng()
    {
        $png = new PNG(realpath(__DIR__ . '/../../test_files/BlueSquare/BlueSquare.png'));
        $xmp = $png->getXMPString();

        $this->assertNotEmpty($xmp);
    }
}
