<?php

namespace Bindto;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class BindResult
{
    /** @var @mixed */
    private $data;
    /** @var ConstraintViolationListInterface */
    private $violations;

    public function __construct($data, $violations = [])
    {
        $this->data = $data;
        $this->violations = $violations;
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
}
