<?php
namespace Bindto\Converter;

use Bindto\ConverterInterface;
use Bindto\Exception\ConversionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractConverter implements ConverterInterface
{

    /**
     * Validates and merges a list of options against those that are available for the converter.
     *
     * @param array $options
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $resolved = $resolver->resolve($options);

        return $resolved;
    }

    /**
     * Configures available options.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {

    }
}
