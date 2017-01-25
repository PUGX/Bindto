<?php

namespace Bindto\Mapper;

use Doctrine\Common\Annotations\Reader;
use Liuggio\Filler\PropertyTrait;
use Bindto\Annotation\Cast;
use Bindto\MapperInterface;

/**
 * Mapper that reads @Cast annotations and attempts to cast the input value.
 *
 * To function this requires another mapper to do the initial binding then this operates on the result.
 */
class CastingObjectMapper implements MapperInterface
{

    /**
     * @var MapperInterface
     */
    private $propertyMapper;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @param MapperInterface $sourceMapper
     * @param Reader $annotationReader
     */
    public function __construct(MapperInterface $propertyMapper, Reader $annotationReader)
    {
        $this->propertyMapper = $propertyMapper;
        $this->annotationReader = $annotationReader;
    }

    public function map($from, $to)
    {
        $this->propertyMapper->map($from, $to);

        if (is_object($to)) {
            $reflector = new \ReflectionClass($to);

            foreach ($reflector->getProperties() as $property) {
                $annotation = $this->annotationReader->getPropertyAnnotation($property, Cast::class);

                if ($annotation !== null) {
                    $this->cast($to, $property->getName(), $annotation->to);
                }
            }
        }

        return $to;
    }

    protected function cast($obj, $propertyName, $type)
    {
        $value = $obj->{$propertyName};

        if ($type === 'int') {
            $type = 'integer';
        } else if ($type === 'bool') {
            $type = 'boolean';
        }

        // don't try converting if the value is already of the target type
        if (gettype($value) === $type) {
            return;
        }

        // if value is an object and we're not being asked to cast to an array then leave it as an object. this is
        // desirable in most situations as it exposes bugs in your validation workflow.
        if ((true === is_object($obj)) && ('array' !== $type)) {
            return $obj;
        }

        switch ($type) {
            case 'integer':
                $value = is_numeric($value) ? (int) $value : null;
                break;

            case 'float':
                $tmp = floatval($value);
                $tmp2 = (float) $value;
                $value = $tmp == $tmp2 ? $tmp2 : null;
                break;

            case 'double':
                $tmp = doubleval($value);
                $tmp2 = (double) $value;
                $value = $tmp == $tmp2 ? $tmp2 : null;
                break;

            case 'boolean':
                $tmp = boolval($value);
                $tmp2 = (bool) $value;
                $value = $tmp == $tmp2 ? $tmp2 : null;
                break;

            case 'string':
                $value = (string) $value;
                break;

            case 'array':
                $value = (array) $value;
                break;
        }

        $obj->{$propertyName} = $value;
    }
}
