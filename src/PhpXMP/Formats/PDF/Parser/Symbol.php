<?php
namespace PhpXMP\Format\PDF\Parser;


class Symbol
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __equals(Symbol $symbol)
    {
        return $this->getName() == $symbol->getName();
    }

    public function __toString()
    {
        return "Symbol [" . $this->name . "]";
    }

    public function getName()
    {
        return $this->name;
    }

    public static function create($name)
    {
        $symbol = new self($name);
        return $symbol;
    }
}