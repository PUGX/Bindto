<?php

namespace OB\Exception;

class MapperNotFoundException extends \Exception implements ExceptionInterface
{
    public function __construct($object)
    {
        if (is_object($object)) {
            $type = get_class($object);
        } else {
            $type = gettype($object);
        }

        parent::__construct(sprintf('Strategy for %s doesn\'t exists, try using a fallback.', $type));
    }
}