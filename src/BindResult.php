<?php

namespace Bindto;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class BindResult
{
    /** @var @mixed */
    private $data;
    /** @var ConstraintViolationListInterface */
    private $violations;
    /** @var @mixed */
    private $aliases;

    public function __construct($data, $violations = [], $metadata = [])
    {
        $this->data = $data;
        $this->violations = $violations;
        $this->metadata = $metadata;
    }

    public function isValid()
    {
        return count($this->violations) < 1;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getViolations()
    {
        return $this->violations;
    }
    public function getMetadata()
    {
        return $this->metadata;
    }
}
