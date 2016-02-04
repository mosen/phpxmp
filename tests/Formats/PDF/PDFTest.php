<?php
namespace Tests\Formats\PDF;

use PhpXMP\Format\PDF\PDF;

class PDFTest extends \PHPUnit_Framework_TestCase
{
    public function testPdf()
    {
        $pdf = new PDF(realpath(__DIR__ . '/../../../test_files/BlueSquare/BlueSquare.pdf'));
        $xmp = $pdf->getXMPString();

        $this->assertNotEmpty($xmp);
    }
}
