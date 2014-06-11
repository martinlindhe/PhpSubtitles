<?php
namespace Writer;

class Subtitle
{
    var $caps;

    public function __construct($caps = array())
    {
        $this->setCaps($caps);
    }

    public function setCaps($caps)
    {
        if (!$caps)
            return;

        $this->caps = $caps;
    }

    public function write($filename)
    {
        file_put_contents($filename, $this->render());
    }
    
    public function factory($format)
    {
        if ($format == 'srt') {
            return new \Writer\Subtitle\Srt();
        }
        if ($format == 'ass') {
            return new \Writer\Subtitle\Ass();
        }
        
        throw new \Exception('unknown '.$format);
    }

    abstract function render();
}
