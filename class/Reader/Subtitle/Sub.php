<?php
namespace cd;

class Sub extends \Reader\Subtitle
{
    function isRecognized($data)
    {
        // TODO use regexp for   {number}{number}text string\r\n
        if (substr($data, 0, 1) == "{")
            return true;

        return false;
    }
}
