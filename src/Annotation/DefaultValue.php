<?php
namespace Bindto\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class DefaultValue
{

    /**
     * Expression to apply.
     *
     * See http://symfony.com/doc/current/components/expression_language.html
     *
     * @var string
     */
    public $expr;

    /**
     * Constant to use use, e.g. My\Class::SOME_CONSTANT
     *
     * @var string
     */
    public $const;

    /**
     * A path can be provided for setting child properties.
     *
     * @var string
     */
    public $propertyPath;
}
