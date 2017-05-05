<?php
namespace Bindto\Converter;

use Bindto\Converter\AbstractConverter;
use Bindto\Exception\ConversionException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UuidConverter extends AbstractConverter
{

    /**
     * {@inheritdoc}
     */
    public function apply($value, $propertyPath, array $options, $from)
    {
        if (true === is_object($value)) {
            return $value;
        }

        try {
            return Uuid::fromString($value);
        } catch (\Throwable $ex) {
            throw ConversionException::fromDomain($propertyPath, $value, $ex->getMessage(), 'conversion_exception.invalid_argument_exception',$ex);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'version' => 4,
        ]);
        $resolver->addAllowedTypes('format', ['int']);
    }
}
