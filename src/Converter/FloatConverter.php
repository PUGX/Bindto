<?php
namespace Bindto\Converter;

use Bindto\Exception\ConversionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FloatConverter extends AbstractPrimitiveConverter
{

    /**
     * {@inheritdoc}
     */
    public function onApply($value, $propertyName, array $options, $from)
    {
        $tmp = (float) $value;

        return $value == (string) $tmp ? $tmp : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function needsConverting($value)
    {
        return !is_float($value);
    }
}
