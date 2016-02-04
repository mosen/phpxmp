<?php
namespace Tests\Formats\InDesign;


use PhpXMP\Format\InDesign\INDD;

class INDDTest extends \PHPUnit_Framework_TestCase
{
    public function testIndd()
    {
        $indd = new INDD(realpath(__DIR__ . '/../../../test_files/BlueSquare/BlueSquare.indd'));
        $xmp = $indd->getXMPString();

        $this->assertNotEmpty($xmp);
    }
}
