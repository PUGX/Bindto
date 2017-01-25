<?php
namespace Bindto;

use Bindto\Exception\ConversionException;

interface ConverterInterface
{

    /**
     * Convert the value.
     *
     * @param mixed $value
     * @param string $propertyName
     * @param array $options
     * @param mixed $from
     * @return mixed
     * @throws ConversionException When the conversion fails
     */
    public function apply($value, $propertyName, array $options, $from);
}
