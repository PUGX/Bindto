<?php
namespace Bindto\Converter;

use Bindto\Exception\ConversionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegerConverter extends AbstractPrimitiveConverter
{

    /**
     * {@inheritdoc}
     */
    public function onApply($value, $propertyName, array $options, $from)
    {
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function needsConverting($value)
    {
        return !is_int($value);
    }
}
