<?php
namespace Bindto\Converter;

use Bindto\Exception\ConversionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeConverter extends AbstractConverter
{

    /**
     * {@inheritdoc}
     */
    public function apply($value, $propertyName, array $options, $from)
    {
        $options = $this->resolveOptions($options);
        $date = null;

        if ($options['format']) {
            $date = \DateTime::createFromFormat($options['format'], $value);

            if ($date === false) {
                throw ConversionException::fromDomain($propertyName, $value, 'Invalid format');
            }
        } else {
            try {
                $date = new \DateTime($value);
            } catch (\Exception $ex) {
                throw ConversionException::fromDomain($propertyName, $value, $ex->getMessage());
            }
        }

        return $date;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'format' => null,
        ]);
        $resolver->addAllowedTypes('format', ['null', 'string']);
    }
}
