<?php
namespace PhpXMP\Format\Photoshop;


class LayerInfo
{
    public $length;
    public $count;
    public $records = [];
    public $imageData = [];

    public static function unpack($buf)
    {

    }
}