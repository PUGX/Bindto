<?php

namespace Bindto\Mapper;

use Liuggio\Filler\PropertyTrait;
use Bindto\MapperInterface;

class StandardObjectMapper implements MapperInterface
{
    use PropertyTrait;

    public function map($from, $to)
    {
        $this->fillProperties($from, $to);

        return $to;
    }
}
