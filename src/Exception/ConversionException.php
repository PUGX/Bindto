<?php

namespace Bindto\Exception;

class ConversionException extends \Exception implements ExceptionInterface
{

    const DOMAIN = 1;
    const NOT_FOUND = 2;
    const SYSTEM = 3;

    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $propertyPath
     * @param mixed $value
     * @param string $message
     * @param int $code
     * @param mixed $previous
     */
    public function __construct($propertyPath, $value, $message = null, $code = 0, $previous = null)
    {
        parent::__construct(sprintf('The conversion of "%s" failed: %s', $propertyPath, $message ?: 'Unknown reason'), $code, $previous);

        $this->propertyPath = $propertyPath;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Overrides the property path.
     *
     * @param string $path
     * @internal
     */
    public function setPropertyPath(string $path)
    {
        $this->propertyPath = $path;
    }

    /**
     * Creates a new exception where domain logic caused a failure.
     *
     * @param string $propertyPath
     * @param mixed $value
     * @param string $message
     * @param mixed $previous
     * @return static
     */
    public static function fromDomain($propertyPath, $value, $message = null, $previous = null)
    {
        return new static($propertyPath, $value, $message, static::DOMAIN, $previous);
    }

    /**
     * Creates a new exception where a lookup was not found.
     *
     * @param string $propertyPath
     * @param mixed $value
     * @param mixed $previous
     * @return static
     */
    public static function fromNotFound($propertyPath, $value, $previous = null)
    {
        return new static($propertyPath, $value, 'Not found', static::NOT_FOUND, $previous);
    }

    /**
     * Creates a new exception caused by the underlying system.
     *
     * @param string $propertyPath
     * @param mixed $value
     * @param string $message
     * @param mixed $previous
     * @return static
     */
    public static function fromSystem($propertyPath, $value, $message = null, $previous = null)
    {
        return new static($propertyPath, $value, $message, static::SYSTEM, $previous);
    }
}
