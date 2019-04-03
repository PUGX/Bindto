<?php
namespace Bindto\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class Convert /* FIXME: extends Valid */
{

    /**
     * Name of the converter to apply.
     *
     * @var string
     * @Required()
     */
    public $converter;

    /**
     * Is this a list of things that need converting?
     *
     * @var bool
     */
    public $isArray = false;

    /**
     * Options to pass to the converter.
     *
     * @var array
     */
    public $options = [];
}
