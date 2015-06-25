<?php

namespace Bindto;


interface MapperInterface {

    /**
     * Map all the properties from $from to $to, returning a new object
     *
     * @param $from
     * @param $to
     *
     * @return mixed
     */
    function map($from, $to);
}