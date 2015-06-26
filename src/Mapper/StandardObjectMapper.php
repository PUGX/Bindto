<?php

namespace Bindto\Mapper;

use Liuggio\Filler\PropertyTrait;
use Bindto\MapperInterface;

class StandardObjectMapper implements MapperInterface
{
    use PropertyTrait;

    public function map($from, $to)
    {
        $copyOfTo = $to;
        $this->fillProperties($from, $copyOfTo);

        return $copyOfTo;
    }
}
