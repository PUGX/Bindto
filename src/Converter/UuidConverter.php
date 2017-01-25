<?php
namespace Bindto\Converter;

use Bindto\Converter\AbstractConverter;
use Bindto\Exception\ConversionException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UuidConverter extends AbstractConverter
{

    /**
     * {@inheritdoc}
     */
    public function apply($value, $propertyPath, array $options, $from)
    {
        try {
            return Uuid::fromString($value);
        } catch (\InvalidArgumentException $ex) {
            throw ConversionException::fromDomain($propertyPath, $value, $ex->getMessage(), $ex);
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
