<?php
namespace Bindto\Converter;

use Bindto\Binder;
use Bindto\Converter\AbstractConverter;
use Bindto\Mapper\ConvertingObjectMapper;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NestedObjectConverter extends AbstractConverter
{

    /**
     * @var Binder
     */
    private $binder;

    /**
     * @var ConvertingObjectMapper
     */
    private $parentMapper;

    /**
     * @param Binder $binder
     * @param ConvertingObjectMapper $parentMapper
     */
    public function __construct(Binder $binder, ConvertingObjectMapper $parentMapper)
    {
        $this->binder = $binder;
        $this->parentMapper = $parentMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($value, $propertyName, array $options, $from)
    {
        $options = $this->resolveOptions($options);
        $prefix = $options['prefix'];

        if (is_object($from) === true) {
            $source = (array)$from;
        } else if (is_array($from) === true) {
            $source = $from;
        } else {
            throw new \InvalidArgumentException('$from must be a simple object or array');
        }

        if (true === array_key_exists($prefix, $source)) {
            $nestedSource = $source[$prefix];
        } else {
            $nestedSource = [];
        }

        $this->parentMapper->enterNestedExceptionStack($propertyName);
        {
            $result = $this->parentMapper->map($nestedSource, new $options['class']);
        }
        $this->parentMapper->exitNestedExceptionStack();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => null,
            'prefix' => null,
        ]);
        $resolver->addAllowedTypes('class', ['string']);
        $resolver->addAllowedTypes('prefix', ['string']);
    }
}
