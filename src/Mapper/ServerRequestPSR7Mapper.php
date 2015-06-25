<?php
namespace OB\Mapper;

use OB\MapperInterface;
use OB\PSR7RequestTrait;

class ServerRequestPSR7Mapper implements MapperInterface
{
    use PSR7RequestTrait;

    function map($from, $to)
    {
        $copyOfTo = $to;
        $this->fillPropertiesFromPSR7Request($from, $copyOfTo);

        return $copyOfTo;
    }
}