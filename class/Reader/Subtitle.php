<?php
namespace Reader;

abstract class Subtitle
{
    var $caps = array();

    function __construct($data = '')
    {
        $this->parse($data);
    }

    abstract function parse($data);
}