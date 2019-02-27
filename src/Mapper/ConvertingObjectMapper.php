<?php

namespace Bindto\Mapper;

use Bindto\Annotation\Converters;
use Bindto\Converter\NestedObjectConverter;
use Bindto\ConverterInterface;
use Bindto\Exception\ConversionException;
use Doctrine\Common\Annotations\Reader;
use Bindto\Annotation\Convert;
use Bindto\MapperInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Mapper that reads @Convert annotations and attempts to convert the value.
 *
 * To function this requires another mapper to do the initial binding then this operates on the result.
 */
class ConvertingObjectMapper implements MapperInterface
{

    const STACK_TEMPLATE = [
        'children' => [],
        'exceptions' => [],
        'parent' => null,
    ];

    /**
     * @var MapperInterface
     */
    private $propertyMapper;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var PropertyAccess
     */
    private $propertyAccessor;

    /**
     * @var DefaultValueProcessor
     */
    private $defaultValueProcessor;

    /**
     * @var array
     */
    private $converters = [];

    /**
     * @var array
     */
    private $exceptionStack = null;

    /**
     * @var array Reference to a position in $exceptionStack
     */
    private $currentExceptionStackPointer = null;

    /**
     * @var bool
     */
    private $collectExceptions;

    /**
     * Is the conversion phase enabled?
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * @param MapperInterface $propertyMapper
     * @param Reader $annotationReader
     * @param DefaultValueProcessor $defaultValueProcessor
     * @param bool $collectExceptions
     */
    public function __construct(MapperInterface $propertyMapper, Reader $annotationReader, DefaultValueProcessor $defaultValueProcessor, $collectExceptions = false)
    {
        $this->propertyMapper = $propertyMapper;
        $this->annotationReader = $annotationReader;
        $this->defaultValueProcessor = $defaultValueProcessor;
        $this->collectExceptions = $collectExceptions;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        $this->clearNestedExceptionStack();
    }

    /**
     * Makes a converter available.
     *
     * @param string $name
     * @param string|object $converter
     *
     * @throws \LogicException When the converter isn't an implementation of {@see Bindto\ConverterInterface}
     */
    public function addConverter($name, $converter)
    {
        if (is_subclass_of($converter, ConverterInterface::class) === false) {
            throw new \LogicException('$converter does not implement ConverterInterface');
        }

        $this->converters[$name] = $converter;
    }

    /**
     * Disables the conversion phase.
     *
     * Nested converters will still run to create the correct object structure.
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Enables the conversion phase. Enabled by default.
     */
    public function enable()
    {
        $this->enabled = true;
    }

    public function map($from, $to)
    {
        $this->propertyMapper->map($from, $to);

        if (is_object($to)) {
            $reflector = new \ReflectionClass($to);

            array_map(function (\ReflectionProperty $property) use ($from, $to) {
                array_map(function ($annotation) use ($property, $from, $to) {
                    $convertAnnotations = [];

                    if ($annotation instanceof Converters) {
                        $convertAnnotations = $annotation->converters;
                    } else {
                        if ($annotation instanceof Convert) {
                            $convertAnnotations[] = $annotation;
                        }
                    }

                    if ((true === $this->enabled) && (count($convertAnnotations) > 0)) {
                        $this->defaultValueProcessor->process($property, $to);
                    }

                    foreach ($convertAnnotations as $convertAnnotation) {
                        $this->processProperty($convertAnnotation, $property, $from, $to);
                    }
                }, $this->annotationReader->getPropertyAnnotations($property));
            }, $reflector->getProperties());
        }

        return $to;
    }

    private function processProperty(Convert $annotation, \ReflectionProperty $property, $source, $obj)
    {
        $propertyName = $property->getName();
        $converter = $annotation->converter;
        $options = $annotation->options;
        $value = $this->getPropertyValue($obj, $propertyName);

        if (true === $annotation->isArray) {
            if (null === $value) {
                return;
            }

            foreach ($value as $key => $item) {
                $filteredItem = $this->filterNestedObjects($item);
                $propertyPath = sprintf('%s[%s]', $property->getName(), $key);
                $convertedItem = null;

                if (null !== $filteredItem) {
                    $convertedItem = $this->convert($filteredItem, $propertyPath, $converter, $options, $obj);
                }

                $this->setPropertyValue($obj, $propertyPath, $convertedItem);
            }
        } else {
            $filteredValue = $this->filterNestedObjects($value);
            $convertedValue = null;

            if (null !== $filteredValue) {
                $convertedValue = $this->convert($filteredValue, $propertyName, $converter, $options, $obj);
            }

            $this->setPropertyValue($obj, $propertyName, $convertedValue);
        }
    }

    /**
     * Pushes a new conversion exception stack on.
     *
     * @param string $propertyName
     */
    public function enterNestedExceptionStack($propertyName)
    {
        $this->currentExceptionStackPointer['children'][$propertyName] = self::STACK_TEMPLATE;
        $this->currentExceptionStackPointer['children'][$propertyName]['parent'] = &$this->currentExceptionStackPointer;
        $this->currentExceptionStackPointer = &$this->currentExceptionStackPointer['children'][$propertyName];
    }

    public function exitNestedExceptionStack()
    {
        if (null !== $this->currentExceptionStackPointer['parent']) {
            $this->currentExceptionStackPointer = &$this->currentExceptionStackPointer['parent'];
        }
    }

    public function clearNestedExceptionStack()
    {
        $this->exceptionStack = static::STACK_TEMPLATE;
        $this->currentExceptionStackPointer = &$this->exceptionStack;
    }

    public function flattenNestedExceptionStack(): array
    {
        return $this->flattenNestedExceptionStackRecursive($this->exceptionStack);
    }

    private function flattenNestedExceptionStackRecursive(array $level, array $propertyPath = [])
    {
        $flattened = [];

        foreach ($level['exceptions'] as $exception) {
            if ($exception instanceof ConversionException) {
                $exception->setPropertyPath(
                    join('.', array_merge($propertyPath, [$exception->getPropertyPath()]))
                );
            }

            $flattened[] = $exception;
        }

        foreach ($level['children'] as $propertyName => $childStack) {
            $flattened = array_merge($flattened,
                $this->flattenNestedExceptionStackRecursive($childStack, array_merge($propertyPath, [$propertyName]))
            );
        }

        return $flattened;
    }

    protected function convert($value, $propertyPath, $converterName, array $converterOptions, $from)
    {
        $converter = $this->getConverterInstance($converterName);
        $isNestedConverter = $converter instanceof NestedObjectConverter;

        // when converters are disabled we only want to run the nested converters so that we create the correct object structure
        if ((false === $this->enabled) && (false === $isNestedConverter)) {
            return $value;
        }

        if ((null === $value) && (false === $isNestedConverter)) {
            return null;
        }

        try {
            return $converter->apply($value, $propertyPath, $converterOptions, $from);
        } catch (ConversionException $ex) {
            if ($this->collectExceptions === true) {
                $this->currentExceptionStackPointer['exceptions'][] = $ex;
            } else {
                throw $ex;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return ConverterInterface
     *
     * @throws \LogicException When the converter does not exist
     */
    protected function getConverterInstance($name)
    {
        if (array_key_exists($name, $this->converters) === false) {
            throw new \LogicException('Unknown converter: ' . $name);
        }

        $converter = $this->converters[$name];

        if (is_object($converter) === false) {
            $converter = new $converter;
        }

        return $converter;
    }

    protected function getPropertyValue($obj, $propertyPath)
    {
        return $this->propertyAccessor->getValue($obj, $propertyPath);
    }

    protected function setPropertyValue($obj, $propertyPath, $value)
    {
        $this->propertyAccessor->setValue($obj, $propertyPath, $value);
    }

    /**
     * Some values cause problems with nested objects, filter those out.
     *
     * Example:
     *
     *      [
     *          key1 => value1,
     *          key2 => null,
     *          nested1 => [
     *              key1 => null
     *          ]
     *      ]
     *
     * Where nested1 is marked as a nested object but IS allowed to be null and where nested1.key1 is NOT allowed to be
     * null. Without filtering nested1.key1 out and setting nested1 to null, we will try to convert this and it will
     * fail validation because nested1 will become an instance of the target class but with key1 being null.
     *
     * @param mixed $item
     * @return mixed
     */
    private function filterNestedObjects($item)
    {
        if ((false === $this->enabled) || (false === is_array($item))) {
            return $item;
        }

        // filter null values to satisfy the case where a nested object is optional
        $itemSize = count($item);
        $filtered = array_filter($item, function($e) {
            return !is_null($e);
        });
        $newSize = count($filtered);

        // if $item is empty because of our filter, set it to null also
        if (($newSize !== $itemSize) && ($newSize === 0)) {
            return null;
        }

        return $filtered;
    }
}
