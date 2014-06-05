<?php
namespace Writer;

class Subtitle
{
    var $caps;

    function __construct($caps = array())
    {
        $this->setCaps($caps);
    }

    function setCaps($caps)
    {
        if (!$caps)
            return;

        $this->caps = $caps;
    }

    function write($filename)
    {
        file_put_contents($filename, $this->render());
    }

    abstract function render();
}
