<?php
namespace Bindto\Converter;

use Bindto\Exception\ConversionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StringConverter extends AbstractPrimitiveConverter
{

    /**
     * {@inheritdoc}
     */
    public function onApply($value, $propertyName, array $options, $from)
    {
        return null !== $value ? (string) $value : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function needsConverting($value)
    {
        return !is_string($value);
    }

    /**
     * {@inheritdoc}
     */
    protected function createInvalidTypeException(string $propertyName, $value)
    {
        return ConversionException::fromDomain($propertyName, $value, 'Not a valid string', 'conversion_exception.primitive.string.not_a_valid_type');
    }
}
