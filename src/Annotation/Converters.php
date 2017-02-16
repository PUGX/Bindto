<?php
namespace Bindto\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Converters
{

    /**
     * @var \Bindto\Annotation\Convert[]
     */
    public $converters;
}
