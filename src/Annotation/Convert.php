<?php
namespace Bindto\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *   @Attribute("converter", type="string", required=true),
 *   @Attribute("isArray", type="bool"),
 *   @Attribute("options", type="array", required=true),
 * })
 */
class Convert
{

    /**
     * Name of the converter to apply.
     *
     * @var string
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
