<?php
namespace Bindto\Converter;

use Bindto\Exception\ConversionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArrayConverter extends AbstractPrimitiveConverter
{

    /**
     * {@inheritdoc}
     */
    public function onApply($value, $propertyName, array $options, $from)
    {
        return (array) $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function needsConverting($value)
    {
        return !is_array($value);
    }
}
