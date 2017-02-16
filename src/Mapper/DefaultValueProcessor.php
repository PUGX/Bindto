<?php

declare(strict_types=1);

namespace Bindto\Mapper;

use Bindto\Annotation\DefaultValues;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Bindto\Annotation\DefaultValue;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Mapper that processes @DefaultValue annotations for a property.
 */
class DefaultValueProcessor
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var PropertyAccess
     */
    private $propertyAccessor;

    /**
     * Track which defaults have been applied to objects.
     *
     * @var bool[]
     */
    private $processedMap = [];

    /**
     * @param Reader annotationReader
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(Reader $annotationReader, ExpressionLanguage $expressionLanguage)
    {
        $this->annotationReader = $annotationReader;
        $this->expressionLanguage = $expressionLanguage;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param \ReflectionProperty $property
     * @param                     $obj
     */
    public function process(\ReflectionProperty $property, $obj)
    {
        array_map(
            function ($annotation) use ($property, $obj) {
                $valueAnnotations = [];

                if ($annotation instanceof DefaultValues) {
                    $valueAnnotations = $annotation->defaults;
                } else if ($annotation instanceof DefaultValue) {
                    $valueAnnotations[] = $annotation;
                }

                foreach ($valueAnnotations as $valueAnnotation) {
                    if (false === $this->hasProcessed($valueAnnotation, $obj, $property)) {
                        $this->processProperty($valueAnnotation, $property, $obj);
                    }
                }
            }, $this->annotationReader->getPropertyAnnotations($property)
        );
    }

    private function processProperty(DefaultValue $annotation, \ReflectionProperty $property, $obj)
    {
        $propertyName = $property->getName();
        $value = $this->getPropertyValue($obj, $propertyName, $annotation->propertyPath);

        // if there's already a value do nothing
        if (null === $value) {
            if (null !== $annotation->expr) {
                $newValue = $this->evaluateExpression($annotation->expr, $obj);
            } elseif (null !== $annotation->const) {
                $newValue = $this->evaluateConstant($annotation->const);
            } else {
                throw new \LogicException('No default value mechanism provided');
            }

            $this->setPropertyValue($obj, $propertyName, $annotation->propertyPath, $newValue);
            $this->setProcessed($annotation, $obj, $property);
        }
    }

    private function hasProcessed(DefaultValue $annotation, $obj, \ReflectionProperty $property): bool
    {
        return in_array($this->createCacheKey($annotation, $obj, $property), $this->processedMap, true);
    }

    private function setProcessed(DefaultValue $annotation, $obj, \ReflectionProperty $property)
    {
        $this->processedMap[] = $this->createCacheKey($annotation, $obj, $property);
    }

    private function createCacheKey(DefaultValue $annotation, $obj, \ReflectionProperty $property): string
    {
        return implode('', [
            spl_object_hash($annotation),
            spl_object_hash($obj),
            spl_object_hash($property),
        ]);
    }

    private function getPropertyValue($obj, string $rootProperty, string $childPropertyPath = null)
    {
        $propertyPath = $rootProperty;

        if (null !== $childPropertyPath) {
            $propertyPath = sprintf('%s[%s]', $propertyPath, $childPropertyPath);
        }

        try {
            return $this->propertyAccessor->getValue($obj, $propertyPath);
        } catch (UnexpectedTypeException $ex) {
            // this can happen when one or more parts of the property path is not an object or array
            return null;
        } catch (NoSuchIndexException $ex) {
            // this can happen when one or more parts of the property path is an object without public properties
            return null;
        }
    }

    private function setPropertyValue($obj, string $rootProperty, string $childPropertyPath = null, $value)
    {
        $propertyPath = $rootProperty;

        if (null !== $childPropertyPath) {
            $propertyPath = sprintf('%s[%s]', $propertyPath, $childPropertyPath);
        }

        // the root property itself can be null
        if (null === $this->getPropertyValue($obj, $rootProperty)) {
            $this->propertyAccessor->setValue($obj, $rootProperty, []);
        }

        try {
            $this->propertyAccessor->setValue($obj, $propertyPath, $value);
        } catch (NoSuchIndexException $ex) {
            // this can happen when one or more parts of the property path is an object without public properties
            return null;
        }
    }

    private function evaluateExpression(string $expr, $obj)
    {
        $exprValue = $this->expressionLanguage->evaluate($expr, [
            'this' => $obj,
        ]);

        return $exprValue;
    }

    private function evaluateConstant(string $constant)
    {
        return constant($constant);
    }
}
