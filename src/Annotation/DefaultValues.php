<?php
namespace Bindto\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class DefaultValues
{

    /**
     * @var \Bindto\Annotation\DefaultValue[]
     */
    public $defaults;
}
