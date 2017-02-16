<?php
namespace Bindto\Mapper;

use Bindto\MapperInterface;
use Bindto\Annotation\DefaultValue;

/**
 * Mapper that reads @DefaultValue annotations and attempts to set a default value.
 */
class DefaultValueMapper implements MapperInterface
{
    /**
     * @var MapperInterface
     */
    private $propertyMapper;

    /**
     * @var DefaultValueProcessor
     */
    private $defaultValueProcessor;

    /**
     * @param MapperInterface       $propertyMapper
     * @param DefaultValueProcessor $defaultValueProcessor
     */
    public function __construct(MapperInterface $propertyMapper, DefaultValueProcessor $defaultValueProcessor)
    {
        $this->propertyMapper = $propertyMapper;
        $this->defaultValueProcessor = $defaultValueProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function map($from, $to)
    {
        if (is_object($to)) {
            $reflector = new \ReflectionClass($to);

            array_map(function (\ReflectionProperty $property) use ($from, $to) {
                $this->defaultValueProcessor->process($property, $to);
            }, $reflector->getProperties());
        }

        return $to;
    }
}
